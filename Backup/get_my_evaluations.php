<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['teacher_id']) || !in_array($_SESSION['role'] ?? '', ['teacher', 'programchair'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}
$teacher_id = (int) $_SESSION['teacher_id'];
require_once '../includes/conn.php';
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed.']);
    exit();
}

$sql = "SELECT ea.id AS assignment_id, ea.submitted_at, ea.evaluatee_id,
        tf.full_name AS evaluatee_name, tf.employee_id AS evaluatee_emp_id,
        tf.program AS evaluatee_program, tf.branch AS evaluatee_branch,
        ef.id AS form_id, ef.title AS form_title, ef.school_year, ef.semester
        FROM eval_assignments ea
        JOIN eval_forms  ef ON ef.id = ea.form_id
        JOIN tbl_faculty tf ON tf.fac_id = ea.evaluatee_id
        WHERE ea.evaluator_id = $teacher_id AND ea.status = 'submitted'
        ORDER BY ea.submitted_at DESC";

$res = mysqli_query($conn, $sql);
if (!$res) {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    exit();
}

$evaluations = [];
while ($row = mysqli_fetch_assoc($res)) {
    $aid = (int) $row['assignment_id'];
    $qsql = "SELECT eq.id AS question_id, eq.question, eq.type,
             er.rating_value, er.text_response,
             ef2.rating_labels, ef2.rating_min
             FROM eval_questions eq
             JOIN eval_responses er  ON er.question_id = eq.id AND er.assignment_id = $aid
             JOIN eval_forms     ef2 ON ef2.id = eq.form_id
             WHERE eq.form_id = {$row['form_id']}
             ORDER BY eq.sort_order ASC";
    $qres = mysqli_query($conn, $qsql);
    $items = [];
    if ($qres) {
        while ($qrow = mysqli_fetch_assoc($qres)) {
            $entry = ['question_id' => $qrow['question_id'], 'question' => $qrow['question'], 'type' => $qrow['type']];
            if ($qrow['type'] === 'quantitative') {
                $labels = explode(',', $qrow['rating_labels']);
                $idx = (int) $qrow['rating_value'] - (int) $qrow['rating_min'];
                $entry['response'] = isset($labels[$idx]) ? trim($labels[$idx]) : $qrow['rating_value'];
            } else {
                $entry['response'] = $qrow['text_response'];
            }
            $items[] = $entry;
        }
    }
    $evaluations[] = [
        'assignment_id' => $aid,
        'submitted_at' => $row['submitted_at'],
        'evaluatee_id' => (int) $row['evaluatee_id'],
        'evaluatee_name' => $row['evaluatee_name'],
        'evaluatee_emp_id' => $row['evaluatee_emp_id'],
        'evaluatee_program' => $row['evaluatee_program'],
        'evaluatee_branch' => $row['evaluatee_branch'],
        'form_id' => (int) $row['form_id'],
        'form_title' => $row['form_title'],
        'school_year' => $row['school_year'],
        'semester' => $row['semester'],
        'responses' => $items,
    ];
}
echo json_encode(['success' => true, 'data' => $evaluations]);