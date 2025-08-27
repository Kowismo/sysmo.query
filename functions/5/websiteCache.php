<?php
class websiteCache {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $jsonData = [
            'clientList' => $ts->getElement('data', $ts->clientList('-uid -away -voice -times -groups -info -icon -country -ip')),
            'serverInfo' => $ts->getElement('data', $ts->serverInfo()),
            'serverGroupList' => $ts->getElement('data', $ts->serverGroupList()),
            'channelGroupList' => $ts->getElement('data', $ts->channelGroupList()),
            'channelList' => $ts->getElement('data', $ts->channelList('-topic -flags -voice -limits -icon -secondsempty')),
            'banList' => $ts->getElement('data', $ts->banList()),
        ];
        file_put_contents('cache/websiteCache.json', json_encode($jsonData));
    }
}