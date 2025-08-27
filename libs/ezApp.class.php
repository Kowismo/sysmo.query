<?php
class ezApp {
    private array $cache;
    public function clientList(): array {
        global $ts;
        if(isset($this->cache['clientList']['time']) && !empty($this->cache['clientList']['data']) && time() - $this->cache['clientList']['time'] < 4) {
            return $this->cache['clientList']['data'];
        } else {
            $this->cache['clientList']['data'] = $ts->getElement('data', $ts->clientList('-uid -away -voice -times -groups -info -country -icon -ip -badges'));
            $this->cache['clientList']['time'] = time();
            return $this->cache['clientList']['data'];
        }
    }
    public function serverGroupList(): array {
        global $ts;
        if(isset($this->cache['serverGroupList']['time']) && !empty($this->cache['serverGroupList']['data']) && time() - $this->cache['serverGroupList']['time'] < 7) {
            return $this->cache['serverGroupList']['data'];
        } else {
            $this->cache['serverGroupList']['data'] = $ts->getElement('data', $ts->serverGroupList());
            $this->cache['serverGroupList']['time'] = time();
            return $this->cache['serverGroupList']['data'];
        }
    }
    public function serverGroupClientList(int $sgid) {
        global $ts;
        if(isset($this->cache['serverGroupClientList'][$sgid]['time']) && !empty($this->cache['serverGroupClientList'][$sgid]['data']) && time() - $this->cache['serverGroupClientList'][$sgid]['time'] < 6) {
            return $this->cache['serverGroupClientList'][$sgid]['data'];
        } else {
            $this->cache['serverGroupClientList'][$sgid]['data'] = $ts->getElement('data', $ts->serverGroupClientList($sgid, $names = true));
            $this->cache['serverGroupClientList'][$sgid]['time'] = time();
            return $this->cache['serverGroupClientList'][$sgid]['data'];
        }
    }
    public function channelList(): array {
        global $ts;
        if(isset($this->cache['channelList']['time']) && !empty($this->cache['channelList']['data']) && time() - $this->cache['channelList']['time'] < 8) {
            return $this->cache['channelList']['data'];
        } else {
            $this->cache['channelList']['data'] = $ts->getElement('data', $ts->channelList('-topic -flags -voice -limits -icon'));
            $this->cache['channelList']['time'] = time();
            return $this->cache['channelList']['data'];
        }
    }
    public static function getUserClans($groups, $mongoDB) {
        $inClan = [];
        $clans = $mongoDB->clanChannels->find([], ['sort' => ['_id' => -1]])->toArray();
        if(count($clans) > 0) {
            foreach ($clans as $clan) {
                foreach (explode(',', $groups) as $group) {
                    if ($clan['clanGroup'] == $group) {
                        $inClan[] = $clan;
                    }
                }
            }
        }
        return $inClan;
    }
    public static function createId($client) {
        if(isset($client['clid'])) {
            return '[url=client://' . $client['clid'] . '/' . $client['client_unique_identifier'] . ']' . $client['client_nickname'] . '[/url]';
        } else {
            return '[url=client://0/' . $client['client_unique_identifier'] . ']' . $client['client_nickname'] . '[/url]';
        }
    }
    public static function convertInterval($interval) {
        return $interval['days'] * 86400 + $interval['hours'] * 3600 + $interval['minutes'] * 60 + $interval['seconds'];
    }
    public static function getNameByNumber($number, $plural1, $plural2, $plural3) {
        $plural_matches = [
            $plural1 => ['1'],
            $plural2 => ['*0', '*1', '*5', '*6', '*7', '*8', '*9'],
            $plural3 => ['*2', '*3', '*4']
        ];
        $last_digit = substr($number, -1);
        $look_for = strlen($number) == $last_digit && $last_digit == 1 ? '1' : "*$last_digit";
        foreach ($plural_matches as $key=>$variants) {
            if (in_array($look_for, $variants)) {
                return $key;
            }
        }
        return '?';
    }
    static function unEscapeText($text) {
        $escapedChars = ["\t", "\v", "\r", "\n", "\f", "\s", "\p", "\/"];
        $unEscapedChars = ['', '', '', '', '', ' ', '|', '/'];
        $text = str_replace($escapedChars, $unEscapedChars, $text);
        return $text;
    }
    public static function generateString($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string     = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $string;
    }
    public static function inGroup($checkGroup, $clientGroups) {
        $clientGroups = explode(',', $clientGroups);
        if (gettype($checkGroup) == 'array') {
            foreach ($checkGroup as $group) {
                if (in_array($group, $clientGroups)) {
                    return true;
                }
            }
        } else if (in_array($checkGroup, $clientGroups)) {
            return true;
        }
        return false;
    }
    public static function getGroupName($grp, $serverGroupList) {
        global $ts;
        foreach ($serverGroupList as $group) {
            if ($group['sgid'] == $grp) {
                return $group['name'];
            }
        }
    }
    public function timeConverter(int $seconds): string {
        if($seconds == 0) {
            return '0 sekund';
        }
        $txt = '';
        $convert['days'] = floor($seconds / 86400);
        $convert['hours'] = floor(($seconds - ($convert['days'] * 86400)) / 3600);
        $convert['minutes'] = floor(($seconds - ($convert['days'] * 86400) - ($convert['hours'] * 3600)) / 60);
        $convert['seconds'] = floor($seconds - ($convert['days'] * 86400) - ($convert['hours'] * 3600) - ($convert['minutes'] * 60));
        if($convert['days'] > 0) {
            switch($convert['days']) {
                case 1:
                    $txt .= ' '.$convert['days'].' day';
                    break;
                default:
                    $txt .= ' '.$convert['days'].' days';
                    break;
            }
        }
        $range = [5,6,7,8,9,10,11,12,13,14,16,17,17,18,19,20,21];
        if($convert['hours'] > 0) {
            switch($convert['hours']) {
                case 1:
                    $txt .= ' '.$convert['hours'].' hour';
                    break;
                case (in_array($convert['hours'], $range)):
                    $txt .= ' '.$convert['hours'].' hours';
                    break;
                default:
                    $txt .= ' '.$convert['hours'].' hours';
                    break;
            }
        }
        $range = [2,3,4];
        if($convert['minutes'] > 0) {
            switch($convert['minutes']) {
                case 1:
                    $txt .= ' '.$convert['minutes'].' minute';
                    break;
                case (in_array($convert['minutes'], $range)):
                    $txt .= ' '.$convert['minutes'].' minutes';
                    break;
                default:
                    $txt .= ' '.$convert['minutes'].' minute';
                    break;
            }
        }
        if($convert['seconds'] > 0) {
            switch($convert['seconds']) {
                case 1:
                    $txt .= ' '.$convert['seconds'].' second';
                    break;
                case (in_array($convert['seconds'], $range)):
                    $txt .= ' '.$convert['seconds'].' seconds';
                    break;
                default:
                    $txt .= ' '.$convert['seconds'].' second';
                    break;
            }
        }
        return $txt;
    }
    public static function codeToCountry($code) {
        $countryList = [
            'AF' => 'Afghanistan',
            'AX' => 'Aland Islands',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas the',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island (Bouvetoya)',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory (Chagos Archipelago)',
            'VG' => 'British Virgin Islands',
            'BN' => 'Brunei Darussalam',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos (Keeling) Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros the',
            'CD' => 'Congo',
            'CG' => 'Congo the',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'CI' => 'Cote d\'Ivoire',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FO' => 'Faroe Islands',
            'FK' => 'Falkland Islands (Malvinas)',
            'FJ' => 'Fiji the Fiji Islands',
            'FI' => 'Finland',
            'FR' => 'France, French Republic',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia the',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island and McDonald Islands',
            'VA' => 'Holy See (Vatican City State)',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KP' => 'Korea',
            'KR' => 'Korea',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyz Republic',
            'LA' => 'Lao',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libyan Arab Jamahiriya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macao',
            'MK' => 'Macedonia',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico',
            'FM' => 'Micronesia',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'AN' => 'Netherlands Antilles',
            'NL' => 'Netherlands the',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestinian Territory',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn Islands',
            'PL' => 'Poland',
            'PT' => 'Portugal, Portuguese Republic',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'RE' => 'Reunion',
            'RO' => 'Romania',
            'RU' => 'Russian Federation',
            'RW' => 'Rwanda',
            'BL' => 'Saint Barthelemy',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'MF' => 'Saint Martin',
            'PM' => 'Saint Pierre and Miquelon',
            'VC' => 'Saint Vincent and the Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome and Principe',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SK' => 'Slovakia (Slovak Republic)',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia, Somali Republic',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard & Jan Mayen Islands',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland, Swiss Confederation',
            'SY' => 'Syrian Arab Republic',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States of America',
            'UM' => 'United States Minor Outlying Islands',
            'VI' => 'United States Virgin Islands',
            'UY' => 'Uruguay, Eastern Republic of',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'WF' => 'Wallis and Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
            null => 'Nieznany',
            '' => 'Nieznany',
        ];
        if(!$countryList[$code]) {
            return $code;
        } else {
            return $countryList[$code];
        }
    }
    public static function createCollections($mongoDB) {
        $allCollections = [];
        foreach ($mongoDB->listCollections() as $collectionInfo) {
            $allCollections[] = $collectionInfo['name'];
        }
        if(!in_array('adminChannels', $allCollections)) {
            $mongoDB->createCollection('adminChannels');
        }
        if(!in_array('antyVpn', $allCollections)) {
            $mongoDB->createCollection('antyVpn');
        }
        if(!in_array('botData', $allCollections)) {
            $mongoDB->createCollection('botData');
        }
        if(!in_array('clanChannels', $allCollections)) {
            $mongoDB->createCollection('clanChannels');
        }
        if(!in_array('newUsersToday', $allCollections)) {
            $mongoDB->createCollection('newUsersToday');
        }
        if(!in_array('privateChannels', $allCollections)) {
            $mongoDB->createCollection('privateChannels');
        }
        if(!in_array('serverClients', $allCollections)) {
            $mongoDB->createCollection('serverClients');
        }
        if(!in_array('websiteApi', $allCollections)) {
            $mongoDB->createCollection('websiteApi');
        }
        if(!in_array('clanRecrutations', $allCollections)) {
            $mongoDB->createCollection('clanRecrutations');
        }
        if(!in_array('timeGroups', $allCollections)) {
            $mongoDB->createCollection('timeGroups');
        }
        if(!in_array('serverLogs', $allCollections)) {
            $mongoDB->createCollection('serverLogs');
        }
        if(!in_array('accountLogs', $allCollections)) {
            $mongoDB->createCollection('accountLogs');
        }
        if(!in_array('profileVisitors', $allCollections)) {
            $mongoDB->createCollection('profileVisitors');
        }
        if(!in_array('secondLeader', $allCollections)) {
            $mongoDB->createCollection('secondLeader');
        }
        if(!in_array('webAdmin', $allCollections)) {
            $mongoDB->createCollection('webAdmin');
            foreach(['kTluO8pULcPmH0yfzB8MC9q2Oq4=', 'T1UsBal/a6mBW2LKEvAEPFvpev4='] as $clientUniqueIdentifier) {
                $mongoDB->webAdmin->insertOne(['clientUniqueIdentifier' => $clientUniqueIdentifier, 'isRoot' => true]);
            }
        }
        if(!in_array('recrutationWeb', $allCollections)) {
            $mongoDB->createCollection('recrutationWeb');
        }
        if(!in_array('clanLogs', $allCollections)) {
            $mongoDB->createCollection('clanLogs');
        }
        if(!in_array('adminLogs', $allCollections)) {
            $mongoDB->createCollection('adminLogs');
        }
        if(!in_array('webSettings', $allCollections)) {
            $mongoDB->createCollection('webSettings');
            $mongoDB->webSettings->insertOne(['recrutationStatus' => true, 'isRecrutation' => true]);
            $mongoDB->webSettings->insertOne(['alertType' => 'success', 'alertTitle' => 'Hello!', 'alertText' => 'Thank you for using our panel and our Ts3 server! Greetings!', 'isAlert' => true]);
        }
        if(!in_array('webApplicationsRatings', $allCollections)) {
            $mongoDB->createCollection('webApplicationsRatings');
        }
        if(!in_array('webApplications', $allCollections)) {
            $mongoDB->createCollection('webApplications');
        }
    }
    public static function uptimeConverter($seconds) {
        $convert = [];
        $time = '';
        $convert['year']=floor($seconds / 31536000);
        $convert['month']=floor(($seconds - ($convert['year'] * 31536000)) / 2628000);
        $convert['days']=floor(($seconds - (($convert['year'] * 31536000)+($convert['month'] * 2628000))) / 86400);
        $convert['hours']=floor(($seconds - (($convert['year'] * 31536000)+($convert['month'] * 2628000)+($convert['days']*86400)) ) / 3600);
        $convert['minutes']=floor(($seconds - (($convert['year'] * 31536000)+($convert['month'] * 2628000)+($convert['days'] * 86400)+($convert['hours']*3600))) / 60);

        $time = '';
        if($seconds < 60) {
            $time.= '' . $seconds . ' second ';
        } else {
            if($convert['year'] != 0) {
                if($convert['year']>0 && $convert['year']>4)
                    $time .= ''.$convert['year']. ' years ';
                else if($convert['year']==1 && $convert['year']>0)
                    $time .= ''.$convert['year']. ' year ';
                else if($convert['year']>0 && $convert['year']>1 && $convert['year']<=4)
                    $time .= ''.$convert['year']. ' year ';
            } elseif($convert['month'] != 0) {
                if($convert['month']>0 && $convert['month']>4)
                    $time .= ''.$convert['month']. ' months ';
                else if($convert['month']==1 && $convert['month']>0)
                    $time .= ''.$convert['month']. ' month ';
                else if($convert['month']>0 && $convert['month']>1 && $convert['month']<=4)
                    $time .= ''.$convert['month']. ' months ';
            } elseif($convert['days'] != 0) {
                if($convert['days']>0 && $convert['days']>1)
                    $time .= ''.$convert['days']. ' days ';
                else if($convert['days']>0 && $convert['days']==1)
                    $time .= ''.$convert['days']. ' day ';
            } elseif($convert['hours'] != 0) {
                if($convert['hours']>0 && $convert['hours']>4)
                    $time .= ''.$convert['hours']. ' hour ';
                else if($convert['hours']==1 && $convert['hours']>0)
                    $time .= ''.$convert['hours']. ' hours ';
                else if($convert['hours']>0 && $convert['hours']>1 && $convert['hours']<=4)
                    $time .= ''.$convert['hours']. ' hours ';
            } elseif($convert['minutes'] != 0) {
                if($convert['minutes']>0 && $convert['minutes']>4)
                    $time .= ''.$convert['minutes']. ' minute ';
                else if($convert['minutes']==1 && $convert['minutes']>0)
                    $time .= ''.$convert['minutes']. ' minute ';
                else if($convert['minutes']>0 && $convert['minutes']>1 && $convert['minutes']<=4)
                    $time .= ''.$convert['minutes']. ' minutes ';
            }
        }
        return $time;
    }
    public static function deleteSelectedChannelGroup($ts, $channel, $scannedGroup, $defaultGroup) {
        $channelUsers = $ts->channelGroupClientList($channel)['data'];
        if (empty($channelUsers)) {
            return false;
        }
        foreach ($channelUsers as $channelUser) {
            if ($channelUser['cgid'] == $scannedGroup) {
                $ts->channelGroupAddClient($defaultGroup, $channel, $channelUser['cldbid']);
            }
        }
        return false;
    }
    public static function getChannelsLeader($ts, $firstChannel, $lastChannel) {
        $channelList = $ts->channelList()['data'];
        $channels = [];
        $order = 0;
        foreach($channelList as $key => $channel) {
            if($channel['cid']==$firstChannel) {
                $channels[] = $channel;
                $order = $key;
                break;
            }
        }
        foreach($channelList as $key => $channel) {   
            if($key>$order) {
                $channels[] = $channel;
            }
            if($channel['cid']==$lastChannel) {
                break;
            }
        }
        return $channels;
    }
    public static function createLog($mongoDB, $function = '', $clientUniqueIdentifier, $clientNickname, $message) {
        $mongoDB->serverLogs->insertOne(['function' => $function, 'clientNickname' => $clientNickname, 'clientUniqueIdentifier' => $clientUniqueIdentifier, 'message' => $message, 'time' => time()]);
    }
    public static function getEvents() {
        global $ts;
        if(!empty($ts->runtime['lost_events'])) {
            foreach($ts->runtime['lost_events'] as $key => $event) {
                unset($ts->runtime['lost_events'][$key]);
                return self::eventToArray($event);
            }
        } else {
            return self::eventToArray(@fgets($ts->runtime['socket'], 4096));
        }
    }
    static function eventToArray($event) {
        if(!empty($event)) {
            $datasets = explode(' ', $event);
            $output = [];
            foreach($datasets as $dataset) {
                $dataset = explode ('=', $dataset);
                if(count($dataset) > 2) {
                    for($i = 2; $i < count($dataset); $i++) {
                        $dataset[1] .= '='.$dataset[$i];
                    }
                    $output[self::unEscapeText($dataset[0])] = self::unEscapeText($dataset[1]);
                } else {
                    if(count($dataset) == 1) {
                        $output[self::unEscapeText($dataset[0])] = '';
                    } else {
                        $output[self::unEscapeText($dataset[0])] = self::unEscapeText($dataset[1]);
                    }
                }
            }
            return $output;
        }
    }
}
