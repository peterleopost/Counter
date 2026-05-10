<?php
// Log the OS, hardware, and external referers to refs.json
$os = php_uname();
$hardware = (strpos($os, 'Windows') !== false) ? 'Windows' : (strpos($os, 'Linux') !== false ? 'Linux' : 'Other');
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'None';

// Get the current data from refs.json
$file = 'refs.json';
if (file_exists($file)) {
    $data = json_decode(file_get_contents($file), true);
} else {
    $data = [];
}

// Add new log data
$data[] = [
    'os' => $os,
    'hardware' => $hardware,
    'referer' => $referer,
    'timestamp' => date('Y-m-d H:i:s')
];

// Save updated data back to refs.json
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
