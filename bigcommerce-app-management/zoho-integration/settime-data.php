<?php
include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqlconstants.php";
include "$_SERVER[DOCUMENT_ROOT]/bigcommerce-app-management/mysql/mysqllib.php";
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//echo '<pre>';
//print_r($_REQUEST);

$hour = $_POST['hoursVal'];
$mins = $_POST['minuteVal'];
//echo '<br>';
//echo 'Hour : '.$hour;
//echo '<br>';
//echo 'mins : '.$mins;
try{
 $connection_status = Connectdb();
 $open_status = Opendb();
 //
 $id='515739';
 $userData = $condID . $id;
 $resVal = fetch($userTable, $userData);
 $totCount = count($resVal);
/* echo 'total count :'.$totCount;
 echo '<br>';
 echo 'user_name :'.$resVal[0]['user_name'];
 echo '<br>';
 echo 'user_id :'.$resVal[0]['user_id'];
 echo '<br>';*/
 //
 $tblScheduleTime ='tbl_schedule_time';
 /*$resVal1 = fetch($tblScheduleTime, $userData);
 $totCount1 = count($resVal1);
 echo 'sysnc table count :'.$totCount1; */
 
 //if($totCount1 == 0)
 //{
    $timeVal=$hour.':'.$mins;
    $userId = $resVal[0]['user_id'];
    $cond = " AND user_id = 515739";
    $userDataArr = array(
            //   'user_id' => $userId,
               'schedule_time' => $timeVal,
               'last_update' => time()           
           );
           $insertData = update($tblScheduleTime, $userDataArr,$cond);
           if ($insertData > 0) {
               echo 'Record Update Successfully..';
           }else{
               echo 'Record Update Failed..';
           }
 // } else {
  //    echo 'Duplicate Entry Or user Id not Found..';
 // }
  
 
 
}catch (Exception $ex) {
        echo 'Error :' . $ex;
    }
 