<?php
$fileId = html_entity_decode(filter_var($_POST['fileId'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$privateKey = "private_yuDBuAtEO0mMujifa4DSzDuUBqI=";

$deleteUrl = "https://api.imagekit.io/v1/files/$fileId";

$ch = curl_init($deleteUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // très important
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, $privateKey . ":"); // auth HTTP Basic

// SSL sécurisé
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if($response === false){
    echo "Erreur cURL : " . curl_error($ch);
} else {
    echo "HTTP Code : $httpCode\n";
    var_dump($response);
}

curl_close($ch);
