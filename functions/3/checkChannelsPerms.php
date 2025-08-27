<?php
class checkChannelsPerms {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $channelList = $ezApp->channelList();
        foreach($channelList as $item) {
            if(!in_array($item['cid'], $cfg['ignoredChannels'])) {
                $channelPerms = $ts->getElement('data', $ts->channelPermList($item['cid'], true));
                if(!empty($channelPerms)) {
                    $toDelete = [];
                    foreach($channelPerms as $perm) {
                        if(!in_array($perm['permsid'], $cfg['ignoredPerms'])) {
                            $toDelete[] = $perm['permsid'];
                        }
                    }
                    if(!empty($toDelete)) {
                        $ts->channelDelPerm($item['cid'], $toDelete);
                        foreach($ezApp->clientList() as $adminClient) {
                            if($adminClient['client_type'] == 0 && $ezApp->inGroup($cfg['adminsGroups'], $adminClient['client_servergroups'])) {
                                $ts->sendMessage(1, $adminClient['clid'], str_replace(['%channelId%', '%removedPerms%'], ['[url=channelid://' . $item['cid'] . ']' . $item['channel_name'] . '[/url]', implode(', ', $toDelete)], $cfg['messages']['toAdmin']));
                            }
                        }
                    }
                }
            }
        }
    }
}