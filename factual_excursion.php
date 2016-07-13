<?php
include 'help_functions.php';
include 'errors_log.php';
include 'db_settings.php';

error_reporting( E_ERROR );

function factual_excursion(){
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
            $id_fe = clearStr($_REQUEST['id_fe']);
            $sql = "SELECT * FROM factual_excursion";
            if(!empty($id_fe)){
                $sql = "SELECT * FROM factual_excursion WHERE id_fe=?";
            }
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            if(!empty($id_fe)) {
                mysqli_stmt_bind_param($stmt, "i", $id_fe);
            }
            if(mysqli_stmt_execute($stmt) == false){
                errors(4, mysqli_stmt_error($stmt));
                http_response_code(500);
                return EXECUTE_QUERY_ERROR;
            }
            mysqli_stmt_bind_result($stmt, $id_fe, $date, $factual_price, $id_e);
            while (mysqli_stmt_fetch($stmt)) {
                $row = array('id_fe' => $id_fe, 'date' => $date, 'factual_price' => $factual_price,
                    'id' => $id_e, 'comments' => getComments($id_fe, $link));
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
            $factual_excursion = getData();
            if(isEmpty($factual_excursion)){
                errors(6);
                http_response_code(400);
                return EMPTY_PARAM_ERROR;
            }
            $sql = "INSERT INTO factual_excursion(date, factual_price, id_e) VALUES (?, ?, ?)";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            mysqli_stmt_bind_param($stmt, "sdi", $factual_excursion['date'], $factual_excursion['factual_price'],
                                                 $factual_excursion['id']);
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
            $id_fe = $_DELETE['id_fe'];
            $sql = "DELETE FROM factual_excursion WHERE id_fe = ?";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            mysqli_stmt_bind_param($stmt, "i", $id_fe);
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
            $sql = "UPDATE factual_excursion SET date = ?, factual_price = ?, id_e = ?
                    WHERE id_fe = ?";
            if(mysqli_stmt_prepare($stmt, $sql) == false){
                errors(3, mysqli_stmt_error($stmt));
                http_response_code(500);
                return PREPARE_QUERY_ERROR;
            }
            mysqli_stmt_bind_param($stmt, "sdii", $_PUT['date'], $_PUT['factual_price'], $_PUT['id_e'], $_PUT['id_fe']);
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

function getComments($id_fe, $link){
    $stmt2 = mysqli_stmt_init($link);
    if (!$stmt2) {
        errors(2, mysqli_stmt_error($stmt2));
        http_response_code(500);
        return INIT_QUERY_ERROR;
    }
    $sql2 = "SELECT comment FROM visit WHERE id_fe = ?";
    if(mysqli_stmt_prepare($stmt2, $sql2) == false){
        errors(3, mysqli_stmt_error($stmt2));
        http_response_code(500);
        return PREPARE_QUERY_ERROR;
    }
    mysqli_stmt_bind_param($stmt2, "i", $id_fe);
    if(mysqli_stmt_prepare($stmt2, $sql2) == false){
        errors(4, mysqli_stmt_error($stmt2));
        http_response_code(500);
        return EXECUTE_QUERY_ERROR;
    }
    mysqli_stmt_bind_result($stmt2, $comment);
    while (mysqli_stmt_fetch($stmt2)) {
        $row = $comment;
        $res[] = $row;
    }
    if(empty($res)){
        $res = "Nothing was found by your request";
    }
    return $res;
}

factual_excursion();