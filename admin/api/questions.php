<?php
// File: questions.php
// Path: /admin/api/questions.php
// CRUD API for evaluation questions (student, peer, chair types)
// Actions: read, read_one, create, update, delete, toggle, categories

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

// ── Map eval_type to its DB table ──────────────────────────
function getTable(string $type): string
{
    return match ($type) {
        'student' => 'tbl_student_eval_questions',
        'peer' => 'tbl_peer_eval_questions',
        'chair' => 'tbl_chair_eval_questions',
        default => ''
    };
}

switch ($action) {

    // ── READ (all questions for a type) ────────────────────
    case 'read':
        $type = trim($_GET['type'] ?? 'student');
        $category = trim($_GET['category'] ?? '');
        $status = $_GET['status'] ?? '';
        $limit = min(999, max(1, (int) ($_GET['limit'] ?? 999)));

        $table = getTable($type);
        if (!$table) {
            echo json_encode(['success' => false, 'message' => 'Invalid type.']);
            break;
        }

        $where = [];
        $params = [];
        $types = '';
        if ($category !== '') {
            $where[] = 'category = ?';
            $params[] = $category;
            $types .= 's';
        }
        if ($status !== '') {
            $where[] = 'is_active = ?';
            $params[] = (int) $status;
            $types .= 'i';
        }
        $wSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT id, question, type, category, sort_order, is_active, created_at
                 FROM `$table` $wSQL ORDER BY sort_order ASC, id ASC LIMIT $limit";
        $stmt = mysqli_prepare($con, $sql);
        if ($params)
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        echo json_encode([
            'success' => true,
            'data' => $rows,
            'pagination' => [
                'total_records' => count($rows),
                'records_per_page' => $limit
            ]
        ]);
        break;

    // ── READ ONE ──────────────────────────────────────────
    case 'read_one':
        $id = (int) ($_GET['id'] ?? 0);
        $type = trim($_GET['type'] ?? '');
        $table = getTable($type);
        if (!$id || !$table) {
            echo json_encode(['success' => false, 'message' => 'Invalid params.']);
            break;
        }

        $stmt = mysqli_prepare(
            $con,
            "SELECT id, question, type, category, sort_order, is_active FROM `$table` WHERE id=? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Question not found.']);
            break;
        }
        echo json_encode(['success' => true, 'data' => $row]);
        break;

    // ── CREATE ─────────────────────────────────────────────
    case 'create':
        $b = json_decode(file_get_contents('php://input'), true);
        $type = trim($b['eval_type'] ?? '');
        $table = getTable($type);
        if (!$table) {
            echo json_encode(['success' => false, 'message' => 'Invalid eval type.']);
            break;
        }

        if (empty($b['question'])) {
            echo json_encode(['success' => false, 'message' => 'Question text is required.']);
            break;
        }

        $qtype = in_array($b['type'] ?? '', ['quantitative', 'qualitative']) ? $b['type'] : 'quantitative';
        $category = trim($b['category'] ?? 'General');
        $sort = (int) ($b['sort_order'] ?? 0);
        $is_active = isset($b['is_active']) ? (int) $b['is_active'] : 1;

        $stmt = mysqli_prepare(
            $con,
            "INSERT INTO `$table` (question, type, category, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, 'sssii', $b['question'], $qtype, $category, $sort, $is_active);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Question added successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed: ' . mysqli_error($con)]);
        }
        mysqli_stmt_close($stmt);
        break;

    // ── UPDATE ─────────────────────────────────────────────
    case 'update':
        $b = json_decode(file_get_contents('php://input'), true);
        $id = (int) ($b['id'] ?? 0);
        $type = trim($b['eval_type'] ?? '');
        $table = getTable($type);
        if (!$id || !$table) {
            echo json_encode(['success' => false, 'message' => 'Invalid params.']);
            break;
        }

        if (empty($b['question'])) {
            echo json_encode(['success' => false, 'message' => 'Question text is required.']);
            break;
        }

        $qtype = in_array($b['type'] ?? '', ['quantitative', 'qualitative']) ? $b['type'] : 'quantitative';
        $category = trim($b['category'] ?? 'General');
        $sort = (int) ($b['sort_order'] ?? 0);
        $is_active = isset($b['is_active']) ? (int) $b['is_active'] : 1;

        $stmt = mysqli_prepare(
            $con,
            "UPDATE `$table` SET question=?, type=?, category=?, sort_order=?, is_active=?
             WHERE id=?"
        );
        mysqli_stmt_bind_param($stmt, 'sssiii', $b['question'], $qtype, $category, $sort, $is_active, $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Question updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed: ' . mysqli_error($con)]);
        }
        mysqli_stmt_close($stmt);
        break;

    // ── DELETE ─────────────────────────────────────────────
    case 'delete':
        $b = json_decode(file_get_contents('php://input'), true);
        $id = (int) ($b['id'] ?? 0);
        $type = trim($b['eval_type'] ?? '');
        $table = getTable($type);
        if (!$id || !$table) {
            echo json_encode(['success' => false, 'message' => 'Invalid params.']);
            break;
        }

        $stmt = mysqli_prepare($con, "DELETE FROM `$table` WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Question deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed: ' . mysqli_error($con)]);
        }
        mysqli_stmt_close($stmt);
        break;

    // ── TOGGLE ACTIVE ──────────────────────────────────────
    case 'toggle':
        $b = json_decode(file_get_contents('php://input'), true);
        $id = (int) ($b['id'] ?? 0);
        $type = trim($b['eval_type'] ?? '');
        $table = getTable($type);
        if (!$id || !$table) {
            echo json_encode(['success' => false, 'message' => 'Invalid params.']);
            break;
        }

        $stmt = mysqli_prepare(
            $con,
            "UPDATE `$table` SET is_active = IF(is_active=1,0,1) WHERE id=?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Return new state label
        $s = mysqli_prepare($con, "SELECT is_active FROM `$table` WHERE id=?");
        mysqli_stmt_bind_param($s, 'i', $id);
        mysqli_stmt_execute($s);
        $v = mysqli_fetch_assoc(mysqli_stmt_get_result($s))['is_active'];
        mysqli_stmt_close($s);

        echo json_encode(['success' => true, 'message' => 'Question ' . ($v ? 'activated' : 'deactivated') . '.']);
        break;

    // ── CATEGORIES (for datalist suggestions) ─────────────
    case 'categories':
        $type = trim($_GET['type'] ?? 'student');
        $table = getTable($type);
        if (!$table) {
            echo json_encode(['success' => false, 'message' => 'Invalid type.']);
            break;
        }

        $rows = mysqli_fetch_all(
            mysqli_query(
                $con,
                "SELECT DISTINCT category FROM `$table`
                 WHERE category IS NOT NULL AND category != ''
                 ORDER BY category"
            ),
            MYSQLI_ASSOC
        );
        $cats = array_column($rows, 'category');
        echo json_encode(['success' => true, 'data' => $cats]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}

mysqli_close($con);
?>