<?php
include 'help_functions.php';
include 'errors_log.php';
include 'db_settings.php';

error_reporting( E_ERROR );

function stop(){
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
            $id_e = clearStr($_REQUEST['id']);
            $id_cp = clearStr($_REQUEST['id_cp']);
            if(empty($id_e) || empty($id_cp)){
                $sql = "SELECT * FROM stop";
            }else $sql = "SELECT * FROM stop
                          WHERE id_e = ? AND id_cp = ?";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            if(!empty($id_e) && !empty($id_cp)) {
                mysqli_stmt_bind_param($stmt, "ii", $id_e, $id_cp);
            }
            if(mysqli_stmt_execute($stmt) == false){
                errors(4, mysqli_stmt_error($stmt));
                http_response_code(500);
                return EXECUTE_QUERY_ERROR;
            }
            mysqli_stmt_bind_result($stmt, $ordinal_number, $id_e, $id_cp);
            while (mysqli_stmt_fetch($stmt)) {
                $row = array('ordinal_number'=>$ordinal_number, 'id_e'=>$id_e, 'id_cp'=>$id_cp);
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
            $stop = getData();
            if(isEmpty($stop)){
                errors(6);
                http_response_code(400);
                return EMPTY_PARAM_ERROR;
            }
            $sql = "INSERT INTO stop VALUES (?, ?, ?)";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            mysqli_stmt_bind_param($stmt, "iii", $stop['ordinal_number'], $stop['id_e'], $stop['id_cp']);
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
            $id_cp = $_DELETE['id_cp'];
            $id_e = $_DELETE['id_e'];
            $sql = "DELETE FROM stop WHERE id_e = ? AND id_cp = ?";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            mysqli_stmt_bind_param($stmt, "ii", $id_e, $id_cp);
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
            $sql = "UPDATE stop SET ordinal_number = ? WHERE id_e = ? AND id_cp = ?";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            mysqli_stmt_bind_param($stmt, "iii", $_PUT['ordinal_number'], $_PUT['id_e'], $_PUT['id_cp']);
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

stop();