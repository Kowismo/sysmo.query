<?php
class resetMonthlyYear {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $currentDay = date('j');
        $currentHour = date('G');
        
        // Log-Verzeichnis
        $logDir = '/home/query/logs/resetMonthlyYear';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/' . date('Y-m-d') . '.log';
        
        // Prüfe, ob es der erste Tag des Monats ist und zwischen 0-1 Uhr
        if($currentDay == 1 && $currentHour < 1) {
            // Zurücksetzen der monatlichen Aktivität
            $lastResetMonth = $mongoDB->botData->findOne(['type' => 'lastMonthReset']);
            $currentMonth = date('Y-m');
            $currentMonthName = date('F Y'); // z.B. "July 2025"
            $lastMonth = date('Y-m', strtotime('last month'));
            $lastMonthName = date('F Y', strtotime('last month')); // z.B. "June 2025"
            
            if(!$lastResetMonth || $lastResetMonth['month'] != $currentMonth) {
                // Finde den monatlichen Sieger vom letzten Monat
                $topMonthlyUsers = $mongoDB->serverClients->find(
                    [], 
                    ['sort' => ['monthlyActivity' => -1], 'limit' => 20]
                )->toArray();
                
                if (!empty($topMonthlyUsers)) {
                    // Archiviere die Top-Benutzer
                    $mongoDB->botData->insertOne([
                        'type' => 'monthlyTopArchive',
                        'month' => $lastMonth,
                        'monthName' => $lastMonthName,
                        'data' => $topMonthlyUsers,
                        'timestamp' => time()
                    ]);
                    
                    $logMessage = date('Y-m-d H:i:s') . " - Archived monthly top users for $lastMonthName\n";
                    file_put_contents($logFile, $logMessage, FILE_APPEND);
                    
                    // WICHTIG: ERST Template umbenennen, DANN Gewinner-Gruppe erstellen
                    if(isset($cfg['writeTops']['winnerGroups']['monthlyWinnerTemplate'])) {
                        // 1. Template-Gruppe zum neuen Monat umbenennen
                        $this->renameTemplateGroup($ts, $cfg, 'monthly', $currentMonthName, $logFile);
                        
                        // 2. DANN Gewinner-Gruppe für den letzten Monat erstellen
                        $this->createWinnerGroup($ts, $mongoDB, $cfg, $topMonthlyUsers[0], 'month', $lastMonthName, $logFile);
                    }
                }
                
                // Setze monthlyActivity für alle Benutzer zurück
                $mongoDB->serverClients->updateMany(
                    [], 
                    ['$set' => ['monthlyActivity' => 0]]
                );
                
                // Aktualisiere den letzten Reset-Zeitstempel
                if($lastResetMonth) {
                    $mongoDB->botData->updateOne(
                        ['type' => 'lastMonthReset'],
                        ['$set' => ['month' => $currentMonth, 'timestamp' => time()]]
                    );
                } else {
                    $mongoDB->botData->insertOne([
                        'type' => 'lastMonthReset',
                        'month' => $currentMonth,
                        'timestamp' => time()
                    ]);
                }
                
                $logMessage = date('Y-m-d H:i:s') . " - Monthly activity reset performed\n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);
            }
        }     
        
