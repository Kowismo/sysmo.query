<?php
ini_set('memory_limit', '8G');
$instanceId = getopt('i:');
$websiteInstance = getopt('w:');
if(isset($instanceId['i'])) {
    if ($instanceId == null || !array_key_exists('i', $instanceId) || !is_numeric($instanceId['i'])) {
        die('Invalid instance type.' . PHP_EOL);
    }
    $i = $instanceId['i'];
    if(!is_dir(__DIR__.'/cache')) {
        mkdir(__DIR__.'/cache', 0700);
    }
    if(!is_dir(__DIR__.'/cache/icons')) {
        mkdir(__DIR__.'/cache/icons', 0700);
    }
    if(!is_dir(__DIR__.'/cache/avatars')) {
        mkdir(__DIR__.'/cache/avatars', 0700);
    }
    if(!is_dir(__DIR__.'/logs')) {
        mkdir(__DIR__.'/logs', 0700);
    }
    if(!is_dir(__DIR__.'/logs/' . $i)) {
        mkdir(__DIR__.'/logs/' . $i, 0700);
    }
    if(!is_dir(__DIR__.'/logs/website')) {
        mkdir(__DIR__.'/logs/website', 0700);
    }
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('error_log', 'logs/'.$i.'/'.date('d-m-Y').'.log');
    error_reporting(E_ALL);

    date_default_timezone_set('Europe/Warsaw');
    setlocale(LC_ALL, 'UTF-8');
    ini_set('default_charset', 'UTF-8');

    if(file_exists('configs/config.php')) {
        $config = require_once 'configs/config.php';
    } else {
        die('config.php file not found!' . PHP_EOL);
    }
    if(!isset($config[$i])) {
        die('Configuration for this instance does not exist!' . PHP_EOL);
    }
    if(file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
    } else {
        die('autoload.php file not found!' . PHP_EOL);
    }
    if(file_exists('libs/ezApp.class.php')) {
        require_once 'libs/ezApp.class.php';
        $ezApp = new ezApp();
    } else {
        die('Class.php file not found!' . PHP_EOL);
    }
    try {
        $mongoDB = new \MongoDB\Client($config[$i]['mongodb']['srv']);
        $mongoDB = $mongoDB->selectDatabase($config[$i]['mongodb']['dbName']);
        if($i == 1) {
            $ezApp->createCollections($mongoDB);
        }
    } catch (MongoConnectionException $e) {
        die($e . PHP_EOL);
    }

    $ts = new ts3admin($config[$i]['connection']['teamspeakHost'], $config[$i]['connection']['teamspeakPorts']['queryPort']);
    if($ts->succeeded($ts->connect())) {
        print 'Successfully connected!' . PHP_EOL;
        if($ts->succeeded($ts->login($config[$i]['connection']['teamspeakLogin'], $config[$i]['connection']['teamspeakPass']))) {
            print 'Successfully logged in!' . PHP_EOL;
            if($ts->succeeded($ts->selectServer($config[$i]['connection']['teamspeakPorts']['voicePort'], 'port', 1))) {
                print 'Server selected successfully!' . PHP_EOL;
                $ts->setName($config[$i]['settings']['botName']);
                print 'Name setted successfully!' . PHP_EOL;
                $whoAmI = $ts->getElement('data', $ts->whoAmI());
                $ts->clientMove($whoAmI['client_id'], $config[$i]['settings']['channelId']);
                print 'Channel switched successfully!' . PHP_EOL;
                switch ($i) {
                    case 1:
                        $ts->execOwnCommand(0, 'servernotifyregister event=server');
                        $ts->execOwnCommand(0, 'servernotifyregister event=textprivate');
                        $cfg = $config[$i]['configuration'];
                        $language = $config[$i]['messages'];
                        while (true) {
                            $dataFromSocket = $ezApp->getEvents();
                            if (isset($dataFromSocket['notifycliententerview'])) {
                                if($dataFromSocket['client_type'] == 0 && $ezApp->inGroup($cfg['registerGroups'], $dataFromSocket['client_servergroups']) && $dataFromSocket['ctid'] == $cfg['lobbyChannel'] && !$ezApp->inGroup($cfg['ignoredGroups'], $dataFromSocket['client_servergroups'])) {
                                    $userClans = $ezApp->getUserClans($dataFromSocket['client_servergroups'], $mongoDB);
                                    if (count($userClans) > 0) {
                                        $clansNames = [];
                                        foreach ($userClans as $clan) {
                                            $clansNames[] = str_replace(['%clanGroup%', '%clanName%'], [$clan['clanGroup'], $clan['clanName']], $language['userClanFormat']);
                                        }
                                        foreach ($language['whenHaveClan'] as $message) {
                                            $ts->sendMessage(1, $dataFromSocket['clid'], str_replace(['%clientNickname%', '%userClans%'], [$dataFromSocket['client_nickname'], implode(', ', $clansNames)], $message));
                                        }
                                        $ts->clientMove($dataFromSocket['clid'], $userClans[0]['cometId']);
                                    } else {
                                        foreach ($language['whenNotHaveClan'] as $message) {
                                            $ts->sendMessage(1, $dataFromSocket['clid'], str_replace(['%clientNickname%'], [$dataFromSocket['client_nickname']], $message));
                                        }
                                    }
                                }
                            }
                            if(isset($dataFromSocket['notifytextmessage'])) {
                                $clientInfo = $ts->getElement('data', $ts->clientInfo($dataFromSocket['invokerid']));
                                if($clientInfo) {
                                    if($clientInfo['client_type'] == 0 && $ezApp->inGroup($cfg['registerGroups'], $clientInfo['client_servergroups']) && !$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
                                        $args = explode(' ', $dataFromSocket['msg']);
                                        switch ($args[0]) {
                                            case '!teleports':
                                                $ts->sendMessage(1, $dataFromSocket['invokerid'], $language['cometsList']['firstMessage']);
                                                $clans = $mongoDB->clanChannels->find([], ['sort' => ['_id' => -1]])->toArray();
                                                if(count($clans) > 0) {
                                                    foreach($clans as $clan) {
                                                        $ts->sendMessage(1, $dataFromSocket['invokerid'], str_replace(['%clanGroup%', '%clanName%'], [$clan['clanGroup'], $clan['clanName']], $language['cometsList']['clanFormat']));
                                                    }
                                                }
                                                $ts->sendMessage(1, $dataFromSocket['invokerid'], $language['cometsList']['lastMessage']);
                                                break;
                                            case '!teleport':
                                                if(isset($args[1])) {
                                                    if(is_numeric($args[1])) {
                                                        $guildInfo = $mongoDB->clanChannels->findOne(['clanGroup' => (int)$args[1]]);
                                                    } else {
                                                        $guildInfo = $mongoDB->clanChannels->findOne(['clanName' => $args[1]]);
                                                    }
                                                    if($guildInfo) {
                                                        if($clientInfo['cid'] != $guildInfo['cometId']) {
                                                            $ts->clientMove($dataFromSocket['invokerid'], $guildInfo['cometId']);
                                                            $ts->sendMessage(1, $dataFromSocket['invokerid'], $language['movedToComet']);
                                                        } else {
                                                            $ts->sendMessage(1, $dataFromSocket['invokerid'], $language['alreadyOnComet']);
                                                        }
                                                    } else {
                                                        $ts->sendMessage(1, $dataFromSocket['invokerid'], $language['invalidComet']);
                                                    }
                                                } else {
                                                    $ts->sendMessage(1, $dataFromSocket['invokerid'], $language['invalidComet']);
                                                }
                                                break;
                                            default:
                                                $ts->sendMessage(1, $dataFromSocket['invokerid'], $language['invalidArgs']);
                                                break;
                                        }
                                    }
                                }
                            }
                            $ts->version();
                        }
                        break;
                    case 2:
                        $req = $mongoDB->botData->findOne(['type' => 'socketClid']);
                        if($req != null) {
                            $mongoDB->botData->updateOne(['type' => 'socketClid'], ['$set' => ['clid' => (int)$whoAmI['client_id']]]);
                        } else {
                            $mongoDB->botData->insertOne(['type' => 'socketClid', 'clid' => (int)$whoAmI['client_id']]);
                        }

                        $executableChannels = [];
                        $socketFunctions = [];
                        foreach($config[$i]['functions'] as $type => $functions) {
                            switch ($type) {
                                case 'notifyclientmoved':
                                    foreach($functions as $function => $value) {
                                        if($value['enabled']) {
                                            if(file_exists('functions/' . $i . '/' . $function . '.php')) {
                                                require_once 'functions/' . $i . '/' . $function . '.php';
                                                if(isset($value['allChannels'])) {
                                                    foreach($value['allChannels'] as $channelId) {
                                                        $executableChannels[$channelId] = [$function, $value];
                                                    }
                                                } elseif(isset($value['channelId'])) {
                                                    $executableChannels[$value['channelId']] = [$function, $value];
                                                }
                                            }
                                        }
                                    }
                                    break;
                                default:
                                    foreach($functions as $function => $value) {
                                        if($value['enabled']) {
                                            if(file_exists('functions/' . $i . '/' . $function . '.php')) {
                                                require_once 'functions/' . $i . '/' . $function . '.php';
                                                $socketFunctions[$type][$function] = $value;
                                            }
                                        }
                                    }
                                    break;
                            }
                        }

                        $allGuilds = $mongoDB->clanChannels->find(['clanStatus' => true])->toArray();
                        if(count($allGuilds) > 0) {
                            foreach($allGuilds as $guild) {
                                $executableChannels[$guild['groupChangerId']] = ['clanGroup', $config[$i]['functions']['notifyclientmoved']['clanGroup']];
                                if(!$guild['isAcademy']) {
                                    $executableChannels[$guild['musicChangerId']] = ['clanMusicGroup', $config[$i]['functions']['notifyclientmoved']['clanMusicGroup']];
                                    $executableChannels[$guild['recrutationId']] = ['clanRecrutation', $config[$i]['functions']['notifyclientmoved']['clanRecrutation']];
                                }
                            }
                        }

                        $usersList = [];
                        $clientList = $ts->getElement('data', $ts->clientList('-uid -away -voice -times -groups -info -icon -country -ip -badges'));
                        foreach($clientList as $client) {
                            if($client['client_type'] == 0) {
                                $usersList[$client['clid']] = array_merge($client, ['ctid' => $client['cid']]);
                            }
                        }
                        $ts->execOwnCommand(0, 'servernotifyregister event=server');
                        $ts->execOwnCommand(0, 'servernotifyregister event=textprivate');
                        $ts->execOwnCommand(0, 'servernotifyregister event=channel id=0');
                        while(true) {
                            $dataFromSocket = $ezApp->getEvents();
                            if(isset($dataFromSocket['notifyclientmoved'])) {
                                if(isset($executableChannels[$dataFromSocket['ctid']])) {
                                    $clientInfo = $ts->getElement('data', $ts->clientInfo($dataFromSocket['clid']));
                                    if($clientInfo) {
                                        if($clientInfo['client_type'] == 0) {
                                            if(isset($usersList[$dataFromSocket['clid']])) {
                                                new $executableChannels[$dataFromSocket['ctid']][0]($ts, array_merge($clientInfo, $dataFromSocket, ['lastChannel' => $usersList[$dataFromSocket['clid']]['ctid']]), $mongoDB, $executableChannels[$dataFromSocket['ctid']][1], $ezApp);
                                                $usersList[$dataFromSocket['clid']] = array_merge($dataFromSocket, $clientInfo, ['ctid' => $usersList[$dataFromSocket['clid']]['ctid'], 'cid' => $usersList[$dataFromSocket['clid']]['ctid']]);
                                            }
                                        }
                                    }
                                } else {
                                    $usersList[$dataFromSocket['clid']]['ctid'] = $dataFromSocket['ctid'];
                                }
                            } elseif(isset($dataFromSocket['notifycliententerview'])) {
                                if($dataFromSocket['client_type'] == 0) {
                                    $clientInfo = $ts->getElement('data', $ts->clientInfo($dataFromSocket['clid']));
                                    if($clientInfo) {
                                        $usersList[$dataFromSocket['clid']] = array_merge($dataFromSocket, $clientInfo);
                                        foreach($socketFunctions['notifycliententerview'] as $function => $value) {
                                            new $function($ts, array_merge($clientInfo, $dataFromSocket), $mongoDB, $value, $ezApp);
                                        }
                                    }
                                }
                            } elseif(isset($dataFromSocket['notifyclientleftview'])) {
                                if(isset($usersList[$dataFromSocket['clid']])) {
                                    if(isset($usersList[$dataFromSocket['clid']]['client_type'])) {
                                        if($usersList[$dataFromSocket['clid']]['client_type'] == 0) {
                                            foreach($socketFunctions['notifyclientleftview'] as $function => $value) {
                                                new $function($ts, array_merge($usersList[$dataFromSocket['clid']], $dataFromSocket), $mongoDB, $value, $ezApp);
                                            }
                                        }
                                    }
                                    unset($usersList[$dataFromSocket['clid']]);
                                }
                            } elseif(isset($dataFromSocket['notifytextmessage'])) { 
                                $clientInfo = $ts->getElement('data', $ts->clientInfo($dataFromSocket['invokerid']));
                                if($clientInfo['client_type'] == 1) {
                                    $args = explode(':', $dataFromSocket['msg']);
                                    switch ($args[0]) {
                                        case 'add':
                                            $guildInfo = $mongoDB->clanChannels->findOne(['clanGroup' => (int)$args[1]]);
                                            if($guildInfo != null) {
                                                $executableChannels[$guildInfo['groupChangerId']] = ['clanGroup', $config[$i]['functions']['notifyclientmoved']['clanGroup']];
                                                if(!$guildInfo['isAcademy']) {
                                                    $executableChannels[$guildInfo['musicChangerId']] = ['clanMusicGroup', $config[$i]['functions']['notifyclientmoved']['clanMusicGroup']];
                                                    $executableChannels[$guildInfo['recrutationId']] = ['clanRecrutation', $config[$i]['functions']['notifyclientmoved']['clanRecrutation']];
                                                }
                                            }
                                            break;
                                        case 'del':
                                            $guildInfo = $mongoDB->clanChannels->findOne(['clanGroup' => (int)$args[1]]);
                                            if($guildInfo != null) {
                                                if($guildInfo['isAcademy']) {
                                                    unset($executableChannels[$guildInfo['groupChangerId']]);
                                                } else {
                                                    unset($executableChannels[$guildInfo['groupChangerId']], $executableChannels[$guildInfo['musicChangerId']], $executableChannels[$guildInfo['recrutationId']]);
                                                }
                                            }
                                            break;
                                    }
                                }
                            }
                            $ts->version();
                        }
                        break;
                    case 3:
                        foreach($config[$i]['functions'] as $function => $value) {
                            if($value['enabled'] && file_exists('functions/' . $i . '/' . $function . '.php')) {
                                if(isset($value['interval'])) {
                                    $intervalFunctions[$function] = ['noInterval' => false, 'cfg' => $value, 'interval' => $ezApp->convertInterval($value['interval']), 'nextUpdate' => time() + $ezApp->convertInterval($value['interval'])];
                                } else {
                                    $intervalFunctions[$function] = ['noInterval' => true, 'cfg' => $value];
                                }
                                require_once 'functions/' . $i . '/' . $function . '.php';
                            }
                        }
                        while(true) {
                            if(!empty($intervalFunctions)) {
                                foreach($intervalFunctions as $function => $value) {
                                    if($value['noInterval']) {
                                        new $function($ts, $value['cfg'], $mongoDB, $ezApp);
                                    } else {
                                        if(time() >= $value['nextUpdate']) {
                                            new $function($ts, $value['cfg'], $mongoDB, $ezApp);
                                            $intervalFunctions[$function]['nextUpdate'] = time() + $value['interval'];
                                        }
                                    }
                                }
                            }
                            usleep(1000000);
                        }
                        break;
                    case 4:
                        foreach($config[$i]['functions'] as $function => $value) {
                            if($value['enabled'] && file_exists('functions/' . $i . '/' . $function . '.php')) {
                                if(isset($value['interval'])) {
                                    $intervalFunctions[$function] = ['noInterval' => false, 'cfg' => $value, 'interval' => $ezApp->convertInterval($value['interval']), 'nextUpdate' => time() + $ezApp->convertInterval($value['interval'])];
                                } else {
                                    $intervalFunctions[$function] = ['noInterval' => true, 'cfg' => $value];
                                }
                                require_once 'functions/' . $i . '/' . $function . '.php';
                            }
                        }
                        while(true) {
                            if(!empty($intervalFunctions)) {
                                foreach($intervalFunctions as $function => $value) {
                                    if($value['noInterval']) {
                                        new $function($ts, $value['cfg'], $mongoDB, $ezApp);
                                    } else {
                                        if(time() >= $value['nextUpdate']) {
                                            new $function($ts, $value['cfg'], $mongoDB, $ezApp);
                                            $intervalFunctions[$function]['nextUpdate'] = time() + $value['interval'];
                                        }
                                    }
                                }
                            }
                            usleep(1000000);
                        }
                        break;
                    case 5:
                        foreach($config[$i]['functions'] as $function => $value) {
                            if($value['enabled'] && file_exists('functions/' . $i . '/' . $function . '.php')) {
                                if(isset($value['interval'])) {
                                    $intervalFunctions[$function] = ['noInterval' => false, 'cfg' => $value, 'interval' => $ezApp->convertInterval($value['interval']), 'nextUpdate' => time() + $ezApp->convertInterval($value['interval'])];
                                } else {
                                    $intervalFunctions[$function] = ['noInterval' => true, 'cfg' => $value];
                                }
                                require_once 'functions/' . $i . '/' . $function . '.php';
                            }
                        }
                        while(true) {
                            if(!empty($intervalFunctions)) {
                                foreach($intervalFunctions as $function => $value) {
                                    if($value['noInterval']) {
                                        new $function($ts, $value['cfg'], $mongoDB, $ezApp);
                                    } else {
                                        if(time() >= $value['nextUpdate']) {
                                            new $function($ts, $value['cfg'], $mongoDB, $ezApp);
                                            $intervalFunctions[$function]['nextUpdate'] = time() + $value['interval'];
                                        }
                                    }
                                }
                            }
                            usleep(1000000);
                        }
                        break;
                    case 6:
                        foreach($config[$i]['functions'] as $function => $value) {
                            if($value['enabled']) {
                                if(file_exists('functions/' . $i . '/' . $function . '.php')) {
                                    require_once 'functions/' . $i . '/' . $function . '.php';
                                    $registeredCommands[$value['commandUsage']] = [$function, $value];
                                }
                            }
                        }
                        $ts->execOwnCommand(0, 'servernotifyregister event=server');
                        $ts->execOwnCommand(0, 'servernotifyregister event=textprivate');
                        while(true) {
                            $dataFromSocket = $ezApp->getEvents();
                            if(isset($dataFromSocket['notifytextmessage'])) {
                                $args = explode(' ', $dataFromSocket['msg']);
                                if(isset($registeredCommands[$args[0]])) {
                                    $clientInfo = $ts->getElement('data', $ts->clientInfo($dataFromSocket['invokerid']));
                                    if($clientInfo) {
                                        if($clientInfo['client_type'] == 0 && $ezApp->inGroup($registeredCommands[$args[0]][1]['authorizedGroups'], $clientInfo['client_servergroups'])) {
                                            new $registeredCommands[$args[0]][0]($ts, array_merge($clientInfo, $dataFromSocket), $args, $registeredCommands[$args[0]][1], $mongoDB, $ezApp);
                                        }
                                    }
                                }
                            }
                            $ts->version();
                        }
                        break;
                }
            }
        }
    }
} elseif(isset($websiteInstance['w'])) {
    if ($websiteInstance == null || !array_key_exists('w', $websiteInstance) || !is_numeric($websiteInstance['w'])) {
        die('Invalid website instance type.' . PHP_EOL);
    }
    $i = $websiteInstance['w'];
    if(!is_dir(__DIR__.'/cache')) {
        mkdir(__DIR__.'/cache', 0700);
    }
    if(!is_dir(__DIR__.'/cache/icons')) {
        mkdir(__DIR__.'/cache/icons', 0700);
    }
    if(!is_dir(__DIR__.'/cache/avatars')) {
        mkdir(__DIR__.'/cache/avatars', 0700);
    }
    if(!is_dir(__DIR__.'/logs')) {
        mkdir(__DIR__.'/logs', 0700);
    }
    if(!is_dir(__DIR__.'/logs/website')) {
        mkdir(__DIR__.'/logs/website', 0700);
    }
    if(!is_dir(__DIR__.'/logs/website/'. $i)) {
        mkdir(__DIR__.'/logs/website/'. $i, 0700);
    }
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('error_log', __DIR__.'/logs/website/'.$i.'/'.date('d-m-Y').'.log');
    error_reporting(E_ALL);

    if(file_exists(__DIR__.'/configs/config.php')) {
        $config = require_once __DIR__.'/configs/website.php';
    } else {
        die('config.php file not found!' . PHP_EOL);
    }
    if(!isset($config[$i])) {
        die('Configuration for this instance does not exist!' . PHP_EOL);
    }
    if(file_exists(__DIR__.'/vendor/autoload.php')) {
        require_once __DIR__.'/vendor/autoload.php';
    } else {
        die('autoload.php file not found!' . PHP_EOL);
    }
    $ts = new ts3admin($config[$i]['connection']['teamspeakHost'], $config[$i]['connection']['teamspeakPorts']['queryPort']);
    if($ts->succeeded($ts->connect())) {
        print 'Successfully connected!' . PHP_EOL;
        if($ts->succeeded($ts->login($config[$i]['connection']['teamspeakLogin'], $config[$i]['connection']['teamspeakPass']))) {
            print 'Successfully logged in!' . PHP_EOL;
            if($ts->succeeded($ts->selectServer($config[$i]['connection']['teamspeakPorts']['voicePort'], 'port', 1))) {
                print 'Server selected successfully!' . PHP_EOL;
                if($ts->succeeded($ts->setName($config[$i]['settings']['botName']))) {
                    print 'Name seted successfully!' . PHP_EOL;
                    $whoAmI = $ts->getElement('data', $ts->whoAmI());
                    $ts->clientMove($whoAmI['client_id'], $config[$i]['settings']['channelId']);
                    print 'Channel switched successfully!' . PHP_EOL;
                    switch ($i) {
                        case 1:
                            $serverGroupList = $ts->getElement('data', $ts->serverGroupList());
                            foreach ($serverGroupList as $group) {
                                $icon = $ts->serverGroupGetIconBySGID($group['sgid']);
                                if($icon['data'] != '') {
                                    $dIcon = base64_decode($icon['data']);
                                    file_put_contents(__DIR__.'/cache/icons/'.$group['sgid'].'.png', $dIcon);
                                }
                            }
                            break;
                        case 2:
                            $clientList = $ts->getElement('data', $ts->clientList('-uid'));
                            foreach ($clientList as $client) {
                                if($client['client_type'] == 0) {
                                    $avatar = $ts->clientAvatar($client['client_unique_identifier']);
                                    if($avatar['data'] == '' && file_exists(__DIR__.'/cache/avatars/'.$client['client_database_id'].'.png')) {
                                        unlink(__DIR__.'/cache/avatars/'.$client['client_database_id'].'.png');
                                    }
                                    if($avatar['data'] != '') {
                                        $dAvatar = base64_decode($avatar['data']);
                                        file_put_contents(__DIR__.'/cache/avatars/'.$client['client_database_id'].'.png', $dAvatar);
                                    }
                                }	
                            }
                            sleep(3);
                            break;
                    }
                }
            }
        }
    }
}
