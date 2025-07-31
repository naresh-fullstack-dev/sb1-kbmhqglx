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
    
    $pdfIds = $input['pdf_ids'] ?? [];
    $difficulty = $input['difficulty'] ?? 'medium';
    $numQuestions = intval($input['num_questions'] ?? 10);
    $questionTypes = $input['question_types'] ?? ['mcq'];
    $marksPerQuestion = intval($input['marks_per_question'] ?? 1);
    
    if (empty($pdfIds)) {
        echo json_encode(['success' => false, 'message' => 'Please select at least one PDF']);
        exit;
    }
    
    // Get PDF information for context
    $placeholders = str_repeat('?,', count($pdfIds) - 1) . '?';
    $stmt = $pdo->prepare("SELECT title, subject, chapter FROM pdfs WHERE id IN ($placeholders)");
    $stmt->execute($pdfIds);
    $pdfs = $stmt->fetchAll();
    
    // Build context for AI
    $context = "Generate educational questions based on the following study materials: ";
    foreach ($pdfs as $pdf) {
        $context .= $pdf['title'] . " (" . $pdf['subject'] . " - " . $pdf['chapter'] . "), ";
    }
    $context = rtrim($context, ', ') . ". ";
    
    // Question type instructions
    $typeInstructions = [
        'mcq' => 'multiple choice questions with 4 options (A, B, C, D)',
        'short_answer' => 'short answer questions requiring 1-2 sentence responses',
        'long_answer' => 'long answer questions requiring detailed explanations',
        'fill_blanks' => 'fill in the blanks questions with missing key terms',
        'true_false' => 'true/false questions with clear statements',
        'assertion_reason' => 'assertion-reason questions with two statements to evaluate'
    ];
    
    $selectedTypes = array_intersect_key($typeInstructions, array_flip($questionTypes));
    $context .= "Create " . $numQuestions . " questions with " . $difficulty . " difficulty level. ";
    $context .= "Include these question types: " . implode(', ', $selectedTypes) . ". ";
    $context .= "Each question should be worth " . $marksPerQuestion . " mark(s). ";
    $context .= "Format the response as a JSON array with objects containing: type, difficulty, question, marks fields.";
    
    // OpenAI API call
    $apiKey = 'sk-or-v1-5bb2ef40a9478fff23986ae47a449be8212fbf4f54601706bb8c609a5ad7ac6aa';
    $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
    
    $data = [
        'model' => 'openai/gpt-3.5-turbo',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are an educational content generator. Create high-quality academic questions based on the given study materials. Always respond with valid JSON format.'
            ],
            [
                'role' => 'user',
                'content' => $context
            ]
        ],
        'max_tokens' => 1500,
        'temperature' => 0.8
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
    
    $generatedQuestions = [];
    
    if ($result !== FALSE) {
        $apiResponse = json_decode($result, true);
        if (isset($apiResponse['choices'][0]['message']['content'])) {
            $content = $apiResponse['choices'][0]['message']['content'];
            
            // Try to extract JSON from the response
            $jsonStart = strpos($content, '[');
            $jsonEnd = strrpos($content, ']');
            
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonContent = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
                $decodedQuestions = json_decode($jsonContent, true);
                
                if (is_array($decodedQuestions)) {
                    $generatedQuestions = array_slice($decodedQuestions, 0, $numQuestions);
                }
            }
        }
    }
    
    // Fallback if AI generation fails
    if (empty($generatedQuestions)) {
        $fallbackQuestions = [
            'What are the key concepts covered in the selected study materials?',
            'Explain the main principles discussed in the chapters.',
            'Define the important terms from the subject matter.',
            'How do the concepts relate to real-world applications?',
            'What are the fundamental theories presented in the materials?'
        ];
        
        for ($i = 0; $i < min($numQuestions, count($fallbackQuestions)); $i++) {
            $generatedQuestions[] = [
                'type' => $questionTypes[0],
                'difficulty' => $difficulty,
                'question' => $fallbackQuestions[$i],
                'marks' => $marksPerQuestion
            ];
        }
    }
    
    // Save questions to database
    foreach ($generatedQuestions as $question) {
        $stmt = $pdo->prepare("INSERT INTO questions (pdf_id, type, difficulty, question_text, marks) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$pdfIds[0], $question['type'], $question['difficulty'], $question['question'], $question['marks']]);
    }
    
    echo json_encode([
        'success' => true,
        'questions' => $generatedQuestions,
        'message' => 'Questions generated successfully'
    ]);
}
?>