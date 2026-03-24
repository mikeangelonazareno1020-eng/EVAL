<?php
/**
 * Filename: api/courses.php
 * Course API Endpoints
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
require_once '../models/Course.php';

$database = new Database();
$db = $database->getConnection();
$course = new Course($db);

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'create':
        createCourse();
        break;

    case 'read':
        readCourses();
        break;

    case 'read_one':
        readOneCourse();
        break;

    case 'update':
        updateCourse();
        break;

    case 'delete':
        deleteCourse();
        break;

    case 'toggle_active':
        toggleActiveStatus();
        break;

    case 'search':
        searchCourses();
        break;

    case 'get_departments':
        getDepartments();
        break;

    case 'get_by_grade_level':
        getCoursesByGradeLevel();
        break;

    case 'get_by_department':
        getCoursesByDepartment();
        break;

    default:
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Invalid action."));
        break;
}

/**
 * CREATE - Add new course
 */
function createCourse()
{
    global $course;

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->course_name) && !empty($data->course_code)) {
        $course->course_code = $data->course_code;
        $course->course_name = $data->course_name;
        $course->description = $data->description ?? '';
        $course->credits = $data->credits ?? 3;
        $course->hours_per_week = $data->hours_per_week ?? 0;
        $course->grade_level_id = $data->grade_level_id ?? null;
        $course->department = $data->department ?? '';
        $course->is_active = $data->is_active ?? 1;
        $course->sort_order = $data->sort_order ?? 0;
        $course->created_by = $_SESSION['user_id'];

        // Check if course code already exists
        if ($course->courseCodeExists()) {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Course code already exists."
            ));
            return;
        }

        if ($course->create()) {
            http_response_code(201);
            echo json_encode(array(
                "success" => true,
                "message" => "Course created successfully.",
                "id" => $course->id
            ));
        } else {
            http_response_code(503);
            echo json_encode(array(
                "success" => false,
                "message" => "Unable to create course."
            ));
        }
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Unable to create course. Data is incomplete."
        ));
    }
}

/**
 * READ - Get all courses with pagination
 */
function readCourses()
{
    global $course;

    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $records_per_page = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $grade_level_filter = isset($_GET['grade_level']) ? $_GET['grade_level'] : '';
    $department_filter = isset($_GET['department']) ? $_GET['department'] : '';
    $active_only = isset($_GET['active_only']) ? filter_var($_GET['active_only'], FILTER_VALIDATE_BOOLEAN) : false;

    if ($active_only) {
        $stmt = $course->readAll(true);
        $courses_arr = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $courses_arr[] = $row;
        }

        echo json_encode(array(
            "success" => true,
            "data" => $courses_arr,
            "total" => count($courses_arr)
        ));
    } else {
        $stmt = $course->readPaginated($page, $records_per_page, $search, $grade_level_filter, $department_filter);
        $total_records = $course->countAll($search, $grade_level_filter, $department_filter);

        $courses_arr = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $courses_arr[] = $row;
        }

        echo json_encode(array(
            "success" => true,
            "data" => $courses_arr,
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
 * READ ONE - Get single course
 */
function readOneCourse()
{
    global $course;

    $course->id = isset($_GET['id']) ? $_GET['id'] : die();

    if ($course->readOne()) {
        $course_arr = array(
            "id" => $course->id,
            "course_code" => $course->course_code,
            "course_name" => $course->course_name,
            "description" => $course->description,
            "credits" => $course->credits,
            "hours_per_week" => $course->hours_per_week,
            "grade_level_id" => $course->grade_level_id,
            "department" => $course->department,
            "is_active" => $course->is_active,
            "sort_order" => $course->sort_order,
            "created_at" => $course->created_at,
            "updated_at" => $course->updated_at
        );

        http_response_code(200);
        echo json_encode(array("success" => true, "data" => $course_arr));
    } else {
        http_response_code(404);
        echo json_encode(array("success" => false, "message" => "Course not found."));
    }
}

/**
 * UPDATE - Update course
 */
function updateCourse()
{
    global $course;

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->id) && !empty($data->course_name) && !empty($data->course_code)) {
        $course->id = $data->id;
        $course->course_code = $data->course_code;
        $course->course_name = $data->course_name;
        $course->description = $data->description ?? '';
        $course->credits = $data->credits ?? 3;
        $course->hours_per_week = $data->hours_per_week ?? 0;
        $course->grade_level_id = $data->grade_level_id ?? null;
        $course->department = $data->department ?? '';
        $course->is_active = $data->is_active ?? 1;
        $course->sort_order = $data->sort_order ?? 0;
        $course->updated_by = $_SESSION['user_id'];

        // Check if course code already exists (excluding current record)
        if ($course->courseCodeExists($data->id)) {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Course code already exists."
            ));
            return;
        }

        if ($course->update()) {
            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "message" => "Course updated successfully."
            ));
        } else {
            http_response_code(503);
            echo json_encode(array(
                "success" => false,
                "message" => "Unable to update course."
            ));
        }
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Unable to update course. Data is incomplete."
        ));
    }
}

/**
 * DELETE - Delete course
 */
function deleteCourse()
{
    global $course;

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->id)) {
        $course->id = $data->id;
        $course->updated_by = $_SESSION['user_id'];

        // Use soft delete by default
        if ($course->softDelete()) {
            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "message" => "Course deleted successfully."
            ));
        } else {
            http_response_code(503);
            echo json_encode(array(
                "success" => false,
                "message" => "Unable to delete course."
            ));
        }
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Unable to delete course. ID is missing."
        ));
    }
}

/**
 * TOGGLE ACTIVE STATUS
 */
function toggleActiveStatus()
{
    global $course;

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->id)) {
        $course->id = $data->id;
        $course->updated_by = $_SESSION['user_id'];

        if ($course->toggleActive()) {
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
 * SEARCH - Search courses
 */
function searchCourses()
{
    global $course;

    $keywords = isset($_GET['keywords']) ? $_GET['keywords'] : '';

    if (!empty($keywords)) {
        $stmt = $course->search($keywords);
        $courses_arr = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $courses_arr[] = $row;
        }

        echo json_encode(array(
            "success" => true,
            "data" => $courses_arr,
            "total" => count($courses_arr)
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
    global $course;

    $stmt = $course->getDepartments();
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
 * GET COURSES BY GRADE LEVEL
 */
function getCoursesByGradeLevel()
{
    global $course;

    $grade_level_id = isset($_GET['grade_level_id']) ? $_GET['grade_level_id'] : '';

    if (!empty($grade_level_id)) {
        $stmt = $course->getByGradeLevel($grade_level_id);
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
            "message" => "Grade level ID is required."
        ));
    }
}

/**
 * GET COURSES BY DEPARTMENT
 */
function getCoursesByDepartment()
{
    global $course;

    $department = isset($_GET['department']) ? $_GET['department'] : '';

    if (!empty($department)) {
        $stmt = $course->getByDepartment($department);
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
            "message" => "Department is required."
        ));
    }
}
?>