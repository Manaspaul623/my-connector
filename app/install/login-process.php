<?php


include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

//From Action Checking
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = md5($_POST['passwd']);


        $cond = " AND user_email='$username' AND user_password='$password'";
        $usrDetails = fetch($userLogin, $cond);
        if (empty($usrDetails)) {
            $login_error = 1;
        } else {
            //Session Create
            session_start();
            $_SESSION['user_id'] = $usrDetails[0]['user_id'];
            $_SESSION['user_email'] = $usrDetails[0]['user_email'];
            $_SESSION['user_name'] = $username;
            $_SESSION['app'] = 'AQUAAPI';
            //Checking Any Subscription Previously  Created by This User
            $user_id = $usrDetails[0]['user_id'];
            $cond = " AND user_id='$user_id'";
            $subscription_exist = count_row($userSubscription, $cond);

            if ($subscription_exist != 0) {
                header("Location:https://" . APP_DOMAIN . "/app/subscription/user-dashboard.php");
            } else {
                header("Location:https://" . APP_DOMAIN . "/app/install/crm-type.php");
            }
        }

    }
    if (isset($_POST['register'])) {
        //For Registration

        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['confirm_passwd'];
        //Existing Checking
        $cond = " AND user_email='$email'";
        $exist_user = count_row($userLogin, $cond);
        if ($exist_user == 0) {
            $insertArr = array(
                'user_name' => $username,
                'user_email' => $email,
                'user_password' => md5($password)
            );


            $query_result = insert($userLogin, $insertArr);
            if (!$query_result) {
                $register_error = 1;
            } else {
                //Session Create
                session_start();
                $_SESSION['user_id'] = $query_result;
                $_SESSION['user_name'] = $username;
                $_SESSION['user_email'] = $email;
                $_SESSION['app'] = 'AQUAAPI';
                //Checking Any Subscription Previously  Created by This User
                $user_id = $query_result;
                $cond = " AND user_id='$user_id'";
                $subscription_exist = count_row($userSubscriptionFinal, $cond);



                if ($subscription_exist != 0) {
                    header("Location:https://" . APP_DOMAIN . "/app/subscription/user-dashboard.php");
                } else {
                    header("Location:https://" . APP_DOMAIN . "/app/install/crm-type.php");
                }
            }
        } else {
            $user_exist = 1;
        }
    }
    if (isset($_POST['reset_email'])) {
        $email = $_POST['reset_email'];
        $cond = " AND user_email='$email'";
        $fetch_count = count_row($userLogin, $cond);
        if ($fetch_count == 0) {
            $user_not_found = 1;
        } else {
            $date = date('Y-m-d h:i:s');
            $link = urlencode(base64_encode($date));
            $upd_array = array(
                'user_password' => $date
            );
            $cond = " AND user_email='$email'";
            $upd_email = update($userLogin, $upd_array, $cond);
            if ($upd_email) {
                $url = "https://" . APP_DOMAIN . "/app/install/password-reset.php?code=" . $link;
                //Email information
                $email = $_REQUEST['reset_email'];
                $subject = 'Reset Password';
                $message = "For Resetting Your Aquaapi User Password Please Click The link or Copy the link and paste to a different tab " . $url;
                $headVal = "From: support@aquaapi.com";

                //send email
                $flag = mail($email, "$subject", $message, $headVal);
                if ($flag = TRUE) {
                    $mail_send = 1;
                } else {
                    $mail_not_send = 1;
                }
            }
        }
    }

}


?>