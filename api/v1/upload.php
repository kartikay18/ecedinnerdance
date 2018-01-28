<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/upload', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, true);
    if (!is_null($not_authorized)){
        return $not_authorized;
    }

    $this->logger->addInfo("Received CSV File");

    $files = $request->getUploadedFiles();
    if (empty($files['newfile'])) {
        throw new Exception('Expected a newfile');
    }
 
    $userList = $files['newfile']->getStream()->getContents();

    $users = explode("\n", $userList);
    
    $json = array();
    
    //set_time_limit(1200);
    foreach ($users as $userString) {
        $userString = trim($userString);
        if (!empty($userString)){
            $userInfo = explode(",", $userString);
            $ticketNum = (int) str_replace('"', '', $userInfo[0]);
            $fullName = str_replace('"', '', $userInfo[1]);
            $email = str_replace('"', '', $userInfo[2]);
            $year = date("Y");

            $lastNamePosition = strrpos ( $fullName , " ");
            $firstName = trim(substr($fullName, 0, $lastNamePosition));
            $lastName = trim(substr($fullName, $lastNamePosition + 1));

            echo "$ticketNum, $firstName, $lastName, $email\n";

            $drinking = ($ticketNum < 251) ? 1 : 0; //ranges 250 and below are considered non-drinking
            $earlyBird = 1;
            
            $password = passwordUtils::generatePassword();
            //TODO: Change to bulk
            if ($this->mailer->sendAccountCreationEmail(requestUtils::getAppHome($request), $email, $fullName, $ticketNum, $password)){
                $this->logger->addInfo("Account Creation", array("ticket_num" => $ticketNum , "email" => $email, "password" => $password));
                $password_hash = passwordUtils::hash($password);
                $sql = "INSERT INTO users (ticket_num, dinnerdance_year, email, first_name, last_name, display_name, password, is_drinking_ticket, is_early_bird) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(1, $ticketNum);
                $stmt->bindParam(2, $year);
                $stmt->bindParam(3, $email);
                $stmt->bindParam(4, $firstName);
                $stmt->bindParam(5, $lastName);
                $stmt->bindParam(6, $fullName);
                $stmt->bindParam(7, $password_hash);
                $stmt->bindParam(8, $drinking);
                $stmt->bindParam(9, $earlyBird);
                if (!$stmt->execute()) {
                    $this->logger->addInfo("Failed DB entry for $ticketNum, $email, $fullName");
                }
            } else {
                $this->logger->addInfo("Could not send email for $ticketNum, $email, $fullName");
            }
        }
    }

    return $response;
 });

?>