<?php
class offlineMessages {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        try {
            // Log-Verzeichnis definieren
            $logDir = '/home/query/logs/offlineMessages';
            
            // Sicherstellen, dass das Log-Verzeichnis existiert
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            // Logging-Funktion
            $logFile = $logDir . '/' . date('Y-m-d') . '.log';
            $logMessage = function($message) use ($logFile, $clientInfo) {
                $timestamp = date('Y-m-d H:i:s');
                $clientId = isset($clientInfo['client_unique_identifier']) ? $clientInfo['client_unique_identifier'] : 'unknown';
                $nickname = isset($clientInfo['client_nickname']) ? $clientInfo['client_nickname'] : 'unknown';
                $logEntry = "[$timestamp] [$clientId] [$nickname] $message\n";
                file_put_contents($logFile, $logEntry, FILE_APPEND);
            };
            
            // Prüfen, ob der Benutzer ignoriert werden soll
            if(isset($cfg['ignoredGroups']) && $ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
                $logMessage("Client in ignored group, skipping offline messages check");
                return;
            }
            
            $logMessage("Checking offline messages for client");
            
            // Prüfen, ob Offline-Nachrichten für den Benutzer vorliegen
            $offlineMessages = $mongoDB->offlineMessages->find([
                'clientUniqueIdentifier' => $clientInfo['client_unique_identifier']
            ])->toArray();
            
            if(count($offlineMessages) > 0) {
                $logMessage("Found " . count($offlineMessages) . " offline messages");
                
                // Dem Benutzer eine kurze Zeit geben, um sich zu verbinden
                sleep(2);
                
                // Nachrichten senden
                foreach($offlineMessages as $message) {
                    if(isset($message['isPokeMessage']) && $message['isPokeMessage']) {
                        $ts->clientPoke($clientInfo['clid'], $message['message']);
                        $logMessage("Sent poke message: " . $message['message']);
                    } else {
                        $ts->sendMessage(1, $clientInfo['clid'], $message['message']);
                        $logMessage("Sent normal message: " . $message['message']);
                    }
                }
                
                // Nachrichten als zugestellt markieren
                $result = $mongoDB->offlineMessages->deleteMany([
                    'clientUniqueIdentifier' => $clientInfo['client_unique_identifier']
                ]);
                $logMessage("Deleted " . $result->getDeletedCount() . " delivered messages from database");
                
                // Log erstellen
                $ezApp->createLog($mongoDB, __CLASS__, $clientInfo['client_unique_identifier'], $clientInfo['client_nickname'], 'Received ' . count($offlineMessages) . ' offline messages');
            } else {
                $logMessage("No offline messages found");
            }
        } catch (Exception $e) {
            // Definiere Log-Verzeichnis wenn noch nicht geschehen
            if (!isset($logDir)) {
                $logDir = '/home/query/logs/offlineMessages';
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0755, true);
                }
            }
            
            // Direktes Logging im Catch-Block
            $logFile = $logDir . '/' . date('Y-m-d') . '.log';
            $timestamp = date('Y-m-d H:i:s');
            $clientId = isset($clientInfo['client_unique_identifier']) ? $clientInfo['client_unique_identifier'] : 'unknown';
            $nickname = isset($clientInfo['client_nickname']) ? $clientInfo['client_nickname'] : 'unknown';
            $errorMsg = "[$timestamp] [$clientId] [$nickname] EXCEPTION: " . $e->getMessage() . "\n";
            $errorMsg .= "[$timestamp] [$clientId] [$nickname] TRACE: " . $e->getTraceAsString() . "\n";
            file_put_contents($logFile, $errorMsg, FILE_APPEND);
        }
    }
}