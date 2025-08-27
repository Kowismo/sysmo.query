<?php
class pokeAllCommand {
    public function __construct($ts, $clientInfo, $args, $cfg, $mongoDB, $ezApp) {
        $clientList = $ts->getElement('data', $ts->clientList('-groups'));
        $i = 0;
        foreach($clientList as $client) {
            if(!$ezApp->inGroup($cfg['ignoredGroups'], $client['client_servergroups']) && $client['client_type'] == 0 && $client['clid'] != $clientInfo['invokerid']) {
                $ts->clientPoke($client['clid'], str_replace(['%message%'], [str_replace($cfg['commandUsage'] . ' ', '', $clientInfo['msg'])], $cfg['messages']['messageFormat']));
                $i++;
            }
        }
        $ts->sendMessage(1, $clientInfo['invokerid'], str_replace(['%i%', '%message%'], [$i, str_replace($cfg['commandUsage'] . ' ', '', $clientInfo['msg'])], $cfg['messages']['toInvoker']));
    }
}