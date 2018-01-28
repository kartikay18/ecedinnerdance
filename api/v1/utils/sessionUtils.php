<?php

class sessionUtils {

    public static function getSession(){
        if (!isset($_SESSION)) {
            session_start();
        }

        $sess = array();
        
        if(isset($_SESSION['id']))
        {
            $sess["id"] = $_SESSION['id'];
            $sess["is_admin"] = $_SESSION['is_admin'];
        }
        else
        {
            $sess["id"] = '';
            $sess["is_admin"] = '';
        }
        return $sess;
    }

    public static function destroySession(){
        if (!isset($_SESSION)) {
            session_start();
        }

        if(isSet($_SESSION['id']))
        {
            unset($_SESSION['id']);
            
            $info='info';
            if(isSet($_COOKIE[$info]))
            {
                setcookie ($info, '', time() - $cookie_time);
            }
            $msg="Logged Out Successfully...";
        }
        else
        {
            $msg = "Not logged in...";
        }
        return $msg;
    }
 
}

?>
