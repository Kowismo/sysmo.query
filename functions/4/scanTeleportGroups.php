<?php
class scanTeleportGroups {
    public function __construct($ts, $mongoDB, $cfg, $ezApp) {
        try {
            $logDir = '/home/query/logs/scanTeleportGroups';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            $logFile = $logDir . '/' . date('Y-m-d') . '.log';
            $logMessage = function($message) use ($logFile) {
                $timestamp = date('Y-m-d H:i:s');
                $logEntry = "[$timestamp] $message\n";
                file_put_contents($logFile, $logEntry, FILE_APPEND);
            };
            
            $logMessage("=== Scanning for teleport channel groups ===");
            
            // FIX: MongoDB-Verbindung herstellen falls $mongoDB ein Array ist
            if (is_array($mongoDB)) {
                $logMessage("MongoDB is array (config), creating connection...");
                
                try {
                    // MongoDB-Config aus dem Array lesen
                    $mongoSrv = $mongoDB['srv'] ?? 'mongodb://127.0.0.1:12345/';
                    $mongoDbName = $mongoDB['dbName'] ?? 'sysmopro';
                    
                    $logMessage("Connecting to: $mongoSrv, DB: $mongoDbName");
                    
                    // MongoDB-Verbindung herstellen
                    $mongoClient = new MongoDB\Client($mongoSrv);
                    $mongoDatabase = $mongoClient->selectDatabase($mongoDbName);
                    $logMessage("MongoDB connection established to: $mongoDbName");
                } catch (Exception $e) {
                    $logMessage("ERROR: Failed to connect to MongoDB: " . $e->getMessage());
                    return;
                }
            } else {
                $logMessage("MongoDB is object, using existing connection");
                $mongoDatabase = $mongoDB;
            }
            
            // Prüfe ob channelTeleports Collection existiert
            try {
                $collections = $mongoDatabase->listCollections();
                $collectionNames = [];
                foreach ($collections as $collection) {
                    $collectionNames[] = $collection->getName();
                }
                $logMessage("Available collections: " . implode(', ', $collectionNames));
                
                if (!in_array('channelTeleports', $collectionNames)) {
                    $logMessage("Creating channelTeleports collection...");
                    $mongoDatabase->createCollection('channelTeleports');
                }
                
                $count = $mongoDatabase->channelTeleports->countDocuments();
                $logMessage("channelTeleports collection ready with $count documents");
                
            } catch (Exception $e) {
                $logMessage("ERROR: MongoDB collection error: " . $e->getMessage());
                return;
            }
            
            // Teleport Channel Groups aus Config
            $teleportGroups = [32, 33, 34, 35, 36];
            
            // Alle Online-Clients abrufen
            $onlineClients = $ts->getElement('data', $ts->clientList());
            
            if (!$onlineClients) {
                $logMessage("No clients online or clientList failed");
                return;
            }
            
            $clientCount = is_array($onlineClients) ? count($onlineClients) : 0;
            $logMessage("Scanning $clientCount online clients...");
            
            $teleportsFound = 0;
            $teleportsUpdated = 0;
            
            foreach ($onlineClients as $client) {
                try {
                    $clid = $client['clid'] ?? 0;
                    
                    if ($clid == 0) {
                        continue;
                    }
                    
                    // Ignoriere Query-Clients (Bots)
                    if (isset($client['client_type']) && $client['client_type'] == 1) {
                        continue;
                    }
                    
                    // Vollständige Client-Info vom Server holen
                    $fullClientInfo = $ts->getElement('data', $ts->clientInfo($clid));
                    
                    if (!$fullClientInfo) {
                        continue;
                    }
                    
                    $clientUID = $fullClientInfo['client_unique_identifier'] ?? '';
                    $clientNickname = $fullClientInfo['client_nickname'] ?? 'Unknown';
                    $currentChannelId = $fullClientInfo['cid'] ?? 0;
                    $currentChannelGroupId = $fullClientInfo['client_channel_group_id'] ?? 0;
                    
                    if (empty($clientUID)) {
                        continue;
                    }
                    
                    $logMessage("Checking client: $clientNickname - Channel: $currentChannelId, Group: $currentChannelGroupId");
                    
                    // Prüfe ob aktueller Channel Group eine Teleport-Group ist
                    if (in_array($currentChannelGroupId, $teleportGroups)) {
                        $logMessage("Found teleport group $currentChannelGroupId for $clientNickname");
                        $teleportsFound++;
                        
                        // Channel-Info abrufen
                        $channelInfo = $ts->getElement('data', $ts->channelInfo($currentChannelId));
                        
                        if ($channelInfo) {
                            $channelName = $channelInfo['channel_name'] ?? 'Unknown';
                            
                            try {
                                // Lösche alte Einträge für diesen User
                                $deleteResult = $mongoDatabase->channelTeleports->deleteMany([
                                    'clientUniqueIdentifier' => $clientUID
                                ]);
                                
                                // Neuen Eintrag erstellen
                                $insertResult = $mongoDatabase->channelTeleports->insertOne([
                                    'clientUniqueIdentifier' => $clientUID,
                                    'clientNickname' => $clientNickname,
                                    'targetChannelId' => $currentChannelId,
                                    'channelName' => $channelName,
                                    'channelGroupId' => $currentChannelGroupId,
                                    'setAt' => new MongoDB\BSON\UTCDateTime(),
                                    'setBy' => 'auto_scan',
                                    'lastScanned' => new MongoDB\BSON\UTCDateTime()
                                ]);
                                
                                if ($insertResult->getInsertedCount()) {
                                    $teleportsUpdated++;
                                    $logMessage("✓ Updated teleport for $clientNickname -> $channelName");
                                } else {
                                    $logMessage("✗ Failed to insert teleport for $clientNickname");
                                }
                                
                            } catch (Exception $e) {
                                $logMessage("ERROR: MongoDB operation failed: " . $e->getMessage());
                            }
                        }
                        
                    } else {
                        // User hat keine Teleport-Group, entferne alte Einträge
                        try {
                            $deleteResult = $mongoDatabase->channelTeleports->deleteMany([
                                'clientUniqueIdentifier' => $clientUID
                            ]);
                            
                            if ($deleteResult->getDeletedCount() > 0) {
                                $logMessage("✓ Removed old teleport for $clientNickname (no teleport groups)");
                            }
                        } catch (Exception $e) {
                            // Ignore delete errors for non-existing entries
                        }
                    }
                    
                } catch (Exception $e) {
                    $logMessage("Error processing client: " . $e->getMessage());
                    continue;
                }
            }
            
            $logMessage("=== Scan complete: Found $teleportsFound teleports, updated $teleportsUpdated in database ===");
            
        } catch (Exception $e) {
            if (!isset($logDir)) {
                $logDir = '/home/query/logs/scanTeleportGroups';
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0755, true);
                }
            }
            
            $logFile = $logDir . '/' . date('Y-m-d') . '_error.log';
            $timestamp = date('Y-m-d H:i:s');
            $errorMsg = "[$timestamp] EXCEPTION: " . $e->getMessage() . "\n";
            $errorMsg .= "[$timestamp] TRACE: " . $e->getTraceAsString() . "\n";
            file_put_contents($logFile, $errorMsg, FILE_APPEND);
        }
    }
}