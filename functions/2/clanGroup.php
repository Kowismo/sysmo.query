<?php
class clanGroup {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        if(!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
            $guildInfo = $mongoDB->clanChannels->findOne(['groupChangerId' => (int)$clientInfo['ctid'], 'clanStatus' => true]);
            if($guildInfo != null) {
                if($ezApp->inGroup($guildInfo['clanGroup'], $clientInfo['client_servergroups'])) {
                    $ts->clientPoke($clientInfo['clid'], str_replace(['%clanName%'], [$guildInfo['clanName']], $cfg['messages']['clanGroupRemoved']));
                    $ts->serverGroupDeleteClient($guildInfo['clanGroup'], $clientInfo['client_database_id']);
                    $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
                    if($cfg['setChannelGroup'] && $guildInfo['clientUniqueIdentifier'] != $clientInfo['client_unique_identifier']) {
                        $ts->setClientChannelGroup($cfg['guestGroup'], $guildInfo['mainId'], $clientInfo['client_database_id']);
                    }
                } else {
                    $ts->clientPoke($clientInfo['clid'], str_replace(['%clanName%'], [$guildInfo['clanName']], $cfg['messages']['clanGroupAdded']));
                    $ts->serverGroupAddClient($guildInfo['clanGroup'], $clientInfo['client_database_id']);
                    $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
                    if($cfg['setChannelGroup'] && $guildInfo['clientUniqueIdentifier'] != $clientInfo['client_unique_identifier']) {
                        $ts->setClientChannelGroup($cfg['recruitGroup'], $guildInfo['mainId'], $clientInfo['client_database_id']);
                    }
                }
            }
        } else {
            $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
        }
    }
}