<?php
/**
 * Filename: api/grade_levels.php
 * Grade Level API Endpoints
 * Handles all CRUD operations via AJAX
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array("success" => false, "message" => "Unauthorized access."));
    exit();
}

require_once '../config/database.php';
require_once '../models/GradeLevel.php';

$database = new Database();
$db = $database->getConnection();
$gradeLevel = new GradeLevel($db);

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'create':
        createGradeLevel();
        break;

    case 'read':
        readGradeLevels();
        break;

    case 'read_one':
        readOneGradeLevel();
        break;

    case 'update':
        updateGradeLevel();
        break;

    case 'delete':
        deleteGradeLevel();
        break;

    case 'toggle_active':
        toggleActiveStatus();
        break;

    case 'search':
        searchGradeLevels();
        break;

    default:
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Invalid action."));
        break;
}

/**
 * CREATE - Add new grade level
 */
function createGradeLevel()
{
    global $gradeLevel;

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->grade_name) && !empty($data->grade_code)) {
        $gradeLevel->grade_name = $data->grade_name;
        $gradeLevel->grade_code = $data->grade_code;
        $gradeLevel->description = $data->description ?? '';
        $gradeLevel->sort_order = $data->sort_order ?? 0;
        $gradeLevel->is_active = $data->is_active ?? 1;
        $gradeLevel->created_by = $_SESSION['user_id'];

        // Check if grade code already exists
        if ($gradeLevel->gradeCodeExists()) {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Grade code already exists."
            ));
            return;
        }

        if ($gradeLevel->create()) {
            http_response_code(201);
            echo json_encode(array(
                "success" => true,
                "message" => "Grade level created successfully.",
                "id" => $gradeLevel->id
            ));
        } else {
            http_response_code(503);
            echo json_encode(array(
                "success" => false,
                "message" => "Unable to create grade level."
            ));
        }
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Unable to create grade level. Data is incomplete."
        ));
    }
}

/**
 * READ - Get all grade levels with pagination
 */
function readGradeLevels()
{
    global $gradeLevel;

    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $records_per_page = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $active_only = isset($_GET['active_only']) ? filter_var($_GET['active_only'], FILTER_VALIDATE_BOOLEAN) : false;

    if ($active_only) {
        $stmt = $gradeLevel->readAll(true);
        $grade_levels_arr = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $grade_levels_arr[] = $row;
        }

        echo json_encode(array(
            "success" => true,
            "data" => $grade_levels_arr,
            "total" => count($grade_levels_arr)
        ));
    } else {
        $stmt = $gradeLevel->readPaginated($page, $records_per_page, $search);
        $total_records = $gradeLevel->countAll($search);

        $grade_levels_arr = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $grade_levels_arr[] = $row;
        }

        echo json_encode(array(
            "success" => true,
            "data" => $grade_levels_arr,
            "pagination" => array(
                "total_records" => $total_records,
                "current_page" => $page,
                "records_per_page" => $records_per_page,
                "total_pages" => ceil($total_records / $records_per_page)
            )
        ));
    }
}

/**
 * READ ONE - Get single grade level
 */
function readOneGradeLevel()
{
    global $gradeLevel;

    $gradeLevel->id = isset($_GET['id']) ? $_GET['id'] : die();

    if ($gradeLevel->readOne()) {
        $grade_level_arr = array(
            "id" => $gradeLevel->id,
            "grade_name" => $gradeLevel->grade_name,
            "grade_code" => $gradeLevel->grade_code,
            "description" => $gradeLevel->description,
            "sort_order" => $gradeLevel->sort_order,
            "is_active" => $gradeLevel->is_active,
            "created_at" => $gradeLevel->created_at,
            "updated_at" => $gradeLevel->updated_at
        );

        http_response_code(200);
        echo json_encode(array("success" => true, "data" => $grade_level_arr));
    } else {
        http_response_code(404);
        echo json_encode(array("success" => false, "message" => "Grade level not found."));
    }
}

/**
 * UPDATE - Update grade level
 */
function updateGradeLevel()
{
    global $gradeLevel;

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->id) && !empty($data->grade_name) && !empty($data->grade_code)) {
        $gradeLevel->id = $data->id;
        $gradeLevel->grade_name = $data->grade_name;
        $gradeLevel->grade_code = $data->grade_code;
        $gradeLevel->description = $data->description ?? '';
        $gradeLevel->sort_order = $data->sort_order ?? 0;
        $gradeLevel->is_active = $data->is_active ?? 1;
        $gradeLevel->updated_by = $_SESSION['user_id'];

        // Check if grade code already exists (excluding current record)
        if ($gradeLevel->gradeCodeExists($data->id)) {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Grade code already exists."
            ));
            return;
        }

        if ($gradeLevel->update()) {
            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "message" => "Grade level updated successfully."
            ));
        } else {
            http_response_code(503);
            echo json_encode(array(
                "success" => false,
                "message" => "Unable to update grade level."
            ));
        }
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Unable to update grade level. Data is incomplete."
        ));
    }
}

/**
 * DELETE - Delete grade level
 */
function deleteGradeLevel()
{
    global $gradeLevel;

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->id)) {
        $gradeLevel->id = $data->id;
        $gradeLevel->updated_by = $_SESSION['user_id'];

        // Use soft delete by default
        if ($gradeLevel->softDelete()) {
            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "message" => "Grade level deleted successfully."
            ));
        } else {
            http_response_code(503);
            echo json_encode(array(
                "success" => false,
                "message" => "Unable to delete grade level."
            ));
        }
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Unable to delete grade level. ID is missing."
        ));
    }
}

/**
 * TOGGLE ACTIVE STATUS
 */
function toggleActiveStatus()
{
    global $gradeLevel;

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->id)) {
        $gradeLevel->id = $data->id;
        $gradeLevel->updated_by = $_SESSION['user_id'];

        if ($gradeLevel->toggleActive()) {
            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "message" => "Status updated successfully."
            ));
        } else {
            http_response_code(503);
            echo json_encode(array(
                "success" => false,
                "message" => "Unable to update status."
            ));
        }
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Unable to update status. ID is missing."
        ));
    }
}

/**
 * SEARCH - Search grade levels
 */
function searchGradeLevels()
{
    global $gradeLevel;

    $keywords = isset($_GET['keywords']) ? $_GET['keywords'] : '';

    if (!empty($keywords)) {
        $stmt = $gradeLevel->search($keywords);
        $grade_levels_arr = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $grade_levels_arr[] = $row;
        }

        echo json_encode(array(
            "success" => true,
            "data" => $grade_levels_arr,
            "total" => count($grade_levels_arr)
        ));
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Search keywords are required."
        ));
    }
}
?>