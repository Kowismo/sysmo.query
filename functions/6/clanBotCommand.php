<?php
class clanBotCommand {
    public function __construct($ts, $clientInfo, $args, $cfg, $mongoDB, $ezApp) {
        if(isset($args[1])) {
            switch ($args[1]) {
                case 'add':
                    $guild = $mongoDB->clanChannels->findOne(['clanGroup' => (int)$args[2], 'isAcademy' => false]);
                    if($guild != null) {
                        if(count($guild['musicBots']) < 6) {
                            $bots = [];
                            $channelClientList = $ts->getElement('data', $ts->channelClientList($cfg['musicBots']['channelId'], '-uid -groups'));
                            foreach($channelClientList as $client) {
                                if($ezApp->inGroup($cfg['musicBots']['groupId'], $client['client_servergroups'])) {
                                    $bots[] = [
                                        'clid' => $client['clid'],
                                        'client_unique_identifier' => $client['client_unique_identifier'],
                                    ];
                                }
                            }
                            if(!empty($bots)) {
                                foreach($cfg['musicBots']['sendCommandsAdd'] as $message) {
                                    $ts->sendMessage(1, $bots[0]['clid'], str_replace(['%i%', '%clanName%', '%channelId%'], [count($guild['musicBots']) + 1, $guild['clanName'], $guild['mainId']], $message));
                                }
                                $ts->clientMove($bots[0]['clid'], $guild['mainId']);
                                $guild['musicBots'][] = $bots[0]['client_unique_identifier'];
                                $mongoDB->clanChannels->updateOne(['clanGroup' => $guild['clanGroup']], ['$set' => ['musicBots' => $guild['musicBots']]]);
                            }
                        } else {
                            $ts->sendMessage(1, $clientInfo['invokerid'], $cfg['messages']['maxBots']);
                        }
                    } else {
                        $ts->sendMessage(1, $clientInfo['invokerid'], $cfg['messages']['guildNotExist']);
                    }
                    break;
                case 'del':
                    $guild = $mongoDB->clanChannels->findOne(['clanGroup' => (int)$args[2], 'isAcademy' => false]);
                    if($guild != null) {
                        if(count($guild['musicBots']) > 1) {
                            $clientid = $ts->getElement('data', $ts->clientGetIds($guild['musicBots'][count($guild['musicBots']) - 1]));
                            if(!empty($clientid)) {
                                $botInfo = $ts->getElement('data', $ts->clientInfo($clientid[0]['clid']));
                                if($botInfo['cid'] != $cfg['musicBots']['channelId']) {
                                    $ts->clientMove($clientid[0]['clid'], $cfg['musicBots']['channelId']);
                                }
                                $generatedName = $ezApp->generateString(6);
                                foreach($cfg['musicBots']['sendCommandsDel'] as $command) {
                                    $ts->sendMessage(1, $clientid[0]['clid'], str_replace(['%channelId%', '%generatedName%'], [$cfg['musicBots']['channelId'], $generatedName], $command));
                                }
                            }
                            unset($guild['musicBots'][count($guild['musicBots']) - 1]);
                            $mongoDB->clanChannels->updateOne(['clanGroup' => $guild['clanGroup']], ['$set' => ['musicBots' => $guild['musicBots']]]);
                        } else {
                            $ts->sendMessage(1, $clientInfo['invokerid'], $cfg['messages']['minBots']);
                        }
                    } else {
                        $ts->sendMessage(1, $clientInfo['invokerid'], $cfg['messages']['guildNotExist']);
                    }
                    break;
                default:
                    $ts->sendMessage(1, $clientInfo['invokerid'], $cfg['messages']['commandUsage']);
                    break;
            }
        } else {
            $ts->sendMessage(1, $clientInfo['invokerid'], $cfg['messages']['commandUsage']);
        }
    }
}