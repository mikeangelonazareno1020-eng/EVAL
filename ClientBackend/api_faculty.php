<?php
// File: api_faculty.php
// Path: /ClientBackend/api_faculty.php
// Faculty-facing API for peer (teacher) and chair evaluations
// Actions: get_staff, get_dept_staff, get_peer_questions, get_chair_questions,
//          submit_peer_eval, submit_chair_eval,
//          my_peer_submissions, my_peer_detail,
//          my_chair_submissions, my_chair_detail

session_start();

if (!isset($_SESSION['client_id']) || $_SESSION['client_type'] !== 'faculty') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

header('Content-Type: application/json');
require_once 'connection.php';
$con = conn();

$action = $_GET['action'] ?? '';
$fac_id = (int) $_SESSION['client_id'];
$fac_role = $_SESSION['client_role'] ?? 'teacher';

switch ($action) {

    // ── GET ALL STAFF (for teacher peer eval) ─────────────────
    case 'get_staff':
        // Always use session ID — never trust GET for security
        $me = $fac_id;

        $stmt = mysqli_prepare(
            $con,
            "SELECT f.fac_id, f.full_name, f.department, f.program, f.course, f.branch, f.role,
                    CASE WHEN EXISTS (
                        SELECT 1 FROM tbl_peer_eval_responses per
                        WHERE per.evaluator_id = ? AND per.evaluatee_id = f.fac_id
                    ) THEN 1 ELSE 0 END AS already_evaluated
             FROM tbl_faculty f
             WHERE f.fac_id != ? AND f.is_active = 1
             ORDER BY f.department, f.full_name"
        );
        mysqli_stmt_bind_param($stmt, 'ii', $me, $me);
        mysqli_stmt_execute($stmt);
        $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        foreach ($rows as &$r)
            $r['already_evaluated'] = (int) $r['already_evaluated'];

        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── GET DEPT STAFF (for program chair eval) ───────────────
    case 'get_dept_staff':
        $me = $fac_id; // Always use session ID

        $dStmt = mysqli_prepare(
            $con,
            "SELECT department FROM tbl_faculty WHERE fac_id = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($dStmt, 'i', $me);
        mysqli_stmt_execute($dStmt);
        $dept = mysqli_fetch_assoc(mysqli_stmt_get_result($dStmt))['department'] ?? '';
        mysqli_stmt_close($dStmt);

        $stmt = mysqli_prepare(
            $con,
            "SELECT f.fac_id, f.full_name, f.department, f.program, f.course, f.branch, f.role,
                    CASE WHEN EXISTS (
                        SELECT 1 FROM tbl_chair_eval_responses cer
                        WHERE cer.evaluator_id = ? AND cer.evaluatee_id = f.fac_id
                    ) THEN 1 ELSE 0 END AS already_evaluated
             FROM tbl_faculty f
             WHERE f.fac_id != ?
               AND f.department = ?
               AND f.role = 'teacher'
               AND f.is_active = 1
             ORDER BY f.full_name"
        );
        mysqli_stmt_bind_param($stmt, 'iis', $me, $me, $dept);
        mysqli_stmt_execute($stmt);
        $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        foreach ($rows as &$r)
            $r['already_evaluated'] = (int) $r['already_evaluated'];

        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── GET PEER EVAL QUESTIONS ───────────────────────────────
    case 'get_peer_questions':
        $rows = mysqli_fetch_all(
            mysqli_query(
                $con,
                "SELECT id, question, type, category, sort_order
                 FROM tbl_peer_eval_questions
                 WHERE is_active = 1 ORDER BY sort_order"
            ),
            MYSQLI_ASSOC
        );
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── GET CHAIR EVAL QUESTIONS ──────────────────────────────
    case 'get_chair_questions':
        $rows = mysqli_fetch_all(
            mysqli_query(
                $con,
                "SELECT id, question, type, category, sort_order
                 FROM tbl_chair_eval_questions
                 WHERE is_active = 1 ORDER BY sort_order"
            ),
            MYSQLI_ASSOC
        );
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── SUBMIT PEER EVAL (teacher → teacher) ──────────────────
    case 'submit_peer_eval':
        $b = json_decode(file_get_contents('php://input'), true);
        $evaluator_id = (int) ($b['evaluator_id'] ?? 0);
        $evaluatee_id = (int) ($b['evaluatee_id'] ?? 0);
        $school_year = trim($b['school_year'] ?? '');
        $semester = trim($b['semester'] ?? '1st');
        $responses = $b['responses'] ?? [];

        // Security: evaluator must be the logged-in user
        if ($evaluator_id !== $fac_id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized evaluator.']);
            break;
        }
        if (!$evaluatee_id || empty($responses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid submission.']);
            break;
        }
        // Cannot evaluate yourself
        if ($evaluator_id === $evaluatee_id) {
            echo json_encode(['success' => false, 'message' => 'You cannot evaluate yourself.']);
            break;
        }
        // Duplicate check
        $chk = mysqli_prepare(
            $con,
            "SELECT COUNT(*) AS c FROM tbl_peer_eval_responses
             WHERE evaluator_id=? AND evaluatee_id=? AND school_year=? AND semester=?"
        );
        mysqli_stmt_bind_param($chk, 'iiss', $evaluator_id, $evaluatee_id, $school_year, $semester);
        mysqli_stmt_execute($chk);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($chk))['c'] > 0) {
            mysqli_stmt_close($chk);
            echo json_encode(['success' => false, 'message' => 'You have already submitted a peer evaluation for this colleague.']);
            break;
        }
        mysqli_stmt_close($chk);

        mysqli_begin_transaction($con);
        $ins = mysqli_prepare(
            $con,
            "INSERT INTO tbl_peer_eval_responses
                (evaluator_id,evaluatee_id,question_id,rating_value,text_response,school_year,semester)
             VALUES (?,?,?,?,?,?,?)"
        );

        $ok = true;
        foreach ($responses as $r) {
            $qid = (int) ($r['question_id'] ?? 0);
            $rv = isset($r['rating_value']) && $r['rating_value'] !== null ? (int) $r['rating_value'] : null;
            $txt = isset($r['text_response']) && $r['text_response'] !== null ? trim($r['text_response']) : null;
            if (!$qid)
                continue;
            mysqli_stmt_bind_param(
                $ins,
                'iiiisss',
                $evaluator_id,
                $evaluatee_id,
                $qid,
                $rv,
                $txt,
                $school_year,
                $semester
            );
            if (!mysqli_stmt_execute($ins)) {
                $ok = false;
                break;
            }
        }
        mysqli_stmt_close($ins);

        if ($ok) {
            mysqli_commit($con);
            echo json_encode(['success' => true, 'message' => 'Peer evaluation submitted.']);
        } else {
            mysqli_rollback($con);
            echo json_encode(['success' => false, 'message' => 'Failed: ' . mysqli_error($con)]);
        }
        break;

    // ── SUBMIT CHAIR EVAL (programchair → teacher) ────────────
    case 'submit_chair_eval':
        $b = json_decode(file_get_contents('php://input'), true);
        $evaluator_id = (int) ($b['evaluator_id'] ?? 0);
        $evaluatee_id = (int) ($b['evaluatee_id'] ?? 0);
        $school_year = trim($b['school_year'] ?? '');
        $semester = trim($b['semester'] ?? '1st');
        $responses = $b['responses'] ?? [];

        if ($evaluator_id !== $fac_id || $fac_role !== 'programchair') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
            break;
        }
        if (!$evaluatee_id || empty($responses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid submission.']);
            break;
        }

        // Verify evaluatee is a teacher in same dept
        $vStmt = mysqli_prepare(
            $con,
            "SELECT fac_id FROM tbl_faculty
             WHERE fac_id=? AND role='teacher' AND department=(
                SELECT department FROM tbl_faculty WHERE fac_id=? LIMIT 1
             ) LIMIT 1"
        );
        mysqli_stmt_bind_param($vStmt, 'ii', $evaluatee_id, $evaluator_id);
        mysqli_stmt_execute($vStmt);
        mysqli_stmt_store_result($vStmt);
        if (mysqli_stmt_num_rows($vStmt) === 0) {
            mysqli_stmt_close($vStmt);
            echo json_encode(['success' => false, 'message' => 'This teacher is not in your department.']);
            break;
        }
        mysqli_stmt_close($vStmt);

        // Duplicate check
        $chk = mysqli_prepare(
            $con,
            "SELECT COUNT(*) AS c FROM tbl_chair_eval_responses
             WHERE evaluator_id=? AND evaluatee_id=? AND school_year=? AND semester=?"
        );
        mysqli_stmt_bind_param($chk, 'iiss', $evaluator_id, $evaluatee_id, $school_year, $semester);
        mysqli_stmt_execute($chk);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($chk))['c'] > 0) {
            mysqli_stmt_close($chk);
            echo json_encode(['success' => false, 'message' => 'You have already submitted a chair evaluation for this teacher.']);
            break;
        }
        mysqli_stmt_close($chk);

        mysqli_begin_transaction($con);
        $ins = mysqli_prepare(
            $con,
            "INSERT INTO tbl_chair_eval_responses
                (evaluator_id,evaluatee_id,question_id,rating_value,text_response,school_year,semester)
             VALUES (?,?,?,?,?,?,?)"
        );

        $ok = true;
        foreach ($responses as $r) {
            $qid = (int) ($r['question_id'] ?? 0);
            $rv = isset($r['rating_value']) && $r['rating_value'] !== null ? (int) $r['rating_value'] : null;
            $txt = isset($r['text_response']) && $r['text_response'] !== null ? trim($r['text_response']) : null;
            if (!$qid)
                continue;
            mysqli_stmt_bind_param(
                $ins,
                'iiiisss',
                $evaluator_id,
                $evaluatee_id,
                $qid,
                $rv,
                $txt,
                $school_year,
                $semester
            );
            if (!mysqli_stmt_execute($ins)) {
                $ok = false;
                break;
            }
        }
        mysqli_stmt_close($ins);

        if ($ok) {
            mysqli_commit($con);
            echo json_encode(['success' => true, 'message' => 'Chair evaluation submitted.']);
        } else {
            mysqli_rollback($con);
            echo json_encode(['success' => false, 'message' => 'Failed: ' . mysqli_error($con)]);
        }
        break;

    // ── MY PEER SUBMISSIONS ───────────────────────────────────
    case 'my_peer_submissions':
        $me = (int) ($_GET['fac_id'] ?? $fac_id);
        $stmt = mysqli_prepare(
            $con,
            "SELECT per.evaluatee_id, MIN(per.submitted_at) AS submitted_at,
                    per.school_year, per.semester,
                    f.full_name AS evaluatee_name, f.department AS evaluatee_dept, f.role AS evaluatee_role
             FROM tbl_peer_eval_responses per
             JOIN tbl_faculty f ON f.fac_id = per.evaluatee_id
             WHERE per.evaluator_id = ?
             GROUP BY per.evaluatee_id, per.school_year, per.semester
             ORDER BY MIN(per.submitted_at) DESC"
        );
        mysqli_stmt_bind_param($stmt, 'i', $me);
        mysqli_stmt_execute($stmt);
        $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── MY PEER DETAIL ────────────────────────────────────────
    case 'my_peer_detail':
        $me = (int) ($_GET['evaluator_id'] ?? $fac_id);
        $evaluatee_id = (int) ($_GET['evaluatee_id'] ?? 0);
        $school_year = trim($_GET['school_year'] ?? '');
        $semester = trim($_GET['semester'] ?? '');

        $iStmt = mysqli_prepare(
            $con,
            "SELECT f.full_name AS evaluatee_name, f.department AS evaluatee_dept, f.role AS evaluatee_role,
                    per.school_year, per.semester, MIN(per.submitted_at) AS submitted_at
             FROM tbl_peer_eval_responses per
             JOIN tbl_faculty f ON f.fac_id = per.evaluatee_id
             WHERE per.evaluator_id=? AND per.evaluatee_id=? AND per.school_year=? AND per.semester=?
             GROUP BY f.fac_id LIMIT 1"
        );
        mysqli_stmt_bind_param($iStmt, 'iiss', $me, $evaluatee_id, $school_year, $semester);
        mysqli_stmt_execute($iStmt);
        $info = mysqli_fetch_assoc(mysqli_stmt_get_result($iStmt));
        mysqli_stmt_close($iStmt);
        if (!$info) {
            echo json_encode(['success' => false, 'message' => 'Not found.']);
            break;
        }

        $rStmt = mysqli_prepare(
            $con,
            "SELECT q.id AS question_id, q.question, q.type, q.category, q.sort_order,
                    per.rating_value, per.text_response
             FROM tbl_peer_eval_responses per
             JOIN tbl_peer_eval_questions q ON q.id = per.question_id
             WHERE per.evaluator_id=? AND per.evaluatee_id=? AND per.school_year=? AND per.semester=?
             ORDER BY q.sort_order"
        );
        mysqli_stmt_bind_param($rStmt, 'iiss', $me, $evaluatee_id, $school_year, $semester);
        mysqli_stmt_execute($rStmt);
        $responses = mysqli_fetch_all(mysqli_stmt_get_result($rStmt), MYSQLI_ASSOC);
        mysqli_stmt_close($rStmt);

        echo json_encode(['success' => true, 'data' => ['info' => $info, 'responses' => $responses]]);
        break;

    // ── MY CHAIR SUBMISSIONS ──────────────────────────────────
    case 'my_chair_submissions':
        $me = (int) ($_GET['fac_id'] ?? $fac_id);
        $stmt = mysqli_prepare(
            $con,
            "SELECT cer.evaluatee_id, MIN(cer.submitted_at) AS submitted_at,
                    cer.school_year, cer.semester,
                    f.full_name AS evaluatee_name, f.department AS evaluatee_dept
             FROM tbl_chair_eval_responses cer
             JOIN tbl_faculty f ON f.fac_id = cer.evaluatee_id
             WHERE cer.evaluator_id = ?
             GROUP BY cer.evaluatee_id, cer.school_year, cer.semester
             ORDER BY MIN(cer.submitted_at) DESC"
        );
        mysqli_stmt_bind_param($stmt, 'i', $me);
        mysqli_stmt_execute($stmt);
        $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── MY CHAIR DETAIL ───────────────────────────────────────
    case 'my_chair_detail':
        $me = (int) ($_GET['evaluator_id'] ?? $fac_id);
        $evaluatee_id = (int) ($_GET['evaluatee_id'] ?? 0);
        $school_year = trim($_GET['school_year'] ?? '');
        $semester = trim($_GET['semester'] ?? '');

        $iStmt = mysqli_prepare(
            $con,
            "SELECT f.full_name AS evaluatee_name, f.department AS evaluatee_dept,
                    cer.school_year, cer.semester, MIN(cer.submitted_at) AS submitted_at
             FROM tbl_chair_eval_responses cer
             JOIN tbl_faculty f ON f.fac_id = cer.evaluatee_id
             WHERE cer.evaluator_id=? AND cer.evaluatee_id=? AND cer.school_year=? AND cer.semester=?
             GROUP BY f.fac_id LIMIT 1"
        );
        mysqli_stmt_bind_param($iStmt, 'iiss', $me, $evaluatee_id, $school_year, $semester);
        mysqli_stmt_execute($iStmt);
        $info = mysqli_fetch_assoc(mysqli_stmt_get_result($iStmt));
        mysqli_stmt_close($iStmt);
        if (!$info) {
            echo json_encode(['success' => false, 'message' => 'Not found.']);
            break;
        }

        $rStmt = mysqli_prepare(
            $con,
            "SELECT q.id AS question_id, q.question, q.type, q.category, q.sort_order,
                    cer.rating_value, cer.text_response
             FROM tbl_chair_eval_responses cer
             JOIN tbl_chair_eval_questions q ON q.id = cer.question_id
             WHERE cer.evaluator_id=? AND cer.evaluatee_id=? AND cer.school_year=? AND cer.semester=?
             ORDER BY q.sort_order"
        );
        mysqli_stmt_bind_param($rStmt, 'iiss', $me, $evaluatee_id, $school_year, $semester);
        mysqli_stmt_execute($rStmt);
        $responses = mysqli_fetch_all(mysqli_stmt_get_result($rStmt), MYSQLI_ASSOC);
        mysqli_stmt_close($rStmt);

        echo json_encode(['success' => true, 'data' => ['info' => $info, 'responses' => $responses]]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}

mysqli_close($con);
?>