<?php
// File: api_student.php
// Path: /ClientBackend/api_student.php
// Student-facing API
// Actions: my_subjects, get_questions, submit_evaluation,
//          my_submissions, my_detail

session_start();

if (!isset($_SESSION['client_id']) || $_SESSION['client_type'] !== 'student') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

header('Content-Type: application/json');
require_once 'connection.php';
$con = conn();

$action = $_GET['action'] ?? '';

switch ($action) {

    // ── MY SUBJECTS ───────────────────────────────────────────
    case 'my_subjects':
        $stud_id = (int) ($_SESSION['client_id']);

        $sql = "SELECT
                    ss.id           AS section_id,
                    ss.section_name,
                    ss.school_year,
                    ss.semester,
                    ss.schedule,
                    ss.room,
                    ss.branch,
                    c.id            AS course_id,
                    c.course_code,
                    c.course_name,
                    f.fac_id        AS faculty_id,
                    f.full_name     AS teacher_name,
                    f.role          AS teacher_role,
                    CASE WHEN EXISTS (
                        SELECT 1 FROM tbl_student_eval_responses ser
                        WHERE ser.section_id = ss.id AND ser.stud_id = ?
                    ) THEN 1 ELSE 0 END AS already_evaluated
                FROM tbl_student_enrollments e
                JOIN tbl_subject_sections ss ON ss.id    = e.section_id
                JOIN courses c               ON c.id     = ss.course_id
                JOIN tbl_faculty f           ON f.fac_id = ss.faculty_id
                WHERE e.stud_id = ?
                  AND ss.is_active = 1
                ORDER BY ss.semester, c.course_name";

        $stmt = mysqli_prepare($con, $sql);
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Query prepare error: ' . mysqli_error($con)]);
            break;
        }

        mysqli_stmt_bind_param($stmt, 'ii', $stud_id, $stud_id);
        mysqli_stmt_execute($stmt);
        $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        foreach ($rows as &$row) {
            $row['already_evaluated'] = (int) $row['already_evaluated'];
        }

        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── GET QUESTIONS ─────────────────────────────────────────
    case 'get_questions':
        $result = mysqli_query(
            $con,
            "SELECT id, question, type, category, sort_order
             FROM tbl_student_eval_questions
             WHERE is_active = 1
             ORDER BY sort_order ASC"
        );

        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Query error: ' . mysqli_error($con)]);
            break;
        }

        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── SUBMIT EVALUATION ─────────────────────────────────────
    case 'submit_evaluation':
        $body = json_decode(file_get_contents('php://input'), true);
        $section_id = (int) ($body['section_id'] ?? 0);
        $faculty_id = (int) ($body['faculty_id'] ?? 0);
        $stud_id = (int) ($_SESSION['client_id']);
        $responses = $body['responses'] ?? [];

        if (!$section_id || !$faculty_id || empty($responses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid submission data.']);
            break;
        }

        // Verify enrollment
        $chk = mysqli_prepare(
            $con,
            "SELECT id FROM tbl_student_enrollments
             WHERE section_id = ? AND stud_id = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($chk, 'ii', $section_id, $stud_id);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) === 0) {
            mysqli_stmt_close($chk);
            echo json_encode(['success' => false, 'message' => 'You are not enrolled in this section.']);
            break;
        }
        mysqli_stmt_close($chk);

        // Verify teacher teaches this section
        $chkF = mysqli_prepare(
            $con,
            "SELECT id FROM tbl_subject_sections
             WHERE id = ? AND faculty_id = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($chkF, 'ii', $section_id, $faculty_id);
        mysqli_stmt_execute($chkF);
        mysqli_stmt_store_result($chkF);
        if (mysqli_stmt_num_rows($chkF) === 0) {
            mysqli_stmt_close($chkF);
            echo json_encode(['success' => false, 'message' => 'Invalid teacher for this section.']);
            break;
        }
        mysqli_stmt_close($chkF);

        // Check not already submitted
        $chkDup = mysqli_prepare(
            $con,
            "SELECT COUNT(*) AS c FROM tbl_student_eval_responses
             WHERE section_id = ? AND stud_id = ?"
        );
        mysqli_stmt_bind_param($chkDup, 'ii', $section_id, $stud_id);
        mysqli_stmt_execute($chkDup);
        $dupCount = mysqli_fetch_assoc(mysqli_stmt_get_result($chkDup))['c'];
        mysqli_stmt_close($chkDup);

        if ($dupCount > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'You have already submitted an evaluation for this teacher.'
            ]);
            break;
        }

        // Insert all responses in a transaction
        mysqli_begin_transaction($con);
        $stmt = mysqli_prepare(
            $con,
            "INSERT INTO tbl_student_eval_responses
                (section_id, stud_id, faculty_id, question_id, rating_value, text_response)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $allOk = true;
        foreach ($responses as $resp) {
            $question_id = (int) ($resp['question_id'] ?? 0);
            $rating_value = isset($resp['rating_value']) && $resp['rating_value'] !== null ? (int) $resp['rating_value'] : null;
            $text_response = isset($resp['text_response']) && $resp['text_response'] !== null ? trim($resp['text_response']) : null;

            if (!$question_id)
                continue;

            mysqli_stmt_bind_param(
                $stmt,
                'iiiiss',
                $section_id,
                $stud_id,
                $faculty_id,
                $question_id,
                $rating_value,
                $text_response
            );

            if (!mysqli_stmt_execute($stmt)) {
                $allOk = false;
                break;
            }
        }
        mysqli_stmt_close($stmt);

        if ($allOk) {
            mysqli_commit($con);
            echo json_encode(['success' => true, 'message' => 'Evaluation submitted successfully. Thank you!']);
        } else {
            mysqli_rollback($con);
            echo json_encode(['success' => false, 'message' => 'Failed to save: ' . mysqli_error($con)]);
        }
        break;

    // ── MY SUBMISSIONS ────────────────────────────────────────
    case 'my_submissions':
        $stud_id = (int) ($_SESSION['client_id']);

        $stmt = mysqli_prepare(
            $con,
            "SELECT ser.section_id,
                    MIN(ser.submitted_at) AS submitted_at,
                    f.fac_id   AS faculty_id,
                    f.full_name AS teacher_name,
                    f.role      AS teacher_role,
                    c.course_code,
                    c.course_name,
                    ss.section_name,
                    ss.school_year,
                    ss.semester
             FROM tbl_student_eval_responses ser
             JOIN tbl_subject_sections ss ON ss.id    = ser.section_id
             JOIN courses c               ON c.id     = ss.course_id
             JOIN tbl_faculty f           ON f.fac_id = ser.faculty_id
             WHERE ser.stud_id = ?
             GROUP BY ser.section_id, ser.faculty_id
             ORDER BY MIN(ser.submitted_at) DESC"
        );

        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => mysqli_error($con)]);
            break;
        }

        mysqli_stmt_bind_param($stmt, 'i', $stud_id);
        mysqli_stmt_execute($stmt);
        $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── MY DETAIL ─────────────────────────────────────────────
    case 'my_detail':
        $stud_id = (int) ($_SESSION['client_id']);
        $section_id = (int) ($_GET['section_id'] ?? 0);

        if (!$section_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid section.']);
            break;
        }

        // Info
        $is = mysqli_prepare(
            $con,
            "SELECT st.student_id,
                    st.full_name     AS student_name,
                    f.full_name      AS teacher_name,
                    f.role           AS teacher_role,
                    c.course_code,
                    c.course_name,
                    ss.section_name,
                    ss.school_year,
                    ss.semester,
                    MIN(ser.submitted_at) AS submitted_at
             FROM tbl_student_eval_responses ser
             JOIN tbl_students st         ON st.stud_id  = ser.stud_id
             JOIN tbl_subject_sections ss ON ss.id       = ser.section_id
             JOIN courses c               ON c.id        = ss.course_id
             JOIN tbl_faculty f           ON f.fac_id    = ser.faculty_id
             WHERE ser.stud_id = ? AND ser.section_id = ?
             GROUP BY f.fac_id, ss.id
             LIMIT 1"
        );

        if (!$is) {
            echo json_encode(['success' => false, 'message' => mysqli_error($con)]);
            break;
        }

        mysqli_stmt_bind_param($is, 'ii', $stud_id, $section_id);
        mysqli_stmt_execute($is);
        $info = mysqli_fetch_assoc(mysqli_stmt_get_result($is));
        mysqli_stmt_close($is);

        if (!$info) {
            echo json_encode(['success' => false, 'message' => 'Submission not found.']);
            break;
        }

        // Responses with questions
        $rs = mysqli_prepare(
            $con,
            "SELECT q.id AS question_id,
                    q.question,
                    q.type,
                    q.category,
                    q.sort_order,
                    ser.rating_value,
                    ser.text_response
             FROM tbl_student_eval_responses ser
             JOIN tbl_student_eval_questions q ON q.id = ser.question_id
             WHERE ser.stud_id = ? AND ser.section_id = ?
             ORDER BY q.sort_order ASC"
        );

        mysqli_stmt_bind_param($rs, 'ii', $stud_id, $section_id);
        mysqli_stmt_execute($rs);
        $responses = mysqli_fetch_all(mysqli_stmt_get_result($rs), MYSQLI_ASSOC);
        mysqli_stmt_close($rs);

        echo json_encode(['success' => true, 'data' => ['info' => $info, 'responses' => $responses]]);
        break;

    // ── FALLBACK ──────────────────────────────────────────────
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}

mysqli_close($con);
?>