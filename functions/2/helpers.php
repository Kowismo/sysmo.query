<?php
function botLog($message, $type = "INFO") {
    $logDir = "/home/ts3bot_logs";
    
    // Erstelle das Verzeichnis, falls es nicht existiert
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Logdatei-Namen mit heutigem Datum
    $logFile = $logDir . "/ts3bot_" . date('Y-m-d') . ".txt";
    
    // Formatiere die Nachricht mit Zeitstempel und Typ
    $timestamp = date('Y-m-d H:i:s');
    $formattedMessage = "[$timestamp][$type] $message" . PHP_EOL;
    
    // Schreibe in die Logdatei
    file_put_contents($logFile, $formattedMessage, FILE_APPEND);
}
?>