        // Prüfe, ob es der erste Tag des Jahres ist und zwischen 0-1 Uhr
        if($currentDay == 1 && date('n') == 1 && $currentHour < 1) {
            // Zurücksetzen der jährlichen Aktivität
            $lastResetYear = $mongoDB->botData->findOne(['type' => 'lastYearReset']);
            $currentYear = date('Y');
            $lastYear = date('Y', strtotime('last year'));
            
            if(!$lastResetYear || $lastResetYear['year'] != $currentYear) {
                // Finde den jährlichen Sieger
                $topYearlyUsers = $mongoDB->serverClients->find(
                    [], 
                    ['sort' => ['yearlyActivity' => -1], 'limit' => 20]
                )->toArray();
                
                if (!empty($topYearlyUsers)) {
                    // Archiviere die Top-Benutzer
                    $mongoDB->botData->insertOne([
                        'type' => 'yearlyTopArchive',
                        'year' => $lastYear,
                        'data' => $topYearlyUsers,
                        'timestamp' => time()
                    ]);
                    
                    $logMessage = date('Y-m-d H:i:s') . " - Archived yearly top users for $lastYear\n";
                    file_put_contents($logFile, $logMessage, FILE_APPEND);
                    
                    // WICHTIG: ERST Template umbenennen, DANN Gewinner-Gruppe erstellen
                    if(isset($cfg['writeTops']['winnerGroups']['yearlyWinnerTemplate'])) {
                        // 1. Template-Gruppe zum neuen Jahr umbenennen
                        $this->renameTemplateGroup($ts, $cfg, 'yearly', $currentYear, $logFile);
                        
                        // 2. DANN Gewinner-Gruppe für das letzte Jahr erstellen
                        $this->createWinnerGroup($ts, $mongoDB, $cfg, $topYearlyUsers[0], 'year', $lastYear, $logFile);
                    }
                }
                
                // Setze yearlyActivity für alle Benutzer zurück
                $mongoDB->serverClients->updateMany(
                    [], 
                    ['$set' => ['yearlyActivity' => 0]]
                );
                
                // Aktualisiere den letzten Reset-Zeitstempel
                if($lastResetYear) {
                    $mongoDB->botData->updateOne(
                        ['type' => 'lastYearReset'],
                        ['$set' => ['year' => $currentYear, 'timestamp' => time()]]
                    );
                } else {
                    $mongoDB->botData->insertOne([
                        'type' => 'lastYearReset',
                        'year' => $currentYear,
                        'timestamp' => time()
                    ]);
                }
                
                $logMessage = date('Y-m-d H:i:s') . " - Yearly activity reset performed\n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);
            }
        }
    }
    
    private function createWinnerGroup($ts, $mongoDB, $cfg, $winner, $period, $periodName, $logFile) {
        $winnerId = $winner['clientDatabaseId'];
        $winnerNickname = $winner['clientNickname'];
        
        // Bestimme die Template-Gruppe
        $templateKey = $period . 'lyWinnerTemplate';
        $baseGroupId = $cfg['writeTops']['winnerGroups'][$templateKey];
        $newGroupName = "Most Active - " . $periodName;
        
        $logMessage = date('Y-m-d H:i:s') . " - Creating winner group '$newGroupName' for $winnerId ($winnerNickname)\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Kopiere die Template-Gruppe für den Gewinner
        $createResult = $ts->serverGroupCopy($baseGroupId, 0, $newGroupName, 1);
        if($ts->succeeded($createResult)) {
            $data = $ts->getElement('data', $createResult);
            $newGroupId = isset($data['sgid']) ? $data['sgid'] : $data;
            
            $logMessage = date('Y-m-d H:i:s') . " - Successfully created group '$newGroupName' with ID $newGroupId\n";
            file_put_contents($logFile, $logMessage, FILE_APPEND);
            
            // Versuche den Gewinner zur neuen Gruppe hinzuzufügen
            $addResult = $ts->serverGroupAddClient($newGroupId, $winnerId);
            if($ts->succeeded($addResult)) {
                $logMessage = date('Y-m-d H:i:s') . " - Successfully added user $winnerId ($winnerNickname) to group $newGroupId\n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);
                
                // Speichere die Information über die erstellte Gruppe
                $mongoDB->botData->insertOne([
                    'type' => 'winnerGroup',
                    'period' => $period,
                    'periodName' => $periodName,
                    'groupId' => (int)$newGroupId,
                    'groupName' => $newGroupName,
                    'winnerId' => (int)$winnerId,
                    'winnerNickname' => $winnerNickname,
                    'timestamp' => time(),
                    'assigned' => true
                ]);
            } else {
                $errorMsg = $ts->getElement('msg', $addResult);
                $logMessage = date('Y-m-d H:i:s') . " - Failed to add user $winnerId ($winnerNickname) to group $newGroupId. User probably offline. Error: $errorMsg\n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);
                
                // Speichere Pending Assignment für offline User
                $mongoDB->botData->insertOne([
                    'type' => 'pendingGroupAssignment',
                    'clientDatabaseId' => (int)$winnerId,
                    'clientNickname' => $winnerNickname,
                    'groupId' => (int)$newGroupId,
                    'groupName' => $newGroupName,
                    'reason' => ucfirst($period) . 'ly winner - ' . $periodName,
                    'timestamp' => time(),
                    'retryCount' => 0
                ]);
                
                // Speichere auch die Gruppen-Info
                $mongoDB->botData->insertOne([
                    'type' => 'winnerGroup',
                    'period' => $period,
                    'periodName' => $periodName,
                    'groupId' => (int)$newGroupId,
                    'groupName' => $newGroupName,
                    'winnerId' => (int)$winnerId,
                    'winnerNickname' => $winnerNickname,
                    'timestamp' => time(),
                    'assigned' => false
                ]);
                
                $logMessage = date('Y-m-d H:i:s') . " - Created pending assignment for offline user $winnerId ($winnerNickname)\n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);
            }
        } else {
            $errorMsg = $ts->getElement('msg', $createResult);
            $logMessage = date('Y-m-d H:i:s') . " - Failed to create group for $periodName. Error: $errorMsg\n";
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        }
    }
    
    private function renameTemplateGroup($ts, $cfg, $period, $newPeriodName, $logFile) {
        $templateKey = $period . 'WinnerTemplate';
        $templateGroupId = $cfg['writeTops']['winnerGroups'][$templateKey];
        
        if ($period == 'monthly') {
            $newGroupName = "Most Active - " . $newPeriodName;
        } else {
            $newGroupName = "Most Active - Year " . $newPeriodName;
        }
        
        $logMessage = date('Y-m-d H:i:s') . " - Renaming template group $templateGroupId to '$newGroupName'\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Benenne die Template-Gruppe um
        $renameResult = $ts->serverGroupRename($templateGroupId, $newGroupName);
        if($ts->succeeded($renameResult)) {
            $logMessage = date('Y-m-d H:i:s') . " - Successfully renamed template group to '$newGroupName'\n";
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        } else {
            $errorMsg = $ts->getElement('msg', $renameResult);
            $logMessage = date('Y-m-d H:i:s') . " - Failed to rename template group. Error: $errorMsg\n";
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        }
    }
}