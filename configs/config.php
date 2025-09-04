<?php
return [
   1 => [
       'connection' => [
           'teamspeakHost' => 'ts.sysmo.pro',
           'teamspeakLogin' => 'serveradmin',
           'teamspeakPass' => 'i5RAyzuGgGp9YmKh',
           'teamspeakPorts' => [
               'voicePort' => 9987,
               'queryPort' => 10011,
           ],
       ],  
       'settings' => [
           'botName' => '[#] Teleport',    
           'channelId' => 4,
       ],
       'mongodb' => [
           'srv' => 'mongodb://127.0.0.1:12345/',
           'dbName' => 'sysmopro',
       ],
       'configuration' => [
           'ignoredGroups' => [283,391],
           'registerGroups' => [377],
           'lobbyChannel' => 1,
       ],
       'messages' => [
           'userClanFormat' => '[color=#7be24c]%clanName% ([b]%clanGroup%[/b])[/color]',
           'whenHaveClan' => [
               '[color=#eb4d4d]We detected that you belong to the given clan[/color]: %userClans%',
               'You have been moved to the first channel in the list provided.',
               'More information at: [b][color=#7be24c]!teleports[/color][/b]',
           ],
           'whenNotHaveClan' => [
   		    '[color=#eb4d4d]📢 We recommend to create an myTeamspeak Account so your Identity is safe! [ALT+P and register]. 📢[/color]',
               '[color=#eb4d4d]We detected that you dont belong to any clan.[/color]',
               '[color=#eb4d4d]More information at:[/color] [b][color=#7be24c]!teleports[/color][/b]',
           ],
           'cometsList' => [
               'firstMessage' => '[b]Below is a list of all available teleports:[/b]',
               'clanFormat' => '    :: [b][color=#7be24c]%clanName%[/color][/b] => %clanGroup%',
               'lastMessage' => 'Use the command [b][color=#7be24c]!teleport <name of the clan>/<id of the clan>[/color][/b], to move to the selected guild channel.',
           ],
           'invalidArgs' => '[b][color=#cf2157]Error![/color][/b] More information at: [b][color=#7be24c]!teleports[/color][/b]',
           'invalidComet' => '[b][color=#cf2157]Error![/color][/b] Clan not found! [color=#7be24c][b]Try again![/b][/color]',
           'movedToComet' => '[b][color=#00bf30]Success![/color][/b] Correctly transferred to the selected clan channel!',
           'alreadyOnComet' => '[b][color=#cf2157]Error![/color][/b] You are already on the clan of your choice!',
       ],
   ],
   2 => [
       'connection' => [
           'teamspeakHost' => 'ts.sysmo.pro',
           'teamspeakLogin' => 'serveradmin',
           'teamspeakPass' => 'i5RAyzuGgGp9YmKh',
           'teamspeakPorts' => [
               'voicePort' => 9987,
               'queryPort' => 10011,
           ],
       ],
       'settings' => [
           'botName' => '[#] Worker',
           'channelId' => 4,
       ],
       'mongodb' => [
           'srv' => 'mongodb://127.0.0.1:12345/',
           'dbName' => 'sysmopro',
       ],
       'functions' => [
           'notifyclientmoved' => [
               'adminPoke' => [
                   'enabled' => true,
                   'allChannels' => [124,125],
                   'channels' => [
                       4 => [
                           'pokeGroups' => [6,33,370,390],
                           'ignoredGroups' => [369],
                           'messageType' => 'pw',
                       ],
                       5 => [
                           'pokeGroups' => [6,33,370,390],
                           'ignoredGroups' => [369],
                           'messageType' => 'poke',
                       ],
                   ],
                   'messages' => [
                       'toUserWhenAdmin' => [
                           'Welcome [b][color=#7be24c]%clientNickname%[/color][/b] to the [b]Help Channel[/b]!',
                           'The following administrators have been informed of your stay:',
                           '%adminsList%',
                       ],
                       'toUserWhenNoAdmin' => [
                           'Welcome [b][color=#7be24c]%clientNickname%[/color][/b] to the [b]Help Channel[/b]!',
                           'Currently there is no administrator, come at another time.',
                       ],
                       'toAdmin' => [
                           '[b]Heyo, Wake up![/b]',
                           '%clientId% is waiting for help.',
                           'Click on his nickname, then: [b]Find in the channel tree[/b]',
                       ],
                   ],
               ],
               'adminHelpCount' => [
                   'enabled' => true,
                   'allChannels' => [115,116,117],
                   'adminsGroups' => [6,33,370,390],
               ],
               'clientInfo' => [
                   'enabled' => true,
                   'channelId' => 120,
                   'ignoredGroups' => [283,391],
                   'messages' => [
                       'toUser' => [
                           'Welcome [b][color=#7be24c]%clientNickname%[/color][/b], we got the following information about you:',
   						'  Your IP: [b][color=#7be24c]%clientIP%[/color][/b]',
                           '  Your ID in the Database: [b][color=#7be24c]%clientDatabaseId%[/color][/b]',
                           '  Your ID: [b][color=#7be24c]%clientUniqueIdentifier%[/color][/b]',
   						'  Your myTeamspeak ID: [b][color=#7be24c]%clientMyTeamspeak%[/color][/b]',
                           '  Client Version: [b][color=#7be24c]%clientVersion%[/color][/b]',
                           '  First join: [b][color=#7be24c]%clientCreated%[/color][/b]',
                           '  Number of connections: [b][color=#7be24c]%clientTotalConnections%[/color][/b]',
                           '  Version: [b][color=#7be24c]%clientPlatform%[/color][/b]',
   						'  Data Recv: [b][color=#7be24c]%clientDataReceived%[/color][/b]',
   						'  Data Sent: [b][color=#7be24c]%clientDataSend%[/color][/b]',
                           '  Your country: [b][color=#7be24c]%clientCountry%[/color][/b]',
                       ],
                   ],
               ],
               'privateChannels' => [
                   'enabled' => true,
                   'channelId' => 140,
                   'ignoredGroups' => [369],
                   'registerGroups' => [377],
                   'channels' => [
                       'settings' => [
                           'createUnder' => 55,
                           'passwordLength' => 8,
                           'channelGroup' => 32,
                       ],
                       'main' => [
                           'channelName' => '%i%. Private channel: %clientNickname%',
                           'channelDescription' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n[B]Owner:[/B]\n%clientId%\n\n[b]Date and time of establishment:[/b]\n[color=#7be24c]%channelCreated%[/color]\n[/size][/center][hr][right][size=12]Powered by: [url=https://sysmo.pro][color=#9319bf]SYSMO.PRO[/color][/url]',
                       ],
                       'subChannels' => [
                           'channelName' => 'Subchannel: #%i%',
                           'channelDescription' => '[hr][center][size=10]\n[color=#7be24c][b]Subchannel #%i%[/color]\n[/size][/center][hr][right][size=12]Powered by: [url=https://sysmo.pro][color=#9319bf]SYSMO.PRO[/color][/url]',
                           'channelsCount' => 0,
                       ],
                   ],
                   'messages' => [
                       'noAccess' => [
                           '[b][color=#cf2157]Error![/color][/b] You do not meet the conditions for setting up a private channel, in order to set it up you must meet the conditions given:',
                           '[b]Not be in an ignored group and be verified[/b].'
                       ],
                       'haveChannel' => '[b][color=#cf2157]Error![/color][/b] You already own your private channel!',
                       'channelCreateFailed' => '[b][color=#cf2157]Error![/color][/b] Failed to create a channel! Report it to the server administration.',
                       'toUser' => [
                           'Congratulations [b][color=#7be24c]%clientNickname%![/color][/b]',
                           'You have created your private channel with the number [b][color=#7be24c]%channelNumber%[/color]![/b] The password for it is: [b][color=#7be24c]%channelPassword%[/color][/b]',
                           'We wish you successful discussions!',
                       ],
                   ],
               ],
               'privateChannels2' => [
                   'enabled' => true,
                   'channelId' => 121,
                   'ignoredGroups' => [369],
                   'registerGroups' => [377],
                   'channels' => [
                       'settings' => [
                           'createUnder' => 47,
                           'passwordLength' => 8,
                           'channelGroup' => 32,
                       ],
                       'main' => [
                           'channelName' => '%i%. Private channel: %clientNickname%',
                           'channelDescription' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n[B]Owner:[/B]\n%clientId%\n\n[b]Date and time of establishment:[/b]\n[color=#7be24c]%channelCreated%[/color]\n[/size][/center][hr][right][size=12]Powered by: [url=https://sysmo.pro][color=#9319bf]SYSMO.PRO[/color][/url]',
                       ],
                       'subChannels' => [
                           'channelName' => 'Subchannel: #%i%',
                           'channelDescription' => '[hr][center][size=10]\n[color=#7be24c][b]Subchannel #%i%[/color]\n[/size][/center][hr][right][size=12]Powered by: [url=https://sysmo.pro][color=#9319bf]SYSMO.PRO[/color][/url]',
                           'channelsCount' => 2,
                       ],
                   ],
                   'messages' => [
                       'noAccess' => [
                           '[b][color=#cf2157]Error![/color][/b] You do not meet the conditions for setting up a private channel, in order to set it up you must meet the conditions given:',
                           '[b]Required group: 2 Week Activity and be verified[/b].'
                       ],
                       'haveChannel' => '[b][color=#cf2157]Error![/color][/b] You already own your private channel!',
                       'channelCreateFailed' => '[b][color=#cf2157]Error![/color][/b] Failed to create a channel! Report it to the server administration.',
                       'toUser' => [
                           'Congratulations [b][color=#7be24c]%clientNickname%![/color][/b]',
                           'You have created your private channel with the number [b][color=#7be24c]%channelNumber%[/color]![/b] The password for it is: [b][color=#7be24c]%channelPassword%[/color][/b]',
                           'We wish you successful discussions!',
                       ],
                   ],
               ],
               'clanGroup' => [
                   'enabled' => true,
                   'ignoredGroups' => [],
                   'setChannelGroup' => true,
                   'guestGroup' => 8,
                   'recruitGroup' => 29,
                   'messages' => [
                       'clanGroupAdded' => 'Correctly added to the group - [b][color=#7be24c]%clanName%',
                       'clanGroupRemoved' => 'Correctly removed from the group - [b][color=#7be24c]%clanName%',
                   ],
               ],
               'clanMusicGroup' => [
                   'enabled' => true,
                   'ignoredGroups' => [],
                   'musicAccessGroup' => 372,
                   'messages' => [
                       'musicAccessAdded' => '[color=#7be24c][b]Access to music bots has been added correctly.',
                       'musicAccessRemoved' => '[color=#7be24c][b]Correctly removed access to music bots.',
                   ],
               ],
               'clanRecrutation' => [
                   'enabled' => true,
                   'ignoredGroups' => [],
                   'channelGroups' => [24,18,19,20],
                   'messages' => [
                       'recrutationOn' => [
                           'Welcome [b][color=#7be24c]%clientNickname%![/color][/b]',
                           'The designated people have been informed about your stay on the recruitment channel!',
                       ],
                       'recrutationOff' => [
                           'Welcome [b][color=#7be24c]%clientNickname%![/color][/b]',
                           'Unfortunately but currently recruitment in our clan is closed!',
                           'You are welcome to join us at another time when it happens to be open.'
                       ],
                       'toGuild' => [
                           'Welcome [b][color=#7be24c]%clientNickname%![/color][/b]',
                           'Currently on the guild channel [b][color=#7be24c]%clanName%[/color][/b] someone is waiting to recruit!',
                           'He/she: %clientId%'
                       ],
                       'noUsersToPoke' => [
                           'Welcome [b][color=#7be24c]%clientNickname%![/color][/b]',
                           'Currently there is no one to recruit you! Come at another time.',
                       ],
                   ],
               ],
               'removeClanGroup' => [
                   'enabled' => true,
                   'channelId' => 122,
                   'ignoredGroups' => [],
                   'setChannelGroup' => true,
                   'guestGroup' => 8,
               ],
           ],
           'notifycliententerview' => [
               'countryGroup' => [
                   'enabled' => true,
                   'ignoredGroups' => [283,391],
                   'options' => [
   					'AT' => 309,
   					'AR' => 313,
   					'DZ' => 316,
   					'AZ' => 330,
   					'AM' => 355,
   					'AL' => 367,
   					'BR' => 297,
   					'BY' => 307,
   					'BA' => 318,
   					'BG' => 335,
   					'BE' => 343,
   					'BS' => 351,
   					'CO' => 306,
   					'CZ' => 312,
   					'CN' => 323,
   					'HR' => 327,
   					'CA' => 328,
   					'CU' => 333,
   					'CL' => 334,
   					'DK' => 326,
   					'DO' => 350,
   					'EG' => 325,
   					'EC' => 338,
   					'EE' => 342,
   					'FR' => 300,
   					'FI' => 357,
   					'DE' => 276,
   					'GB' => 304,
   					'GR' => 341,
   					'GE' => 346,
   					'HU' => 324,
   					'IR' => 299,
   					'IE' => 340,
   					'ID' => 365,
   					'IT' => 320,
   					'IL' => 331,
   					'IQ' => 337,
   					'KZ' => 310,
   					'LT' => 329,
   					'LV' => 332,
   					'LU' => 363,
   					'LI' => 368,
   					'MK' => 302,
   					'MX' => 317,
   					'MA' => 347,
   					'MD' => 349,
   					'MT' => 356,
   					'MY' => 359,
   					'ME' => 361,
   					'NL' => 303,
   					'NZ' => 314,
   					'NO' => 360,
   					'OM' => 362,
   					'PL' => 298,
   					'PT' => 305,
   					'PE' => 339,
   					'PK' => 348,
   					'RU' => 296,
   					'RO' => 319,
   					'ES' => 308,
   					'RS' => 311,
   					'CH' => 321,
   					'SK' => 322,
   					'SE' => 336,
   					'SI' => 344,
   					'KR' => 358,
   					'LK' => 364,
   					'TR' => 294,
   					'TW' => 352,
   					'TJ' => 366,
   					'UA' => 295,
   					'US' => 301,
   					'UY' => 345,
   					'AE' => 353,
   					'UZ' => 354,
   					'VE' => 315,
   					'PY' => 398,
   					'MZ' => 402,
   					'SA' => 404,
   					'CR' => 407,
   					'MU' => 408,
   					'VN' => 409,
   					'HK' => 410,
   					'SY' => 411,
   					'BD' => 412,
   					'ZA' => 416,
   					'AU' => 417,
   					'IN' => 418,
   					'CY' => 419,
   					'TN' => 420,
   					'PK' => 421,
   					'QA' => 423,
   					'KW' => 424,
   					'TM' => 425,
   					'JP' => 426,
   					'PH' => 427,
   					'SG' => 428,
   					'KG' => 431,
   					'VG' => 432,
   					'TH' => 433,
   					'HN' => 434,
                   ]
               ],
               'newUsersTodaySave' => [
                   'enabled' => true,
				   
               ],
			   'pendingGroupAssignments' => [
					'enabled' => true,
					'ignoredGroups' => [283, 391],
				],
				'populationTracking' => [
                'enabled' => true,
                'ignoredGroups' => [283, 391],
				],
				'antyVpn' => [
                   'enabled' => false,
                   'ignoredGroups' => [283,391],
                   'ignoredIps' => [],
                   'apiKey' => 'MjE2OTc6bEZsZWF1R3IyQkhxT3o1bVNobHhSVmI4Q25oZkRHUnI=',
                   'messages' => [
                       'toUser' => 'Disable proxy/VPN before joining.',
                   ],
               ],
               'welcomeMessage' => [
                   'enabled' => true,
                   'ignoredGroups' => [283,391],
                   'registerGroups' => [377],
                   'messages' => [
                       'isRegistered' => [
   					    '[b][color=#478eff]⸻⸻⸻⸻⸻⸻  ◎  ⸻⸻⸻⸻⸻⸻[/color][/b]',
                           'Welcome back [b][color=#7be24c]%clientNickname%[/color][/b] on [b][color=#7be24c]SYSMO.PRO[/color][/b]!',
                           ' ',
   						'IP Address: [b][color=#7be24c]%clientIP%[/color][/b]',
   						'Connections: [b][color=#7be24c]%connections%[/color][/b]',
   						'Client UID: [b][color=#7be24c]%clientUID%[/color][/b]',
   						'myTeamspeak ID: [b][color=#7be24c]%clientMyTeamspeak%[/color][/b]',
   						'Version: [b][color=#7be24c]%clientVersion%[/color][/b] on [b][color=#7be24c]%clientPlatform%[/color][/b]',
   						'Country: [b][color=#7be24c]%clientCountry%[/color][/b]',
   						' ',
   						'You first joined [b][color=#7be24c]%created%[/color][/b] ago...',
                           'Thank you for choosing our server! 💕',
   						'[b][color=#478eff]⸻⸻⸻⸻⸻⸻  ◎  ⸻⸻⸻⸻⸻⸻[/color][/b]',
                       ],
                       'isNotRegistered' => [
   					    '[b][color=#478eff]⸻⸻⸻⸻⸻⸻  ◎  ⸻⸻⸻⸻⸻⸻[/color][/b]',
                           'Welcome, [b][color=#7be24c]%clientNickname%[/color][/b]!',
                           ' ',
                           'Our system has detected that [b][color=#7be24c]you are not registered[/color][/b]!',
                           'You must wait [b]** minutes[/b] to use our server to its full capacity/to be verified!',
   						'Before creating a private channel, you need to be verified.',
   						'You are free to use the Public Channel as long, or just create a temporary channel!',
                           ' ',
                           'Thank you for choosing our server! 💕',
   						'[b][color=#478eff]⸻⸻⸻⸻⸻⸻  ◎  ⸻⸻⸻⸻⸻⸻[/color][/b]',
                       ],
                   ],
               ],
               'addGroupByIp' => [
                   'enabled' => true,
                   'ignoredGroups' => [283,391],
                   'options' => [
                       '127.0.0.1' => [283,391],
                   ],
               ],
               'clanConnections' => [
                   'enabled' => true,
                   'ignoredGroups' => [],
               ],
               'privateChannelsTeleport' => [
                   'enabled' => true,
                   'ignoredGroups' => [],
               ],
               'moveToRecrutation' => [
                   'enabled' => true,
                   'ignoredGroups' => [],
               ],
               'clientPlatform' => [
                   'enabled' => true,
                   'ignoredGroups' => [],
                   'options' => [
                       'Windows' => 278,
                       'Linux' => 279,
                       'OS X' => 280,
   					'macOS' => 280,
                       'iOS' => 281,
                       'Android' => 282,
                   ]
               ],
   			'offlineMessages' => [
   			'enabled' => true,
   			'ignoredGroups' => [283,391],
   			   ],
               // ===== NEU: TELEPORT ON CONNECT =====
               'teleportOnConnect' => [
                   'enabled' => true,
                   'ignoredGroups' => [283, 391], // Bots und Admin-Gruppen ausschließen
                   'waitTime' => 1, // Sekunden warten vor Teleport (für Verbindungsstabilität)
                   'showWelcomeMessage' => true, // Willkommensnachricht nach Teleport
                   'showErrorMessages' => true, // Fehlermeldungen bei Problemen
               ],
			'newUserGroups' => [
				'enabled' => true,
				'ignoredGroups' => [283, 391],
				],
			],
           'notifyclientleftview' => [
               'saveConnectionLost' => [
                   'enabled' => true,
                   'ignoredGroups' => [283,391],
               ],
               'checkLeaveMessage' => [
                   'enabled' => true,
                   'ignoredGroups' => [],
                   'adminsGroups' => [6,33,370,390],
                   'banTime' => 7200,
                   'badWords' => ['.ROOT.', '.com','.org','.net','.int','.edu','.gov','.mil','ts3.','.ovh','.xyz','https:#','http:#','nowe ip','zarząd','informacje', 'informacja','zapraszam na ts','ip:','wbijać:','jebać','jebany','jeb się','kurw','spierdol','napierd','wypierd','tsforum','wredna.','wright.blue','bravets','∙vCEO ∙', '∙CEO∙', '∙ROOT∙', '∙Technik∙', '∙HA∙', '∙SSA∙', '∙SA∙', '∙NA∙', '∙SUPPORT∙', '[VIP]', '[BOT]', '[INFO]', '[Youtuber]','[Twitch]', '∙Sponsor∙', '∙Premium∙', '∙vip∙', 'য়দতজঠওত চওঈডদশএতদ', '﷽', '꧂', 'tsforum', 'pizda', 'pizd', 'kutas', 'pindol', 'huj', 'pedał', 'pedzio','japier', 'cipa', 'jew', 'porno', 'pornhub', 'xnxx', 'xhamster', 'redtube', 'brodaci', 'UdpREMIXCrasher', '卐', 'пидорасы', 'Server', 'Admin', 'пофурсетка', 'pofursetka', 'pidar', 'пидар', 'шлюха', 'shlyukha', 'мать'],
                   'messages' => [
                       'toClient' => 'Remove the phrase (%FRAZE%) from your leave message!',
                       'toAdmin' => "[b][color=#cf2157]Attention![/color][/b] User: %clientId% left the server with a blocked phrase in the leave message: [b]%FRAZE%[/b], verify if it's not too bad.",
                   ],
               ],
           ],
           // ===== NEU: CHANNEL GROUP CHANGE TRACKING =====
   		'notifychannelgroupclientadded' => [
               'teleportChannelGroups' => [
                   'enabled' => true,
                   'teleportChannelGroups' => [32, 33, 34, 35, 36], // Head, Admin, Operator, Voice, No Channel (alle +Teleport)
               ],
           ],
           'notifychannelgroupclientdeleted' => [
               'teleportChannelGroups' => [
                   'enabled' => true,
                   'teleportChannelGroups' => [32, 33, 34, 35, 36], // Head, Admin, Operator, Voice, No Channel (alle +Teleport)
               ],
           ],
       ],
   ],
   3 => [
       'connection' => [
           'teamspeakHost' => 'ts.sysmo.pro',
           'teamspeakLogin' => 'serveradmin',
           'teamspeakPass' => 'i5RAyzuGgGp9YmKh',
           'teamspeakPorts' => [
               'voicePort' => 9987,
               'queryPort' => 10011,
           ],
       ],
       'settings' => [
           'botName' => '[#] Guard',
           'channelId' => 4,
       ],
       'mongodb' => [
           'srv' => 'mongodb://127.0.0.1:12345/',
           'dbName' => 'sysmopro',
       ],
       'functions' => [
           'blockRecording' => [
               'enabled' => true,
               'ignoredGroups' => [],
               'messages' => [
                   'toUser' => 'Recording is prohibited on our server!',
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 20],
           ],
           'nickProtection' => [
               'enabled' => true,
               'ignoredGroups' => [391],
               'messages' => [
                   'toUser' => 'Your nickname contains the blocked phrase: %badWord%',
                   'badWords' => ['Adolf', 'Hitler', 'pidar', 'пидар', 'шлюха', 'shlyukha', 'nazizm', 'faszyzm', 'komunizm', 'hitla', 'iOS_Client','CEO', 'য়দতজঠওত চওঈডদশএতদ', '﷽','◦ CEO ◦', '◦ vCEO ◦','◦ Query  ◦','◦ RooT ◦','◦ HSA ◦','◦ SSA ◦','◦ SA ◦','◦ NA ◦','◦ TA ◦','꧂','psie','kurwo','ssie','sci.ek','kur.wa','sciek','.pl','.eu','.net','.com','huj','cipa','pizda','kutas','hitler','chuj','[QUERY]','[ROOT]','[HSA]','[SSA]','[SA]','[jSA]','[JSA]','[tSA]','[TSA]','[NA]','Właściciel','tsforum', 'egcforum', 'holokaust', 'jebać', 'masturbacja', 'konia', 'walenie', 'jew', '卐', 'пидорасы', 'Server', 'Admin', 'admin', 'пофурсетка', 'pofursetka', 'мать', 'руский'],
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 20],
           ],
           'countryChecker' => [
               'enabled' => false,
               'ignoredGroups' => [],
               'allowedIps' => ['130.61.61.155', '2603:c020:8005:257e:9aeb:8a44:d6b2:a3b0'],
               'blockedCountries' => ['AM','IN','CN','HK','TR','KZ','PK','IR','IQ','LK','FI','SI','SY','KR'],
               'messages' => [
                   'toUser' => 'Your country is banned from us: %blockedCountry%',
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 20],
           ],
   		'checkUserPerms' => [
               'enabled' => true,
               'ignoredGroups' => [283,391],
               'adminsGroups' => [6,33,370,390],
               'allowedPerms' => ['i_icon_id'],
               'messages' => [
                   'toAdmin' => '[b][color=#cf2157]Attention![/color][/b] User %clientId% had unauthorized permissions: [b]%removedPerms%[/b]',
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 20],
           ],
           'saveClients' => [
               'enabled' => true,
               'ignoredGroups' => [6,283,391],
               'coinsAmount' => 0.05,
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],
           ],
			'cleanup' => [
					'enabled' => false,
					'ignoredGroups' => [391, 283],
					'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 10], // Läuft nur kurz
			],
           'changeStatusIcon' => [
               'enabled' => true,
               'ignoredGroups' => [],
               'options' => [
                   'online' => [
                       'icon' => 653887835,
                   ],
                   'idle' => [
                       'icon' => 3521682962,
                       'time' => 180,
                   ],
                   'afk' => [
                       'icon' => 2420307281,
                       'time' => 1800,
                   ],
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 5],
           ],
           'clientLevels' => [
               'enabled' => true,
               'ignoredGroups' => [6,33,390,283,391],
               'levels' => [
                   1 => ['groupId' => 9, 'timeSpent' => 3600],
                   2 => ['groupId' => 10, 'timeSpent' => 10800],
                   3 => ['groupId' => 11, 'timeSpent' => 21600],
                   4 => ['groupId' => 12, 'timeSpent' => 43200],
                   5 => ['groupId' => 13, 'timeSpent' => 86400],
   				6 => ['groupId' => 14, 'timeSpent' => 172800],
   				7 => ['groupId' => 264, 'timeSpent' => 259200],
   				8 => ['groupId' => 15, 'timeSpent' => 345600],
   				9 => ['groupId' => 265, 'timeSpent' => 432000],
   				10 => ['groupId' => 266, 'timeSpent' => 518400],
   				11 => ['groupId' => 17, 'timeSpent' => 1209600],
   				12 => ['groupId' => 263, 'timeSpent' => 1814400],
   				13 => ['groupId' => 18, 'timeSpent' => 2592000],
   				14 => ['groupId' => 19, 'timeSpent' => 5184000],
   				15 => ['groupId' => 20, 'timeSpent' => 7776000],
   				16 => ['groupId' => 21, 'timeSpent' => 10368000],
   				17 => ['groupId' => 22, 'timeSpent' => 12960000],
   				18 => ['groupId' => 23, 'timeSpent' => 15552000],
   				19 => ['groupId' => 24, 'timeSpent' => 18144000],
   				20 => ['groupId' => 25, 'timeSpent' => 20736000],
   				21 => ['groupId' => 26, 'timeSpent' => 23328000],
   				22 => ['groupId' => 27, 'timeSpent' => 25920000],
   				23 => ['groupId' => 28, 'timeSpent' => 28512000],
   				24 => ['groupId' => 29, 'timeSpent' => 31536000],
   				25 => ['groupId' => 30, 'timeSpent' => 63072000],
   				26 => ['groupId' => 31, 'timeSpent' => 94608000],
               ],
               'messages' => [
                   'firstLevel' => '[b][color=#00bf30]Congratulations![/color][/b] You have been promoted to: [b][color=#7be24c]New![/color][/b] From now on, you will gain more and more activity as time goes by!',
   			    'nextLevels' => '[b][color=#00bf30]Congratulations![/color][/b] You have been promoted,[b][color=#7be24c] congrats![/color][/b]',
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],
           ],
           'autoRegister' => [
               'enabled' => true,
               'ignoredGroups' => [391,283],
               'timeSpent' => 21600,
               'groupId' => 377,
               'messages' => [
                   'toUser' => [
                       'Congratulations [b][color=#7be24c]%clientNickname%![/color][/b]',
                       'You have already spent a total of [b][color=#7be24c]60 minutes[/color][/b] on our server, for which we thank you very much!',
                       'From now on you have a little more authority on our server, so you can feel more at ease,',
                       '[b]We wish you successful conversations![/b]',
                   ],
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],
           ],
           'checkChannelsPerms' => [
               'enabled' => true,
               'ignoredChannels' => [],
               'adminsGroups' => [6,33,370,390],
               'ignoredPerms' => ['i_channel_join_power','i_channel_needed_subscribe_power','i_channel_subscribe_power','i_icon_id','i_channel_needed_modify_power','i_client_poke_power', 'i_client_needed_talk_power','i_channel_needed_delete_power','i_channel_needed_join_power','i_channel_needed_description_view_power','i_channel_description_view_power', 'i_client_needed_move_power','b_client_channel_textmessage_send','i_channel_needed_permission_modify_power'],
               'messages' => [
                   'toAdmin' => '[b][color=#cf2157]Attention![/color][/b] The channel %channelId% had unauthorized permissions: [b]%removedPerms%[/b].',
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],
           ],
           'publicChannels' => [
               'enabled' => true,
               'channelName' => 'Public Talk %i% 💬',
   'channelDescription' => "[center]
[size=10][b][color=#7be24c]Check our FAQ[/color][/b] [url=https://sysmo.pro/faq][Click][/url][size=8][/size][size=8][color=#7be24c]|[/color] Often asked questions.[/size]

[size=10][b][color=#7be24c]Check our Dashboard[/color][/b] [url=https://sysmo.pro/dashboard/home][Click][/url][size=8] (Add groups etc.) [/size]

[size=10][b][color=#7be24c]Create Private Channel [/color][/b] [size=8][url=channelID://140]🔛 › Create a Private Channel [GO HERE][/url] [color=#7be24c]|[/color] Need to be in Verified Group, comes automatically when active. [color=#7be24c]|[/color] Temp channels are valid for 36hrs without use. [color=#7be24c]|[/color] Private Channels are valid for 14 days without use.[/size]

[size=10][b][color=#7be24c]Check Changelog: [url=https://sysmo.pro/changelog]Open[/url][/color][/b][size=8] [color=#7be24c]|[/color] See all our new updates.[/size]

[size=10][b][color=#7be24c] Download our recommend Teamspeak Style: [url=https://sysmo.pro/assets/Default%20Skin%20Mod%20Dark%201.0.4%20-%20Save%20the%20eyes.ts3_style]Download[/url][/color][/b][size=8] [color=#7be24c]|[/color] Dark Theme. [color=#7be24c]|[/color] Looks very good.[/size]

[size=10][b][color=#7be24c]You might be connected via an IP address or an old link.
To make sure your bookmark uses our official domain:[/color] [url=ts3server://ts.sysmo.pro]sysmo[/url]

[size=8]➤ To save us as a favorite:
1. Press Ctrl + B in Teamspeak
2. Click \"Add Bookmark\"
3. Confirm[/b][/size]

[size=9][b][color=#7be24c]Need help or have questions? Contact \"Kowismo\":[/color][/b] [url=client://0/WtWgpfS8UE18SW7yGXm3r1yoO/c=][Click][/url]

[hr]
[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]
[/center][hr]",
               'channels' => [
                   21 => [
                       'clientsLimit' => 2,
                       'minChannels' => 3,
                       'maxChannels' => 20,
                   ],
                   25 => [
                       'clientsLimit' => 3,
                       'minChannels' => 3,
                       'maxChannels' => 20,
                   ],
                   29 => [
                       'clientsLimit' => 4,
                       'minChannels' => 3,
                       'maxChannels' => 20,
                   ],
                   33 => [
                       'clientsLimit' => 5,
                       'minChannels' => 3,
                       'maxChannels' => 20,
                   ],
   				102249 => [
                       'clientsLimit' => 0,
                       'minChannels' => 3,
                       'maxChannels' => 20,
                   ],
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 5],  
           ],
           
   		// KORRIGIERTE VOTING API FUNKTION:
           'clientVotingAPI' => [
               'enabled' => true,
               'ignoredGroups' => [], // LEER! Damit deine Votes gespeichert werden
               'ignoredGroupsForRewards' => [6,283, 391], // NEU! Keine Vote-Gruppen für Admins
               'debug' => true,
               
               'teamspeakServers' => [
                   'enabled' => true,
                   'apiKey' => 'zWC7khlHSafUmX2OoBlD5ydvFQxu2iFxBTM',
                   'serverId' => '15345',
               ],
               
               'topg' => [
                   'enabled' => true,
                   'serverId' => '652338',
                   'category' => 'ts3-server',
                   'postbackUrl' => 'https://sysmo.pro/voting/topg_callback.php',
               ],
               
               'voteLevels' => [
                   1 => [
                       'votesRequired' => 1,
                       'groupId' => 267,
                       'groupName' => 'First Voter ⭐'
                   ],
                   2 => [
                       'votesRequired' => 10,
                       'groupId' => 268,
                       'groupName' => '10 Votes 🌟'
                   ],
                   3 => [
                       'votesRequired' => 25,
                       'groupId' => 269,
                       'groupName' => '25 Votes 💫'
                   ],
                   4 => [
                       'votesRequired' => 50,
                       'groupId' => 270,
                       'groupName' => '50 Votes ✨'
                   ],
                   5 => [
                       'votesRequired' => 100,
                       'groupId' => 271,
                       'groupName' => '100 Votes 🌠'
                   ],
                   6 => [
                       'votesRequired' => 125,
                       'groupId' => 272,
                       'groupName' => '125 Votes 🎖️'
                   ],
                   7 => [
                       'votesRequired' => 150,
                       'groupId' => 273,
                       'groupName' => '150 Votes 🏆'
                   ],
                   8 => [
                       'votesRequired' => 200,
                       'groupId' => 274,
                       'groupName' => '200 Votes 👑'
                   ],
               ],
               
               'messages' => [
                   'voteLevelUp' => [
                       '[b][color=#00bf30]🎉 Congratulations! 🎉[/color][/b]',
                       'You have reached [b][color=#7be24c]%votes% votes[/color][/b] and received the group [b][color=#7be24c]%groupName%[/color][/b]!',
                       'Thank you for supporting our server by voting!',
                       '[b][color=#7be24c]Keep voting to unlock more rewards! 💪[/color][/b]',
                   ],
                   'voteReceived' => [
                       '[b][color=#00bf30]🗳️ Vote received! 🗳️[/color][/b]',
                       'Thank you [b][color=#7be24c]%username%[/color][/b] for voting on [b][color=#7be24c]%source%[/color][/b]!',
                       'Your vote helps our server grow! 💕',
                   ],
               ],
               
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],
           ],
       ],
   ],
   4 => [
       'connection' => [
           'teamspeakHost' => 'ts.sysmo.pro',
           'teamspeakLogin' => 'serveradmin',
           'teamspeakPass' => 'i5RAyzuGgGp9YmKh',
           'teamspeakPorts' => [
               'voicePort' => 9987,
               'queryPort' => 10011,
           ],
       ],
       'settings' => [
           'botName' => '[#] Updater',
           'channelId' => 4,
       ],
       'mongodb' => [
           'srv' => 'mongodb://127.0.0.1:12345/',
           'dbName' => 'sysmopro',
       ],
       'functions' => [
           'resetMonthlyYear' => [
               'enabled' => true,
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 30, 'seconds' => 0],
           ],
           'multiFunction' => [
               'enabled' => true,
               'options' => [			      
				 'currentTime' => [
                    'enabled' => true,
                    'channelId' => 6, // DEINE CHANNEL ID HIER EINTRAGEN
                    'channelName' => '[cspacer]🕐 Time: %time%',
                    'channelDescription' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n[b][color=#7be24c]🌍 WORLD TIME ZONES 🌍[/color][/b]\n\n%worldtimes%\n\n[b][color=#7be24c]Server Time (Germany):[/color][/b] [b][color=#7be24c]%servertime%[/color][/b]\n[/size][/center][hr]',
                    'timeFormat' => 'H:i',
                    'timezone' => 'Europe/Berlin',
                    'showWorldTimes' => true,
                    'cities' => [
                        ['name' => '🇩🇪 Berlin', 'timezone' => 'Europe/Berlin'],
                        ['name' => '🇬🇧 London', 'timezone' => 'Europe/London'],
                        ['name' => '🇫🇷 Paris', 'timezone' => 'Europe/Paris'],
                        ['name' => '🇺🇸 New York', 'timezone' => 'America/New_York'],
                        ['name' => '🇺🇸 Los Angeles', 'timezone' => 'America/Los_Angeles'],
                        ['name' => '🇯🇵 Tokyo', 'timezone' => 'Asia/Tokyo'],
                        ['name' => '🇦🇺 Sydney', 'timezone' => 'Australia/Sydney'],
                        ['name' => '🇷🇺 Moscow', 'timezone' => 'Europe/Moscow'],
                        ['name' => '🇦🇪 Dubai', 'timezone' => 'Asia/Dubai'],
                        ['name' => '🇨🇳 Beijing', 'timezone' => 'Asia/Shanghai'],
                    ],
                ],
                   'serverName' => [
                       'enabled' => true,
                       'name' => '🔥 sysmo › [⚙️ Dashboard 🎯 Clans 📊 Stats 🏆 MVPs] 🔥',
                   ],
                   'recordOnline' => [
                       'enabled' => true,
                       'channelName' => '[cspacer]🏆 Online record: %onlineClients%',
                       'channelDescription' => "[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr]\n[center][size=12]The server's online record is [b][color=#7be24c]%onlineClients% %name%.[/color][/b][/size]\n[size=12]It has been established: [b][color=#7be24c]%recordDate%[/color][/b]\n[/size][/center]\n[hr]",
                       'channelId' => 89,
                   ],
                   'todayOnline' => [
                       'enabled' => true,
                       'channelName' => '[cspacer]🏆️ Online record today: %onlineClients%',
                       'channelDescription' => "[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr]\n[center][size=12] The server's record for the day is [b][color=#7be24c]%onlineClients% %name%.[/color][/b][/size]\n[size=12]It was established at the hour: [b][color=#7be24c]%recordHour%[/color][/b]\n[/size][/center]\n[hr]",
                       'channelId' => 88,
                   ],
                   'onlineClients' => [
                       'enabled' => true,
                       'channelName' => '[cspacer]✨ Online Users: %onlineClients%',
                       'channelId' => 92,
                   ],
                   'clientsVisits' => [
                       'enabled' => true,
                       'channelName' => '[cspacer] 🔆 Client Connections: %visits%',
                       'channelId' => 93,
                   ],
                   'serverPacketloss' => [
                       'enabled' => true,
                       'channelName' => '[cspacer] ✅ Average Packet Loss: %packetloss%%',
                       'channelId' => 94,
                   ],
                   'serverPing' => [
                       'enabled' => true,
                       'channelName' => '[cspacer] 🌐 Average Ping: %ping%ms',
                       'channelId' => 95,
                   ],
                   'serverBytes' => [
                       'enabled' => true,
                       'channelName' => '[cspacer]📂 U: %upload% | D: %download%',
                       'channelId' => 433,
                   ],
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],
           ],
   		'scanTeleportGroups' => [
   			'enabled' => true,
   			'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],
   		],
   		 'debugTeleportScanner' => [
               'enabled' => false,
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 30],
           ],
           'adminsList' => [
               'enabled' => true,
               'channels' => [
                   123 => [
                       'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]',
                       'adminsGroups' => [6,33,370,390],
                       'downFooter' => '[/size][/center][hr][right][size=12]Powered by: [url=https://sysmo.pro][color=#9319bf]SYSMO.PRO[/color][/url]',
                   ],
               ],
               'descriptions' => [
                   'status' => [
                       'userOnline' => '[color=#00bf30][b]ONLINE[/b][/color]',
                       'userAway' => '[color=#ffed00][b]AWAY[/b][/color]',
                       'userOffline' => '[color=#cf2157][b]OFFLINE[/b][/color]',
                   ],
                   'main' => [
                       'groupLine' => '\n[size=12][b][color=#7be24c]%groupName%[/color][/b][/size]\n',
                       'allCountLine' => 'Everyone in the group: [b][color=#7be24c]%allInGroup%[/color][/b] %name%\n',
                       'onlineCountLine' => 'Currently online: [b][color=#7be24c]%adminsOnline%[/color][/b] %name%\n',
                       'userLine' => '\n%clientId%\n[b]%adminStatus%[/b] from: [b]%userTime%[/b]\n',
                       'noAdmins' => '\n[color=#7be24c]Currently there is no one in the group![/color]\n',
                   ],
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],
           ],
           'adminsOnlineList' => [
               'enabled' => true,
               'channels' => [
                   90 => [
                       'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n',
                       'channelName' => '[cspacer] 💂 Staff Online: %count%',
                       'adminsGroups' => [6,33,370,390],
                       'ignoredGroups' => [],
                       'downFooter' => '[/size][/center][hr][right][size=12]Powered by: [url=https://sysmo.pro][color=#9319bf]SYSMO.PRO[/color][/url]',
                   ],
               ],
               'descriptions' => [
                   'status' => [
                       'userOnline' => '[color=#00bf30][b]ONLINE[/b][/color]',
                       'userAway' => '[color=#ffed00][b]AWAY[/b][/color]',
                   ],
                   'main' => [
                       'userLine' => '[b][color=#7be24c]%groupName%[/color][/b] %clientId% is: [b]%adminStatus%[/b] from: [b]%userTime%[/b]\n',
                       'noAdmins' => '[color=#7be24c]Currently there is no admin![/color]\n',
                   ],
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],
           ],
           'helpCenter' => [
               'enabled' => true,
               'channels' => [
                   124 => [
                       'openName' => '• Help Center [GO HERE]',
                       'closeName' => '• Help Center [ Closed ]',
                       'timeOpen' => '23:00',
                       'timeClose' => '23:00',
                   ],
                   125 => [
                       'openName' => '∙ Help Center [ Premium ]',
                       'closeName' => '∙ Help Center [ Closed ]',
                       'timeOpen' => '23:00',
                       'timeClose' => '23:00',
                   ]
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 10],
           ],
           'privateChannelsChecker' => [
               'enabled' => true,
               'daysExpire' => 14,
               'invalidWords' => ["ʜsᴀ", "ᴏwɴᴇʀ", "ᴏẄпᴇʀ", "ᴏvvɴᴇʀ", "ᴛᴇᴄʜṇɪᴋ", "ᴛᴇᴄʜɴɪᴋ", "CEO", "য়দতজঠওত চওঈডদশএতদ", "﷽","◦ CEO ◦", "◦ vCEO ◦","◦ Query  ◦","◦ RooT ◦","◦ HSA ◦","◦ SSA ◦","◦ SA ◦","◦ NA ◦","◦ TA ◦","꧂","psie","kurwo","ssie","sci.ek","kur.wa","sciek","]","[",".pl",".eu",".net",".com","huj","cipa","pizda","kutas","hitler","[QUERY]","[ROOT]","[HSA]","[SSA]","[SA]","[jSA]","[JSA]","[tSA]","[TSA]","[NA]","Właściciel","tsforum", "NIGER", 'niger', 'hitler', 'auschwitz', 'nyger', 'NYGER', 'pedal', 'gruby', 'simp', 'ciota', 'cioto', 'cioteczny', 'pedalski', 'pedaly', 'nigerki', 'nigusie', 'Mondey', 'Devanger', '卐', 'пидорасы', 'Server', 'Admin', 'pidar', 'пидар', 'шлюха', 'shlyukha', 'пофурсетка', 'pofursetka'],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],	
           ],
           'privateChannelsChecker2' => [
               'enabled' => true,
               'daysExpire' => 7,
               'invalidWords' => ["ʜsᴀ", "ᴏwɴᴇʀ", "ᴏẄпᴇʀ", "ᴏvvɴᴇʀ", "ᴛᴇᴄʜṇɪᴋ", "ᴛᴇᴄʜɴɪᴋ", "CEO", "য়দতজঠওত চওঈডদশএতদ", "﷽","◦ CEO ◦", "◦ vCEO ◦","◦ Query  ◦","◦ RooT ◦","◦ HSA ◦","◦ SSA ◦","◦ SA ◦","◦ NA ◦","◦ TA ◦","꧂","psie","kurwo","ssie","sci.ek","kur.wa","sciek","]","[",".pl",".eu",".net",".com","huj","cipa","pizda","kutas","hitler","[QUERY]","[ROOT]","[HSA]","[SSA]","[SA]","[jSA]","[JSA]","[tSA]","[TSA]","[NA]","Właściciel","tsforum", "NIGER", 'niger', 'hitler', 'auschwitz', 'nyger', 'NYGER', 'pedal', 'gruby', 'simp', 'ciota', 'cioto', 'cioteczny', 'pedalski', 'pedaly', 'nigerki', 'nigusie', 'Mondey', 'Devanger', '卐', 'пидорасы', 'Server', 'Admin', 'pidar', 'пидар', 'шлюха', 'shlyukha', 'пофурсетка', 'pofursetka'],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],	
           ],
           'newUsersToday' => [
               'enabled' => true,
               'channelName' => '[cspacer]👋 New Users: %newUsers%',
               'channelId' => 91,
               'descriptions' => [
                   'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n',
                   'userLine' => '%clientId% joined at [b][color=#7be24c]%joinHour%[/color][/b]\n',
                   'noNewUsers' => 'No new users joined today!\n',
                   'downFooter' => '[/size][/center][hr][right][size=12]Powered by: [url=https://sysmo.pro][color=#9319bf]SYSMO.PRO[/color][/url]',
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 5, 'seconds' => 0],
           ],
			'populationStatistics' => [
				'enabled' => true,
				'channelId' => 13, // Channel ID aus deinem Screenshot
				'name' => '[cspacer]📊 Population Statistics 📊',
				'topic' => 'Record: %g_record% Date: %recordDate%',
				'description' => '%stats%',
				'descriptionDays' => 30, // Wie viele Tage in der Tabelle
				'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 00],
			],
           'partnersList' => [
               'enabled' => false,
               'channelId' => 111,
               'partnersList' => [
                   "[cspacer] 👑 Powered by SYSMO.PRO 👑",
                   "[cspacer] 🗣️ Powered by Teamspeak.com 🗣️",
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 00],
           ],
           'adminsStatus' => [
               'enabled' => true,
               'adminsGroups' => [6,33,370,390],
               'channelName' => '› %clientNickname% [%adminStatus%]',
               'descriptions' => [
                   'status' => [
                       'userOnline' => 'ONLINE',
                       'userAway' => 'AWAY',
                       'userOffline' => 'OFFLINE',
                   ],
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],
           ],
           'createClan' => [
               'enabled' => true,
               'floodWhenCreate' => 99999,
               'floodAfterCreate' => 7,
               'templates' => [
                   'PREMIUM' => [
                       'createOrder' => 82,
                       'sourceGroup' => 396,
                       'channelGroup' => 18,
                       'musicBots' => [
                           'enabled' => true,
                           'groupId' => 283,
                           'channelId' => 4,
                           'musicBotCount' => 2,
                           'sendCommands' => [
                               '!name "%clanName% #%i%"',
                               '!channel %channelId%'
                           ],
                       ],
                       'channels' => [
                           [
                               'channelName' => '[cspacer%generatedString%]» ―――――• %clanName% •―――――  «',
                               'channelDescription' => '[hr][center]\n[img]https://i.imgur.com/sFjHhPM.gif[/img]\n[/center][hr][center][size=10]\n[B]Owner:[/B]\n%clientId%\n\n[b]Name of the clan:[/b] \n[color=#7be24c]%clanName%[/color]\n\n[b]Date and time of establishment:[/b] \n[color=#7be24c]%channelCreated%[/color]\n[/center][hr][right][size=12]Powered by: [url=https://mscode.xyz][color=#9319bf]MSCODE.XYZ[/color][/url]',
                               'channelOption' => 'numeration',
                           ],
                           [
                               'channelName' => '[cspacer%generatedString%]» Status: 0/0 | 0% «',
                               'channelDescription' => '',
                               'channelOption' => 'online',
                           ],
                           [
                               'channelName' => '[cspacer%generatedString%]» Teleport %clanName% «',
                               'channelDescription' => '',
                               'channelOption' => 'comet',
                           ],
                           [
                               'channelName' => '[spacer%generatedString%-1]-..',
                               'channelDescription' => '',
                           ],
                           [
                               'channelName' => '[cspacer%generatedString%]💻 Guild Management 💻',
                               'channelDescription' => '',
                               'subChannels' => [
                                   [
                                       'channelName' => '• Change Rank: %clanName% ⚙️',
                                       'channelDescription' => 'Join or move User in this Channel to add/remove the Guild group!',
                                       'channelOption' => 'groupChanger',
                                   ],
                                   [
                                       'channelName' => '• Change Rank: Access to Musicbots ⚙️',
                                       'channelDescription' => 'Join this Channel to add/remove the Music Bot Access group!',
                                       'channelOption' => 'musicChanger',
                                   ],
                               ],
                           ],
                           [
                               'channelName' => '[spacer%generatedString%-2]-..',
                               'channelDescription' => '',
                           ],
                           [
                               'channelName' => '[cspacer%generatedString%]💼 Leader Channels 💼',
                               'channelDescription' => '',
                               'subChannels' => [
                                   [
                                       'channelName' => '• %clanName% - Leadership #1',
                                       'channelDescription' => '',
                                   ],
                                   [
                                       'channelName' => '• %clanName% - Leadership #2',
                                       'channelDescription' => '',
                                   ],
                               ],
                           ],
   						[
                               'channelName' => '[spacer%generatedString%-3]-..',
                               'channelDescription' => '',
                           ],
                           [
                               'channelName' => '[cspacer%generatedString%]📣 Lobby Channels 📣',
                               'channelDescription' => '',
                               'channelOption' => 'main',
                               'subChannels' => [
                                   [
                                       'channelName' => '• %clanName% - Channel #1',
                                       'channelDescription' => '',
                                   ],
                                   [
                                       'channelName' => '• %clanName% - Channel #2',
                                       'channelDescription' => '',
                                   ],
                                   [
                                       'channelName' => '• %clanName% - Channel #3',
                                       'channelDescription' => '',
                                   ],
                                   [
                                       'channelName' => '• %clanName% - Channel #4',
                                       'channelDescription' => '',
                                   ],
                                   [
                                       'channelName' => '• %clanName% - Channel #5',
                                       'channelDescription' => '',
                                   ],
                               ],
                           ],
   						[
                               'channelName' => '[spacer%generatedString%-55]-..',
                               'channelDescription' => '',
                           ],
                           [
                               'channelName' => '[cspacer%generatedString%]🪖 Recruitment Channels 🪖‍',
                               'channelDescription' => '',
                               'subChannels' => [
                                   [
                                       'channelName' => '• %clanName% - Recruitment',
                                       'channelDescription' => '',
                                   ],
                                   [
                                       'channelName' => '• %clanName% - Waiting Room',
                                       'channelDescription' => '',
                                       'channelOption' => 'recrutation',
                                   ],
                               ],
                           ],
                           [
                               'channelName' => '[*spacer%clanName%]═══',
                               'channelDescription' => '',
                           ],
                       ],
                   ],
               ],
           ],
           'clanChecker' => [
               'enabled' => true,
               'floodWhenDelete' => 99999,
               'floodAfterDelete' => 7,
               'musicBots' => [
                   'enabled' => true,
                   'channelId' => 4,
                   'sendCommands' => [
                       '!name "%generatedName%"',
                       '!channel %channelId%'
                   ],
               ],
               'checkClanNumbers' => false,
               'templates' => [],
           ],
           'onlineFromGroup' => [
               'enabled' => true,
               'channels' => [
                   130 => [
                       'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][size=10]\n',
                       'channelName' => '[cspacer]🎵 Music Bots Online: %online% ',
                       'groupId' => 283,
                       'downFooter' => '[/size][hr][right][size=12]Powered by: [url=https://sysmo.pro][color=#9319bf]SYSMO.PRO[/color][/url]',
                   ],
               ],
               'descriptions' => [
                   'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[size=12]Online status: [b][color=#7be24c]%clanName%[/color][/b]\n[/size][/center][hr]\n[center][table][tr][th][center]Time spent:[/center][/th][th] [/th][th][center]Rekord Online: [/center][/th][th] [/th][th][center]AFK time spent:[/center][/th][th] [/th][th][center]Number of Connections:[/center][/th][th] [/th][th][center]Number of Points: [/center][/th][/tr][tr][td][center][color=#7be24c]%timeSpent%[/color][/center][/td][td] [/td][td][center][color=#7be24c]%recordOnline% %recordName%[/color][/center][/td][td] [/td][td][center][color=#7be24c]%timeSpentAfk%[/color][/center][/td][td]   [/td][td][center][color=#7be24c]%connections% %connectionsName%[/color][/center][/td][td] [/td][td][center][color=#7be24c]%points% %pointsName%[/color][/center][/td][/tr][/table]\n[/center][hr][size=10]\n',
                   'limitReached' => '\There are too [color=#7be24c][b]many people in your clan[/b][/color]!To see a list of people in the clan go to the panel. [url=https://sysmo.pro]Click to go to the panel![/url]!',
                   'onlineClient' => '%clientId%: [color=#00bf30][b]ONLINE[/b][/color]\n',
                   'offlineClient' => '[b]%clientNickname%[/b]: [color=#cf2157][b]OFFLINE[/b][/color] from: [b]%userTime%[/b]\n',
                   'downFooter' => '[/size][hr][right][size=12]Powered by: [url=https://sysmo.pro][color=#9319bf]SYSMO.PRO[/color][/url]',
                   'emptyGroup' => '[color=#7be24c][b]There is no user in this group![/b][/color]\n',
               ],
               'db' => [
                   'default' => '[cspacer%groupId%]» Status: %online%/%maxClients% | %%%% «',
                   'Academy' => '[cspacer%groupId%]» Status (aka): %online%/%maxClients% | %%%% «',
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 2, 'seconds' => 0],
           ],
           'clanStats' => [
               'enabled' => true,
               'coinsAmount' => 1,
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],
           ],
           'clanNotice' => [
               'enabled' => true,
               'channelName' => '[cspacer]» 🪖 Recruitment : %clanName% 🪖 «',
               'channelId' => 131,
               'descriptions' => [
                   'main' => "[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][size=10]\nWe invite you to recruit for the clan [color=#7be24c][b]%clanName%[/b][/color]\n[url=channelid://%recrutationId%][color=#7be24c]Click here to go to this guild's recruitment channel![/color][/url]\n[/size][hr][right][size=12]Powered by: [url=https://sysmo.pro][color=#9319bf]SYSMO.PRO[/color][/url]",
                   'noClans' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][size=10]\n[color=#7be24c]Currently there are no clans on the server![/color]\n[/size][hr][right][size=12]Powered by: [url=https://sysmo.pro][color=#9319bf]SYSMO.PRO[/color][/url]',
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],
           ],
           'clanAcademy' => [
               'enabled' => true,
               'sourceGroup' => 388,
               'academyName' => '%clanName%-aka',
               'online' => '[cspacer%academyGroup%]Status (aka): 0/0 | 0%',
               'groupChanger' => '• Change of rank: %academyName%',
           ],
   		'writeTops' => [
               'enabled' => true,
               'connections' => [
                   'channelId' => 101,
                   'awardsEnabled' => true,
                   'recordsLimit' => 20,
                   'groupId' => 383,
                   'descriptions' => [
                       'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n',
                       'userLine' => '[color=#7be24c][b]%i%.[/b][/color] %clientId% - [color=#7be24c][b]%value%[/b][/color] connections\n',
                       'downFooter' => '[/size][/center][hr]',
                   ],
               ],
               'timeSpent' => [
                   'channelId' => 98,
                   'awardsEnabled' => true,
                   'recordsLimit' => 20,
                   'groupId' => 381,
                   'descriptions' => [
                       'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n',
                       'userLine' => '[color=#7be24c][b]%i%.[/b][/color] %clientId% - [color=#7be24c][b]%value%[/b][/color] time spent\n',
                       'downFooter' => '[/size][/center][hr]',
                   ],
               ],
               'timeSpentAfk' => [
                   'channelId' => 128,
                   'awardsEnabled' => true,
                   'recordsLimit' => 20,
                   'groupId' => 382,
                   'descriptions' => [
                       'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n',
                       'userLine' => '[color=#7be24c][b]%i%.[/b][/color] %clientId% - [color=#7be24c][b]%value%[/b][/color] AFK time spent\n',
                       'downFooter' => '[/size][/center][hr]',
                   ],
               ],
               'connectionTime' => [
                   'channelId' => 102,
                   'awardsEnabled' => true,
                   'recordsLimit' => 20,
                   'groupId' => 384,
                   'descriptions' => [
                       'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n',
                       'userLine' => '[color=#7be24c][b]%i%.[/b][/color] %clientId% - [color=#7be24c][b]%value%[/b][/color] connection time\n',
                       'downFooter' => '[/size][/center][hr]',
                   ],
               ],
               'connectionLost' => [
                   'channelId' => 103,
                   'awardsEnabled' => true,
                   'recordsLimit' => 20,
                   'groupId' => 385,
                   'descriptions' => [
                       'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n',
                       'userLine' => '[color=#7be24c][b]%i%.[/b][/color] %clientId% - [color=#7be24c][b]%value%[/b][/color] connections lost\n',
                       'downFooter' => '[/size][/center][hr]',
                   ],
               ],
               'level' => [
                   'channelId' => 127,
                   'awardsEnabled' => true,
                   'recordsLimit' => 20,
                   'groupId' => 399,
                   'descriptions' => [
                       'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n',
                       'userLine' => '[color=#7be24c][b]%i%.[/b][/color] %clientId% - Level [color=#7be24c][b]%value%[/b][/color] \n',
                       'downFooter' => '[/size][/center][hr]',
                   ],
               ],
               'activeMonth' => [
                   'enabled' => true,
                   'channelId' => 100,
                   'awardsEnabled' => true,
                   'recordsLimit' => 20,
                   'groupId' => 436,
                   'descriptions' => [
                       'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n',
                       'userLine' => '[color=#7be24c][b]%i%.[/b][/color] %clientId% - [color=#7be24c][b]%value%[/b][/color] this month\n',
                       'downFooter' => '[/size][/center][hr]',
                   ],
               ],
               'activeYear' => [
                   'enabled' => true,
                   'channelId' => 99,
                   'awardsEnabled' => true,
                   'recordsLimit' => 20,
                   'groupId' => 437,
                   'descriptions' => [
                       'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n',
                       'userLine' => '[color=#7be24c][b]%i%.[/b][/color] %clientId% - [color=#7be24c][b]%value%[/b][/color] this year\n',
                       'downFooter' => '[/size][/center][hr]',
                   ],
               ],
   			'hallOfFame' => [
                   'enabled' => true,
                   'channelId' => 101794,
                   'maxEntries' => 48,
                   'descriptions' => [
                       'header' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n',
                       'subheader' => '[color=#7be24c][b]Hall of Fame[/b][/color] - These users had the highest activity each month and year:\n',
                       'yearlyHeader' => "\n[b][color=#7be24c]Yearly Winners:[/color][/b]\n",
                       'monthlyHeader' => "\n[b][color=#7be24c]Monthly Winners:[/color][/b]\n",
                       'footer' => "\n[/size][/center][hr][right][size=8]Updated: %timestamp%[/size][/right]",
                       'monthlyEntryFormat' => "\n[color=#7be24c][b]•[/b][/color] %monthName% — [color=#7be24c][b]%winnerName%[/b][/color] — %activity%",
                       'yearlyEntryFormat' => "\n[color=#7be24c][b]•[/b][/color] %yearName% — [color=#7be24c][b]%winnerName%[/b][/color] — %activity%",
                   ],
               ],
               'winnerGroups' => [
       // Update: feste Template-ID für Most Active Gruppen
       'templateId' => 442,
      ],
               'points' => [
                   'channelId' => 126,
                   'awardsEnabled' => true,
                   'recordsLimit' => 20,
                   'groupId' => 400,
                   'descriptions' => [
                       'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n',
                       'userLine' => '[color=#7be24c][b]%i%.[/b][/color] %clientId% - [color=#7be24c][b]%value%[/b][/color] points\n',
                       'downFooter' => '[/size][/center][hr]',
                   ],
               ],
               'interval' => ['days' => 0, 'hours' => 00, 'minutes' => 1, 'seconds' => 0],
           ],
           'adminsStats' => [
               'enabled' => true,
               'channelId' => 129,
               'helpChannels' => [115,116,117],
               'adminsGroups' => [6,33,370,390],
               'descriptions' => [
                   'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n',
                   'userLine' => '%clientId%\nHelp count: [color=#7be24c][b]%helpCount%[/b][/color]\nAdded groups: [color=#7be24c][b]%addedGroups%[/b][/color]\nRemoved groups: [color=#7be24c][b]%removedGroups%[/b][/color]\nTime spent on the help channel: [color=#7be24c][b]%timeSpentOnCp%[/b][/color]\n\n',
                   'downFooter' => '[/size][/center][hr][right][size=12]Powered by: [url=https://sysmo.pro][color=#9319bf]SYSMO.PRO[/color][/url]',
               ],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],
           ],
           
           // NEUE VOTING STATISTIKEN FUNKTION:
           'votingStats' => [
               'enabled' => true,
               'ignoredGroups' => [], // LEER! Damit du in Listen erscheinst
               'ignoredGroupsForAwards' => [6,283, 391], // NEU! Keine Awards für Admins
               
               'topVoters' => [
                   'enabled' => true,
                   'ignoredUIDs' => [], // Falls du dich trotzdem ausblenden willst
                   'channelId' => 103526,
                   'recordsLimit' => 15,
                   'awardsEnabled' => true,
                   'groupId' => 438, // Top Voter ALL TIME
                   'descriptions' => [
                       'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n[b][color=#7be24c]🏆 TOP VOTERS ALL TIME 🏆[/color][/b]\n\n',
                       'userLine' => '[color=#7be24c][b]%i%.[/b][/color] %clientId% - [color=#7be24c][b]%value% votes[/b][/color] 🗳️\n',
                       'noVoters' => '[color=#7be24c]No votes yet! Be the first to vote![/color]\n',
                       'downFooter' => '\n[/size][/center][hr][center][size=8]\n[b]🗳️ VOTE FOR OUR SERVER AND GET REWARDS! 🗳️[/b]\n[url=https://teamspeak-servers.org/server/15345/]TeamSpeak-Servers.org[/url] | [url=https://topg.org/ts3-server/server-652338]TopG.org[/url]\n\n[b][color=#7be24c]📋 HOW TO VOTE:[/color][/b]\n\n[b]TeamSpeak-Servers.org:[/b]\n• Click the link and enter your [color=#7be24c]EXACT TeamSpeak nickname[/color]\n• Case sensitive! "Kowismo" ≠ "kowismo"\n\n[b]TopG.org:[/b]\n• Click the link above\n• A popup will ask for your nickname\n• Enter your [color=#7be24c]EXACT TeamSpeak nickname[/color]\n• The site saves it as a cookie for future votes\n\n[b][color=#00bf30]✅ Vote Rewards:[/color][/b]\n• Instant group upgrades based on total votes\n• Monthly competition for special rewards\n• Support the server and show your dedication!\n\n[b][color=#7be24c]Need help?[/color][/b] Contact [url=client://0/WtWgpfS8UE18SW7yGXm3r1yoO/c=]Kowismo[/url]\n[/size][/center][hr]',
                   ],
               ],
               
               'monthlyVoters' => [
                   'enabled' => true,
                   'channelId' => 103527,
                   'recordsLimit' => 10,
                   'awardsEnabled' => true,
                   'groupId' => 439, // Top Voter THIS MONTH
                   'descriptions' => [
                       'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n[b][color=#7be24c]📅 TOP VOTERS THIS MONTH 📅[/color][/b]\n[i]Resets on the 1st of each month[/i]\n\n',
                       'userLine' => '[color=#7be24c][b]%i%.[/b][/color] %clientId% - [color=#7be24c][b]%value% votes[/b][/color] this month\n',
                       'noVoters' => '[color=#7be24c]No votes recorded this month yet![/color]\n[size=8]Be the first to vote and claim the top spot![/size]\n',
                       'downFooter' => '\n[/size][/center][hr][center][size=8]\n[b]🗳️ VOTE FOR OUR SERVER AND GET REWARDS! 🗳️[/b]\n[url=https://teamspeak-servers.org/server/15345/]TeamSpeak-Servers.org[/url] | [url=https://topg.org/ts3-server/server-652338]TopG.org[/url]\n\n[b][color=#7be24c]📋 HOW TO VOTE:[/color][/b]\n\n[b]TeamSpeak-Servers.org:[/b]\n• Click the link and enter your [color=#7be24c]EXACT TeamSpeak nickname[/color]\n• Case sensitive! "Kowismo" ≠ "kowismo"\n\n[b]TopG.org:[/b]\n• Click the link above\n• A popup will ask for your nickname\n• Enter your [color=#7be24c]EXACT TeamSpeak nickname[/color]\n• The site saves it as a cookie for future votes\n\n[b][color=#00bf30]✅ Vote Rewards:[/color][/b]\n• Instant group upgrades based on total votes\n• Monthly competition for special rewards\n• Support the server and show your dedication!\n\n[b][color=#7be24c]Need help?[/color][/b] Contact [url=client://0/WtWgpfS8UE18SW7yGXm3r1yoO/c=]Kowismo[/url]\n[/size][/center][hr]',
                   ],
               ],
               

   				'votingOverview' => [
                   'enabled' => true,
                   'channelId' => 103528,
                   'descriptions' => [
                       'upHeader' => '[hr][center]\n[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n[/center][hr][center][size=10]\n[b][color=#7be24c]📊 VOTING STATISTICS 📊[/color][/b]\n\n',
                       'mainContent' => '[table]\n[tr][th]📈 Total Votes:[/th][td][color=#7be24c][b]%totalVotes%[/b][/color][/td][/tr]\n[tr][th]👥 Total Voters:[/th][td][color=#7be24c][b]%totalVoters%[/b][/color][/td][/tr]\n[tr][th]📅 Today:[/th][td][color=#7be24c][b]%todayVotes%[/b][/color][/td][/tr]\n[tr][th]🗓️ This Month:[/th][td][color=#7be24c][b]%monthVotes%[/b][/color][/td][/tr]\n[tr][th]🏆 Top Voter (Month):[/th][td][color=#7be24c][b]%topVoter% (%topVoterVotes% votes)[/b][/color][/td][/tr]\n[/table]\n\n[b][color=#7be24c]📊 Votes by Platform:[/color][/b]\n\n%sourceStats%',
                       'sourceLine' => '• [color=#7be24c][b]%source%:[/b][/color] %count% votes\n',
                       'downFooter' => '\n\n[/size][/center][hr][center][size=8]\n[b]🗳️ VOTE FOR OUR SERVER AND GET REWARDS! 🗳️[/b]\n[url=https://teamspeak-servers.org/server/15345/]TeamSpeak-Servers.org[/url] | [url=https://topg.org/ts3-server/server-652338]TopG.org[/url]\n\n[b][color=#7be24c]📋 HOW TO VOTE:[/color][/b]\n\n[b]TeamSpeak-Servers.org:[/b]\n• Click the link and enter your [color=#7be24c]EXACT TeamSpeak nickname[/color]\n• Case sensitive! "Kowismo" ≠ "kowismo"\n\n[b]TopG.org:[/b]\n• Click the link above\n• A popup will ask for your nickname\n• Enter your [color=#7be24c]EXACT TeamSpeak nickname[/color]\n• The site saves it as a cookie for future votes\n\n[b][color=#00bf30]✅ Vote Rewards:[/color][/b]\n• Instant group upgrades based on total votes\n• Monthly competition for special rewards\n• Support the server and show your dedication!\n\n[b][color=#7be24c]Need help?[/color][/b] Contact [url=client://0/WtWgpfS8UE18SW7yGXm3r1yoO/c=]Kowismo[/url]\n[/size][/center][hr]',
                   ],
               ],
               
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],
           ],
       ],
   ],
   5 => [
       'connection' => [
           'teamspeakHost' => 'ts.sysmo.pro',
           'teamspeakLogin' => 'serveradmin',
           'teamspeakPass' => 'i5RAyzuGgGp9YmKh',
           'teamspeakPorts' => [
               'voicePort' => 9987,
               'queryPort' => 10011,
           ],
       ],
       'settings' => [
           'botName' => '[#] Website',
           'channelId' => 4,
       ],
       'mongodb' => [
           'srv' => 'mongodb://127.0.0.1:12345/',
           'dbName' => 'sysmopro',
       ],
       'functions' => [
           'websiteCache' => [
               'enabled' => true,
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 30],
           ],
           'clanCache' => [
               'enabled' => true,
               'defaultGroup' => 8,
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 8],
           ],
           'adminsCache' => [
               'enabled' => true,
               'adminsGroups' => [6,33,370,390],
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 30],
           ],
           'websiteApi' => [
               'enabled' => true,
               'defaultGroup' => 377,
               'ownGroupCopy' => 388,
               'neededSubscribePowerOn' => 18,
               'neededSubscribePowerOff' => 0,
               'musicBotAccess' => 372,
               'musicBots' => [
                   'groupId' => 283,
                   'channelId' => 4,
                   'sendCommandsAdd' => [
                       '!name "%clanName% #%i%"',
                       '!channel %channelId%'
                   ],
                   'sendCommandsDel' => [
                       '!name "%generatedName%"',
                       '!channel %channelId%'
                   ],
               ],
               'reportToAdmin' => [
                   'pokeGroups' => [6,33,370,390],
                   'ignoredGroups' => [],
                   'msg' => [
                       '[b]Heyo, Wake up![/b]',
                       'Clan: %guildName% calls you to its Channel.',
                       'CTRL + F %guildName% ',
                   ],
               ],
               'reportToAdminUser' => [
                   'pokeGroups' => [6,33,370,390],
                   'ignoredGroups' => [],
                   'msg' => [
                       '[b]Heyo, Wake up![/b]',
                       'User: [b]%userName%[/b] calls you on your Private Channel.',
                       'CTRL + F %userName% ',
                   ],
               ],
               'sendToken' => 'Here is your login token: [b][color=#7be24c]%token%[/color][/b]',
               'sendCommands' => [
                   '!name "%botName%"',
               ],
           ],
           'timeGroups' => [
               'enabled' => true,
               'interval' => ['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 0],
           ],
       ],
   ],
   6 => [
       'connection' => [
           'teamspeakHost' => 'ts.sysmo.pro',
           'teamspeakLogin' => 'serveradmin',
           'teamspeakPass' => 'i5RAyzuGgGp9YmKh',
           'teamspeakPorts' => [
               'voicePort' => 9987,
               'queryPort' => 10011,
           ],
       ],
       'settings' => [
           'botName' => '[#] Notifications',
           'channelId' => 4,
       ],
       'mongodb' => [
           'srv' => 'mongodb://127.0.0.1:12345/',
           'dbName' => 'sysmopro',
       ],
       'functions' => [
           'helpCommand' => [
               'enabled' => true,
               'commandUsage' => '!help',
               'authorizedGroups' => [6,33,370,390],
               'helpMessage' => [
                   '[b][color=#7be24c]!help[/color][/b] - List of commands',
                   '[b][color=#7be24c]!pwall <message>[/color][/b] - Message to all',
                   '[b][color=#7be24c]!pokeall <message>[/color][/b] - Poke to all',
                   '[b][color=#7be24c]!clanbot <add/del> <clanGroup>[/color][/b] - Adding/removing clan bot',
                   '[b][color=#7be24c]!clanchannel <add/del> <clanGroup>[/color][/b] - Adding/removing of an additional channel to the clan',
               ],
           ],
           'pwAllCommand' => [
               'enabled' => true,
               'commandUsage' => '!pwall',
               'authorizedGroups' => [6],
               'ignoredGroups' => [283,391],
               'messages' => [
                   'messageFormat' => 'A message from [b][color=#7be24c]sysmo.pro[/color][/b]: %message%',
                   'toInvoker' => 'Message sent to [b][color=#7be24c]%i%[/color][/b] users with content: %message%',
               ],
           ],
           'pokeAllCommand' => [
               'enabled' => true,
               'commandUsage' => '!pokeall',
               'authorizedGroups' => [6],
               'ignoredGroups' => [283,391],
               'messages' => [
                   'messageFormat' => '%message%',
                   'toInvoker' => 'Poke sent to [b][color=#7be24c]%i%[/color][/b] users with content: %message%',
               ],
           ],
           'clanBotCommand' => [
               'enabled' => true,
               'commandUsage' => '!clanbot',
               'authorizedGroups' => [6,33,370,390],
               'musicBots' => [
                   'groupId' => 283,
                   'channelId' => 4,
                   'sendCommandsAdd' => [
                       '!name "%clanName% #%i%"',
                       '!channel %channelId%'
                   ],
                   'sendCommandsDel' => [
                       '!name "%generatedName%"',
                       '!channel %channelId%'
                   ],
               ],
               'messages' => [
                   'commandUsage' => '[b][color=#7be24c]!clanbot <add/del> <groupId>[/color][/b]',
                   'minBots' => '[b][color=#7be24c]This clan has a minimum number of bots![/color][/b]',
                   'maxBots' => '[b][color=#7be24c]This clan has the maximum number of bots![/color][/b]',
                   'guildNotExist' => '[b][color=#7be24c]Such a clan does not exist![/color][/b]',
               ],
           ],
           'clanChannelCommand' => [
               'enabled' => true,
               'commandUsage' => '!clanchannel',
               'authorizedGroups' => [6,33,370,390],
               'channelName' => '[cspacer%clanName%]Additional spacer #%i%',
               'messages' => [
                   'commandUsage' => '[b][color=#7be24c]!clanbot <add/del> <groupId>[/color][/b]',
                   'maxChannels' => '[b][color=#7be24c]This clan already has the maximum number of additional Channels![/color][/b]',
                   'guildNotExist' => '[b][color=#7be24c]Such a clan does not exist![/color][/b]',
               ],
           ],
       ],
   ],
];