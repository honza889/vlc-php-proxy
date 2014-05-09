<?php
// smeruje pozadavky na port VLC
require("config.php");
$crop = strlen($_SERVER["SCRIPT_NAME"])-strlen("/proxy.php");
$url = $CONFIG["vlc-http"].substr($_SERVER["REQUEST_URI"],$crop);

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_USERPWD, ":".$CONFIG["vlc-password"]);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
$exec = curl_exec($curl);

