<?php
// smeruje pozadavky na port VLC
$crop = strlen($_SERVER["SCRIPT_NAME"])-strlen("/proxy.php");
$url = "http://localhost:8080".substr($_SERVER["REQUEST_URI"],$crop);
$password = "admin";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_USERPWD, ":".$password);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
$exec = curl_exec($curl);

