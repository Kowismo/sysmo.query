<?php
class welcomeMessage {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        if(!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
            if($ezApp->inGroup($cfg['registerGroups'], $clientInfo['client_servergroups'])) {
                $serverInfo = $ts->getElement('data', $ts->serverInfo());
                foreach($cfg['messages']['isRegistered'] as $message) {
                    $ts->sendMessage(1, $clientInfo['clid'], str_replace(['%clientMyTeamspeak%', '%clientUID%', '%clientUpload%', '%clientCountry%', '%clientIP%', '%clientPlatform%', '%clientVersion%', '%clientNickname%', '%serverName%', '%online%', '%channels%', '%uptime%', '%maxclients%', '%version%', '%created%', '%connections%', '%platform%'], [$clientInfo['client_myteamspeak_id'], $clientInfo['client_unique_identifier'], $clientInfo['connection_bytes_sent_total'], $ezApp->codeToCountry($clientInfo['client_country']), $clientInfo['connection_client_ip'], $clientInfo['client_platform'], $clientInfo['client_version'], $clientInfo['client_nickname'], $serverInfo['virtualserver_name'], $serverInfo['virtualserver_clientsonline'] - $serverInfo['virtualserver_queryclientsonline'], $serverInfo['virtualserver_channelsonline'], $ezApp->uptimeConverter($serverInfo['virtualserver_uptime']), $serverInfo['virtualserver_maxclients'], $serverInfo['virtualserver_version'], $ezApp->timeConverter(time() - $clientInfo['client_created']), $clientInfo['client_totalconnections'], $serverInfo['virtualserver_platform']], $message));
                }
            } else {
                foreach($cfg['messages']['isNotRegistered'] as $message) {
                    $ts->sendMessage(1, $clientInfo['clid'], str_replace('%clientNickname%', $clientInfo['client_nickname'], $message));
                }
            }
        }
    }
}