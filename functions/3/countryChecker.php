<?php
class countryChecker {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        foreach($ezApp->clientList() as $client) {
            if(!$ezApp->inGroup($cfg['ignoredGroups'], $client['client_servergroups']) && $client['client_type'] == 0) {
                if(!in_array($client['connection_client_ip'], $cfg['allowedIps']) && in_array($client['client_country'], $cfg['blockedCountries'])) {
                    $ts->clientKick($client['clid'], 'server', str_replace(['%blockedCountry%'], [$client['client_country']], $cfg['messages']['toUser']));
                    $ezApp->createLog($mongoDB, __CLASS__, $client['client_unique_identifier'], $client['client_nickname'], 'Join server from blocked country.');
                }
            }
        }
    }
}
