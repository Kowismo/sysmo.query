<?php
class multiFunction {
    static private function convertMemory($size)
	{
		$unit = ['B','KiB','MiB','GiB','TiB','PiB'];
		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	}
    
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $serverInfo = $ts->getElement('data', $ts->serverInfo());
        $onlineClients = $serverInfo['virtualserver_clientsonline'] - $serverInfo['virtualserver_queryclientsonline'];
        
        if($cfg['options']['serverName']['enabled']) {
            $serverName = str_replace(['%online%', '%maxClients%', '%%%'], [$onlineClients, $serverInfo['virtualserver_maxclients'], round($onlineClients/$serverInfo['virtualserver_maxclients']*100)], $cfg['options']['serverName']['name']);
            $getMessage = $mongoDB->botData->findOne(['type' => 'hostMessage']);
            if($getMessage == null) {
                $mongoDB->botData->insertOne(['type' => 'hostMessage', 'message' => 'Welcome to [color=#7c86f9][b]SYSMO.PRO[/b][/color]\nOnline: [b]%online%/%maxClients% %%%%[/b]']);
                $hostMessage = 'Welcome to [color=#7c86f9][b]SYSMO.PRO[/b][/color]\nOnline: [b]%online%/%maxClients% %%%%[/b]';
            } else {
                $hostMessage = str_replace(['%online%', '%maxClients%', '%%%'], [$onlineClients, $serverInfo['virtualserver_maxclients'], round($onlineClients/$serverInfo['virtualserver_maxclients']*100)], $getMessage['message']);
            }
            if($serverInfo['virtualserver_name'] != $serverName) {
                $ts->serverEdit(['virtualserver_name' => $serverName, 'virtualserver_hostmessage' => $hostMessage]);
            }
        }
        
        if($cfg['options']['recordOnline']['enabled']) {
            $getRecord = $mongoDB->botData->findOne(['type' => 'recordOnline']);
            if($getRecord != null) {
                if($getRecord['onlineClients'] < $onlineClients) {
                    $channelData = [
                        'channel_name' => str_replace(['%onlineClients%', '%name%'], [$onlineClients, $ezApp->getNameByNumber($onlineClients, 'person', 'people', 'persons')], $cfg['options']['recordOnline']['channelName']),
                        'channel_description' => str_replace(['%onlineClients%', '%name%', '%recordDate%'], [$onlineClients, $ezApp->getNameByNumber($onlineClients, 'person', 'people', 'persons'), date('d/m/Y H:i')], $cfg['options']['recordOnline']['channelDescription']),
                    ];
                    $mongoDB->botData->updateOne(['type' => 'recordOnline'], ['$set' => ['onlineClients' => $onlineClients, 'recordDate' => date('d/m/Y H:i:s')]]);
                }
            } else {
                $channelData = [
                    'channel_name' => str_replace(['%onlineClients%', '%name%'], [$onlineClients, $ezApp->getNameByNumber($onlineClients, 'person', 'people', 'persons')], $cfg['options']['recordOnline']['channelName']),
                    'channel_description' => str_replace(['%onlineClients%', '%name%', '%recordDate%'], [$onlineClients, $ezApp->getNameByNumber($onlineClients, 'person', 'people', 'persons'), date('d/m/Y H:i')], $cfg['options']['recordOnline']['channelDescription']),
                ];
                $mongoDB->botData->insertOne(['type' => 'recordOnline', 'onlineClients' => $onlineClients, 'recordDate' => date('d/m/Y H:i:s')]);
            }
            if(isset($channelData)) {
                $ts->channelEdit($cfg['options']['recordOnline']['channelId'], $channelData);
            }
        }
        
        if($cfg['options']['todayOnline']['enabled']) {
            $getRecord = $mongoDB->botData->findOne(['type' => 'todayOnline']);
            if($getRecord != null) {
                if($getRecord['recordDate'] != date('d/m/Y')) {
                    $channelData = [
                        'channel_name' => str_replace(['%onlineClients%', '%name%'], [$onlineClients, $ezApp->getNameByNumber($onlineClients, 'person', 'people', 'persons')], $cfg['options']['todayOnline']['channelName']),
                        'channel_description' => str_replace(['%onlineClients%', '%name%', '%recordHour%'], [$onlineClients, $ezApp->getNameByNumber($onlineClients, 'person', 'people', 'persons'), date('H:i:s')], $cfg['options']['todayOnline']['channelDescription']),
                    ];
                    $mongoDB->botData->updateOne(['type' => 'todayOnline'], ['$set' => ['onlineClients' => $onlineClients, 'recordHour' => date('H:i:s'), 'recordDate' => date('d/m/Y')]]);
                } else {
                    if($getRecord['onlineClients'] < $onlineClients) {
                        $channelData = [
                            'channel_name' => str_replace(['%onlineClients%', '%name%'], [$onlineClients, $ezApp->getNameByNumber($onlineClients, 'person', 'people', 'persons')], $cfg['options']['todayOnline']['channelName']),
                            'channel_description' => str_replace(['%onlineClients%', '%name%', '%recordHour%'], [$onlineClients, $ezApp->getNameByNumber($onlineClients, 'person', 'people', 'persons'), date('H:i:s')], $cfg['options']['todayOnline']['channelDescription']),
                        ];
                        $mongoDB->botData->updateOne(['type' => 'todayOnline'], ['$set' => ['onlineClients' => $onlineClients, 'recordHour' => date('H:i:s')]]);
                    }
                }
            } else {
                $channelData = [
                    'channel_name' => str_replace(['%onlineClients%', '%name%'], [$onlineClients, $ezApp->getNameByNumber($onlineClients, 'person', 'people', 'persons')], $cfg['options']['todayOnline']['channelName']),
                    'channel_description' => str_replace(['%onlineClients%', '%name%', '%recordHour%'], [$onlineClients, $ezApp->getNameByNumber($onlineClients, 'person', 'people', 'persons'), date('H:i:s')], $cfg['options']['todayOnline']['channelDescription']),
                ];
                $mongoDB->botData->insertOne(['type' => 'todayOnline', 'onlineClients' => $onlineClients, 'recordHour' => date('H:i:s'), 'recordDate' => date('d/m/Y')]);
            }
            if(isset($channelData)) {
                $ts->channelEdit($cfg['options']['todayOnline']['channelId'], $channelData);
            }
        }
        
