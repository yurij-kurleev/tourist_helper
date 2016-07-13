<?php
include 'errors_log.php';
include 'db_settings.php';

error_reporting( E_ERROR );

class Database
{
    protected $link;//дескриптор соединения с БД

    public function __construct()
    {
        $this->link = new mysqli(DB_HOST, DB_LOGIN, DB_PASSWORD, DB_NAME);
        if ($this->link->connect_errno) {
            errors(1, mysqli_connect_error());
            http_response_code(401);
            return CONNECT_DB_ERROR;
        }
    }

    public function isEmpty($data){
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

    public function searchAroundPoint(){
        $data = array(
            'longitude' => $this->clearStr($_GET['longitude']),
            'latitude' => $this->clearStr($_GET['latitude']),
            'limit' => $this->clearStr($_GET['limit']),
            'offset' => $this->clearStr($_GET['offset'])
        );
        if($this->isEmpty($data)){
            errors(6);
            http_response_code(400);
            return EMPTY_PARAM_ERROR;
        }
        $stmt = $this->link->prepare(
            "SELECT eid_e, etitle, elanguage, edescr, ctitle, ecur_price
             FROM(
                SELECT ex.id_e AS 'eid_e', ex.title AS 'etitle', ex.language AS 'elanguage', ex.description AS 'edescr', cat.title AS 'ctitle', 
                       ex.current_price AS 'ecur_price', MIN(SQRT((coord_point.longitude - ?) * (coord_point.longitude - ?) + 
                                (coord_point.latitude - ?) * (coord_point.latitude - ?))) AS 'min_rad'
                FROM excursion ex
                INNER JOIN stop INNER JOIN coord_point INNER JOIN category cat
                ON ex.id_e = stop.id_e AND ex.id_c = cat.id_c AND stop.id_cp = coord_point.id_cp
                GROUP BY ex.id_e
                ORDER BY min_rad ASC
                LIMIT ? OFFSET ?
             ) AS t2"
        );
        if($stmt == false){
            errors(3, mysqli_stmt_error($stmt));
            http_response_code(500);
            return PREPARE_QUERY_ERROR;
        }
        $stmt->bind_param('ddddii', $data['longitude'], $data['longitude'], $data['latitude'],
                                    $data['latitude'], $data['limit'], $data['offset']);
        if($stmt->execute() == false){
            errors(4, mysqli_stmt_error($stmt));
            http_response_code(500);
            return EXECUTE_QUERY_ERROR;
        }
        $stmt->bind_result($id, $title_e, $language, $descr, $title_cat, $price);
        /* Выбрать значения */
        while ($stmt->fetch()) {
            $row = array('id'=>$id, 'title'=>$title_e, 'description'=>$descr, 'language'=>$language, 'category'=>$title_cat, 'price'=>$price);
            $result[] = $row;
        }
        if(empty($result)){
            errors(5);
            http_response_code(404);
            return EMPTY_RESULT_SET_ERROR;
        }
        $result = json_encode($result);
        $stmt->close();
        return $result;
    }

    public function searchInSquare(){
        $data = array(
            'leftLat' => $this->clearStr($_GET['leftLat']),
            'leftLong' => $this->clearStr($_GET['leftLong']),
            'rightLat' => $this->clearStr($_GET['rightLat']),
            'rightLong' => $this->clearStr($_GET['rightLong'])
        );
        if($this->isEmpty($data)){
            errors(6);
            http_response_code(400);
            return EMPTY_PARAM_ERROR;
        }
        $stmt = $this->link->prepare(
            "SELECT eid_e, etitle, elanguage, edescr, ctitle, ecur_price
             FROM(
                SELECT ex.id_e AS 'eid_e', ex.title AS 'etitle', ex.language AS 'elanguage', ex.description AS 'edescr', cat.title AS 'ctitle', 
                       ex.current_price AS 'ecur_price'
                FROM excursion ex
                INNER JOIN stop INNER JOIN coord_point INNER JOIN category cat
                ON ex.id_e = stop.id_e AND ex.id_c = cat.id_c AND stop.id_cp = coord_point.id_cp
                WHERE coord_point.latitude >= ? AND coord_point.latitude <= ? 
                AND coord_point.longitude <= ? AND coord_point.longitude >= ?
             ) AS T
             GROUP BY eid_e"
        );
        if($stmt == false){
            errors(3, mysqli_stmt_error($stmt));
            http_response_code(500);
            return PREPARE_QUERY_ERROR;
        }
        $stmt->bind_param('dddd', $data['leftLat'], $data['rightLat'], $data['leftLong'], $data['rightLong']);
        if($stmt->execute() == false){
            errors(4, mysqli_stmt_error($stmt));
            http_response_code(500);
            return EXECUTE_QUERY_ERROR;
        }
        $stmt->bind_result($id, $title_e, $language, $descr, $title_cat, $price);
        /* Выбрать значения */
        while ($stmt->fetch()) {
            $row = array('id'=>$id, 'title'=>$title_e, 'language'=>$language, 'description'=>$descr, 'category'=>$title_cat, 'price'=>$price);
            $result[] = $row;
        }
        if(empty($result)){
            errors(5);
            http_response_code(404);
            return EMPTY_RESULT_SET_ERROR;
        }
        $result = json_encode($result);
        $stmt->close();
        return $result;
    }

    public function clearStr($data){
        return mysqli_real_escape_string($this->link, trim(strip_tags($data)));
    }
    
    public function getExcursionsFullInfo(){
        $stmt = $this->link->prepare(
            "SELECT factual_excursion.id_fe, excursion.title, excursion.description, category.title, 
                    excursion.language, factual_excursion.date, factual_excursion.factual_price FROM excursion
             INNER JOIN factual_excursion INNER JOIN category
             ON excursion.id_e = factual_excursion.id_e AND excursion.id_c = category.id_c"
        );
        if($stmt == false){
            errors(3, mysqli_stmt_error($stmt));
            http_response_code(500);
            return PREPARE_QUERY_ERROR;
        }
        if($stmt->execute() == false){
            errors(4, mysqli_stmt_error($stmt));
            http_response_code(500);
            return EXECUTE_QUERY_ERROR;
        }
        $stmt->bind_result($id_fe, $title, $descr, $cat_title, $language, $date, $fact_price);
        /* Выбрать значения */
        while ($stmt->fetch()) {
            $row = array('id_fe'=>$id_fe, 'title'=>$title, 'description'=>$descr, 'cat_title'=>$cat_title,
                         'language'=>$language, 'date'=>$date, 'factual_price'=>$fact_price);
            $result[] = $row;
        }
        if(empty($result)){
            errors(5);
            http_response_code(404);
            return EMPTY_RESULT_SET_ERROR;
        }
        $result = json_encode($result);
        $stmt->close();
        return $result;
    }
    
    public function authorizateUser(){
        $login = $this->clearStr($_GET['login']);
        $password = $this->clearStr($_GET['password']);
        $stmt = $this->link->prepare(
            "SELECT * FROM user WHERE login = ? AND password = ?"
        );
        if($stmt == false){
            errors(3, mysqli_stmt_error($stmt));
            http_response_code(500);
            return PREPARE_QUERY_ERROR;
        }
        $stmt->bind_param('ss', $login, $password);
        if($stmt->execute() == false){
            errors(4, mysqli_stmt_error($stmt));
            http_response_code(500);
            return EXECUTE_QUERY_ERROR;
        }
        $stmt->bind_result($id, $full_name, $img_ref, $id_r, $login, $password);
        $stmt->fetch();
        $row = array('id'=>$id, 'full_name'=>$full_name, 'img_ref'=>$img_ref, 'id_r'=>$id_r,
                     'login'=>$login, 'password'=>$password);
        if($this->isEmpty($row)){
            errors(5);
            http_response_code(404);
            return EMPTY_RESULT_SET_ERROR;
        }
        $result = json_encode($row);
        $stmt->close();
        return $result;
    }
    
    /*
    public function addFactualExcursion(){
        $json = json_decode($_POST['fact_excursion'], true);
        if($this->isEmpty($json)){
            return json_encode(array("result"=>"Missing input data"));
        }
        $language = $this->clearStr($json['language']);
        $date = $this->clearStr($json['date']);
        $id_e = $this->clearStr($json['id_e']);
        $stmt = $this->link->prepare(
            "SELECT current_price FROM excursion
             WHERE id_e = ?"
        );
        if($stmt == false){
            echo $this->link->error;
            //$stmt->close();
            return false;
        }
        $stmt->bind_param('i', $id_e);
        $stmt->execute();
        $stmt->bind_result($current_price);
        $stmt->fetch();
        $stmt->close();
        $stmt = $this->link->prepare(
            "INSERT INTO factual_excursion(language, date, factual_price, id_e)
                          VALUE(?, ?, ?, ?)"
        );
        if($stmt == false){
            printf("Ошибка: %s.\n", $stmt->error);
            return false;
        }
        $stmt->bind_param('ssdi', $language, $date, $current_price, $id_e);
        $stmt->execute();
        $stmt->close();
        return true;
    }
    */
/*
    public function getExcursionRate(){
        $id_e = $this->clearStr($_GET['id_e']);
        $stmt = $this->link->prepare(
            "SELECT AVG(rate) AS 'avg_rate' FROM excursion
             INNER JOIN factual_excursion INNER JOIN visits
             ON excursion.id_e = factual_excursion.id_e AND factual_excursion.id_fe = visits.id_fe
             WHERE excursion.id_e = ?"
        );
        if($stmt == false){
            printf("Ошибка: %s.\n", $stmt->error);
            return false;
        }
        $stmt->bind_param('i', $id_e);
        $stmt->execute();
        $stmt->bind_result($avg_rate);
        $stmt->fetch();
        if(empty($avg_rate)){
            echo "Nothing was found by your request";
            exit();
        }
        $stmt->close();
        $_POST['rate'] = $avg_rate;
        return $avg_rate;
    }
*/
    /*
    public function getUserAvarageRate(){
        $id_u = $this->clearStr($_GET['id_u']);
        $stmt = $this->link->prepare(
            "SELECT AVG(visits.rate) AS 'avg_rate' FROM user
             INNER JOIN visits
             ON user.id_u = visits.id_u
             WHERE user.id_u = ?"
        );
        if($stmt == false){
            printf("Ошибка: %s.\n", $stmt->error);
            return false;
        }
        $stmt->bind_param('i', $id_u);
        $stmt->execute();
        $stmt->bind_result($avg_rate);
        $stmt->fetch();
        if(empty($avg_rate)){
            echo "Nothing was found by your request";
            exit();
        }
        $stmt->close();
        $_POST['rate'] = $avg_rate;
        return $avg_rate;
    }
*/
    /*
    public function getRouteByExcursionId(){
        $id_e = $this->clearStr($_GET['id_e']);
        $stmt = $this->link->prepare(
            "SELECT stop.ordinal_number, coord_point.latitude, coord_point.longitude
             FROM stop INNER JOIN excursion INNER JOIN coord_point 
             ON stop.id_e = excursion.id_e AND stop.id_t = coord_point.id_t
             WHERE excursion.id_e = ?
             ORDER BY stop.ordinal_number ASC"
        );
        if($stmt == false){
            printf("Ошибка: %s.\n", $stmt->error);
            return false;
        }
        $stmt->bind_param('i', $id_e);
        $stmt->execute();
        $stmt->bind_result($number, $latitude, $longitude);
        while ($stmt->fetch()) {
            $row = array('ordinal_number'=>$number, 'latitude'=>$latitude, 'longitude'=>$longitude);
            $result[] = $row;
        }
        if(empty($result)){
            echo "Nothing was found by your request";
            exit();
        }
        $result = json_encode($result);
        $stmt->close();
        $_POST['route'] = $result;
        return $result;
    }
*/
    public function getUserExcursions(){
        $id_r = $this->clearStr($_GET['id_r']);
        $id_u = $this->clearStr($_GET['id_u']);
        $sql = "SELECT e.title, e.description, e.language, category.title, fe.date, fe.factual_price 
                FROM factual_excursion fe
                INNER JOIN excursion e INNER JOIN category INNER JOIN visit INNER JOIN user INNER JOIN role
                ON e.id_e = fe.id_e AND e.id_c = category.id_c 
                AND visit.id_fe = fe.id_fe AND visit.id_u = user.id_u AND visit.id_r = role.id_r
                WHERE role.id_r = ? AND user.id_u = ?";
        $stmt = $this->link->prepare($sql);
        if($stmt == false){
            errors(3, mysqli_stmt_error($stmt));
            http_response_code(500);
            return PREPARE_QUERY_ERROR;
        }
        $stmt->bind_param('ii', $id_r, $id_u);
        if($stmt->execute() == false){
            errors(4, mysqli_stmt_error($stmt));
            http_response_code(500);
            return EXECUTE_QUERY_ERROR;
        }
        $stmt->bind_result($title, $descr, $language, $cat_title, $date, $fact_price);
        /* Выбрать значения */
        while ($stmt->fetch()) {
            $row = array('title'=>$title, 'description'=>$descr, 'language'=>$language, 
                         'cat_title'=>$cat_title, 'date'=>$date, 'factual_price'=>$fact_price);
            $result[] = $row;
        }
        if(empty($result)){
            errors(5);
            http_response_code(404);
            return EMPTY_RESULT_SET_ERROR;
        }
        $result = json_encode($result);
        $stmt->close();
        return $result;
    }

    public function setRate(){
        $id_u = $this->clearStr($_GET['id_u']);
        $id_fe = $this->clearStr($_GET['id_fe']);
        $rate = $this->clearStr($_GET['rate']);
        $sql = "UPDATE visit
                SET rate = ?
                WHERE id_u = ? AND id_fe = ? AND rate IS NULL";
        $stmt = $this->link->prepare($sql);
        if($stmt == false){
            errors(3, mysqli_stmt_error($stmt));
            http_response_code(500);
            return PREPARE_QUERY_ERROR;
        }
        $stmt->bind_param('iii', $rate, $id_u, $id_fe);
        if($stmt->execute() == false){
            errors(4, mysqli_stmt_error($stmt));
            http_response_code(500);
            return EXECUTE_QUERY_ERROR;
        }
        $stmt->close();
    }

    public function setComment(){
        $id_u = $this->clearStr($_GET['id_u']);
        $id_fe = $this->clearStr($_GET['id_fe']);
        $comment = $this->clearStr($_GET['comment']);
        $sql = "UPDATE visit
                SET comment = ?
                WHERE id_u = ? AND id_fe = ? AND comment IS NULL";
        $stmt = $this->link->prepare($sql);
        if($stmt == false){
            errors(3, mysqli_stmt_error($stmt));
            http_response_code(500);
            return PREPARE_QUERY_ERROR;
        }
        $stmt->bind_param('sii', $comment, $id_u, $id_fe);
        if($stmt->execute() == false){
            errors(4, mysqli_stmt_error($stmt));
            http_response_code(500);
            return EXECUTE_QUERY_ERROR;
        }
        $stmt->close();
    }
}

$db = new Database();
$function = $db->clearStr($_GET['action']);
echo $db->$function();