<?php
session_start();

include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

//From Action Checking
if ($_SERVER["REQUEST_METHOD"] == "POST") {


    if (isset($_POST['type'])) {
        $facebook_id = $_POST['id'];
        $facebook_name = $_POST['name'];


        $cond = " AND user_email='$facebook_id' AND user_name='$facebook_name'";
        $usrDetails = fetch($userLogin, $cond);
        if (empty($usrDetails)) {
            $insertArr = array(
                'user_email' => $facebook_id,
                'user_name' => $facebook_name
            );


            $query_result = insert($userLogin, $insertArr);
            if ($query_result) {
                $_SESSION['user_id'] = $query_result;
                //$_SESSION['user_email']=$usrDetails[0]['user_email'];
                $_SESSION['user_name'] = $facebook_name;
                $_SESSION['app'] = 'FACEBOOK';
                echo '1';
            }
        } else {
            $_SESSION['user_id'] = $usrDetails[0]['user_id'];
            $_SESSION['user_name'] = $usrDetails[0]['user_name'];
            $_SESSION['app'] = 'FACEBOOK';
            echo '1';
        }
    }
    else {
        $id = $_POST['id']; //Google ID
        $email = $_POST['email']; //Email ID
        $name = $_POST['name']; //Name

        //Session Create
        $cond = " AND user_email='$email' AND user_name='$name'";
        $usrDetails = fetch($userLogin, $cond);
        if (empty($usrDetails)) {
            $insertArr = array(
                'user_email' => $email,
                'user_name' => $name
            );


            $query_result = insert($userLogin, $insertArr);
            if ($query_result) {
                $_SESSION['user_id'] = $query_result;
                $_SESSION['user_email']=$email;
                $_SESSION['user_name'] = $name;
                $_SESSION['app'] = 'GOOGLE';
                echo '1';
            }
        } else {
            $_SESSION['user_id'] = $usrDetails[0]['user_id'];
            $_SESSION['user_email']=$usrDetails[0]['user_email'];
            $_SESSION['user_name'] = $usrDetails[0]['user_name'];
            $_SESSION['app'] = 'GOOGLE';
            echo '1';
        }

    }

}