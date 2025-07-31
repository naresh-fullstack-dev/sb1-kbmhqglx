<?php
require_once '../db.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, title, subject, chapter, uploaded_at FROM pdfs ORDER BY uploaded_at DESC");
    $pdfs = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'pdfs' => $pdfs]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching PDFs']);
}
?>