<?php
include 'help_functions.php';
include 'errors_log.php';
include 'db_settings.php';

error_reporting( E_ERROR );

function visit(){
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
            $id_u = clearStr($_REQUEST['id_u']);
            $id_fe = clearStr($_REQUEST['id_fe']);
            if(empty($id_u) || empty($id_fe)){
                $sql = "SELECT * FROM visit";
            }else $sql = "SELECT * FROM visit
                          WHERE id_u = ? AND id_fe = ?";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            if(!empty($id_u) && !empty($id_fe)) {
                mysqli_stmt_bind_param($stmt, "ii", $id_u, $id_fe);
            }
            if(mysqli_stmt_execute($stmt) == false){
                errors(4, mysqli_stmt_error($stmt));
                http_response_code(500);
                return EXECUTE_QUERY_ERROR;
            }
            mysqli_stmt_bind_result($stmt, $id_u, $id_fe, $rate, $comment, $id_r);
            while (mysqli_stmt_fetch($stmt)) {
                $row = array('id_u'=>$id_u, 'id_fe'=>$id_fe, 'rate'=>$rate, 'comment'=>$comment, 'id_r'=>$id_r);
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
            $visit = getData();
            if(isEmpty($visit)){
                errors(6);
                http_response_code(400);
                return EMPTY_PARAM_ERROR;
            }
            $sql = "INSERT INTO visit (id_u, id_fe, rate, comment, id_r) VALUES (?, ?, ?, ?, ?)";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            mysqli_stmt_bind_param($stmt, "iidsi", $visit['id_u'], $visit['id_fe'], $visit['rate'],
                                                   $visit['comment'], $visit['id_r']);
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
            $id_u = $_DELETE['id_u'];
            $id_fe = $_DELETE['id_fe'];
            $sql = "DELETE FROM visit WHERE id_u = ? AND id_fe = ?";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            mysqli_stmt_bind_param($stmt, "ii", $id_u, $id_fe);
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
            $sql = "UPDATE visit SET rate = ?, comment = ?
                    WHERE id_u = ? AND id_fe = ?";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            mysqli_stmt_bind_param($stmt, "dsii", $_PUT['rate'], $_PUT['comment'], $_PUT['id_u'], $_PUT['id_fe']);
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

visit();