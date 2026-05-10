<?php
date_default_timezone_set('Europe/Amsterdam');

$stats = json_decode(file_get_contents('stats.json'), true);

if(!$stats){
    $stats = [];
}

$visitors = json_decode(file_get_contents('visitors.json'), true);

if(!$visitors){
    $visitors = [];
}

/* =========================
   RESET LOGICA
========================= */

$today = date("Y-m-d");

if(!isset($stats['last_day']) || $stats['last_day'] != $today){

    $stats['views']['day'] = array_fill(0,24,0);
    $stats['unique']['day'] = array_fill(0,24,0);

    $stats['last_day'] = $today;
}

$currentWeek = date("o-W");

if(!isset($stats['last_week']) || $stats['last_week'] != $currentWeek){

    $stats['views']['week'] = [];
    $stats['unique']['week'] = [];

    $stats['last_week'] = $currentWeek;
}

$currentMonth = date("Y-m");

if(!isset($stats['last_month']) || $stats['last_month'] != $currentMonth){

    $stats['views']['month'] = [];
    $stats['unique']['month'] = [];

    $stats['last_month'] = $currentMonth;
}

$currentYear = date("Y");

if(!isset($stats['last_year']) || $stats['last_year'] != $currentYear){

    $stats['views']['year'] = [];
    $stats['unique']['year'] = [];

    $stats['last_year'] = $currentYear;
}

/* =========================
   BASIS DATA
========================= */

$ip = $_SERVER['REMOTE_ADDR'];
$time = time();

$hour = (int)date("H");
$day = date("D");
$month_day = date("d");
$month = date("M");

// GET USER-AGENT FROM SERVER (NOT FROM GET)
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

/* =========================
   REFERRERS
========================= */

$raw_ref = $_SERVER['HTTP_REFERER'] ?? ($_GET['ref'] ?? 'direct');

$internal_ref = 'direct';
$external_ref = null;

if($raw_ref !== 'direct'){

    $parsed = parse_url($raw_ref);

    $host = strtolower($parsed['host'] ?? '');
    $host = str_replace('www.', '', $host);

    $path = $parsed['path'] ?? '/';

    // INTERNAL
    if(
        strpos($host, 'jaah.nl') !== false ||
        strpos($host, 'onlinemp3player.com') !== false
    ){

        if($path == ''){
            $path = '/';
        }

        $internal_ref = rtrim($path, '/') . '/';

        if($internal_ref == '//'){
            $internal_ref = '/';
        }

    } else {

        // EXTERNAL
        $external_ref = $host;
    }
}

/* =========================
   GEO DATA
========================= */

$geo = @json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);

$country = $geo['countryCode'] ?? 'UN';
$city = $geo['city'] ?? 'Unknown';

/* =========================
   OS DETECTIE
========================= */

function detectOS($ua){

    $ua = strtolower($ua);

    if(strpos($ua,'windows') !== false) return 'Windows 10/11';
    if(strpos($ua,'android') !== false) return 'Android';
    if(strpos($ua,'iphone') !== false) return 'iPhone';
    if(strpos($ua,'ipad') !== false) return 'iPad';
    if(strpos($ua,'mac') !== false) return 'Mac OS';
    if(strpos($ua,'linux') !== false) return 'Linux';

    return 'Unknown';
}

/* =========================
   DEVICE DETECTIE
========================= */

function detectDevice($ua){

    $ua = strtolower($ua);

    if(strpos($ua,'mobile') !== false) return 'Mobiel';
    if(strpos($ua,'iphone') !== false) return 'Mobiel';
    if(strpos($ua,'android') !== false) return 'Mobiel';
    if(strpos($ua,'ipad') !== false) return 'Tablet';
    if(strpos($ua,'macintosh') !== false) return 'Apple Computer';

    return 'PC/Laptop';
}

$os = detectOS($ua);
$device = detectDevice($ua);

/* =========================
   UNIQUE CHECK
========================= */

$unique = false;

if(!isset($visitors[$ip]) || ($time - $visitors[$ip]) > 300){
    $unique = true;
}

$visitors[$ip] = $time;

// cleanup >5 minuten
foreach($visitors as $k=>$v){

    if(($time - $v) > 300){
        unset($visitors[$k]);
    }
}

/* =========================
   INIT STRUCTURES
========================= */

if(!isset($stats['views']['week'][$day])) $stats['views']['week'][$day]=0;
if(!isset($stats['unique']['week'][$day])) $stats['unique']['week'][$day]=0;

if(!isset($stats['views']['month'][$month_day])) $stats['views']['month'][$month_day]=0;
if(!isset($stats['unique']['month'][$month_day])) $stats['unique']['month'][$month_day]=0;

if(!isset($stats['views']['year'][$month])) $stats['views']['year'][$month]=0;
if(!isset($stats['unique']['year'][$month])) $stats['unique']['year'][$month]=0;

if(!isset($stats['os'])) $stats['os'] = [];
if(!isset($stats['devices'])) $stats['devices'] = [];
if(!isset($stats['external_referrers'])) $stats['external_referrers'] = [];

/* =========================
   VIEWS
========================= */

$stats['views']['day'][$hour]++;
$stats['views']['week'][$day]++;
$stats['views']['month'][$month_day]++;
$stats['views']['year'][$month]++;

if($unique){

    $stats['unique']['day'][$hour]++;
    $stats['unique']['week'][$day]++;
    $stats['unique']['month'][$month_day]++;
    $stats['unique']['year'][$month]++;
}

/* =========================
   COUNTRIES
========================= */

$stats['countries'][$country] =
    ($stats['countries'][$country] ?? 0) + 1;

/* =========================
   CITIES
========================= */

$stats['cities'][$city] =
    ($stats['cities'][$city] ?? 0) + 1;

/* =========================
   INTERNAL REFERRERS
========================= */

$stats['referrers'][$internal_ref] =
    ($stats['referrers'][$internal_ref] ?? 0) + 1;

/* =========================
   EXTERNAL REFERRERS
========================= */

if($external_ref){

    $stats['external_referrers'][$external_ref] =
        ($stats['external_referrers'][$external_ref] ?? 0) + 1;
}

/* =========================
   OS
========================= */

$stats['os'][$os] =
    ($stats['os'][$os] ?? 0) + 1;

/* =========================
   DEVICES
========================= */

$stats['devices'][$device] =
    ($stats['devices'][$device] ?? 0) + 1;

/* =========================
   SAVE
========================= */

file_put_contents('stats.json', json_encode($stats));
file_put_contents('visitors.json', json_encode($visitors));

/* =========================
   PIXEL OUTPUT
========================= */

header("Content-Type:image/gif");

echo base64_decode("R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==");

?>
