<?php
// File: eval_submissions.php
// Path: /admin/api/eval_submissions.php
// API for all evaluation submissions (student, peer, chair)
// Actions: read, detail, stats, get_teachers, get_sections

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

header('Content-Type: application/json');
require_once '../../ClientBackend/connection.php';
$con = conn();

$action = $_GET['action'] ?? '';

switch ($action) {

    // ── READ (paginated list of ALL submissions) ───────────────
    case 'read':
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = min(100, max(1, (int) ($_GET['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;
        $search = trim($_GET['search'] ?? '');
        $type = trim($_GET['type'] ?? '');  // student | peer | chair
        $faculty_f = (int) ($_GET['faculty_id'] ?? 0);
        $semester = trim($_GET['semester'] ?? '');

        // Build a UNION of all three sources
        // Each row: eval_type, evaluator_name, evaluatee_name, subject/context, submitted_at, ids

        $rows = [];
        $total = 0;

        // ── 1. Student → Teacher ──────────────────────────────
        if ($type === '' || $type === 'student') {
            $w = [];
            $p = [];
            $t = '';
            if ($search !== '') {
                $w[] = "(st.full_name LIKE ? OR f.full_name LIKE ? OR c.course_name LIKE ?)";
                $lk = "%$search%";
                $p = array_merge($p, [$lk, $lk, $lk]);
                $t .= 'sss';
            }
            if ($faculty_f) {
                $w[] = "ser.faculty_id=?";
                $p[] = $faculty_f;
                $t .= 'i';
            }
            if ($semester !== '') {
                $w[] = "ss.semester=?";
                $p[] = $semester;
                $t .= 's';
            }
            $wSQL = $w ? 'WHERE ' . implode(' AND ', $w) : '';

            $cq = "SELECT COUNT(DISTINCT CONCAT(ser.stud_id,'_',ser.section_id)) AS c
                   FROM tbl_student_eval_responses ser
                   JOIN tbl_students st           ON st.stud_id  = ser.stud_id
                   JOIN tbl_subject_sections ss   ON ss.id       = ser.section_id
                   JOIN courses c                 ON c.id        = ss.course_id
                   JOIN tbl_faculty f             ON f.fac_id    = ser.faculty_id
                   $wSQL";
            $cs = mysqli_prepare($con, $cq);
            if ($p)
                mysqli_stmt_bind_param($cs, $t, ...$p);
            mysqli_stmt_execute($cs);
            $total += (int) mysqli_fetch_assoc(mysqli_stmt_get_result($cs))['c'];
            mysqli_stmt_close($cs);

            $dq = "SELECT 'student' AS eval_type,
                          st.full_name AS evaluator_name, 'Student' AS evaluator_role,
                          f.full_name  AS evaluatee_name, f.role    AS evaluatee_role,
                          c.course_name AS context,
                          ss.section_name, ss.semester, ss.school_year,
                          ser.stud_id AS evaluator_id, ser.section_id,
                          ser.faculty_id AS evaluatee_id,
                          MIN(ser.submitted_at) AS submitted_at
                   FROM tbl_student_eval_responses ser
                   JOIN tbl_students st           ON st.stud_id  = ser.stud_id
                   JOIN tbl_subject_sections ss   ON ss.id       = ser.section_id
                   JOIN courses c                 ON c.id        = ss.course_id
                   JOIN tbl_faculty f             ON f.fac_id    = ser.faculty_id
                   $wSQL
                   GROUP BY ser.stud_id, ser.section_id";
            $ds = mysqli_prepare($con, $dq);
            if ($p)
                mysqli_stmt_bind_param($ds, $t, ...$p);
            mysqli_stmt_execute($ds);
            $rows = array_merge($rows, mysqli_fetch_all(mysqli_stmt_get_result($ds), MYSQLI_ASSOC));
            mysqli_stmt_close($ds);
        }

        // ── 2. Teacher → Teacher (peer) ───────────────────────
        if ($type === '' || $type === 'peer') {
            $w = [];
            $p = [];
            $t = '';
            if ($search !== '') {
                $w[] = "(ev.full_name LIKE ? OR ee.full_name LIKE ?)";
                $lk = "%$search%";
                $p = array_merge($p, [$lk, $lk]);
                $t .= 'ss';
            }
            if ($faculty_f) {
                $w[] = "per.evaluatee_id=?";
                $p[] = $faculty_f;
                $t .= 'i';
            }
            if ($semester !== '') {
                $w[] = "per.semester=?";
                $p[] = $semester;
                $t .= 's';
            }
            $wSQL = $w ? 'WHERE ' . implode(' AND ', $w) : '';

            $cq = "SELECT COUNT(DISTINCT CONCAT(per.evaluator_id,'_',per.evaluatee_id,'_',per.school_year,'_',per.semester)) AS c
                   FROM tbl_peer_eval_responses per
                   JOIN tbl_faculty ev ON ev.fac_id = per.evaluator_id
                   JOIN tbl_faculty ee ON ee.fac_id = per.evaluatee_id
                   $wSQL";
            $cs = mysqli_prepare($con, $cq);
            if ($p)
                mysqli_stmt_bind_param($cs, $t, ...$p);
            mysqli_stmt_execute($cs);
            $total += (int) mysqli_fetch_assoc(mysqli_stmt_get_result($cs))['c'];
            mysqli_stmt_close($cs);

            $dq = "SELECT 'peer' AS eval_type,
                          ev.full_name AS evaluator_name, ev.role AS evaluator_role,
                          ee.full_name AS evaluatee_name, ee.role AS evaluatee_role,
                          'Peer Evaluation' AS context,
                          '' AS section_name, per.semester, per.school_year,
                          per.evaluator_id, NULL AS section_id,
                          per.evaluatee_id,
                          MIN(per.submitted_at) AS submitted_at
                   FROM tbl_peer_eval_responses per
                   JOIN tbl_faculty ev ON ev.fac_id = per.evaluator_id
                   JOIN tbl_faculty ee ON ee.fac_id = per.evaluatee_id
                   $wSQL
                   GROUP BY per.evaluator_id, per.evaluatee_id, per.school_year, per.semester";
            $ds = mysqli_prepare($con, $dq);
            if ($p)
                mysqli_stmt_bind_param($ds, $t, ...$p);
            mysqli_stmt_execute($ds);
            $rows = array_merge($rows, mysqli_fetch_all(mysqli_stmt_get_result($ds), MYSQLI_ASSOC));
            mysqli_stmt_close($ds);
        }

        // ── 3. Chair → Teacher ────────────────────────────────
        if ($type === '' || $type === 'chair') {
            $w = [];
            $p = [];
            $t = '';
            if ($search !== '') {
                $w[] = "(ev.full_name LIKE ? OR ee.full_name LIKE ?)";
                $lk = "%$search%";
                $p = array_merge($p, [$lk, $lk]);
                $t .= 'ss';
            }
            if ($faculty_f) {
                $w[] = "cer.evaluatee_id=?";
                $p[] = $faculty_f;
                $t .= 'i';
            }
            if ($semester !== '') {
                $w[] = "cer.semester=?";
                $p[] = $semester;
                $t .= 's';
            }
            $wSQL = $w ? 'WHERE ' . implode(' AND ', $w) : '';

            $cq = "SELECT COUNT(DISTINCT CONCAT(cer.evaluator_id,'_',cer.evaluatee_id,'_',cer.school_year,'_',cer.semester)) AS c
                   FROM tbl_chair_eval_responses cer
                   JOIN tbl_faculty ev ON ev.fac_id = cer.evaluator_id
                   JOIN tbl_faculty ee ON ee.fac_id = cer.evaluatee_id
                   $wSQL";
            $cs = mysqli_prepare($con, $cq);
            if ($p)
                mysqli_stmt_bind_param($cs, $t, ...$p);
            mysqli_stmt_execute($cs);
            $total += (int) mysqli_fetch_assoc(mysqli_stmt_get_result($cs))['c'];
            mysqli_stmt_close($cs);

            $dq = "SELECT 'chair' AS eval_type,
                          ev.full_name AS evaluator_name, ev.role AS evaluator_role,
                          ee.full_name AS evaluatee_name, ee.role AS evaluatee_role,
                          'Chair Evaluation' AS context,
                          '' AS section_name, cer.semester, cer.school_year,
                          cer.evaluator_id, NULL AS section_id,
                          cer.evaluatee_id,
                          MIN(cer.submitted_at) AS submitted_at
                   FROM tbl_chair_eval_responses cer
                   JOIN tbl_faculty ev ON ev.fac_id = cer.evaluator_id
                   JOIN tbl_faculty ee ON ee.fac_id = cer.evaluatee_id
                   $wSQL
                   GROUP BY cer.evaluator_id, cer.evaluatee_id, cer.school_year, cer.semester";
            $ds = mysqli_prepare($con, $dq);
            if ($p)
                mysqli_stmt_bind_param($ds, $t, ...$p);
            mysqli_stmt_execute($ds);
            $rows = array_merge($rows, mysqli_fetch_all(mysqli_stmt_get_result($ds), MYSQLI_ASSOC));
            mysqli_stmt_close($ds);
        }

        // Sort all combined by submitted_at DESC then paginate
        usort($rows, fn($a, $b) => strtotime($b['submitted_at']) - strtotime($a['submitted_at']));
        $paginated = array_slice($rows, $offset, $limit);
        $total = count($rows);

        echo json_encode([
            'success' => true,
            'data' => $paginated,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => max(1, (int) ceil($total / $limit)),
                'total_records' => $total,
                'records_per_page' => $limit
            ]
        ]);
        break;

    // ── DETAIL ─────────────────────────────────────────────────
    case 'detail':
        $eval_type = $_GET['eval_type'] ?? '';
        $evaluator_id = (int) ($_GET['evaluator_id'] ?? 0);
        $evaluatee_id = (int) ($_GET['evaluatee_id'] ?? 0);
        $section_id = (int) ($_GET['section_id'] ?? 0);
        $school_year = trim($_GET['school_year'] ?? '');
        $semester = trim($_GET['semester'] ?? '');

        if ($eval_type === 'student') {
            // Info
            $is = mysqli_prepare(
                $con,
                "SELECT st.student_id, st.full_name AS evaluator_name, 'Student' AS evaluator_role,
                        f.full_name AS evaluatee_name, f.role AS evaluatee_role,
                        c.course_code, c.course_name,
                        ss.section_name, ss.school_year, ss.semester,
                        MIN(ser.submitted_at) AS submitted_at
                 FROM tbl_student_eval_responses ser
                 JOIN tbl_students st         ON st.stud_id  = ser.stud_id
                 JOIN tbl_subject_sections ss ON ss.id       = ser.section_id
                 JOIN courses c               ON c.id        = ss.course_id
                 JOIN tbl_faculty f           ON f.fac_id    = ser.faculty_id
                 WHERE ser.stud_id=? AND ser.section_id=?
                 GROUP BY f.fac_id LIMIT 1"
            );
            mysqli_stmt_bind_param($is, 'ii', $evaluator_id, $section_id);
            mysqli_stmt_execute($is);
            $info = mysqli_fetch_assoc(mysqli_stmt_get_result($is));
            mysqli_stmt_close($is);
            if (!$info) {
                echo json_encode(['success' => false, 'message' => 'Not found.']);
                break;
            }

            $rs = mysqli_prepare(
                $con,
                "SELECT q.question, q.type, q.category, q.sort_order,
                        ser.rating_value, ser.text_response
                 FROM tbl_student_eval_responses ser
                 JOIN tbl_student_eval_questions q ON q.id = ser.question_id
                 WHERE ser.stud_id=? AND ser.section_id=?
                 ORDER BY q.sort_order"
            );
            mysqli_stmt_bind_param($rs, 'ii', $evaluator_id, $section_id);
            mysqli_stmt_execute($rs);
            $responses = mysqli_fetch_all(mysqli_stmt_get_result($rs), MYSQLI_ASSOC);
            mysqli_stmt_close($rs);

        } elseif ($eval_type === 'peer') {
            $is = mysqli_prepare(
                $con,
                "SELECT ev.full_name AS evaluator_name, ev.role AS evaluator_role,
                        ee.full_name AS evaluatee_name, ee.role AS evaluatee_role,
                        per.school_year, per.semester,
                        MIN(per.submitted_at) AS submitted_at
                 FROM tbl_peer_eval_responses per
                 JOIN tbl_faculty ev ON ev.fac_id = per.evaluator_id
                 JOIN tbl_faculty ee ON ee.fac_id = per.evaluatee_id
                 WHERE per.evaluator_id=? AND per.evaluatee_id=? AND per.school_year=? AND per.semester=?
                 GROUP BY ev.fac_id, ee.fac_id LIMIT 1"
            );
            mysqli_stmt_bind_param($is, 'iiss', $evaluator_id, $evaluatee_id, $school_year, $semester);
            mysqli_stmt_execute($is);
            $info = mysqli_fetch_assoc(mysqli_stmt_get_result($is));
            mysqli_stmt_close($is);
            if (!$info) {
                echo json_encode(['success' => false, 'message' => 'Not found.']);
                break;
            }

            $rs = mysqli_prepare(
                $con,
                "SELECT q.question, q.type, q.category, q.sort_order,
                        per.rating_value, per.text_response
                 FROM tbl_peer_eval_responses per
                 JOIN tbl_peer_eval_questions q ON q.id = per.question_id
                 WHERE per.evaluator_id=? AND per.evaluatee_id=? AND per.school_year=? AND per.semester=?
                 ORDER BY q.sort_order"
            );
            mysqli_stmt_bind_param($rs, 'iiss', $evaluator_id, $evaluatee_id, $school_year, $semester);
            mysqli_stmt_execute($rs);
            $responses = mysqli_fetch_all(mysqli_stmt_get_result($rs), MYSQLI_ASSOC);
            mysqli_stmt_close($rs);

        } elseif ($eval_type === 'chair') {
            $is = mysqli_prepare(
                $con,
                "SELECT ev.full_name AS evaluator_name, ev.role AS evaluator_role,
                        ee.full_name AS evaluatee_name, ee.role AS evaluatee_role,
                        cer.school_year, cer.semester,
                        MIN(cer.submitted_at) AS submitted_at
                 FROM tbl_chair_eval_responses cer
                 JOIN tbl_faculty ev ON ev.fac_id = cer.evaluator_id
                 JOIN tbl_faculty ee ON ee.fac_id = cer.evaluatee_id
                 WHERE cer.evaluator_id=? AND cer.evaluatee_id=? AND cer.school_year=? AND cer.semester=?
                 GROUP BY ev.fac_id, ee.fac_id LIMIT 1"
            );
            mysqli_stmt_bind_param($is, 'iiss', $evaluator_id, $evaluatee_id, $school_year, $semester);
            mysqli_stmt_execute($is);
            $info = mysqli_fetch_assoc(mysqli_stmt_get_result($is));
            mysqli_stmt_close($is);
            if (!$info) {
                echo json_encode(['success' => false, 'message' => 'Not found.']);
                break;
            }

            $rs = mysqli_prepare(
                $con,
                "SELECT q.question, q.type, q.category, q.sort_order,
                        cer.rating_value, cer.text_response
                 FROM tbl_chair_eval_responses cer
                 JOIN tbl_chair_eval_questions q ON q.id = cer.question_id
                 WHERE cer.evaluator_id=? AND cer.evaluatee_id=? AND cer.school_year=? AND cer.semester=?
                 ORDER BY q.sort_order"
            );
            mysqli_stmt_bind_param($rs, 'iiss', $evaluator_id, $evaluatee_id, $school_year, $semester);
            mysqli_stmt_execute($rs);
            $responses = mysqli_fetch_all(mysqli_stmt_get_result($rs), MYSQLI_ASSOC);
            mysqli_stmt_close($rs);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid eval_type.']);
            break;
        }

        echo json_encode(['success' => true, 'data' => ['info' => $info, 'responses' => $responses]]);
        break;

    // ── STATS ──────────────────────────────────────────────────
    case 'stats':
        $student = (int) mysqli_fetch_assoc(mysqli_query(
            $con,
            "SELECT COUNT(DISTINCT CONCAT(stud_id,'_',section_id)) AS c FROM tbl_student_eval_responses"
        ))['c'];
        $peer = (int) mysqli_fetch_assoc(mysqli_query(
            $con,
            "SELECT COUNT(DISTINCT CONCAT(evaluator_id,'_',evaluatee_id,'_',school_year,'_',semester)) AS c FROM tbl_peer_eval_responses"
        ))['c'];
        $chair = (int) mysqli_fetch_assoc(mysqli_query(
            $con,
            "SELECT COUNT(DISTINCT CONCAT(evaluator_id,'_',evaluatee_id,'_',school_year,'_',semester)) AS c FROM tbl_chair_eval_responses"
        ))['c'];
        echo json_encode([
            'success' => true,
            'data' => [
                'total' => $student + $peer + $chair,
                'student' => $student,
                'peer' => $peer,
                'chair' => $chair,
            ]
        ]);
        break;

    // ── FILTER DROPDOWNS ───────────────────────────────────────
    case 'get_teachers':
        // All faculty who have been evaluated (any type)
        $rows = mysqli_fetch_all(
            mysqli_query(
                $con,
                "SELECT DISTINCT f.fac_id, f.full_name, f.role FROM tbl_faculty f
             WHERE f.fac_id IN (
                SELECT DISTINCT faculty_id  FROM tbl_student_eval_responses
                UNION
                SELECT DISTINCT evaluatee_id FROM tbl_peer_eval_responses
                UNION
                SELECT DISTINCT evaluatee_id FROM tbl_chair_eval_responses
             ) ORDER BY f.full_name"
            ),
            MYSQLI_ASSOC
        );
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}

mysqli_close($con);
?>