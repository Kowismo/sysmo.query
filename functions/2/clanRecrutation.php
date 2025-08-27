<?php
class clanRecrutation {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        if(!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
            $guildInfo = $mongoDB->clanChannels->findOne(['recrutationId' => (int)$clientInfo['ctid'], 'clanStatus' => true, 'isAcademy' => false]);
            if($guildInfo != null) {
                if($guildInfo['recrutationStatus'] == 0) {
                    foreach($cfg['messages']['recrutationOff'] as $message) {
                        $ts->sendMessage(1, $clientInfo['clid'], str_replace(['%clientNickname%'], [$clientInfo['client_nickname']], $message));
                    }
                }
                if($guildInfo['recrutationStatus'] == 1) {
                    $allUsers = $mongoDB->clanRecrutations->find(['clanGroup' => $guildInfo['clanGroup']])->toArray();
                    if(count($allUsers) > 0) {
                        foreach($allUsers as $toPoke) {
                            $clientId = $ts->getElement('data', $ts->clientGetIds($toPoke['clientUniqueIdentifier']));
                            if(!empty($clientId)) {
                                $userInfo = $ts->getElement('data', $ts->clientInfo($clientId[0]['clid']));
                                if($userInfo) {
                                    if($ezApp->inGroup($guildInfo['clanGroup'], $userInfo['client_servergroups'])) {
                                        switch($toPoke['infoType']) {
                                            case 'pw':
                                                foreach($cfg['messages']['toGuild'] as $message) {
                                                    $ts->sendMessage(1, $clientId[0]['clid'], str_replace(['%clientNickname%', '%clanName%', '%clientId%'], [$userInfo['client_nickname'], $guildInfo['clanName'], $ezApp->createId($clientInfo)], $message));
                                                }
                                                break;
                                            case 'poke':
                                                foreach($cfg['messages']['toGuild'] as $message) {
                                                    $ts->clientPoke($clientId[0]['clid'], str_replace(['%clientNickname%', '%clanName%', '%clientId%'], [$userInfo['client_nickname'], $guildInfo['clanName'], $ezApp->createId($clientInfo)], $message));
                                                }
                                                break;
                                            default:
                                                foreach($cfg['messages']['toGuild'] as $message) {
                                                    $ts->sendMessage(1, $clientId[0]['clid'], str_replace(['%clientNickname%', '%clanName%', '%clientId%'], [$userInfo['client_nickname'], $guildInfo['clanName'], $ezApp->createId($clientInfo)], $message));
                                                }
                                                break;
                                        }
                                    }
                                }
                            }
                        }
                        foreach($cfg['messages']['recrutationOn'] as $message) {
                            $ts->sendMessage(1, $clientInfo['clid'], str_replace(['%clientNickname%'], [$clientInfo['client_nickname']], $message));
                        }
                    } else {
                        foreach($cfg['messages']['noUsersToPoke'] as $message) {
                            $ts->sendMessage(1, $clientInfo['clid'], str_replace(['%clientNickname%'], [$clientInfo['client_nickname']], $message));
                        }
                    }
                } elseif($guildInfo['recrutationStatus'] == 2) {
                    $channelInfo = $ts->getElement('data', $ts->channelInfo($guildInfo['recrutationId']));
                    if($channelInfo) {
                        $channelGroupClientList = $ts->getElement('data', $ts->channelGroupClientList($channelInfo['pid']));
                        if(!empty($channelGroupClientList)) {
                            $toPoke = [];
                            foreach($channelGroupClientList as $channelClient) {
                                if(in_array($channelClient['cgid'], $cfg['channelGroups'])) {
                                    $toPoke[] = $channelClient;
                                }
                            }
                            if(!empty($toPoke)) {
                                foreach($toPoke as $pokeClient) {
                                    $data = $ts->getElement('data', $ts->clientGetNameFromDbid($pokeClient['cldbid']));
                                    if($data) {
                                        if(isset($data['cluid'])) {
                                            $clientId = $ts->getElement('data', $ts->clientGetIds($data['cluid']));
                                            if(!empty($clientId)) {
                                                $userInfo = $ts->getElement('data', $ts->clientInfo($clientId[0]['clid']));
                                                if($userInfo) {
                                                    if($ezApp->inGroup($guildInfo['clanGroup'], $userInfo['client_servergroups'])) {
                                                        foreach($cfg['messages']['toGuild'] as $message) {
                                                            $ts->sendMessage(1, $clientId[0]['clid'], str_replace(['%clientNickname%', '%clanName%', '%clientId%'], [$userInfo['client_nickname'], $guildInfo['clanName'], $ezApp->createId($clientInfo)], $message));
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                foreach($cfg['messages']['recrutationOn'] as $message) {
                                    $ts->sendMessage(1, $clientInfo['clid'], str_replace(['%clientNickname%'], [$clientInfo['client_nickname']], $message));
                                }
                            } else {
                                foreach($cfg['messages']['noUsersToPoke'] as $message) {
                                    $ts->sendMessage(1, $clientInfo['clid'], str_replace(['%clientNickname%'], [$clientInfo['client_nickname']], $message));
                                }
                            }
                        } else {
                            foreach($cfg['messages']['noUsersToPoke'] as $message) {
                                $ts->sendMessage(1, $clientInfo['clid'], str_replace(['%clientNickname%'], [$clientInfo['client_nickname']], $message));
                            }
                        }
                    }
                }
            }
        } else {
            $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
        }
    }
}