<?php
header("Access-Control-Allow-Origin: *");
$response = array();
include '../constant.php';
include '../function.php';
include '../config.php';
//Get the input request parameters
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array
if (isset($input['Method'])) {
    $method = $input['Method'];
    if ($method == $GET_TASK_MEMBER_LIST) {
        $response = manageMember($response, $input);
    } else if ($method == $SET_NEW_TASK) {
        $response = createNewTask($response, $input);
    } else if ($method == $SET_NEW_SUBTASK) {
        $response = createNewSubtask($response, $input);
    } else if ($method == $GET_ALL_USER_TASK) {
        $response = userTask($response, $input);
    } else if ($method == $SET_TASK_COMMENT) {
        $response = createNewComment($response, $input);
    } else if ($method == $GET_ALL_TASK_BY_TASK_ID) {
        $response = taskById($response, $input);
    } else if ($method == $GET_TASK_DASHBOARD) {
        $response = dashboardTask($response, $input);
    } else if ($method == $UPDATE_MAIN_TASK_COMMENT) {
        $response = mUpdateMainTaskComment($response, $input);
    } else if ($method == $GET_CATEGORY) {
        $response = getCategory($response, $input);
    }else {
        $response["code"] = 103;
        $response["message"] = "Invalid method";
    }
} else {
    $response["code"] = 103;
    $response["message"] = "Missing method";
}

function  mUpdateMainTaskComment($response, $input)
{
    $userId = $input['userId'];
    $taskId = $input['taskId'];
    $comment = $input['comment'];

    global $conn;
    $result = updateMainTaskComment($conn, $userId, $taskId, $comment);
    if ($result) {
        $response["code"] = 200;
        $response["message"] = "Updated";
        $response["data"] = $result;
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occurred";
    }
    return $response;
}

function  dashboardTask($response, $input)
{
    $userId = $input['userId'];
    global $conn;
    $result = getDashboardTaskInfo($conn, $userId);
    if ($result != null || count($result) == 0) {
        $response["code"] = 200;
        $response["message"] = "Success get task dashboard";
        $response["data"] = $result;
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occurred";
    }
    return $response;
}

function createNewComment($response, $input)
{
    $taskItemId = $input['taskItemId'];
    $comment = $input['comment'];
    $userId = $input['userId'];
    $status = $input['status'];

    $result = updateTaskCommentLog($taskItemId, $comment, $userId, $status);
    if ($result == 200) {
        $response["code"] = 200;
        $response["message"] = "Success get task";
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occurred";
    }
    return $response;
}

function taskById($response, $input)
{
    $taskId = $input['taskId'];
    $result = getAllTaskByTaskId($taskId);
    if ($result) {
        $response["code"] = 200;
        $response["message"] = "Success get task";
        $response["data"] = $result;
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occurred";
    }
    return $response;
}

function userTask($response, $input)
{
    $userId = $input['userId'];
    $result = getAllTaskByUser($userId);
    if ($result) {
        $response["code"] = 200;
        $response["message"] = "Success get user task";
        $response["data"] = $result;
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occurred";
    }
    return $response;
}

function manageMember($response, $input)
{
    $result = getAllTaskMember();
    if ($result) {
        $response["code"] = 200;
        $response["message"] = "Success get all member list";
        $response["data"] = $result;
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occurred";
    }
    return $response;
}

function createNewTask($response, $input)
{
    $title = $input['title'];
    $desc = $input['desc'];
    $status = $input['status'];
    $purpose = $input['purpose'];
    $category = $input['category'];
    $priority = $input['priority'];
    $lead = $input['leadId'];
    $estimateCompleted = $input['estimatedComplete'];
    $createdId = $input['userId'];

    $result = createTask(
        $title,
        $desc,
        $status,
        $purpose,
        $category,
        $priority,
        $lead,
        $estimateCompleted,
        $createdId
    );
    if ($result == "200") {
        $response["code"] = 200;
        $response["message"] = "Success create new task";
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occurred";
    }
    return $response;
}

function createNewSubtask($response, $input)
{
    $title = $input['title'];
    $desc = $input['desc'];
    $status = $input['status'];
    $comment = $input['comment'];
    $createdId = $input['userId'];
    $taskId = $input['taskId'];
    $memberList = $input['memberList'];
    $result = createSubtask(
        $taskId,
        $title,
        $desc,
        $status,
        $comment,
        $createdId,
        $memberList
    );
    if ($result == "200") {
        $response["code"] = 200;
        $response["message"] = "Success create new task";
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occurred";
    }
    return $response;
}

function getCategory($response, $input)
{
    $result = getAllCategory();
    if ($result) {
        $response["code"] = 200;
        $response["message"] = "Success get all category list";
        $response["data"] = $result;
    } else {
        $response["code"] = 100;
        $response["message"] = "Unexpected error occurred";
    }
    return $response;
}

echo json_encode($response);
