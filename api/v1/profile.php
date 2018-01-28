<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function getYearOptions(){
    $year_in_hundereds = (int)date("Y"); // (int)2017
    $result = array();

    for( $i = 0; $i<5; $i++) {
        $year_of_grad = (string)(($i + $year_in_hundereds -1)%100);
        $year_of_grad = substr($year_of_grad, 0, 1).'T'.substr($year_of_grad, 1, 1);
        array_push($result, $year_of_grad);
    }
    array_push($result, "Other");
    return $result;
}

$app->get('/profile/{id}', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, false);
    if (!is_null($not_authorized)){
    	return $not_authorized;
    }

    $json = array();
    
    $sql = "SELECT id, ticket_num, email, first_name, last_name, display_name, is_admin, is_activated, year, food, table_num, drinking_age, allergies, bus_depart, bus_return FROM users WHERE id=? LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(1, $_SESSION['id']);
    if ($stmt->execute()){
        $user = $stmt->fetch();
        if ($user != NULL) {
            if ($user['is_activated']){
                $json['status'] = "success";
                $json['message'] = 'Retrieved profile successfully.';
                $json['redirect'] = 'dashboard';

                $profile = array();
                $profile['ticketNum'] = $user['ticket_num'];
                $profile['email'] = $user['email'];
                $profile['firstName'] = $user['first_name'];
                $profile['lastName'] = $user['last_name'];
                $profile['displayName'] = $user['display_name']; 
                $profile['year'] = $user['year'];
                $profile['food'] = $user['food'];
                $profile['tableNum'] = $user['table_num'];
                $profile['drinkingAge'] = $user['drinking_age'];
                $profile['allergies'] = $user['allergies'];
                $profile['departBus'] = $user['bus_depart'];
                $profile['returnBus'] = $user['bus_return'];

                $json['user'] = $profile;
                $json['yearOptions'] = getYearOptions();
            } else {
                $json['status'] = "success";
                $json['message'] = 'Please activate your account first';
            }
        } else {
            $json['status'] = "error";
            $json['message'] = 'No such user is registered';
        }
    } else {
        $json['status'] = "error";
        $json['message'] = 'Failed database query';
    }

    return $response->withJson($json);
});

$app->put('/profile/{id}', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, false);
    if (!is_null($not_authorized)){
    	return $not_authorized;
    }

    //TODO: check if session is admin session
    $r = json_decode($request->getBody());

    //TODO: serverside verification of request
    $id = $_SESSION['id'];
    $email = $r->user->email;
    $firstName = $r->user->firstName;
    $lastName = $r->user->lastName;
    $displayName = $r->user->displayName;
    $food = $r->user->food;
    $drinkingAge = $r->user->drinkingAge;
    $allergies = $r->user->allergies;
    $departBus = $r->user->departBus;
    $returnBus = $r->user->returnBus;

    $json = array();
    $sql = "UPDATE users SET email=?, first_name=?, last_name=?, display_name=?, food=?, drinking_age=?, allergies=?, bus_depart=?, bus_return=?  WHERE id=? AND is_activated=1";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(1, $email);
    $stmt->bindParam(2, $firstName);
    $stmt->bindParam(3, $lastName);
    $stmt->bindParam(4, $displayName);
    $stmt->bindParam(5, $food);
    $stmt->bindParam(6, $drinkingAge);
    $stmt->bindParam(7, $allergies);
    $stmt->bindParam(8, $departBus);
    $stmt->bindParam(9, $returnBus);
    $stmt->bindParam(10, $id);
    if ($stmt->execute()){
    	$json["status"] = "success";
        $json["message"] = "Profile successfully updated";
    } else {
    	$json["status"] = "error";
        $json["message"] = "Failed to update profile"; 
    }

    return $response->withJson($json);

});
?>