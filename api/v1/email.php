<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/email/autoFillTables', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, true);
    if (!is_null($not_authorized)){
        return $not_authorized;
    }

    $sql = "SELECT id, size,num_members FROM tables where num_members < size";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $table_spaces = array();
    while($table = $stmt->fetch()){
        array_push($table_spaces,
            array(
                'id' => $table['id'],
                'space' => (int)$table['size'] - (int)$table['num_members']
                )
            );
    }
    
    $this->logger->addInfo("Table Spaces", $table_spaces);
    $json = array();
    $year = date("Y");
    $sql = "SELECT id, first_name, last_name, email FROM users WHERE table_num IS NULL AND dinnerdance_year=?";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(1, $year);
    $users = array();
    $count = 0;
    $table = array_pop($table_spaces);
    if ($stmt->execute()){
        while($user = $stmt->fetch()) {
            if ($table['space'] <= $count){
                $table = array_pop($table_spaces);
                if ($table == NULL){
                    $this->logger->addInfo("Not enough spaces");
                    break;
                }
                $count = 0;
            }
            $user['table_num'] = $table['id']; 
            array_push($users, $user);
            $sql = "UPDATE users INNER JOIN tables ON tables.id=? AND tables.num_members < tables.size SET users.table_num=?, tables.num_members = tables.num_members + 1 WHERE users.id=?";
            $update_stmt = $this->db->prepare($sql);
            $update_stmt->bindParam(1, $table['id']);
            $update_stmt->bindParam(2, $table['id']);
            $update_stmt->bindParam(3, $user['id']);
            $update_stmt->execute();
            $count++;       
        }

        $this->logger->addInfo("Recepients",$users);
        if ($this->mailer->sendTableAssignmentEmail(requestUtils::getAppHome($request), $users)){
            $json['status'] = "success";
            $json['message'] = "Sent table assignment notifications";
        } else {
            $json['status'] = "error";
            $json['message'] = 'Could not send email at this time';    
        }
    } else {
        $json['status'] = "error";
        $json['message'] = 'Could not execute query';
    }

    return $response->withJson($json);
 });


$app->get('/email/registrationReminder', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, true);
    if (!is_null($not_authorized)){
        return $not_authorized;
    }

    $json = array();
    $year = date("Y");
    $sql = "SELECT id, first_name, last_name, email FROM users WHERE is_activated=0 AND dinnerdance_year=?";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(1, $year);
    if ($stmt->execute()){
        $recipients = array();
        while($row = $stmt->fetch()) {
            array_push(
                $recipients,
                array(
                    'email' => $row['email'],
                    'name' => $row['first_name'] . " " . $row['last_name'],
                    'type' => 'to'
                )
            );
        }

        $global_merge_vars = array(
            array(
                'name' => 'year',
                'content' => $year
            ),
            array(
                'name' => 'profile_url',
                'content' => requestUtils::getAppHome($request) . '#/dashboard'
            ),
            array(
                'name' => 'lockout_date',
                'content' => 'February 2nd, 2017' //TODO: to change with lock out dates
            )
        );

        $this->logger->addInfo("Recepients",$recipients);

        if ($this->mailer->sendMassEmail(requestUtils::getAppHome($request), 'registration-reminder', $recipients, $global_merge_vars)){
            $json['status'] = "success";
            $json['message'] = "Sent registration reminders";
        } else {
            $json['status'] = "error";
            $json['message'] = 'Could not send email at this time';    
        }
    } else {
        $json['status'] = "error";
        $json['message'] = 'Could not execute query';
    }

    return $response->withJson($json);
 });

?>