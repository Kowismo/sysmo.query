<?php
class clanStats {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $clientList = $ezApp->clientList();
        $guilds = $mongoDB->clanChannels->find(['clanStatus' => true, 'isAcademy' => false])->toArray();
        if(count($guilds) > 0) {
            foreach($guilds as $guild) {
                $onlineClients = 0;
                $awayClients = 0;
                $updateMany = [];
                foreach($clientList as $client) {
                    if($ezApp->inGroup($guild['clanGroup'], $client['client_servergroups'])) {
                        if(!$client['client_input_muted'] && !$client['client_output_muted'] && floor($client['client_idle_time'] / 1000) < 60) {
                            $onlineClients++;
                        }
                        if($client['client_input_muted'] || $client['client_output_muted'] || floor($client['client_idle_time'] / 1000) > 60) {
                            $awayClients++;
                        }
                    }
                }
                if($onlineClients != 0 || $awayClients != 0) {
                    if($awayClients < $onlineClients) {
                        $updateMany['timeSpent'] = (int)$guild['timeSpent'] + $ezApp->convertInterval($cfg['interval']);
                        $updateMany['points'] = (int)$guild['points'] + $cfg['coinsAmount'];
                    } else {
                        $updateMany['timeSpentAfk'] = (int)$guild['timeSpentAfk'] + $ezApp->convertInterval($cfg['interval']);
                    }
                    $mongoDB->clanChannels->updateOne(['clanGroup' => $guild['clanGroup']], ['$set' => $updateMany]);
                }
            }
        }
    }
}