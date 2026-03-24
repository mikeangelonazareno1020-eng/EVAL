<?php
// ============================================================
// FILE: save_evaluation.php  (v2 – FK-aware, full error output)
// ============================================================
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['teacher_id']) || !in_array($_SESSION['role'] ?? '', ['teacher', 'programchair'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}
$teacher_id = (int) $_SESSION['teacher_id'];

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON.']);
    exit();
}

$form_id = (int) ($input['form_id'] ?? 0);
$evaluatee_id = (int) ($input['evaluatee_id'] ?? 0);
$responses = $input['responses'] ?? [];

if (!$form_id || !$evaluatee_id || empty($responses)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit();
}
if ($evaluatee_id === $teacher_id) {
    echo json_encode(['success' => false, 'message' => 'You cannot evaluate yourself.']);
    exit();
}

require_once '../includes/conn.php';
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed.']);
    exit();
}

// Verify form active
$chk = mysqli_query($conn, "SELECT id FROM eval_forms WHERE id=$form_id AND status='active' LIMIT 1");
if (!$chk || mysqli_num_rows($chk) === 0) {
    echo json_encode(['success' => false, 'message' => 'Form is not active.']);
    exit();
}

// Prevent duplicate
$dup = mysqli_query(
    $conn,
    "SELECT id FROM eval_assignments
     WHERE form_id=$form_id AND evaluator_id=$teacher_id
       AND evaluatee_id=$evaluatee_id AND status='submitted' LIMIT 1"
);
if ($dup && mysqli_num_rows($dup) > 0) {
    echo json_encode(['success' => false, 'message' => 'Already submitted.']);
    exit();
}

// Upsert assignment
$ins = mysqli_query(
    $conn,
    "INSERT INTO eval_assignments (form_id,evaluator_id,evaluatee_id,status,submitted_at)
     VALUES ($form_id,$teacher_id,$evaluatee_id,'submitted',NOW())
     ON DUPLICATE KEY UPDATE status='submitted',submitted_at=NOW()"
);

if (!$ins) {
    $err = mysqli_error($conn);
    $msg = (stripos($err, 'foreign key') !== false)
        ? 'DB setup error – run fix_eval_fk.sql in phpMyAdmin first. (' . $err . ')'
        : 'DB error: ' . $err;
    echo json_encode(['success' => false, 'message' => $msg]);
    exit();
}

$assignment_id = (int) mysqli_insert_id($conn);
if (!$assignment_id) {
    $r = mysqli_query(
        $conn,
        "SELECT id FROM eval_assignments
         WHERE form_id=$form_id AND evaluator_id=$teacher_id AND evaluatee_id=$evaluatee_id LIMIT 1"
    );
    $assignment_id = (int) (mysqli_fetch_assoc($r)['id'] ?? 0);
}
if (!$assignment_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot resolve assignment ID.']);
    exit();
}

// Save responses
$errors = [];
foreach ($responses as $resp) {
    $q_id = (int) ($resp['question_id'] ?? 0);
    if (!$q_id)
        continue;
    if (($resp['type'] ?? '') === 'quantitative') {
        $rv = (int) $resp['value'];
        $tv = 'NULL';
    } else {
        $rv = 'NULL';
        $tv = "'" . mysqli_real_escape_string($conn, trim((string) ($resp['value'] ?? ''))) . "'";
    }
    $ok = mysqli_query(
        $conn,
        "INSERT INTO eval_responses (assignment_id,question_id,rating_value,text_response)
         VALUES ($assignment_id,$q_id,$rv,$tv)
         ON DUPLICATE KEY UPDATE rating_value=VALUES(rating_value),text_response=VALUES(text_response)"
    );
    if (!$ok)
        $errors[] = mysqli_error($conn);
}

echo json_encode(
    empty($errors)
    ? ['success' => true, 'message' => 'Submitted successfully.']
    : ['success' => false, 'message' => 'Partial error: ' . implode(' | ', array_unique($errors))]
);