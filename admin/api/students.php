<?php
// File: students.php
// Path: /admin/api/students.php
// API for Student Management
// Actions: read, read_one, create, update, delete, toggle_active,
//          stats, get_branches, get_programs, get_departments,
//          get_grade_levels, get_courses, get_sections

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

    // ── READ (paginated) ──────────────────────────────────────
    case 'read':
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = min(100, max(1, (int) ($_GET['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;
        $search = trim($_GET['search'] ?? '');
        $branch = trim($_GET['branch'] ?? '');
        $program = trim($_GET['program'] ?? '');
        $status = $_GET['status'] ?? '';

        $where = [];
        $params = [];
        $types = '';

        if ($search !== '') {
            $where[] = '(s.full_name LIKE ? OR s.student_id LIKE ? OR s.program LIKE ? OR s.department LIKE ?)';
            $l = "%$search%";
            $params = array_merge($params, [$l, $l, $l, $l]);
            $types .= 'ssss';
        }
        if ($branch !== '') {
            $where[] = 's.branch=?';
            $params[] = $branch;
            $types .= 's';
        }
        if ($program !== '') {
            $where[] = 's.program=?';
            $params[] = $program;
            $types .= 's';
        }
        if ($status !== '') {
            $where[] = 's.is_active=?';
            $params[] = (int) $status;
            $types .= 'i';
        }

        $w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Total
        $cs = mysqli_prepare($con, "SELECT COUNT(*) AS t FROM tbl_students s $w");
        if ($params)
            mysqli_stmt_bind_param($cs, $types, ...$params);
        mysqli_stmt_execute($cs);
        $total = mysqli_fetch_assoc(mysqli_stmt_get_result($cs))['t'];
        mysqli_stmt_close($cs);

        // Data with subject count
        $ds = mysqli_prepare(
            $con,
            "SELECT s.stud_id, s.student_id, s.full_name, s.department, s.program,
                    s.grade_level, s.branch, s.school_year, s.is_active,
                    COALESCE(s.is_irregular,0) AS is_irregular,
                    (SELECT COUNT(*) FROM tbl_student_enrollments e WHERE e.stud_id=s.stud_id) AS subject_count
             FROM tbl_students s $w
             ORDER BY s.stud_id DESC LIMIT ? OFFSET ?"
        );
        $ap = array_merge($params, [$limit, $offset]);
        $at = $types . 'ii';
        mysqli_stmt_bind_param($ds, $at, ...$ap);
        mysqli_stmt_execute($ds);
        $rows = mysqli_fetch_all(mysqli_stmt_get_result($ds), MYSQLI_ASSOC);
        mysqli_stmt_close($ds);

        echo json_encode([
            'success' => true,
            'data' => $rows,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => max(1, (int) ceil($total / $limit)),
                'total_records' => (int) $total,
                'records_per_page' => $limit
            ]
        ]);
        break;

    // ── READ ONE (with enrolled sections) ─────────────────────
    case 'read_one':
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
            break;
        }

        $st = mysqli_prepare(
            $con,
            "SELECT stud_id, student_id, full_name, department, program,
                    grade_level, branch, school_year, is_active,
                    COALESCE(is_irregular,0) AS is_irregular, last_login
             FROM tbl_students WHERE stud_id=? LIMIT 1"
        );
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
        mysqli_stmt_close($st);

        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Student not found.']);
            break;
        }

        // Get enrolled sections with teacher info
        $es = mysqli_prepare(
            $con,
            "SELECT ss.id AS section_id, ss.section_name, ss.school_year, ss.semester,
                    ss.schedule, ss.room, ss.branch,
                    c.course_code, c.course_name,
                    f.fac_id AS faculty_id, f.full_name AS teacher_name, f.role AS teacher_role
             FROM tbl_student_enrollments e
             JOIN tbl_subject_sections ss ON ss.id    = e.section_id
             JOIN courses c               ON c.id     = ss.course_id
             JOIN tbl_faculty f           ON f.fac_id = ss.faculty_id
             WHERE e.stud_id = ?
             ORDER BY c.course_name"
        );
        mysqli_stmt_bind_param($es, 'i', $id);
        mysqli_stmt_execute($es);
        $row['enrolled_sections'] = mysqli_fetch_all(mysqli_stmt_get_result($es), MYSQLI_ASSOC);
        mysqli_stmt_close($es);

        echo json_encode(['success' => true, 'data' => $row]);
        break;

    // ── CREATE ────────────────────────────────────────────────
    case 'create':
        $b = json_decode(file_get_contents('php://input'), true);

        foreach (['student_id', 'full_name', 'department', 'program', 'grade_level', 'branch', 'password'] as $f) {
            if (empty($b[$f])) {
                echo json_encode(['success' => false, 'message' => "'$f' is required."]);
                exit;
            }
        }

        // Duplicate check
        $chk = mysqli_prepare($con, "SELECT stud_id FROM tbl_students WHERE student_id=? LIMIT 1");
        mysqli_stmt_bind_param($chk, 's', $b['student_id']);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            mysqli_stmt_close($chk);
            echo json_encode(['success' => false, 'message' => 'Student ID already exists.']);
            break;
        }
        mysqli_stmt_close($chk);

        $pw = md5($b['password']);
        $sy = $b['school_year'] ?? '';
        $act = isset($b['is_active']) ? (int) $b['is_active'] : 1;
        $irr = isset($b['is_irregular']) ? (int) $b['is_irregular'] : 0;

        mysqli_begin_transaction($con);
        $ins = mysqli_prepare(
            $con,
            "INSERT INTO tbl_students
                (student_id,full_name,department,program,grade_level,branch,password,school_year,is_active,is_irregular)
             VALUES (?,?,?,?,?,?,?,?,?,?)"
        );
        mysqli_stmt_bind_param(
            $ins,
            'ssssssssii',
            $b['student_id'],
            $b['full_name'],
            $b['department'],
            $b['program'],
            $b['grade_level'],
            $b['branch'],
            $pw,
            $sy,
            $act,
            $irr
        );

        if (!mysqli_stmt_execute($ins)) {
            mysqli_rollback($con);
            echo json_encode(['success' => false, 'message' => 'Failed to create: ' . mysqli_error($con)]);
            mysqli_stmt_close($ins);
            break;
        }
        $newId = mysqli_insert_id($con);
        mysqli_stmt_close($ins);

        // Sync enrollments
        syncEnrollments($con, $newId, $b['section_ids'] ?? []);

        mysqli_commit($con);
        echo json_encode(['success' => true, 'message' => 'Student added successfully.']);
        break;

    // ── UPDATE ────────────────────────────────────────────────
    case 'update':
        $b = json_decode(file_get_contents('php://input'), true);
        $id = (int) ($b['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
            break;
        }

        // Duplicate check (exclude self)
        $chk = mysqli_prepare($con, "SELECT stud_id FROM tbl_students WHERE student_id=? AND stud_id!=? LIMIT 1");
        mysqli_stmt_bind_param($chk, 'si', $b['student_id'], $id);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            mysqli_stmt_close($chk);
            echo json_encode(['success' => false, 'message' => 'Student ID already in use.']);
            break;
        }
        mysqli_stmt_close($chk);

        $sy = $b['school_year'] ?? '';
        $act = isset($b['is_active']) ? (int) $b['is_active'] : 1;
        $irr = isset($b['is_irregular']) ? (int) $b['is_irregular'] : 0;

        mysqli_begin_transaction($con);

        if (!empty($b['password'])) {
            $pw = md5($b['password']);
            $upd = mysqli_prepare(
                $con,
                "UPDATE tbl_students SET student_id=?,full_name=?,department=?,program=?,
                 grade_level=?,branch=?,password=?,school_year=?,is_active=?,is_irregular=?
                 WHERE stud_id=?"
            );
            mysqli_stmt_bind_param(
                $upd,
                'sssssssssii',
                $b['student_id'],
                $b['full_name'],
                $b['department'],
                $b['program'],
                $b['grade_level'],
                $b['branch'],
                $pw,
                $sy,
                $act,
                $irr,
                $id
            );
        } else {
            $upd = mysqli_prepare(
                $con,
                "UPDATE tbl_students SET student_id=?,full_name=?,department=?,program=?,
                 grade_level=?,branch=?,school_year=?,is_active=?,is_irregular=?
                 WHERE stud_id=?"
            );
            mysqli_stmt_bind_param(
                $upd,
                'ssssssssii',
                $b['student_id'],
                $b['full_name'],
                $b['department'],
                $b['program'],
                $b['grade_level'],
                $b['branch'],
                $sy,
                $act,
                $irr,
                $id
            );
        }

        if (!mysqli_stmt_execute($upd)) {
            mysqli_rollback($con);
            echo json_encode(['success' => false, 'message' => 'Failed to update: ' . mysqli_error($con)]);
            mysqli_stmt_close($upd);
            break;
        }
        mysqli_stmt_close($upd);

        // Sync enrollments (replaces old set with new set)
        syncEnrollments($con, $id, $b['section_ids'] ?? []);

        mysqli_commit($con);
        echo json_encode(['success' => true, 'message' => 'Student updated successfully.']);
        break;

    // ── DELETE ────────────────────────────────────────────────
    case 'delete':
        $b = json_decode(file_get_contents('php://input'), true);
        $id = (int) ($b['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
            break;
        }
        $st = mysqli_prepare($con, "DELETE FROM tbl_students WHERE stud_id=?");
        mysqli_stmt_bind_param($st, 'i', $id);
        echo mysqli_stmt_execute($st)
            ? json_encode(['success' => true, 'message' => 'Student deleted.'])
            : json_encode(['success' => false, 'message' => 'Delete failed: ' . mysqli_error($con)]);
        mysqli_stmt_close($st);
        break;

    // ── TOGGLE ACTIVE ─────────────────────────────────────────
    case 'toggle_active':
        $b = json_decode(file_get_contents('php://input'), true);
        $id = (int) ($b['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
            break;
        }
        $st = mysqli_prepare($con, "UPDATE tbl_students SET is_active=IF(is_active=1,0,1) WHERE stud_id=?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        $r = mysqli_prepare($con, "SELECT is_active FROM tbl_students WHERE stud_id=?");
        mysqli_stmt_bind_param($r, 'i', $id);
        mysqli_stmt_execute($r);
        $v = mysqli_fetch_assoc(mysqli_stmt_get_result($r))['is_active'];
        mysqli_stmt_close($r);
        echo json_encode(['success' => true, 'message' => 'Student ' . ($v ? 'activated' : 'deactivated') . '.']);
        break;

    // ── STATS ─────────────────────────────────────────────────
    case 'stats':
        echo json_encode([
            'success' => true,
            'data' => [
                'total' => (int) mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) c FROM tbl_students"))['c'],
                'active' => (int) mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) c FROM tbl_students WHERE is_active=1"))['c'],
                'branches' => (int) mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT branch) c FROM tbl_students"))['c'],
                'programs' => (int) mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT program) c FROM tbl_students"))['c'],
            ]
        ]);
        break;

    // ── GET ALL SECTIONS (for subject picker) ─────────────────
    case 'get_sections':
        $rows = mysqli_fetch_all(
            mysqli_query(
                $con,
                "SELECT ss.id, ss.section_name, ss.school_year, ss.semester,
                    ss.schedule, ss.room, ss.branch,
                    c.course_code, c.course_name,
                    f.fac_id AS faculty_id, f.full_name AS teacher_name, f.role AS teacher_role
             FROM tbl_subject_sections ss
             JOIN courses c     ON c.id     = ss.course_id
             JOIN tbl_faculty f ON f.fac_id = ss.faculty_id
             WHERE ss.is_active = 1
             ORDER BY ss.school_year DESC, ss.semester, c.course_name"
            ),
            MYSQLI_ASSOC
        );
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── DROPDOWNS ─────────────────────────────────────────────
    case 'get_branches':
        echo json_encode(['success' => true, 'data' => mysqli_fetch_all(mysqli_query($con, "SELECT branch_id,branch_name FROM tbl_branch ORDER BY branch_name"), MYSQLI_ASSOC)]);
        break;
    case 'get_programs':
        echo json_encode(['success' => true, 'data' => mysqli_fetch_all(mysqli_query($con, "SELECT id,program_code,program_name FROM programs WHERE is_active=1 ORDER BY program_name"), MYSQLI_ASSOC)]);
        break;
    case 'get_departments':
        echo json_encode(['success' => true, 'data' => mysqli_fetch_all(mysqli_query($con, "SELECT dept_id,department_name FROM tbl_department ORDER BY department_name"), MYSQLI_ASSOC)]);
        break;
    case 'get_grade_levels':
        echo json_encode(['success' => true, 'data' => mysqli_fetch_all(mysqli_query($con, "SELECT id,grade_code,grade_name FROM grade_levels WHERE is_active=1 ORDER BY sort_order"), MYSQLI_ASSOC)]);
        break;
    case 'get_courses':
        echo json_encode(['success' => true, 'data' => mysqli_fetch_all(mysqli_query($con, "SELECT id,course_code,course_name FROM courses WHERE is_active=1 ORDER BY course_name"), MYSQLI_ASSOC)]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}

mysqli_close($con);

// ── HELPER: sync enrollments ──────────────────────────────
// Deletes enrollments not in new list, inserts new ones.
function syncEnrollments($con, $stud_id, $section_ids)
{
    $section_ids = array_map('intval', $section_ids ?? []);

    // Remove all current enrollments for this student
    $del = mysqli_prepare($con, "DELETE FROM tbl_student_enrollments WHERE stud_id=?");
    mysqli_stmt_bind_param($del, 'i', $stud_id);
    mysqli_stmt_execute($del);
    mysqli_stmt_close($del);

    // Insert new enrollments
    if (empty($section_ids))
        return;

    $ins = mysqli_prepare(
        $con,
        "INSERT IGNORE INTO tbl_student_enrollments (section_id,stud_id) VALUES (?,?)"
    );
    foreach ($section_ids as $sid) {
        if ($sid <= 0)
            continue;
        mysqli_stmt_bind_param($ins, 'ii', $sid, $stud_id);
        mysqli_stmt_execute($ins);
    }
    mysqli_stmt_close($ins);
}
?>