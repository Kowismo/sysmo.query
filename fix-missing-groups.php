<?php
// fix-missing-groups.php - Unified logging version
require_once('vendor/autoload.php');
require_once('libs/ezApp.class.php'); // We still load the class to avoid errors

$config = require_once 'configs/config.php';
$instanceId = 2; // The bot responsible for groups

// Unified logging function - only console output, let cron handle file redirection
function writeLog($message) {
    $timestamp = date('Y-m-d H:i:s');
    $formatted = "[$timestamp] $message";
    
    // Only output to console - let cron handle file redirection
    echo $formatted . "\n";
}

// Custom function to check if client is in ignored groups
function isInIgnoredGroups($client_groups, $ignoredGroups) {
    foreach($ignoredGroups as $ig) {
        if(in_array((string)$ig, $client_groups)) {
            return true;
        }
    }
    return false;
}

// Helper function to assign a group
function assignGroup($ts, $clientInfo, $groupId, $groupType, $client_groups) {
    // Check if the group is already assigned
    $hasGroup = false;
    foreach($client_groups as $group) {
        if((int)$group === (int)$groupId) {
            $hasGroup = true;
            break;
        }
    }
    
    if($hasGroup) {
        return false; // Already has group
    } else {
        $addResult = $ts->serverGroupAddClient($groupId, $clientInfo['client_database_id']);
        
        // Additional error handling for "duplicate entry"
        if(!$ts->succeeded($addResult) && isset($addResult['errors']) && is_array($addResult['errors'])) {
            $isDuplicate = false;
            foreach($addResult['errors'] as $error) {
                if(strpos($error, 'duplicate entry') !== false) {
                    $isDuplicate = true;
                    break;
                }
            }
            if(!$isDuplicate) {
                $clientName = $clientInfo['client_nickname'] ?? 'unknown';
                writeLog("✗ Failed to add {$groupType} group {$groupId} to {$clientName}");
            }
            return false;
        } else if($ts->succeeded($addResult)) {
            return true; // Successfully added
        }
    }
    return false;
}

// Start main execution
writeLog("=== FIX MISSING GROUPS PROCESS STARTED ===");
writeLog("Target: Logo + Country + Platform groups");

// Establish connection
$ts = new ts3admin($config[$instanceId]['connection']['teamspeakHost'], $config[$instanceId]['connection']['teamspeakPorts']['queryPort']);

if($ts->connect()['success']) {
    if($ts->login($config[$instanceId]['connection']['teamspeakLogin'], $config[$instanceId]['connection']['teamspeakPass'])['success']) {
        if($ts->selectServer($config[$instanceId]['connection']['teamspeakPorts']['voicePort'])['success']) {
            
            writeLog("✓ Successfully connected to TeamSpeak");
            
            // Load configurations
            $countryConfig = $config[$instanceId]['functions']['notifycliententerview']['countryGroup'];
            $platformConfig = $config[$instanceId]['functions']['notifycliententerview']['clientPlatform'];
            
            // Array of ignored groups
            $countryIgnoredGroups = $countryConfig['ignoredGroups'];
            $platformIgnoredGroups = $platformConfig['ignoredGroups'];
            
            // Logo group (like in countryGroup.php)
            $logoGroup = 435; // ID of temporary group with logo
            
            writeLog("Logo Group ID: {$logoGroup}");
            writeLog("Country ignored groups: " . implode(', ', $countryIgnoredGroups));
            writeLog("Platform ignored groups: " . implode(', ', $platformIgnoredGroups));
            
            // Load all clients
            $clients = $ts->clientList('-uid -country -info')['data'];
            
            $stats = [
                'processed' => 0,
                'skipped' => 0,
                'logoGroupAdded' => 0,
                'countryGroupAdded' => 0,
                'platformGroupAdded' => 0,
                'totalAdded' => 0
            ];
            
            writeLog("Found " . count($clients) . " clients to process");
            writeLog("------------------------");
            
            foreach($clients as $client) {
                if($client['client_type'] != 0) {
                    $stats['skipped']++;
                    continue; // Skip Query clients
                }
                
                $clientInfo = $ts->clientInfo($client['clid'])['data'];
                
                // Make sure client_servergroups is a string
                if (!isset($clientInfo['client_servergroups']) || !is_string($clientInfo['client_servergroups'])) {
                    $clientInfo['client_servergroups'] = '';
                }
                
                $client_groups = explode(',', $clientInfo['client_servergroups']);
                $clientName = $clientInfo['client_nickname'] ?? 'unknown';
                $clientCountry = $clientInfo['client_country'] ?? 'unknown';
                
                // Skip clients with group ID 283 (Music bots)
                if(in_array('283', $client_groups)) {
                    $stats['skipped']++;
                    continue;
                }
                
                $stats['processed']++;
                $clientChanged = false;
                
                // 1. Check and add logo group (435)
                if(!isInIgnoredGroups($client_groups, $countryIgnoredGroups)) {
                    // Assign logo group
                    if(assignGroup($ts, $clientInfo, $logoGroup, 'logo', $client_groups)) {
                        $stats['logoGroupAdded']++;
                        $stats['totalAdded']++;
                        $clientChanged = true;
                    }
                    
                    // 2. Assign country-specific group
                    if(isset($clientInfo['client_country']) && isset($countryConfig['options'][$clientInfo['client_country']])) {
                        $countryGroup = $countryConfig['options'][$clientInfo['client_country']];
                        if(assignGroup($ts, $clientInfo, $countryGroup, 'country', $client_groups)) {
                            $stats['countryGroupAdded']++;
                            $stats['totalAdded']++;
                            $clientChanged = true;
                        }
                    }
                }
                
                // 3. Check and add platform group
                if(!isInIgnoredGroups($client_groups, $platformIgnoredGroups)) {
                    if(isset($clientInfo['client_platform']) && isset($platformConfig['options'][$clientInfo['client_platform']])) {
                        $platformGroup = $platformConfig['options'][$clientInfo['client_platform']];
                        if(assignGroup($ts, $clientInfo, $platformGroup, 'platform', $client_groups)) {
                            $stats['platformGroupAdded']++;
                            $stats['totalAdded']++;
                            $clientChanged = true;
                        }
                    }
                }
                
                // Only log when changes were made
                if($clientChanged) {
                    writeLog("✓ Updated groups for: {$clientName} [{$clientCountry}]");
                }
                
                // Progress indicator every 50 clients
                if($stats['processed'] % 50 == 0) {
                    writeLog("→ Progress: {$stats['processed']} clients processed...");
                }
            }
            
            writeLog("------------------------");
            writeLog("=== FINAL RESULTS ===");
            writeLog("Processed clients: {$stats['processed']}");
            writeLog("Total groups added: {$stats['totalAdded']}");
            writeLog("├─ Logo groups: {$stats['logoGroupAdded']}");
            writeLog("├─ Country groups: {$stats['countryGroupAdded']}");
            writeLog("└─ Platform groups: {$stats['platformGroupAdded']}");
            writeLog("Skipped clients: {$stats['skipped']}");
            writeLog("=== FIX MISSING GROUPS COMPLETED ===");
            
        } else {
            writeLog("✗ ERROR: Could not select server");
        }
    } else {
        writeLog("✗ ERROR: Could not login to TeamSpeak");
    }
} else {
    writeLog("✗ ERROR: Could not connect to TeamSpeak");
}
?>