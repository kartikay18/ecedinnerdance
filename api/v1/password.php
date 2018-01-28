<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function checkIfLoggedIn(Request $request, Response $response){
    if (!isset($_SESSION)) {
        session_start();
    }

    if (isset($_SESSION) && isset($_SESSION['id'])) { //check if they are logged in
        $json['status'] = "error";
        $json['message'] = 'You are already loggedin.';
        $json['redirect'] = 'login';
        return $response->withJson($json, 201);
    }
    return null;
}

$app->get('/password/reset/{resetLink}', function(Request $request, Response $response) {
    $loggedin = checkIfLoggedIn($request, $response);
    if ($loggedin != null){
        return $loggedin;
    }

    $json = array();

    $resetLink = $request->getAttribute('resetLink');
    if ($resetLink == null) {
        $json["status"] = "error";
        $json["message"] = "This link is invalid. Woo";
        $json["redirect"] = "login";
    } else {
        $sql = "SELECT id, ticket_num, email, first_name, reset_time FROM users WHERE reset_link=? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $resetLink);
        if ($stmt->execute()){
            $user = $stmt->fetch();
            if($user){
                $date = new DateTime();
                $reset_expire_time = DateTime::createFromFormat('Y-m-d H:i:s', $user['reset_time']);
                if ($date < $reset_expire_time) {
                    $password = passwordUtils::generatePassword();
                    $password_hash = passwordUtils::hash($password);
                    $this->logger->addInfo("Account Reset", array("ticket_num" => $user['ticket_num'] , "password" => $password));
                    if ($this->mailer->sendPasswordResetEmail(requestUtils::getAppHome($request), $user['email'], $user['first_name'], $user['ticket_num'], $password)) {
                        $sql = "UPDATE users SET password=? ,reset_link=null, reset_time=null, is_activated=0 where id=? LIMIT 1";
                        $stmt = $this->db->prepare($sql);
                        $stmt->bindParam(1, $password_hash);
                        $stmt->bindParam(2, $user['id']);
                        if ($stmt->execute()){    
                            $json["status"] = "success";
                            $json["message"] = "We have reset your password. Please check your email for the details.";
                        } else {
                            $json["status"] = "error";
                            $json["message"] = "We could not reset your password at this time";
                            $json["redirect"] = "login";
                        }    
                    } else {
                        $json["status"] = "error";
                        $json["message"] = "Sorry we couldn't send you an email at this time! Please try signing up later.";
                    }
                } else {
                    //destroy the reset_link and reset_time
                    $sql = "UPDATE users SET reset_link=null, reset_time=null where id=? LIMIT 1";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(1, $user['id']);
                    $stmt->execute();

                    $json["status"] = "error";
                    $json["message"] = "This link has expired";
                    $json["redirect"] = "login";
                }
            } else {
                $json["status"] = "error";
                $json["message"] = "This link is invalid";
                $json["redirect"] = "login";
            }
        } else {
            $json["status"] = "error";
            $json["message"] = "Failed database query";
            $json["redirect"] = "login";
        }
    }
    return $response->withJson($json);
});

$app->post('/password/reset', function(Request $request, Response $response) {
    $loggedin = checkIfLoggedIn($request, $response);
    if ($loggedin != null){
        return $loggedin;
    }

    $r = json_decode($request->getBody());

    $ticketNum = $r->user->ticketNum;
    $email = $r->user->email;
    $dinnerdance_year = date("Y");

    $json = array();

    $sql = "SELECT id, first_name FROM users WHERE ticket_num=? AND email=? AND dinnerdance_year=? LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(1, $ticketNum);
    $stmt->bindParam(2, $email);
    $stmt->bindParam(3, $dinnerdance_year);
    if ($stmt->execute()){
        $user = $stmt->fetch();
        if($user['id']){

            $resetLink = passwordUtils::generatePassword(256) . $user['id'];
            $date = new DateTime();
            $date->add(new DateInterval('PT1H'));
            $resetTime = $date->format('Y-m-d H:i:s');

            $this->logger->addInfo("Account Reset Request", array("id" => $user['id'], "reset_time" => $resetTime, "reset_link" => $resetLink));

            $sql = "UPDATE users SET reset_link=?, reset_time=? where id=? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $resetLink);
            $stmt->bindParam(2, $resetTime);
            $stmt->bindParam(3, $user['id']);
            if ($stmt->execute()){
                if ($this->mailer->sendPasswordResetRequestEmail(requestUtils::getAppHome($request), $email, $user['first_name'], $resetLink)) {
                    $json["status"] = "success";
                    $json["message"] = "We have sent you a password reset request. Please check your email. This request will expire in an hour.";
                } else {
                    $json["status"] = "error";
                    $json["message"] = "Sorry we couldn't send you an email at this time! Please try signing up later.";
                }
            } else {
                $json["status"] = "error";
                $json["message"] = "We could not reset your password at this time";
            }
        } else {
            $json["status"] = "error";
            $json["message"] = "We don't have a user registered under those credentials";
        }
        return $response->withJson($json);
    } else {
        $json["status"] = "error";
        $json["message"] = "Failed database query";
    }
});

$app->put('/password/{id}', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, false);
    if (!is_null($not_authorized)){
        return $not_authorized;
    }
    
    $r = json_decode($request->getBody());

    $currentPass = $r->credentials->currentPass;
    $newPass = $r->credentials->newPass;

    $id = $_SESSION['id'];

    $json = array();

    $sql = "SELECT password FROM users WHERE id=? LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(1, $id);
    if ($stmt->execute()){
        $user = $stmt->fetch();
        if($user){
            if(passwordUtils::checkPassword($user['password'],$currentPass)){
                $password_hash = passwordUtils::hash($newPass);
                $sql = "UPDATE users SET password=?, is_activated=1 WHERE id=?";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(1, $password_hash);
                $stmt->bindParam(2, $id);
                if ($stmt->execute()) {
                    $json["status"] = "success";
                    $json["message"] = "Successfully updated password";
                    $json["redirect"] = "dashboard";
                } else {
                    $json["status"] = "error";
                    $json["message"] = "Failed to update password"; 
                }
            } else {
                $json['status'] = "error";
                $json['message'] = 'Current password is incorrect';
            }
        } else {
            $json["status"] = "error";
            $json["message"] = "We could not verify you at this time";
        }
    } else {
        $json["status"] = "error";
        $json["message"] = "Failed database query";
    }
    return $response->withJson($json);
});

?>
