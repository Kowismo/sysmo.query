<?php
class partnersList {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $i = rand(0, count($cfg['partnersList']) - 1);
        $ts->channelEdit($cfg['channelId'], ['channel_name' => $cfg['partnersList'][$i]]);
    }
}