<?php
/**
 * Filename: api/users.php
 * Users API for CRUD operations
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
require_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'create': createUser(); break;
    case 'read': readUsers(); break;
    case 'read_one': readOneUser(); break;
    case 'update': updateUser(); break;
    case 'delete': deleteUser(); break;
    case 'search': searchUsers(); break;
    default:
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Invalid action."));
        break;
}

function createUser()
{
    global $user;
    $data = json_decode(file_get_contents('php://input'));

    if (!empty($data->first_name) && !empty($data->last_name) && !empty($data->email)) {
        $user->first_name = $data->first_name;
        $user->middle_name = $data->middle_name ?? '';
        $user->last_name = $data->last_name;
        $user->email = $data->email;
        $user->password = $data->password ?? '';
        $user->role = $data->role ?? '';
        $user->profile = $data->profile ?? '';
        $user->branch = $data->branch ?? '';

        if ($user->create()) {
            http_response_code(201);
            echo json_encode(array("success" => true, "message" => "User created.", "id" => $user->id));
            return;
        }

        http_response_code(503);
        echo json_encode(array("success" => false, "message" => "Unable to create user."));
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Incomplete data."));
    }
}

function readUsers()
{
    global $user;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    $stmt = $user->readPaginated($page, $limit, $search);
    $total = $user->countAll($search);

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

function readOneUser()
{
    global $user;
    $user->id = isset($_GET['id']) ? $_GET['id'] : die();

    if ($user->readOne()) {
        $u = array(
            'id' => $user->id,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => $user->role,
            'profile' => $user->profile,
            'branch' => $user->branch
        );

        echo json_encode(array("success" => true, "data" => $u));
    } else {
        http_response_code(404);
        echo json_encode(array("success" => false, "message" => "User not found."));
    }
}

function updateUser()
{
    global $user;
    $data = json_decode(file_get_contents('php://input'));

    if (!empty($data->id) && !empty($data->first_name) && !empty($data->last_name)) {
        $user->id = $data->id;
        $user->first_name = $data->first_name;
        $user->middle_name = $data->middle_name ?? '';
        $user->last_name = $data->last_name;
        $user->email = $data->email ?? '';
        $user->password = $data->password ?? '';
        $user->role = $data->role ?? '';
        $user->profile = $data->profile ?? '';
        $user->branch = $data->branch ?? '';

        if ($user->update()) {
            echo json_encode(array("success" => true, "message" => "User updated."));
            return;
        }

        http_response_code(503);
        echo json_encode(array("success" => false, "message" => "Unable to update user."));
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Incomplete data."));
    }
}

function deleteUser()
{
    global $user;
    $data = json_decode(file_get_contents('php://input'));

    if (!empty($data->id)) {
        $user->id = $data->id;
        if ($user->delete()) {
            echo json_encode(array("success" => true, "message" => "User deleted."));
            return;
        }

        http_response_code(503);
        echo json_encode(array("success" => false, "message" => "Unable to delete user."));
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "ID is required."));
    }
}

function searchUsers()
{
    global $user;
    $keywords = isset($_GET['keywords']) ? $_GET['keywords'] : '';

    if (empty($keywords)) {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Keywords required."));
        return;
    }

    $stmt = $user->readPaginated(1, 100, $keywords);
    $arr = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $arr[] = $row;
    }

    echo json_encode(array("success" => true, "data" => $arr, "total" => count($arr)));
}

?>
