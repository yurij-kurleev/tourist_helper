<?php
function getData(){
    $_PUT = array();
    $_DELETE = array();
    if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        $putdata = clearStr(file_get_contents('php://input'));
        if(!empty($putdata)) {
            $exploded = explode('&', $putdata);
            foreach ($exploded as $pair) {
                $item = explode('=', $pair);
                if (count($item) == 2) {
                    $_DELETE[urldecode($item[0])] = urldecode($item[1]);
                }
            }
        }
        return $_DELETE;
    }
    if($_SERVER['REQUEST_METHOD'] == 'PUT'){
        $putdata = clearStr(file_get_contents('php://input'));
        if(!empty($putdata)){
            $_PUT = json_decode($putdata, true);
            return $_PUT;
        }
    }
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $putdata = clearStr(file_get_contents('php://input'));
        if(!empty($putdata)){
            $_POST = json_decode($putdata, true);
            return $_POST;
        }
    }
}

function isEmpty($data){
    if(!is_array($data)){
        return true;
    }
    foreach ($data as $value){
        if(empty($value) && !is_numeric($value)){
            return true;
        }
    }
    return false;
}

function clearStr($data){
    return trim(strip_tags($data));
}

function clearArray($array){
    foreach ($array as $value){
        trim(strip_tags($value));
    }
}
