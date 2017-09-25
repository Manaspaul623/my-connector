<?php

$name = trim($_POST['name']);
$name = filter_var($name, FILTER_SANITIZE_STRING);
$email = trim($_POST['user_email']);
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$user_message = trim($_POST['message']);
$user_message = filter_var($user_message, FILTER_SANITIZE_STRING);
if(filter_var($email, FILTER_VALIDATE_EMAIL) === false){
    $response_arr = array(
        'flag'=>0,
        'error'=>'Invalid Email'
    );
}else{
    $message_for_user = <<<EOF
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <div style="background:#384556; max-width:500px; padding:15px 15px 10px; font-family:'Arial', sans-serif;">
            <div style="border-radius:5px; text-align:center; padding:15px 0; ">
                <a href="#"><img src="http://aquaapi.com/images/logo-sml.png"/></a>
            </div>
            <div style="border-radius:5px; background:#fff; padding:25px 15px; color: rgb(95, 95, 95);">
                <h1 style="font-size:22px; margin:0 0 10px;">Hello !  $name</h1>
                <h2 style="font-size:18px; margin:0 0 10px;">Thank you for contacting us.</h2>
                <p style="text-align:justify; font-size:17px;  line-height: 26px; margin:0 0 10px;">Your query successfully submitted. We'll contact you soon. </p>
            </div>
            <div style="color:#fff; text-align:center; font-size:14px; padding:5px 0 0;">© auqaAPI</div> 
        </div>
    </body>
</html>
EOF;
    $from = "info@aquaapi.com";
    $to = $email;
    $subject = "Thank you for contacting with us";
    $headers = "From: $from\r\n";
    $headers .= "Content-type: text/html\r\n";
    $response = mail($to, $subject, $message_for_user, $headers);
    
    $message_for_admin = <<<EOF
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Contact Us</title>
    </head>
    <body>
        <div style="background:#384556; max-width:500px; padding:15px 15px 10px; font-family:'Arial', sans-serif;">
            <div style="border-radius:5px; text-align:center; padding:15px 0; ">
                <a href="#"><img src="http://aquaapi.com/images/logo-sml.png"/></a>
            </div>
            <div style="border-radius:5px; background:#fff; padding:25px 15px; color: rgb(95, 95, 95);">
                <h1 style="font-size:22px; margin:0 0 10px;">Hello !  Admin,</h1>
                <h2 style="font-size:18px; margin:0 0 10px;">User details given below:</h2>
                <p style="text-align:justify; font-size:17px;  line-height: 26px; margin:0 0 10px;"><b>Name: </b> $name</p>
                <p style="text-align:justify; font-size:17px;  line-height: 26px; margin:0 0 10px;"><b>Email: </b> $email</p>
                <p style="text-align:justify; font-size:17px;  line-height: 26px; margin:0 0 10px;"><b>Message: </b> $user_message</p>
            </div>
            <div style="color:#fff; text-align:center; font-size:14px; padding:5px 0 0;">© aquaAPI</div> 
        </div>
    </body>
</html>
EOF;
    $from = "info@aquaapi.com";
    $to = 'info@aquaapi.com';
    $subject = "aquaAPI : New people contacted";
    $headers = "From: $from\r\n";
	//$headers .= "Cc:subhajit.manna@mastiska.com \r\n";
    $headers .= "Content-type: text/html\r\n";
	$headers .= "MIME-Version: 1.0\r\n";

    $response = mail($to, $subject, $message_for_admin, $headers);
    
    
    if($response){
        $response_arr = array(
            'flag'=>1,
            'error'=>NULL
        );
    }else{
        $response_arr = array(
            'flag'=>0,
            'error'=>'Email not sent'
        ); 
    }
}
header('Access-Control-Allow-Origin: *');
//header('Content-type: application/x-javascript');
echo json_encode($response_arr);

