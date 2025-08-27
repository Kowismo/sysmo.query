<?php
class helpCenter {
    public function __construct($ts, $cfg, $mongoDB, $ezApp){
        foreach($cfg['channels'] as $channelId => $item) {
            $channelInfo = $ts->getElement('data', $ts->channelInfo($channelId));
            if($channelInfo['channel_name'] != $item['openName'] && time() > strtotime($item['timeOpen']) && time() < strtotime($item['timeClose'])) {
                $ts->channelEdit($channelId,[
                    'channel_name' => $item['openName'],
                    'channel_maxclients' => 1,
                    'channel_maxfamilyclients' => 1,
                    'channel_flag_maxclients_unlimited'=> 1,
                    'channel_flag_maxfamilyclients_unlimited'=> 1,
                ]);
            } else if($channelInfo['channel_name'] != $item['closeName'] && !(time() > strtotime($item['timeOpen']) && time() < strtotime($item['timeClose']))) {
                $ts->channelEdit($channelId,[
                    'channel_name' => $item['closeName'],
                    'channel_maxclients'=> 0,
                    'channel_maxfamilyclients'=> 0,
                    'channel_flag_maxclients_unlimited'=> 0,
                    'channel_flag_maxfamilyclients_unlimited'=> 0,
                    'channel_flag_maxfamilyclients_inherited'=> 0,
                ]);
            }
        }
    }
}