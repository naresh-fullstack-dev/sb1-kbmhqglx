<?php
require_once '../db.php';

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $chapter = $_POST['chapter'] ?? '';
    
    if (empty($title) || empty($subject) || empty($chapter)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if (!isset($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'PDF file is required']);
        exit;
    }
    
    $file = $_FILES['pdf'];
    $allowedTypes = ['application/pdf'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Only PDF files are allowed']);
        exit;
    }
    
    // Create uploads directory if it doesn't exist
    $uploadDir = '../../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Save to database
        $stmt = $pdo->prepare("INSERT INTO pdfs (title, filename, subject, chapter) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$title, $filename, $subject, $chapter])) {
            echo json_encode(['success' => true, 'message' => 'PDF uploaded successfully']);
        } else {
            unlink($filepath); // Delete file if database insert fails
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'File upload failed']);
    }
}
?>