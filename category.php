<?php
include 'help_functions.php';
include 'errors_log.php';
include 'db_settings.php';

error_reporting( E_ERROR );

function category(){
    $link = mysqli_connect(DB_HOST, DB_LOGIN, DB_PASSWORD, DB_NAME);
    if (!$link) {
        errors(1, mysqli_connect_error());
        http_response_code(401);
        return CONNECT_DB_ERROR;
    }
    $stmt = mysqli_stmt_init($link);
    if (!$stmt) {
        errors(2, mysqli_stmt_error($stmt));
        http_response_code(500);
        return INIT_QUERY_ERROR;
    }
    $method = $_SERVER['REQUEST_METHOD'];
    switch ($method){
        case 'GET':
            $id = clearStr($_REQUEST['category_id']);
            if(empty($id)){
                $sql = "SELECT * FROM category";
            }else $sql = "SELECT * FROM category
                          WHERE id_c=?";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            if(!empty($id)) {
                mysqli_stmt_bind_param($stmt, "i", $id);
            }

            if(mysqli_stmt_execute($stmt) == false){
                errors(4, mysqli_stmt_error($stmt));
                http_response_code(500);
                return EXECUTE_QUERY_ERROR;
            }
            mysqli_stmt_bind_result($stmt, $id, $title);
            while (mysqli_stmt_fetch($stmt)) {
                $row = array('category_id'=>$id, 'title'=>$title);
                $result[] = $row;
            }
            if(empty($result)){
                errors(5);
                http_response_code(404);
                return EMPTY_RESULT_SET_ERROR;
            }
            $result = json_encode($result);
            mysqli_stmt_close($stmt);
            return $result;

        case 'POST':
            $category = getData();
            if(isEmpty($category)){
                errors(6);
                http_response_code(400);
                return EMPTY_PARAM_ERROR;
            }
            $sql = "INSERT INTO category(title)
                    VALUES(?)";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            mysqli_stmt_bind_param($stmt, "s", $category['title']);
            if(mysqli_stmt_execute($stmt) == false){
                errors(4, mysqli_stmt_error($stmt));
                http_response_code(500);
                return EXECUTE_QUERY_ERROR;
            }
            mysqli_stmt_close($stmt);
            break;
        case 'DELETE':
            $_DELETE = getData();
            if(isEmpty($_DELETE)){
                errors(6);
                http_response_code(400);
                return EMPTY_PARAM_ERROR;
            }
            $id = clearStr($_DELETE['category_id']);
            $sql = "DELETE FROM category WHERE id_c=?";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            mysqli_stmt_bind_param($stmt, "i", $id);
            if(mysqli_stmt_execute($stmt) == false){
                errors(4, mysqli_stmt_error($stmt));
                http_response_code(500);
                return EXECUTE_QUERY_ERROR;
            }
            mysqli_stmt_close($stmt);
            break;
        case 'PUT':
            $_PUT = getData();
            if(isEmpty($_PUT)){
                errors(6);
                http_response_code(400);
                return EMPTY_PARAM_ERROR;
            }
            $id = $_PUT['category_id'];
            $title = $_PUT['title'];
            $sql = "UPDATE category SET title = ? WHERE id_c = ?";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            mysqli_stmt_bind_param($stmt, "si", $title, $id);
            if(mysqli_stmt_execute($stmt) == false){
                errors(4, mysqli_stmt_error($stmt));
                http_response_code(500);
                return EXECUTE_QUERY_ERROR;
            }
            mysqli_stmt_close($stmt);
            break;
        default:
            errors(7, $method);
            http_response_code(501);
            return UNKNOWN_HEADER_ERROR;
    }
}

category();