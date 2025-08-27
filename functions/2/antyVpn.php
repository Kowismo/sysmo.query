<?php
class antyVpn {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        if(!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
            if(!in_array($clientInfo['connection_client_ip'], $cfg['ignoredIps'])) {
                $req = $mongoDB->antyVpn->findOne(['connectionClientIp' => $clientInfo['connection_client_ip']]);
                if($req != null) {
                    if($req['userStatus']) {
                        $ts->clientKick($clientInfo['clid'], 'server', $cfg['messages']['toUser']);
                    }
                } else {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'http://v2.api.iphub.info/ip/' . $clientInfo['connection_client_ip']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Key: ' . $cfg['apiKey']]);
                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        print PREFIX . 'antyVpn error:' . curl_error($ch) . PHP_EOL;
                    }
                    curl_close($ch);
                    if(!empty($result)) {
                        $result = json_decode($result, true);
                        if(isset($result['block'])) {
                            if($result['block'] == 1) {
                                $mongoDB->antyVpn->insertOne([
                                    'connectionClientIp' => $clientInfo['connection_client_ip'],
                                    'userStatus' => 1,
                                ]);
                                $ts->banAddByUid($clientInfo['client_unique_identifier'], 15, $cfg['messages']['toUser']);
                            } elseif($result['block'] == 0) {
                                $mongoDB->antyVpn->insertOne([
                                    'connectionClientIp' => $clientInfo['connection_client_ip'],
                                    'userStatus' => 0,
                                ]);
                            }
                        }
                    } else {
                        $mongoDB->antyVpn->insertOne([
                            'connectionClientIp' => $clientInfo['connection_client_ip'],
                            'userStatus' => 0,
                        ]);
                    }
                }
            }
        }
    }
}