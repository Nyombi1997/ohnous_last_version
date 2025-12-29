<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
require_once '../vendor/autoload.php';

use ImageKit\ImageKit;

header("Content-Type: application/json");

$imageKit = new ImageKit(
    "public_RBnOctCZRQjH0d5pMKWrl8jQ/zI=",
    "private_yuDBuAtEO0mMujifa4DSzDuUBqI=",
    "https://ik.imagekit.io/nyombi1997"
);

try {
    $auth = $imageKit->getAuthenticationParameters();
    echo json_encode($auth);
} catch(Exception $e) {
    echo json_encode([
        "error" => $e->getMessage()
    ]);
}
