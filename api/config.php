<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'success' => true,
    'data' => [
        'obf_key' => 'WARTIK_SEMERU_SECRET_2026'
    ]
]);
