<?php

include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

$flag = FALSE;
//if "email" variable is filled out, send email
if (isset($_REQUEST['user_email'])) {

    //Email information
    $admin_email = "support@aquaapi.com";
    $userName = $_REQUEST['user_name'];
    $email = $_REQUEST['user_email'];
    $subject = $_REQUEST['subject'];
    $message = $_REQUEST['message'];
    $headVal = "From : $userName" . " ( " . $email . " )";

    //send email
    $flag = mail($admin_email, "$subject", $message, $headVal);
    if ($flag = TRUE) {
        echo "Thank you for contacting us!";
        header("Location:https://".APP_DOMAIN."/app/subscription/user-dashboard.php");
    } else {
        echo "Message sending failed!";
        header("Location:https://".APP_DOMAIN."/app/subscription/user-dashboard.php");
    }
}



