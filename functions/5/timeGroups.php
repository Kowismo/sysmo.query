<?php
class timeGroups {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $timeGroups = $mongoDB->timeGroups->find()->toArray();
        if(count($timeGroups) > 0) {
            foreach($timeGroups as $item) {
                if($item['timeEnd'] <= time()) {
                    $ts->serverGroupDeleteClient($item['group'], $item['clientDbid']);
                    $mongoDB->timeGroups->deleteOne(['_id' => $item['_id']]);
                }
            }
        }
    }
}