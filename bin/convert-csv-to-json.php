<?php

if ($argc < 2) {
    echo "Uso: php convert-csv-to-json.php <directorio>\n";
    exit(1);
}

$dir = $argv[1];
$files = glob($dir . '/*.csv');

if (empty($files)) {
    echo "No se encontraron archivos CSV\n";
    exit(1);
}

$topics = [];
foreach ($files as $file) {
    $filename = basename($file, '.csv');
    
    if (!preg_match('/^(.+?)_(.+?)_(.+?)_L([1-3])$/', $filename, $matches)) {
        echo "Archivo ignorado: $filename\n";
        continue;
    }
    
    [, $categoryName, $topicName, $quizName, $levelNum] = $matches;
    
    $categoryName = trim(str_replace('_', ' ', $categoryName));
    $topicName = trim(str_replace('_', ' ', $topicName));
    
    $sanitize = function ($name) {
        $name = str_replace('--', '###', $name);
        $name = str_replace('-', ' ', $name);
        $name = str_replace('###', '-', $name);
        return trim(str_replace('_', ' ', $name));
    };
    
    $topicName = $sanitize($topicName);
    $quizName = $sanitize($quizName);
    
    $handle = fopen($file, 'r');
    $headers = fgetcsv($handle, 0, ';', '"') ?: [];
    
    $questions = [];
    $currentQuestion = null;
    $currentExplanation = null;
    
    while (($row = fgetcsv($handle, 0, ';', '"')) !== false) {
        $data = array_combine($headers, $row);
        $questionText = trim($data['question'] ?? '');
        $answerText = trim($data['answer'] ?? '');
        $isCorrect = strtolower(trim($data['correct'] ?? '')) === 'true';
        $explanation = isset($data['explanation']) ? trim($data['explanation']) : null;
        
        if (empty($questionText)) continue;
        
        if (!$currentQuestion || $currentQuestion !== $questionText) {
            if ($currentQuestion && !empty($answers)) {
                $questions[] = [
                    'text' => $currentQuestion,
                    'answers' => $answers
                ];
            }
            $currentQuestion = $questionText;
            $currentExplanation = $explanation;
            $answers = [];
        }
        
        $answers[] = [
            'text' => $answerText,
            'correct' => $isCorrect,
            'explanation' => $explanation
        ];
    }
    
    if ($currentQuestion && !empty($answers)) {
        $questions[] = [
            'text' => $currentQuestion,
            'answers' => $answers
        ];
    }
    
    fclose($handle);
    
    $topicKey = $topicName;
    if (!isset($topics[$topicKey])) {
        $topics[$topicKey] = [
            'category' => $categoryName,
            'name' => $topicName,
            'quizzes' => []
        ];
    }
    
    $topics[$topicKey]['quizzes'][] = [
        'quiz' => $quizName,
        'level' => (int) $levelNum,
        'questions' => $questions
    ];
}

foreach ($topics as $topicName => $data) {
    $filename = $dir . '/' . str_replace(' ', '-', $data['category']) . '_' . str_replace(' ', '-', $data['name']) . '.json';
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($filename, $json);
    echo "Creado: " . basename($filename) . " (" . count($data['quizzes']) . " quizzes)\n";
}

echo "Total: " . count($topics) . " archivos JSON\n";