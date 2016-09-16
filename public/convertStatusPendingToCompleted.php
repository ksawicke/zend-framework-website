<?php

chdir(dirname(__DIR__));

$currentSystem = gethostname();
$currentPath = getcwd();

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'http://' . $currentSystem . ':10080' . substr($currentPath, 20) . '/public/api/set_pending_transaction_to_completed');
$data = curl_exec($ch);
curl_close($ch);
