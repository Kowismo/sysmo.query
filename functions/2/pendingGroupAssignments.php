<?php
class pendingGroupAssignments {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        if(!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
            
            // Suche nach ausstehenden Gruppenzuweisungen für diesen User
            $pendingAssignments = $mongoDB->botData->find([
                'type' => 'pendingGroupAssignment',
                'clientDatabaseId' => (int)$clientInfo['client_database_id']
            ])->toArray();
            
            foreach ($pendingAssignments as $assignment) {
                $groupId = $assignment['groupId'];
                $groupName = $assignment['groupName'];
                $reason = $assignment['reason'];
                
                // Versuche den User zur Gruppe hinzuzufügen
                $addResult = $ts->serverGroupAddClient($groupId, $clientInfo['client_database_id']);
                if($ts->succeeded($addResult)) {
                    // Erfolgreich hinzugefügt - lösche das Pending Assignment
                    $mongoDB->botData->deleteOne(['_id' => $assignment['_id']]);
                    
                    // Aktualisiere auch den winnerGroup Eintrag falls vorhanden
                    $mongoDB->botData->updateOne(
                        [
                            'type' => 'winnerGroup',
                            'groupId' => $groupId,
                            'winnerId' => (int)$clientInfo['client_database_id']
                        ],
                        ['$set' => ['assigned' => true]]
                    );
                    
                    // Sende Nachricht an User
                    $ts->clientPoke($clientInfo['clid'], "🎉 Congratulations! You have been awarded the group '$groupName' for being a $reason! 🏆");
                    
                } else {
                    // Fehlgeschlagen - erhöhe Retry Counter
                    $retryCount = isset($assignment['retryCount']) ? $assignment['retryCount'] + 1 : 1;
                    
                    if ($retryCount < 5) {
                        $mongoDB->botData->updateOne(
                            ['_id' => $assignment['_id']],
                            ['$set' => ['retryCount' => $retryCount, 'lastRetry' => time()]]
                        );
                    } else {
                        // Nach 5 Versuchen aufgeben und löschen
                        $mongoDB->botData->deleteOne(['_id' => $assignment['_id']]);
                    }
                }
            }
        }
    }
}