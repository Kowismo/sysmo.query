<?php
class helpCommand {
    public function __construct($ts, $clientInfo, $args, $cfg, $mongoDB, $ezApp) {
        foreach($cfg['helpMessage'] as $message) {
            $ts->sendMessage(1, $clientInfo['invokerid'], $message);
        }
    }
}