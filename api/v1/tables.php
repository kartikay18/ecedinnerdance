<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/tables', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, false);
    if (!is_null($not_authorized)){
    	return $not_authorized;
    }

    $id = $_SESSION['id'];

    $json = array();

    $sql = "SELECT table_num FROM users WHERE id=?";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(1, $id);
    if ($stmt->execute()){
        $user = $stmt->fetch();
        $tableNum = $user['table_num'];

        $year = date("Y");
        $tables = array();
        for ($i = 1; $i <= 32; $i++) {
            
            $users = array();
            $sql = "SELECT display_name, first_name, last_name FROM users WHERE table_num =? AND dinnerdance_year=?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $i);
            $stmt->bindParam(2, $year);
            if ($stmt->execute()){
                while($row = $stmt->fetch()) {
                    $user = array(
                            "displayName" => $row['display_name'],
                            "name" => $row['first_name'] . ' ' . $row['last_name']
                        );    
                    array_push($users, $user);
                }
            }
            $table = array(
                    "id" => $i,
                    "users" => $users
                );
            array_push($tables, $table);
        }
        /*
        $sql = "SELECT tables.id, users.display_name, users.first_name, users.last_name FROM tables LEFT JOIN users ON tables.id = users.table_num AND users.dinnerdance_year=? AND tables.id < 31 order by tables.id ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $year);
        if ($stmt->execute()){
            $tables = array();
            $currentTableId = null;
            $prevTableId = null;
            $table = null;
            while($row = $stmt->fetch()) {
                $prevTableId = $currentTableId;
                $currentTableId = $row['id'];
                if ($currentTableId != $prevTableId){
                    $table = array(
                        "id" => $row['id'],
                        "users" => array()
                    );
                    if (isset($row['display_name'])){
                        $user = array(
                            "displayName" => $row['display_name'],
                            "name" => $row['first_name'] . ' ' . $row['last_name']
                        );
                        array_push($table['users'], $user);    
                    }
                    array_push($tables, $table);
                } else {
                    $temp = &$tables[key($tables)];
                    $user = array(
                            "displayName" => $row['display_name'],
                            "name" => $row['first_name'] . ' ' . $row['last_name']
                        );
                    array_push($temp['users'], $user);
                }
            }

            $json["status"] = "success";
            $json["message"] = "Tables successfully retrieved";
            $json["tables"] = $tables;
            $json["tableId"] = $tableNum;
        } else {
            $json['status'] = "error";
            $json['message'] = 'Failed database query';    
        }
        */
        $json["status"] = "success";
        $json["message"] = "Tables successfully retrieved";
        $json["tables"] = $tables;
        $json["tableId"] = $tableNum;
    } else {
        $json['status'] = "error";
        $json['message'] = 'Failed database query';
    }
    
    return $response->withJson($json);
});

$app->get('/tables/{tableId}', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, false);
    if (!is_null($not_authorized)){
        return $not_authorized;
    }

    $tableId = $request->getAttribute('tableId');
    
    $json = array();
    
    $sql = "select id, size, num_members from tables where id=? LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(1, $tableId);
    if ($stmt->execute()){
        $table = $stmt->fetch();
        if ($table){
            $json["status"] = "success";
            $json["message"] = "Successfully retrieved table";
            $json["table"] = $table;
        } else {
            $json["status"] = "error";
            $json["message"] = "The requested table does not exist";
        }
    } else {
        $json['status'] = "error";
        $json['message'] = 'Failed database query';
    }
    
    return $response->withJson($json);
});

$app->put('/tables/{tableId}', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, false);
    if (!is_null($not_authorized)){
    	return $not_authorized;
    }
    $id = $_SESSION['id'];
    $tableId = $request->getAttribute('tableId');
    
    $json = array();
    $this->db->beginTransaction();
    $sql = "SELECT table_num FROM users WHERE id=?";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(1, $id);
    if ($stmt->execute()){
        $user = $stmt->fetch();
        $oldTableNum = $user['table_num'];
        if ($oldTableNum == $tableId){
            //same table
            //ditch this transaction
            $this->db->rollBack();

            $json["status"] = "error";
            $json["message"] = "You are already part of this table.";      
            return $response->withJson($json);
        }

        $sql = "UPDATE users INNER JOIN tables ON tables.id=? AND tables.num_members < tables.size SET users.table_num=?, tables.num_members = tables.num_members + 1 WHERE users.id=?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $tableId);
        $stmt->bindParam(2, $tableId);
        $stmt->bindParam(3, $id);
        if ($stmt->execute()){
            if (isset($oldTableNum)) {
                $sql = "UPDATE tables SET num_members = num_members - 1 WHERE id=?";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(1, $oldTableNum);
            }

            if (!isset($oldTableNum) || $stmt->execute()){
                //transaction worked
                $this->db->commit();
                
                $json["status"] = "success";
                $json["message"] = "Successfully added you to the table";
                return $response->withJson($json);
            }
        }    
    } 

    //transaction failed
    $this->db->rollBack();

    $json["status"] = "error";
    $json["message"] = "We couldn't fit you into that table!";      
    return $response->withJson($json);
});

$app->delete('/tables', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, false);
    if (!is_null($not_authorized)){
        return $not_authorized;
    }

    $id = $_SESSION['id'];
    
    $json = array();
    $this->db->beginTransaction();
    $sql = "SELECT table_num FROM users WHERE id=?";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(1, $id);
    if ($stmt->execute()){
        $user = $stmt->fetch();
        $tableNum = $user['table_num'];

        $sql = "UPDATE users SET table_num = NULL WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $id);
        if ($stmt->execute()){
            $sql = "UPDATE tables SET num_members = num_members - 1 WHERE id=? AND tables.num_members > 0";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $tableNum);
            
            if ($stmt->execute()){
                //transaction worked
                $this->db->commit();
                
                $json["status"] = "success";
                $json["message"] = "Successfully removed you from the table";
                return $response->withJson($json);
            }
        }    
    }
    //transaction failed
    $this->db->rollBack();

    $json["status"] = "error";
    $json["message"] = "We couldn't remove you from the table at this time. Please try again later.";      
    return $response->withJson($json);
});

?>
