<?php
class removeClanGroup {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        if(!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
            $userClans = $ezApp->getUserClans($clientInfo['client_servergroups'], $mongoDB);
            if(!empty($userClans) && count($userClans) > 0) {
                foreach($userClans as $clan) {
                    $ts->serverGroupDeleteClient($clan['clanGroup'], $clientInfo['client_database_id']);
                    if($cfg['setChannelGroup'] && $clan['clientUniqueIdentifier'] != $clientInfo['client_unique_identifier']) {
                        $allCids = @array_merge((array)$userClans['allCids'], (array)$userClans['additionalCids']);
                        foreach($allCids as $cid) {
                            $ts->setClientChannelGroup($cfg['guestGroup'], $cid, $clientInfo['client_database_id']);
                        }
                    }
                    $mongoDB->clanLogs->insertOne(['method' => 'exitGuild', 'clanGroup' => $clan['clanGroup'], 'invokerUid' => $clientInfo['client_unique_identifier'], 'text' => 'Zdjął sobie rangę gildyjną', 'time' => time()]);
                }
                $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
            } else {
                $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
            }
        } else {
            $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
        }
    }
}