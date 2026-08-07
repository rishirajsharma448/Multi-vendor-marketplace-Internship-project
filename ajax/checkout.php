<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
echo json_encode(['status' => 'success', 'message' => 'Checkout verified']);
