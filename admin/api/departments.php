<?php
/**
 * Filename: api/departments.php
 * Departments API for CRUD operations
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array("success" => false, "message" => "Unauthorized."));
    exit();
}

require_once '../config/database.php';
require_once '../models/Department.php';

$database = new Database();
$db = $database->getConnection();
$department = new Department($db);

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'create': createDepartment(); break;
    case 'read': readDepartments(); break;
    case 'read_one': readOneDepartment(); break;
    case 'update': updateDepartment(); break;
    case 'delete': deleteDepartment(); break;
    case 'search': searchDepartments(); break;
    default:
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Invalid action."));
        break;
}

function createDepartment()
{
    global $department;
    $data = json_decode(file_get_contents('php://input'));

    if (!empty($data->department_name)) {
        $department->department_name = $data->department_name;
        $department->date_entry = date('Y-m-d H:i:s');

        if ($department->create()) {
            http_response_code(201);
            echo json_encode(array("success" => true, "message" => "Department created.", "id" => $department->dept_id));
            return;
        }

        http_response_code(503);
        echo json_encode(array("success" => false, "message" => "Unable to create department."));
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Incomplete data."));
    }
}

function readDepartments()
{
    global $department;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    $stmt = $department->readPaginated($page, $limit, $search);
    $total = $department->countAll($search);

    $arr = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $arr[] = $row;
    }

    echo json_encode(array(
        "success" => true,
        "data" => $arr,
        "pagination" => array(
            "total_records" => $total,
            "current_page" => $page,
            "records_per_page" => $limit,
            "total_pages" => ($limit>0?ceil($total/$limit):1)
        )
    ));
}

function readOneDepartment()
{
    global $department;
    $department->dept_id = isset($_GET['id']) ? $_GET['id'] : die();

    if ($department->readOne()) {
        $d = array(
            'dept_id' => $department->dept_id,
            'department_name' => $department->department_name,
            'date_entry' => $department->date_entry
        );

        echo json_encode(array("success" => true, "data" => $d));
    } else {
        http_response_code(404);
        echo json_encode(array("success" => false, "message" => "Department not found."));
    }
}

function updateDepartment()
{
    global $department;
    $data = json_decode(file_get_contents('php://input'));

    if (!empty($data->dept_id) && !empty($data->department_name)) {
        $department->dept_id = $data->dept_id;
        $department->department_name = $data->department_name;

        if ($department->update()) {
            echo json_encode(array("success" => true, "message" => "Department updated."));
            return;
        }

        http_response_code(503);
        echo json_encode(array("success" => false, "message" => "Unable to update department."));
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Incomplete data."));
    }
}

function deleteDepartment()
{
    global $department;
    $data = json_decode(file_get_contents('php://input'));

    if (!empty($data->dept_id)) {
        $department->dept_id = $data->dept_id;
        if ($department->delete()) {
            echo json_encode(array("success" => true, "message" => "Department deleted."));
            return;
        }

        http_response_code(503);
        echo json_encode(array("success" => false, "message" => "Unable to delete department."));
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "ID is required."));
    }
}

function searchDepartments()
{
    global $department;
    $keywords = isset($_GET['keywords']) ? $_GET['keywords'] : '';

    if (empty($keywords)) {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Keywords required."));
        return;
    }

    $stmt = $department->readPaginated(1, 100, $keywords);
    $arr = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $arr[] = $row;
    }

    echo json_encode(array("success" => true, "data" => $arr, "total" => count($arr)));
}

?>
