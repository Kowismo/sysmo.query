<?php
class clientInfo {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        if(!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
            foreach($cfg['messages']['toUser'] as $message) {
                $ts->sendMessage(1, $clientInfo['clid'], str_replace(['%clientMyTeamspeak%', '%clientDataReceived%', '%clientDataSend%', '%clientIP%', '%clientNickname%', '%clientDatabaseId%', '%clientUniqueIdentifier%', '%clientVersion%', '%clientCreated%', '%clientTotalConnections%', '%clientPlatform%', '%clientCountry%'], [$clientInfo['client_myteamspeak_id'], $clientInfo['connection_bytes_received_total'], $clientInfo['connection_bytes_sent_total'], $clientInfo['connection_client_ip'], $clientInfo['client_nickname'], $clientInfo['client_database_id'], $clientInfo['client_unique_identifier'], $clientInfo['client_version'], date('d/m/Y H:i:s', $clientInfo['client_created']), $clientInfo['client_totalconnections'], $clientInfo['client_platform'], $ezApp->codeToCountry($clientInfo['client_country'])], $message));
            }
            $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
        } else {
            $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
        }
    }
}