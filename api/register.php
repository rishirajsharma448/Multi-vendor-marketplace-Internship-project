<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

echo json_encode(['status' => 'success', 'message' => 'API Registration Endpoint Ready']);
