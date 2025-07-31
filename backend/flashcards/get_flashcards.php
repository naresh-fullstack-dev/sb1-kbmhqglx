<?php
require_once '../db.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $pdfIds = $_GET['pdf_ids'] ?? '';
    
    if (empty($pdfIds)) {
        echo json_encode(['success' => false, 'message' => 'Please select PDFs']);
        exit;
    }
    
    $pdfIdsArray = explode(',', $pdfIds);
    $placeholders = str_repeat('?,', count($pdfIdsArray) - 1) . '?';
    
    $stmt = $pdo->prepare("SELECT f.*, p.title as pdf_title FROM flashcards f 
                          JOIN pdfs p ON f.pdf_id = p.id 
                          WHERE f.pdf_id IN ($placeholders) 
                          ORDER BY RAND()");
    $stmt->execute($pdfIdsArray);
    $flashcards = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'flashcards' => $flashcards]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $pdfIds = $input['pdf_ids'] ?? [];
    
    if (empty($pdfIds)) {
        echo json_encode(['success' => false, 'message' => 'Please select PDFs']);
        exit;
    }
    
    // Get PDF information for context
    $placeholders = str_repeat('?,', count($pdfIds) - 1) . '?';
    $stmt = $pdo->prepare("SELECT id, title, subject, chapter FROM pdfs WHERE id IN ($placeholders)");
    $stmt->execute($pdfIds);
    $pdfs = $stmt->fetchAll();
    
    // Build context for AI
    $context = "Create educational flashcards based on the following study materials: ";
    foreach ($pdfs as $pdf) {
        $context .= $pdf['title'] . " (" . $pdf['subject'] . " - " . $pdf['chapter'] . "), ";
    }
    $context = rtrim($context, ', ') . ". ";
    $context .= "Generate 10-15 flashcards with key concepts, definitions, formulas, and important facts. ";
    $context .= "Format as JSON array with objects containing 'front' (question/term) and 'back' (answer/definition) fields. ";
    $context .= "Make the front side concise (question or term) and back side informative (answer or explanation).";
    
    // OpenAI API call
    $apiKey = 'sk-or-v1-5bb2ef40a9478fff23986ae47a449be8212fbf4f54601706bb8c609a5ad7ac6aa';
    $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
    
    $data = [
        'model' => 'openai/gpt-3.5-turbo',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are an educational content generator specializing in creating effective flashcards for studying. Always respond with valid JSON format.'
            ],
            [
                'role' => 'user',
                'content' => $context
            ]
        ],
        'max_tokens' => 1200,
        'temperature' => 0.7
    ];
    
    $options = [
        'http' => [
            'header' => [
                "Content-Type: application/json",
                "Authorization: Bearer " . $apiKey,
                "HTTP-Referer: http://localhost",
                "X-Title: StudyFetch"
            ],
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];
    
    $context_stream = stream_context_create($options);
    $result = file_get_contents($apiUrl, false, $context_stream);
    
    $generatedFlashcards = [];
    
    if ($result !== FALSE) {
        $apiResponse = json_decode($result, true);
        if (isset($apiResponse['choices'][0]['message']['content'])) {
            $content = $apiResponse['choices'][0]['message']['content'];
            
            // Try to extract JSON from the response
            $jsonStart = strpos($content, '[');
            $jsonEnd = strrpos($content, ']');
            
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonContent = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
                $decodedFlashcards = json_decode($jsonContent, true);
                
                if (is_array($decodedFlashcards)) {
                    $generatedFlashcards = $decodedFlashcards;
                }
            }
        }
    }
    
    // Fallback if AI generation fails
    if (empty($generatedFlashcards)) {
        $generatedFlashcards = [
            ['front' => 'What are the main topics covered?', 'back' => 'Key concepts and principles from the selected study materials.'],
            ['front' => 'Define the fundamental terms', 'back' => 'Important terminology and definitions from the subject matter.'],
            ['front' => 'Key formulas and equations', 'back' => 'Mathematical relationships and scientific formulas.'],
            ['front' => 'Real-world applications', 'back' => 'How the concepts apply to practical situations.'],
            ['front' => 'Important theories', 'back' => 'Fundamental theories and their implications.']
        ];
    }
    
    // Save flashcards to database
    $savedFlashcards = [];
    foreach ($pdfIds as $pdfId) {
        foreach ($generatedFlashcards as $card) {
            // Check if flashcard already exists
            $stmt = $pdo->prepare("SELECT id FROM flashcards WHERE pdf_id = ? AND front = ?");
            $stmt->execute([$pdfId, $card['front']]);
            
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO flashcards (pdf_id, front, back) VALUES (?, ?, ?)");
                $stmt->execute([$pdfId, $card['front'], $card['back']]);
                $savedFlashcards[] = array_merge($card, ['pdf_id' => $pdfId]);
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'flashcards' => $savedFlashcards,
        'message' => 'Flashcards generated successfully'
    ]);
}
?>