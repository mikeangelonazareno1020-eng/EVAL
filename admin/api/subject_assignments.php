<?php
// File: subject_assignments.php
// Path: /admin/api/subject_assignments.php
// API handler for Subject Assignment Management
// Supports actions: read, read_one, create, update, delete,
//                   stats, get_branches, get_teachers, get_courses,
//                   get_school_years, get_enrolled,
//                   search_students, enroll, unenroll

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

    // ── READ (paginated list with join) ───────────────────────
    case 'read':
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = min(100, max(1, (int) ($_GET['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;
        $search = trim($_GET['search'] ?? '');
        $school_year = trim($_GET['school_year'] ?? '');
        $semester = trim($_GET['semester'] ?? '');
        $branch = trim($_GET['branch'] ?? '');

        $where = [];
        $params = [];
        $types = '';

        if ($search !== '') {
            $where[] = '(c.course_name LIKE ? OR c.course_code LIKE ? OR f.full_name LIKE ? OR ss.section_name LIKE ?)';
            $like = "%$search%";
            $params = array_merge($params, [$like, $like, $like, $like]);
            $types .= 'ssss';
        }
        if ($school_year !== '') {
            $where[] = 'ss.school_year = ?';
            $params[] = $school_year;
            $types .= 's';
        }
        if ($semester !== '') {
            $where[] = 'ss.semester = ?';
            $params[] = $semester;
            $types .= 's';
        }
        if ($branch !== '') {
            $where[] = 'ss.branch = ?';
            $params[] = $branch;
            $types .= 's';
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Total count
        $countSQL = "SELECT COUNT(*) AS total
                     FROM tbl_subject_sections ss
                     JOIN courses c     ON c.id      = ss.course_id
                     JOIN tbl_faculty f ON f.fac_id  = ss.faculty_id
                     $whereSQL";
        $cStmt = mysqli_prepare($con, $countSQL);
        if ($params)
            mysqli_stmt_bind_param($cStmt, $types, ...$params);
        mysqli_stmt_execute($cStmt);
        $total = mysqli_fetch_assoc(mysqli_stmt_get_result($cStmt))['total'];
        mysqli_stmt_close($cStmt);

        // Data
        $dataSQL = "SELECT ss.id, ss.section_name, ss.school_year, ss.semester,
                           ss.branch, ss.schedule, ss.room, ss.max_students,
                           ss.is_active, ss.created_at,
                           c.id AS course_id, c.course_code, c.course_name,
                           f.fac_id AS faculty_id, f.full_name AS teacher_name, f.role AS teacher_role,
                           (SELECT COUNT(*) FROM tbl_student_enrollments e
                            WHERE e.section_id = ss.id) AS student_count
                    FROM tbl_subject_sections ss
                    JOIN courses c     ON c.id     = ss.course_id
                    JOIN tbl_faculty f ON f.fac_id = ss.faculty_id
                    $whereSQL
                    ORDER BY ss.school_year DESC, ss.semester, c.course_name
                    LIMIT ? OFFSET ?";
        $dStmt = mysqli_prepare($con, $dataSQL);
        $allParams = array_merge($params, [$limit, $offset]);
        $allTypes = $types . 'ii';
        mysqli_stmt_bind_param($dStmt, $allTypes, ...$allParams);
        mysqli_stmt_execute($dStmt);
        $rows = mysqli_fetch_all(mysqli_stmt_get_result($dStmt), MYSQLI_ASSOC);
        mysqli_stmt_close($dStmt);

        echo json_encode([
            'success' => true,
            'data' => $rows,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => max(1, (int) ceil($total / $limit)),
                'total_records' => (int) $total,
                'records_per_page' => $limit,
            ]
        ]);
        break;

    // ── READ ONE ──────────────────────────────────────────────
    case 'read_one':
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
            break;
        }

        $stmt = mysqli_prepare(
            $con,
            "SELECT ss.id, ss.section_name, ss.school_year, ss.semester,
                    ss.branch, ss.schedule, ss.room, ss.max_students, ss.is_active,
                    ss.course_id, c.course_code, c.course_name,
                    ss.faculty_id, f.full_name AS teacher_name, f.role AS teacher_role,
                    (SELECT COUNT(*) FROM tbl_student_enrollments e
                     WHERE e.section_id = ss.id) AS student_count
             FROM tbl_subject_sections ss
             JOIN courses c     ON c.id     = ss.course_id
             JOIN tbl_faculty f ON f.fac_id = ss.faculty_id
             WHERE ss.id = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Section not found.']);
            break;
        }
        echo json_encode(['success' => true, 'data' => $row]);
        break;

    // ── CREATE ────────────────────────────────────────────────
    case 'create':
        $body = json_decode(file_get_contents('php://input'), true);

        $required = ['course_id', 'section_name', 'faculty_id', 'branch', 'school_year', 'semester'];
        foreach ($required as $f) {
            if (empty($body[$f])) {
                echo json_encode(['success' => false, 'message' => "Field '$f' is required."]);
                exit;
            }
        }

        // Duplicate check
        $chk = mysqli_prepare(
            $con,
            "SELECT id FROM tbl_subject_sections
             WHERE course_id=? AND section_name=? AND school_year=? AND semester=? LIMIT 1"
        );
        mysqli_stmt_bind_param(
            $chk,
            'isss',
            $body['course_id'],
            $body['section_name'],
            $body['school_year'],
            $body['semester']
        );
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            mysqli_stmt_close($chk);
            echo json_encode([
                'success' => false,
                'message' => 'A section with this course, name, school year and semester already exists.'
            ]);
            break;
        }
        mysqli_stmt_close($chk);

        $max = (int) ($body['max_students'] ?? 40);
        $schedule = $body['schedule'] ?? '';
        $room = $body['room'] ?? '';
        $active = isset($body['is_active']) ? (int) $body['is_active'] : 1;
        $created = $_SESSION['user_id'];

        $stmt = mysqli_prepare(
            $con,
            "INSERT INTO tbl_subject_sections
                (course_id, faculty_id, section_name, school_year, semester,
                 branch, schedule, room, max_students, is_active, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            'iissssssiii',
            $body['course_id'],
            $body['faculty_id'],
            $body['section_name'],
            $body['school_year'],
            $body['semester'],
            $body['branch'],
            $schedule,
            $room,
            $max,
            $active,
            $created
        );

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Section created successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create section: ' . mysqli_error($con)]);
        }
        mysqli_stmt_close($stmt);
        break;

    // ── UPDATE ────────────────────────────────────────────────
    case 'update':
        $body = json_decode(file_get_contents('php://input'), true);
        $id = (int) ($body['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
            break;
        }

        // Duplicate check (excluding self)
        $chk = mysqli_prepare(
            $con,
            "SELECT id FROM tbl_subject_sections
             WHERE course_id=? AND section_name=? AND school_year=? AND semester=? AND id != ? LIMIT 1"
        );
        mysqli_stmt_bind_param(
            $chk,
            'isssi',
            $body['course_id'],
            $body['section_name'],
            $body['school_year'],
            $body['semester'],
            $id
        );
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            mysqli_stmt_close($chk);
            echo json_encode(['success' => false, 'message' => 'Duplicate section already exists.']);
            break;
        }
        mysqli_stmt_close($chk);

        $max = (int) ($body['max_students'] ?? 40);
        $schedule = $body['schedule'] ?? '';
        $room = $body['room'] ?? '';
        $active = isset($body['is_active']) ? (int) $body['is_active'] : 1;

        $stmt = mysqli_prepare(
            $con,
            "UPDATE tbl_subject_sections
             SET course_id=?, faculty_id=?, section_name=?, school_year=?,
                 semester=?, branch=?, schedule=?, room=?, max_students=?, is_active=?
             WHERE id=?"
        );
        mysqli_stmt_bind_param(
            $stmt,
            'iissssssiii',
            $body['course_id'],
            $body['faculty_id'],
            $body['section_name'],
            $body['school_year'],
            $body['semester'],
            $body['branch'],
            $schedule,
            $room,
            $max,
            $active,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Section updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update section: ' . mysqli_error($con)]);
        }
        mysqli_stmt_close($stmt);
        break;

    // ── DELETE ────────────────────────────────────────────────
    case 'delete':
        $body = json_decode(file_get_contents('php://input'), true);
        $id = (int) ($body['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
            break;
        }

        // Enrollments cascade-deleted via FK
        $stmt = mysqli_prepare($con, "DELETE FROM tbl_subject_sections WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Section and all enrollments deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete: ' . mysqli_error($con)]);
        }
        mysqli_stmt_close($stmt);
        break;

    // ── STATS ─────────────────────────────────────────────────
    case 'stats':
        $sections = mysqli_fetch_assoc(mysqli_query(
            $con,
            "SELECT COUNT(*) AS c FROM tbl_subject_sections"
        ))['c'];
        $teachers = mysqli_fetch_assoc(mysqli_query(
            $con,
            "SELECT COUNT(DISTINCT faculty_id) AS c FROM tbl_subject_sections"
        ))['c'];
        $enrolled = mysqli_fetch_assoc(mysqli_query(
            $con,
            "SELECT COUNT(*) AS c FROM tbl_student_enrollments"
        ))['c'];

        echo json_encode([
            'success' => true,
            'data' => [
                'sections' => (int) $sections,
                'teachers' => (int) $teachers,
                'enrolled' => (int) $enrolled,
            ]
        ]);
        break;

    // ── DROPDOWN: BRANCHES ────────────────────────────────────
    case 'get_branches':
        $rows = mysqli_fetch_all(
            mysqli_query($con, "SELECT branch_id, branch_name FROM tbl_branch ORDER BY branch_name"),
            MYSQLI_ASSOC
        );
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── DROPDOWN: TEACHERS (faculty + programchair) ───────────
    case 'get_teachers':
        $rows = mysqli_fetch_all(
            mysqli_query(
                $con,
                "SELECT fac_id, employee_id, full_name, department, role
                 FROM tbl_faculty
                 WHERE is_active = 1
                 ORDER BY full_name"
            ),
            MYSQLI_ASSOC
        );
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── DROPDOWN: COURSES ─────────────────────────────────────
    case 'get_courses':
        $rows = mysqli_fetch_all(
            mysqli_query(
                $con,
                "SELECT id, course_code, course_name, department
                 FROM courses WHERE is_active = 1 ORDER BY course_name"
            ),
            MYSQLI_ASSOC
        );
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── DROPDOWN: SCHOOL YEARS ────────────────────────────────
    case 'get_school_years':
        $result = mysqli_query(
            $con,
            "SELECT DISTINCT school_year FROM tbl_subject_sections
             ORDER BY school_year DESC"
        );
        $years = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $years[] = $row['school_year'];
        }
        // Always include current school year even if no sections yet
        $currentYear = date('Y') . '-' . (date('Y') + 1);
        if (!in_array($currentYear, $years))
            array_unshift($years, $currentYear);

        echo json_encode(['success' => true, 'data' => $years]);
        break;

    // ── GET ENROLLED STUDENTS FOR A SECTION ───────────────────
    case 'get_enrolled':
        $section_id = (int) ($_GET['section_id'] ?? 0);
        if (!$section_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid section ID.']);
            break;
        }

        $stmt = mysqli_prepare(
            $con,
            "SELECT s.stud_id, s.student_id, s.full_name, s.program, s.branch,
                    e.enrolled_at
             FROM tbl_student_enrollments e
             JOIN tbl_students s ON s.stud_id = e.stud_id
             WHERE e.section_id = ?
             ORDER BY s.full_name"
        );
        mysqli_stmt_bind_param($stmt, 'i', $section_id);
        mysqli_stmt_execute($stmt);
        $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── SEARCH STUDENTS TO ENROLL ─────────────────────────────
    case 'search_students':
        $q = trim($_GET['q'] ?? '');
        $section_id = (int) ($_GET['section_id'] ?? 0);

        if ($q === '') {
            echo json_encode(['success' => true, 'data' => []]);
            break;
        }

        $like = "%$q%";
        $stmt = mysqli_prepare(
            $con,
            "SELECT s.stud_id, s.student_id, s.full_name, s.program, s.branch,
                    IF(e.id IS NOT NULL, 1, 0) AS already_enrolled
             FROM tbl_students s
             LEFT JOIN tbl_student_enrollments e
                ON e.stud_id = s.stud_id AND e.section_id = ?
             WHERE s.is_active = 1
               AND (s.full_name LIKE ? OR s.student_id LIKE ?)
             ORDER BY s.full_name
             LIMIT 20"
        );
        mysqli_stmt_bind_param($stmt, 'iss', $section_id, $like, $like);
        mysqli_stmt_execute($stmt);
        $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── ENROLL A STUDENT ──────────────────────────────────────
    case 'enroll':
        $body = json_decode(file_get_contents('php://input'), true);
        $section_id = (int) ($body['section_id'] ?? 0);
        $stud_id = (int) ($body['stud_id'] ?? 0);

        if (!$section_id || !$stud_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid section or student ID.']);
            break;
        }

        // Check max_students not exceeded
        $cap = mysqli_prepare(
            $con,
            "SELECT max_students,
                    (SELECT COUNT(*) FROM tbl_student_enrollments WHERE section_id = ?) AS enrolled
             FROM tbl_subject_sections WHERE id = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($cap, 'ii', $section_id, $section_id);
        mysqli_stmt_execute($cap);
        $capRow = mysqli_fetch_assoc(mysqli_stmt_get_result($cap));
        mysqli_stmt_close($cap);

        if ($capRow && $capRow['enrolled'] >= $capRow['max_students']) {
            echo json_encode([
                'success' => false,
                'message' => "Section is full ({$capRow['max_students']} / {$capRow['max_students']})."
            ]);
            break;
        }

        $by = $_SESSION['user_id'];
        $stmt = mysqli_prepare(
            $con,
            "INSERT IGNORE INTO tbl_student_enrollments (section_id, stud_id, enrolled_by)
             VALUES (?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, 'iii', $section_id, $stud_id, $by);

        if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(['success' => true, 'message' => 'Student enrolled successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Student is already enrolled in this section.']);
        }
        mysqli_stmt_close($stmt);
        break;

    // ── UNENROLL A STUDENT ────────────────────────────────────
    case 'unenroll':
        $body = json_decode(file_get_contents('php://input'), true);
        $section_id = (int) ($body['section_id'] ?? 0);
        $stud_id = (int) ($body['stud_id'] ?? 0);

        if (!$section_id || !$stud_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid section or student ID.']);
            break;
        }

        $stmt = mysqli_prepare(
            $con,
            "DELETE FROM tbl_student_enrollments WHERE section_id = ? AND stud_id = ?"
        );
        mysqli_stmt_bind_param($stmt, 'ii', $section_id, $stud_id);

        if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(['success' => true, 'message' => 'Student removed from section.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Enrollment not found.']);
        }
        mysqli_stmt_close($stmt);
        break;

    // ── FALLBACK ──────────────────────────────────────────────
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}

mysqli_close($con);
?>