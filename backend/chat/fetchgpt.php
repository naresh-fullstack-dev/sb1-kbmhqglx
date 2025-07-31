<?php
require_once '../db.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $message = $input['message'] ?? '';
    $userId = getCurrentUserId();
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
        exit;
    }
    
    // Get context from uploaded PDFs
    $stmt = $pdo->query("SELECT title, subject, chapter FROM pdfs ORDER BY uploaded_at DESC");
    $pdfs = $stmt->fetchAll();
    
    $context = "You are FetchGPT, an AI study assistant. You help students understand concepts from their study materials. ";
    if (!empty($pdfs)) {
        $context .= "The student has access to the following study materials: ";
        foreach ($pdfs as $pdf) {
            $context .= $pdf['title'] . " (" . $pdf['subject'] . " - " . $pdf['chapter'] . "), ";
        }
        $context = rtrim($context, ', ') . ". ";
    }
    $context .= "Provide helpful, educational responses related to these subjects. Keep responses concise but informative.";
    
    // OpenAI API call
    $apiKey = 'sk-or-v1-5bb2ef40a9478fff23986ae47a449be8212fbf4f54601706bb8c609a5ad7ac6aa';
    $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
    
    $data = [
        'model' => 'openai/gpt-3.5-turbo',
        'messages' => [
            [
                'role' => 'system',
                'content' => $context
            ],
            [
                'role' => 'user',
                'content' => $message
            ]
        ],
        'max_tokens' => 500,
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
    
    if ($result === FALSE) {
        // Fallback response if API fails
        $response = "I'm here to help you with your studies! Could you please be more specific about what you'd like to know from your study materials?";
    } else {
        $apiResponse = json_decode($result, true);
        if (isset($apiResponse['choices'][0]['message']['content'])) {
            $response = $apiResponse['choices'][0]['message']['content'];
        } else {
            $response = "I'm having trouble processing your request right now. Please try asking your question in a different way.";
        }
    }
    
    // Save chat log
    $stmt = $pdo->prepare("INSERT INTO chat_logs (user_id, message, response) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $message, $response]);
    
    echo json_encode([
        'success' => true,
        'response' => $response,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = getCurrentUserId();
    
    $stmt = $pdo->prepare("SELECT message, response, timestamp FROM chat_logs WHERE user_id = ? ORDER BY timestamp DESC LIMIT 50");
    $stmt->execute([$userId]);
    $chatHistory = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'chat_history' => array_reverse($chatHistory)]);
}
?>