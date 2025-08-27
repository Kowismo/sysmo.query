<?php
class newUsersToday {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $newUsers = $mongoDB->newUsersToday->find()->toArray();
        $newUsersCount = count($newUsers);
        $desc = $cfg['descriptions']['upHeader'];
        if($newUsersCount > 0) {
            foreach($newUsers as $user) {
                if($user['joinDate'] == date('d/m/Y')) {
                    $desc .= str_replace(['%clientId%', '%joinHour%'], ['[url=client://0/' . $user['clientUniqueIdentifier'] . ']' . $user['clientNickname'] . '[/url]', $user['joinHour']], $cfg['descriptions']['userLine']);
                } else {
                    $mongoDB->newUsersToday->deleteOne(['clientUniqueIdentifier' => $user['clientUniqueIdentifier']]);
                }
            }
        } else {
            $desc .= $cfg['descriptions']['noNewUsers'];
        }
        $desc .= $cfg['descriptions']['downFooter'];
        $channelName = str_replace(['%newUsers%', '%name%'], [$newUsersCount, $ezApp->getNameByNumber($newUsersCount, 'person', 'people', 'persons')], $cfg['channelName']);
        $channelInfo = $ts->getElement('data', $ts->channelInfo($cfg['channelId']));
        if($channelInfo['channel_name'] != $channelName) {
            $ts->channelEdit($cfg['channelId'], ['channel_name' => $channelName]);
        }
        if($channelInfo['channel_description'] != $desc) {
            $ts->channelEdit($cfg['channelId'], ['channel_description' => $desc]);
        }
    }
}