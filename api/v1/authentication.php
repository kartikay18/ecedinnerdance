<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function checkLogin(Request $request, Response $response, $for_admins){
    if (!isset($_SESSION)) {
        session_start();
    }

    $id = $request->getAttribute('id');
    if (!isset($_SESSION) || !isset($_SESSION['id'])) { //check if they are logged in
        $json['status'] = "error";
        $json['message'] = 'You must be logged in to access this page';
        $json['redirect'] = 'login';
        return $response->withJson($json, 401);
    } else if ($for_admins && (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin'])) { //check to see if the user is an admin if its an admin page
        $json['status'] = "error";
        $json['message'] = 'You must be an admin to access this page';
        $json['redirect'] = 'login';
        return $response->withJson($json, 403);
    } else if (!$_SESSION['is_admin'] && (isset($id) && $_SESSION['id'] != $id)) { //if not an admin check to see if its their own information
        $json['status'] = "error";
        $json['message'] = 'You are not authorized to view this page';
        $json['redirect'] = 'login';
        return $response->withJson($json, 403);
    } 
    return NULL;
}

$app->get('/session', function(Request $request, Response $response) {
    $session = sessionUtils::getSession();

    $json = array(
                'id' => $session['id'],
                'isAdmin' => $session['is_admin']
            );
    return $response->withJson($json);
});

$app->post('/login', function(Request $request, Response $response) {
    $r = json_decode($request->getBody());
    //TODO: serverside verification of request
    
    $password = $r->user->password;
    $ticketNum = $r->user->ticketNum;

    $json = array();
    $year = date("Y");
    $sql = "SELECT id, password, is_admin, is_activated FROM users WHERE ticket_num=? AND dinnerdance_year=? LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(1, $ticketNum);
    $stmt->bindParam(2, $year);
    if ($stmt->execute()){
        $user = $stmt->fetch();
        if ($user != NULL) {
            if(passwordUtils::checkPassword($user['password'],$password)){
                if (!isset($_SESSION)) {
                    session_start();
                }
                $_SESSION['id'] = $user['id'];
                $_SESSION['is_admin'] = $user['is_admin'];

                if(!$user['is_activated']){
                    $json['status'] = "success";
                    $json['message'] = 'Logged in successfully. Please set a new password';
                    $json['redirect'] = 'activate';
                } else {
                    $json['status'] = "success";
                    $json['message'] = 'Logged in successfully.';
                    $json['redirect'] = 'dashboard';
                }
            } else {
                $json['status'] = "error";
                $json['message'] = 'Login failed. Incorrect credentials';
            }
        } else {
            $json['status'] = "error";
            $json['message'] = 'No such user is registered';
        }
    } else {
        $json['status'] = "error";
        $json['message'] = 'Login failed. We could not log you in at this time.';
    }

    return $response->withJson($json);
});

$app->post('/signUp', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, true);
    if (!is_null($not_authorized)){
        return $not_authorized;
    }

    $r = json_decode($request->getBody());

    //TODO: serverside verification of request
    $ticketNum = $r->user->ticketNum;
    $email = $r->user->email;
    $firstName = $r->user->firstName;
    $lastName = $r->user->lastName;
    $year = date("Y");
    $food = $r->user->food;
    $allergies = $r->user->allergies;
    $displayName = trim($firstName) . " " . trim($lastName);
    $ticketType = $r->user->ticketType;
    if ($ticketType == "early bird"){
        $earlyBird = true;
        $drinking = true;
    } else if ($ticketType == "drinking"){
        $earlyBird = false;
        $drinking = true;
    } else {
        $earlyBird = false;
        $drinking = false;
    }

    $json = array();
    
    $sql = "SELECT 1 FROM users WHERE ticket_num=? AND dinnerdance_year=? LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(1, $ticketNum);
    $stmt->bindParam(2, $year);
    if ($stmt->execute()){
        $isUserExists = $stmt->fetch();
        if(!$isUserExists){
            $password = passwordUtils::generatePassword();
            if ($this->mailer->sendAccountCreationEmail(requestUtils::getAppHome($request), $email, $displayName, $ticketNum, $password)){
                $this->logger->addInfo("Account Creation", array("ticket_num" => $ticketNum , "password" => $password));
                $password_hash = passwordUtils::hash($password);
                $sql = "INSERT INTO users (ticket_num, dinnerdance_year, email, first_name, last_name, display_name, food, allergies, password, is_drinking_ticket, is_early_bird) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(1, $ticketNum);
                $stmt->bindParam(2, $year);
                $stmt->bindParam(3, $email);
                $stmt->bindParam(4, $firstName);
                $stmt->bindParam(5, $lastName);
                $stmt->bindParam(6, $displayName);
                $stmt->bindParam(7, $food);
                $stmt->bindParam(8, $allergies);
                $stmt->bindParam(9, $password_hash);
                $stmt->bindParam(10, $drinking);
                $stmt->bindParam(11, $earlyBird);
                if ($stmt->execute()) {
                    $json["status"] = "success";
                    $json["message"] = "User account created successfully";
                } else {
                    $json["status"] = "error";
                    $json["message"] = "Failed to create user. Please try again"; 
                }
            } else {
                $json["status"] = "error";
                $json["message"] = "Sorry we couldn't send you an email at this time! Please try signing up later.";    
            }
        }else{
            $json["status"] = "error";
            $json["message"] = "A user already exists with that ticket number.";
        }
    } else {
        $json["status"] = "error";
        $json["message"] = "Failed to database query";
    }

    return $response->withJson($json);
});

$app->get('/logout', function(Request $request, Response $response) {
    $session = sessionUtils::destroySession();
    $json = array(
                "status" => "info",
                "message" => "Logged out successfully"
            );
    return $response->withJson($json);
});
?>