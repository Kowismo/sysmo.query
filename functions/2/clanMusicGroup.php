<?php
class clanMusicGroup {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        if(!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
            $guildInfo = $mongoDB->clanChannels->findOne(['musicChangerId' => (int)$clientInfo['ctid'], 'clanStatus' => true, 'isAcademy' => false]);
            if($guildInfo != null) {
                if($ezApp->inGroup($guildInfo['clanGroup'], $clientInfo['client_servergroups'])) {
                    if($ezApp->inGroup($cfg['musicAccessGroup'], $clientInfo['client_servergroups'])) {
                        $ts->clientPoke($clientInfo['clid'], $cfg['messages']['musicAccessRemoved']);
                        $ts->serverGroupDeleteClient($cfg['musicAccessGroup'], $clientInfo['client_database_id']);
                        $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
                    } else {
                        $ts->clientPoke($clientInfo['clid'], $cfg['messages']['musicAccessAdded']);
                        $ts->serverGroupAddClient($cfg['musicAccessGroup'], $clientInfo['client_database_id']);
                        $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
                    }
                } else {
                    $haveAcademy = $mongoDB->clanChannels->findOne(['clanType' => 'Academy', 'isAcademy' => true, 'mainGuild' => (int)$guildInfo['clanGroup']]);
                    if($haveAcademy != null) {
                        if($ezApp->inGroup($haveAcademy['clanGroup'], $clientInfo['client_servergroups'])) {
                            if($ezApp->inGroup($cfg['musicAccessGroup'], $clientInfo['client_servergroups'])) {
                                $ts->clientPoke($clientInfo['clid'], $cfg['messages']['musicAccessRemoved']);
                                $ts->serverGroupDeleteClient($cfg['musicAccessGroup'], $clientInfo['client_database_id']);
                                $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
                            } else {
                                $ts->clientPoke($clientInfo['clid'], $cfg['messages']['musicAccessAdded']);
                                $ts->serverGroupAddClient($cfg['musicAccessGroup'], $clientInfo['client_database_id']);
                                $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
                            }
                        } else {
                            $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
                        }
                    } else {
                        $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
                    }
                }
            }
        } else {
            $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
        }
    }
}