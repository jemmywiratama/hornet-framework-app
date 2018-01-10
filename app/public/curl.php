<?php

// 并发访问示例
if (!preg_match("/cli/i", php_sapi_name())) {
    echo "not cli mode\n";
    die;
}
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

$max_request = 2;
$requests = array();
for ($i = 0; $i < $max_request; $i++) {
    $requests[] = "http://www.hornet-app.com/index/runtime_error?t=" . microtime_float() . '&file=file1&line=1001';
    usleep(10);
}

for ($i = 0; $i < $max_request; $i++) {
    $requests[] = "http://www.hornet-app.com/index/runtime_error?t=" . microtime_float() . '&file=file2&line=2001';
    usleep(10);
}

for ($i = 0; $i < $max_request; $i++) {
    $requests[] = "http://www.hornet-app.com/index/runtime_error?t=" . microtime_float() . '&file=file3&line=3001';
    usleep(10);
}

$main = curl_multi_init();
$results = array();
$errors = array();
$info = array();
$count = count($requests);
$handles = [];
for ($i = 0; $i < $count; $i++) {
    $handles[$i] = curl_init($requests[$i]);
    //var_dump($requests[$i]);
    curl_setopt($handles[$i], CURLOPT_URL, $requests[$i]);
    curl_setopt($handles[$i], CURLOPT_RETURNTRANSFER, 1);
    curl_multi_add_handle($main, $handles[$i]);
}
$running = 0;
do {
    curl_multi_exec($main, $running);
} while ($running > 0);
for ($i = 0; $i < $count; $i++) {
    $results[] = curl_multi_getcontent($handles[$i]);
    $errors[] = curl_error($handles[$i]);
    $info[] = curl_getinfo($handles[$i]);
    curl_multi_remove_handle($main, $handles[$i]);
}
curl_multi_close($main);
print_r($results);
//print_r($errors);
//print_r($info);