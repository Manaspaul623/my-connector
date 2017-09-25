<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
// Change the line below to your timezone!
//date_default_timezone_set('In');
//$date = date('m/d/Y h:i:s a', time());
/*$timezone = date_default_timezone_get("Asia/Kolkata");
echo "The current server timezone is: " . $timezone;
echo '<br>';
//
$dt = new DateTime();
echo $dt->format('Y-m-d H:i:s'); */
/*$timezone = new DateTimeZone("Asia/Kolkata" );
$date = new DateTime();
$date->setTimezone($timezone );
echo 'Time Zone : Asia/Kolkata';
echo '<br>';
echo  $date->format( 'H:i:s A  /  D, M jS, Y' ); */
$systemTimeZone = system('date +%Z');
echo 'System Time Zone : '.$systemTimeZone;
echo '<br>';
echo 'Date time :'.date('d-m-Y H:i:s');