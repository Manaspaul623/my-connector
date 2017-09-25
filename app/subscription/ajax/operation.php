<?php
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

if(isset($_GET['id']))
{
    $id=$_GET['id'];
    $cond=" AND id='$id'";
    $subscription_delete = delete($userSubscription,$cond);

    echo $subscription_delete;
}
?>