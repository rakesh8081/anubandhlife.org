<?php
// save_data.php
require 'db_config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if ($action === 'register') {
    // 1. Save Participant
    try {
        $stmt = $pdo->prepare("INSERT INTO participants (full_name, email, age, sex, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['age'],
            $data['sex'],
            $data['phone']
        ]);
        $lastId = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'participant_id' => $lastId]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 

elseif ($action === 'save_results') {
    // 2. Save Test Scores
    try {
        $stmt = $pdo->prepare("INSERT INTO interest_results (participant_id, raw_responses, calculated_scores) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['participant_id'],
            json_encode($data['responses']),
            json_encode($data['scores'])
        ]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>