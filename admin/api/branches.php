<?php
/**
 * Filename: api/branches.php
 * Branches API for CRUD operations
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
require_once '../models/Branch.php';

$database = new Database();
$db = $database->getConnection();
$branch = new Branch($db);

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'create': createBranch(); break;
    case 'read': readBranches(); break;
    case 'read_one': readOneBranch(); break;
    case 'update': updateBranch(); break;
    case 'delete': deleteBranch(); break;
    case 'search': searchBranches(); break;
    default:
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Invalid action."));
        break;
}

function createBranch()
{
    global $branch;
    $data = json_decode(file_get_contents('php://input'));

    if (!empty($data->branch_name)) {
        $branch->branch_name = $data->branch_name;
        $branch->date_entry = date('Y-m-d H:i:s');

        if ($branch->create()) {
            http_response_code(201);
            echo json_encode(array("success" => true, "message" => "Branch created.", "id" => $branch->branch_id));
            return;
        }

        http_response_code(503);
        echo json_encode(array("success" => false, "message" => "Unable to create branch."));
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Incomplete data."));
    }
}

function readBranches()
{
    global $branch;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    $stmt = $branch->readPaginated($page, $limit, $search);
    $total = $branch->countAll($search);

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

function readOneBranch()
{
    global $branch;
    $branch->branch_id = isset($_GET['id']) ? $_GET['id'] : die();

    if ($branch->readOne()) {
        $d = array(
            'branch_id' => $branch->branch_id,
            'branch_name' => $branch->branch_name,
            'date_entry' => $branch->date_entry
        );

        echo json_encode(array("success" => true, "data" => $d));
    } else {
        http_response_code(404);
        echo json_encode(array("success" => false, "message" => "Branch not found."));
    }
}

function updateBranch()
{
    global $branch;
    $data = json_decode(file_get_contents('php://input'));

    if (!empty($data->branch_id) && !empty($data->branch_name)) {
        $branch->branch_id = $data->branch_id;
        $branch->branch_name = $data->branch_name;

        if ($branch->update()) {
            echo json_encode(array("success" => true, "message" => "Branch updated."));
            return;
        }

        http_response_code(503);
        echo json_encode(array("success" => false, "message" => "Unable to update branch."));
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Incomplete data."));
    }
}

function deleteBranch()
{
    global $branch;
    $data = json_decode(file_get_contents('php://input'));

    if (!empty($data->branch_id)) {
        $branch->branch_id = $data->branch_id;
        if ($branch->delete()) {
            echo json_encode(array("success" => true, "message" => "Branch deleted."));
            return;
        }

        http_response_code(503);
        echo json_encode(array("success" => false, "message" => "Unable to delete branch."));
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "ID is required."));
    }
}

function searchBranches()
{
    global $branch;
    $keywords = isset($_GET['keywords']) ? $_GET['keywords'] : '';

    if (empty($keywords)) {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Keywords required."));
        return;
    }

    $stmt = $branch->readPaginated(1, 100, $keywords);
    $arr = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $arr[] = $row;
    }

    echo json_encode(array("success" => true, "data" => $arr, "total" => count($arr)));
}

?>