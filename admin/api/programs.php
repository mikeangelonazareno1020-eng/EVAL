<?php
/**
 * Filename: api/programs.php
 * Program API Endpoints
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
require_once '../models/Program.php';

$database = new Database();
$db = $database->getConnection();
$program = new Program($db);

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'create':
        createProgram();
        break;

    case 'read':
        readPrograms();
        break;

    case 'read_one':
        readOneProgram();
        break;

    case 'update':
        updateProgram();
        break;

    case 'delete':
        deleteProgram();
        break;

    case 'toggle_active':
        toggleActiveStatus();
        break;

    case 'search':
        searchPrograms();
        break;

    case 'get_departments':
        getDepartments();
        break;

    case 'get_program_types':
        getProgramTypes();
        break;

    case 'get_program_courses':
        getProgramCourses();
        break;

    case 'add_course_to_program':
        addCourseToProgram();
        break;

    case 'remove_course_from_program':
        removeCourseFromProgram();
        break;

    case 'update_program_course':
        updateProgramCourse();
        break;

    default:
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Invalid action."));
        break;
}

/**
 * CREATE - Add new program
 */
function createProgram()
{
    global $program;

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->program_name) && !empty($data->program_code)) {
        $program->program_code = $data->program_code;
        $program->program_name = $data->program_name;
        $program->description = $data->description ?? '';
        $program->program_type = $data->program_type ?? 'Diploma';
        $program->duration_years = $data->duration_years ?? 0;
        $program->total_credits = $data->total_credits ?? 0;
        $program->department = $data->department ?? '';
        $program->grade_level_id = $data->grade_level_id ?? null;
        $program->is_active = $data->is_active ?? 1;
        $program->start_date = $data->start_date ?? null;
        $program->end_date = $data->end_date ?? null;
        $program->max_students = $data->max_students ?? 0;
        $program->tuition_fee = $data->tuition_fee ?? 0.00;
        $program->sort_order = $data->sort_order ?? 0;
        $program->created_by = $_SESSION['user_id'];

        // Check if program code already exists
        if ($program->programCodeExists()) {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Program code already exists."
            ));
            return;
        }

        if ($program->create()) {
            http_response_code(201);
            echo json_encode(array(
                "success" => true,
                "message" => "Program created successfully.",
                "id" => $program->id
            ));
        } else {
            http_response_code(503);
            echo json_encode(array(
                "success" => false,
                "message" => "Unable to create program."
            ));
        }
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Unable to create program. Data is incomplete."
        ));
    }
}

/**
 * READ - Get all programs with pagination
 */
function readPrograms()
{
    global $program;

    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $records_per_page = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $program_type_filter = isset($_GET['program_type']) ? $_GET['program_type'] : '';
    $department_filter = isset($_GET['department']) ? $_GET['department'] : '';
    $active_only = isset($_GET['active_only']) ? filter_var($_GET['active_only'], FILTER_VALIDATE_BOOLEAN) : false;

    if ($active_only) {
        $stmt = $program->readAll(true);
        $programs_arr = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $programs_arr[] = $row;
        }

        echo json_encode(array(
            "success" => true,
            "data" => $programs_arr,
            "total" => count($programs_arr)
        ));
    } else {
        $stmt = $program->readPaginated($page, $records_per_page, $search, $program_type_filter, $department_filter);
        $total_records = $program->countAll($search, $program_type_filter, $department_filter);

        $programs_arr = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $programs_arr[] = $row;
        }

        echo json_encode(array(
            "success" => true,
            "data" => $programs_arr,
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
 * READ ONE - Get single program
 */
function readOneProgram()
{
    global $program;

    $program->id = isset($_GET['id']) ? $_GET['id'] : die();

    if ($program->readOne()) {
        $program_arr = array(
            "id" => $program->id,
            "program_code" => $program->program_code,
            "program_name" => $program->program_name,
            "description" => $program->description,
            "program_type" => $program->program_type,
            "duration_years" => $program->duration_years,
            "total_credits" => $program->total_credits,
            "department" => $program->department,
            "grade_level_id" => $program->grade_level_id,
            "is_active" => $program->is_active,
            "start_date" => $program->start_date,
            "end_date" => $program->end_date,
            "max_students" => $program->max_students,
            "tuition_fee" => $program->tuition_fee,
            "sort_order" => $program->sort_order,
            "created_at" => $program->created_at,
            "updated_at" => $program->updated_at
        );

        http_response_code(200);
        echo json_encode(array("success" => true, "data" => $program_arr));
    } else {
        http_response_code(404);
        echo json_encode(array("success" => false, "message" => "Program not found."));
    }
}

/**
 * UPDATE - Update program
 */
function updateProgram()
{
    global $program;

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->id) && !empty($data->program_name) && !empty($data->program_code)) {
        $program->id = $data->id;
        $program->program_code = $data->program_code;
        $program->program_name = $data->program_name;
        $program->description = $data->description ?? '';
        $program->program_type = $data->program_type ?? 'Diploma';
        $program->duration_years = $data->duration_years ?? 0;
        $program->total_credits = $data->total_credits ?? 0;
        $program->department = $data->department ?? '';
        $program->grade_level_id = $data->grade_level_id ?? null;
        $program->is_active = $data->is_active ?? 1;
        $program->start_date = $data->start_date ?? null;
        $program->end_date = $data->end_date ?? null;
        $program->max_students = $data->max_students ?? 0;
        $program->tuition_fee = $data->tuition_fee ?? 0.00;
        $program->sort_order = $data->sort_order ?? 0;
        $program->updated_by = $_SESSION['user_id'];

        // Check if program code already exists (excluding current record)
        if ($program->programCodeExists($data->id)) {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Program code already exists."
            ));
            return;
        }

        if ($program->update()) {
            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "message" => "Program updated successfully."
            ));
        } else {
            http_response_code(503);
            echo json_encode(array(
                "success" => false,
                "message" => "Unable to update program."
            ));
        }
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Unable to update program. Data is incomplete."
        ));
    }
}

