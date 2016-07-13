<?php
include 'help_functions.php';
include 'errors_log.php';
include 'db_settings.php';

error_reporting( E_ERROR );

function excursion(){
    $link = mysqli_connect('localhost', 'root', '', 'Excursions');
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
            $id = clearStr($_REQUEST['id']);
            if(empty($id)){
                $sql = "SELECT * FROM excursion";
            }else $sql = "SELECT * FROM excursion
                          WHERE id_e=?";
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
            mysqli_stmt_bind_result($stmt, $id, $title, $language, $description, $id_c, $current_price);
            while (mysqli_stmt_fetch($stmt)) {
                $row = array('id'=>$id, 'title'=>$title, 'language'=>$language, 'description'=>$description,
                             'category_id'=>$id_c, 'current_price'=>$current_price);
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
            $excursion = getData();
            if(isEmpty($excursion)){
                errors(6);
                http_response_code(400);
                return EMPTY_PARAM_ERROR;
            }
            $sql = "INSERT INTO excursion(title, language, description, id_c, current_price)
                    VALUES(?, ?, ?, ?, ?)";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            mysqli_stmt_bind_param($stmt, "sssid", $excursion['title'], $excursion['language'], $excursion['description'],
                                                   $excursion['category_id'], $excursion['current_price']);
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
            $id = $_DELETE['id'];
            $sql = "DELETE FROM excursion WHERE id_e=?";
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
            $sql = "UPDATE excursion 
                    SET title = ?,
                    language = ?,
                    description = ?,
                    id_c = ?,
                    current_price = ?
                    WHERE id_e = ?";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            mysqli_stmt_bind_param($stmt, "sssidi", $_PUT['title'], $_PUT['language'], $_PUT['description'],
                                                   $_PUT['category_id'], $_PUT['current_price'], $_PUT['id']);
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

excursion();