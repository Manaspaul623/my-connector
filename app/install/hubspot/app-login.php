<?php
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqllib.php";
//for Hubspot Access Token

    validateHubspotToken();

function validateHubspotToken() {
    include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
    if(!isset($_REQUEST['code']))
    {
        header('location:https://app.hubspot.com/oauth/authorize?client_id='.HUBSPOT_CLIENT_ID.'&scope=contacts%20automation%20forms&redirect_uri=https://'.APP_DOMAIN.'/app/install/hubspot/app-login.php');
    }
    else {
        $auth_code = $_REQUEST['code'];

        // Get HubSpot access token for the First time Access
        $url = 'https://api.hubapi.com/oauth/v1/token'; //$hubspotUrl.'/contacts/v1/lists/all/contacts/all?hapikey='.$hubspotKey;
        $http_headres = array(
            'Content-Type: application/x-www-form-urlencoded;charset=utf-8'
        );

        $postfields = array(
            'grant_type' => 'authorization_code',
            'client_id' => HUBSPOT_CLIENT_ID,
            'client_secret' => HUBSPOT_CLIENT_SECRET,
            'redirect_uri' => "https://".APP_DOMAIN.'/app/install/hubspot/app-login.php',
            'code' => $auth_code
        );


        $fieldsJSON = http_build_query($postfields);
        //print_r($contactFieldsJSON);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsJSON);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = curl_exec($ch);
        $responseDecode = json_decode($response, true);
        //print_r($responseDecode);
        //Access Token Collected For a User
        $hubspot_access_token = $responseDecode['access_token'];
        $refresh_token = $responseDecode['refresh_token'];
        curl_close($ch);
        if (array_key_exists('refresh_token', $responseDecode)) {
            echo '<pre>';
            echo '<script type="text/javascript">
                    var access_token = "'.$hubspot_access_token.'";
                    var refresh_token = "'.$refresh_token.'";
                        SetToken(access_token,refresh_token);
                        
                        function SetToken(access,refresh) {
                                try {
                                    window.opener.GetToken(access,refresh);
                                }
                                catch (err) {}
                                window.close();
                                return false;
                            }

                            
                </script>';
            //print_r($responseDecode);
        } else {
            echo "false";
        }


    }

}