/**
 * DELETE - Delete program
 */
function deleteProgram()
{
    global $program;

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->id)) {
        $program->id = $data->id;
        $program->updated_by = $_SESSION['user_id'];

        // Use soft delete by default
        if ($program->softDelete()) {
            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "message" => "Program deleted successfully."
            ));
        } else {
            http_response_code(503);
            echo json_encode(array(
                "success" => false,
                "message" => "Unable to delete program."
            ));
        }
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Unable to delete program. ID is missing."
        ));
    }
}

/**
 * TOGGLE ACTIVE STATUS
 */
function toggleActiveStatus()
{
    global $program;

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->id)) {
        $program->id = $data->id;
        $program->updated_by = $_SESSION['user_id'];

        if ($program->toggleActive()) {
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
 * SEARCH - Search programs
 */
function searchPrograms()
{
    global $program;

    $keywords = isset($_GET['keywords']) ? $_GET['keywords'] : '';

    if (!empty($keywords)) {
        $stmt = $program->search($keywords);
        $programs_arr = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $programs_arr[] = $row;
        }

        echo json_encode(array(
            "success" => true,
            "data" => $programs_arr,
            "total" => count($programs_arr)
        ));
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Search keywords are required."
        ));
    }
}

/**
 * GET DEPARTMENTS - Get unique departments list
 */
function getDepartments()
{
    global $program;

    $stmt = $program->getDepartments();
    $departments_arr = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $departments_arr[] = $row['department'];
    }

    echo json_encode(array(
        "success" => true,
        "data" => $departments_arr
    ));
}

/**
 * GET PROGRAM TYPES
 */
function getProgramTypes()
{
    global $program;

    $types = $program->getProgramTypes();

    echo json_encode(array(
        "success" => true,
        "data" => $types
    ));
}

/**
 * GET PROGRAM COURSES
 */
function getProgramCourses()
{
    global $program;

    $program_id = isset($_GET['program_id']) ? $_GET['program_id'] : '';

    if (!empty($program_id)) {
        $stmt = $program->getProgramCourses($program_id);
        $courses_arr = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $courses_arr[] = $row;
        }

        echo json_encode(array(
            "success" => true,
            "data" => $courses_arr
        ));
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Program ID is required."
        ));
    }
}

/**
 * ADD COURSE TO PROGRAM
 */
function addCourseToProgram()
{
    global $program;

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->program_id) && !empty($data->course_id)) {
        $semester = $data->semester ?? 1;
        $is_required = $data->is_required ?? 1;
        $sort_order = $data->sort_order ?? 0;

        if ($program->addCourseToProgram($data->program_id, $data->course_id, $semester, $is_required, $sort_order)) {
            http_response_code(201);
            echo json_encode(array(
                "success" => true,
                "message" => "Course added to program successfully."
            ));
        } else {
            http_response_code(503);
            echo json_encode(array(
                "success" => false,
                "message" => "Unable to add course to program. It may already exist."
            ));
        }
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Program ID and Course ID are required."
        ));
    }
}

/**
 * REMOVE COURSE FROM PROGRAM
 */
function removeCourseFromProgram()
{
    global $program;

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->program_id) && !empty($data->course_id)) {
        if ($program->removeCourseFromProgram($data->program_id, $data->course_id)) {
            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "message" => "Course removed from program successfully."
            ));
        } else {
            http_response_code(503);
            echo json_encode(array(
                "success" => false,
                "message" => "Unable to remove course from program."
            ));
        }
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Program ID and Course ID are required."
        ));
    }
}

/**
 * UPDATE PROGRAM COURSE
 */
function updateProgramCourse()
{
    global $program;

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->id)) {
        $semester = $data->semester ?? 1;
        $is_required = $data->is_required ?? 1;
        $sort_order = $data->sort_order ?? 0;

        if ($program->updateProgramCourse($data->id, $semester, $is_required, $sort_order)) {
            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "message" => "Program course updated successfully."
            ));
        } else {
            http_response_code(503);
            echo json_encode(array(
                "success" => false,
                "message" => "Unable to update program course."
            ));
        }
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Program course ID is required."
        ));
    }
}
?>