        if($cfg['options']['onlineClients']['enabled']) {
            $ts->channelEdit($cfg['options']['onlineClients']['channelId'], ['channel_name' => str_replace(['%onlineClients%', '%name%'], [$onlineClients, $ezApp->getNameByNumber($onlineClients, 'person', 'people', 'persons')], $cfg['options']['onlineClients']['channelName'])]);
        }
        
        if($cfg['options']['clientsVisits']['enabled']) {
            $ts->channelEdit($cfg['options']['clientsVisits']['channelId'], ['channel_name' => str_replace(['%visits%', '%name%'], [$serverInfo['virtualserver_client_connections'], $ezApp->getNameByNumber($serverInfo['virtualserver_client_connections'], 'person', 'people', 'persons')], $cfg['options']['clientsVisits']['channelName'])]);
        } 
        
		if($cfg['options']['serverPacketloss']['enabled']) {
            $rawPacketloss = $serverInfo['virtualserver_total_packetloss_total'];
            $packetloss = round(floatval($rawPacketloss) * 100, 2);
            $ts->channelEdit($cfg['options']['serverPacketloss']['channelId'], ['channel_name' => str_replace(['%packetloss%'], [$packetloss], $cfg['options']['serverPacketloss']['channelName'])]);
        }
        
        if($cfg['options']['serverBytes']['enabled']) {
            $bytesUpload = $serverInfo['connection_bytes_sent_total'];
            if($bytesUpload != 0) {
                $bytesUpload = self::convertMemory($bytesUpload);
            } else {
                $bytesUpload = "0b";
            }
            $bytesDownload = $serverInfo['connection_bytes_received_total'];
            if($bytesDownload != 0) {
                $bytesDownload = self::convertMemory($bytesDownload);
            } else {
                $bytesDownload = "0b";
            }
            $ts->channelEdit($cfg['options']['serverBytes']['channelId'], ['channel_name' => str_replace(['%upload%', '%download%'], [$bytesUpload, $bytesDownload], $cfg['options']['serverBytes']['channelName'])]);
        }
        
        if($cfg['options']['serverPing']['enabled']) {
            $ts->channelEdit($cfg['options']['serverPing']['channelId'], ['channel_name' => str_replace(['%ping%'], [round($serverInfo['virtualserver_total_ping'],2)], $cfg['options']['serverPing']['channelName'])]);
        }

        // ========== ZEIT-FUNKTION ==========
        if(isset($cfg['options']['currentTime'])) {
            $this->updateChannelTime($ts, $cfg, $cfg['options']['currentTime']);
        }
    }

    // ========== ZEIT-FUNKTION ==========
    public function updateChannelTime($ts, $cfg, $timeConfig) {
        if($timeConfig['enabled']) {
            // Deutsche Zeit setzen
            date_default_timezone_set($timeConfig['timezone']);
            
            // Deutsche Zeit für Channel Name
            $germanTime = date($timeConfig['timeFormat']);
            $channelName = str_replace('%time%', $germanTime, $timeConfig['channelName']);
            
            $updateData = ['channel_name' => $channelName];
            
            // Weltzeiten für Beschreibung (falls aktiviert)
            if(isset($timeConfig['showWorldTimes']) && $timeConfig['showWorldTimes']) {
                $worldTimes = '';
                $citiesPerRow = 2; // 2 Städte pro Zeile
                $cityCount = 0;
                
                foreach($timeConfig['cities'] as $city) {
                    date_default_timezone_set($city['timezone']);
                    $cityTime = date($timeConfig['timeFormat']);
                    
                    if($cityCount % $citiesPerRow == 0 && $cityCount > 0) {
                        $worldTimes .= "\n";
                    }
                    
                    $worldTimes .= "[color=#7be24c][b]{$city['name']}:[/b][/color] {$cityTime}";
                    
                    if(($cityCount + 1) % $citiesPerRow != 0) {
                        $worldTimes .= "    [color=#666666]•[/color]    ";
                    }
                    
                    $cityCount++;
                }
                
                // Deutsche Zeit wieder setzen für Server Time
                date_default_timezone_set($timeConfig['timezone']);
                $serverTime = date($timeConfig['timeFormat']);
                
                // Beschreibung erstellen
                $description = str_replace(
                    ['%worldtimes%', '%servertime%'],
                    [$worldTimes, $serverTime],
                    $timeConfig['channelDescription']
                );
                
                $updateData['channel_description'] = $description;
            }
            
            // Channel aktualisieren
            $ts->channelEdit($timeConfig['channelId'], $updateData);
        }
    }
}
?>