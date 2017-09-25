<?php
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";      /* including db related files */
include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqllib.php";
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

if ($_REQUEST['type']) {
    dispatcher($_REQUEST['type']);
}

function dispatcher($type) {
    switch ($type) {
        case 'magento' : magentoForm();
            break;
        case 'bigcommerce' : bigcommerceForm();
            break;
        case 'shopify' : shopifyForm();
            break;
        default : addErrors('no action specified');
    }
}
function addErrors($msg) {
    $arr = array(
        'flag' => 0,
        'msg' => $msg
    );
    echo json_encode($arr);
}

function bigcommerceForm() {
    include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";
    //form create on hubspot
    $hubspot_access_token = trim($_REQUEST['access_token']);
    $row_id = trim($_REQUEST['credential']);
    $formUrl =HUBAPI_URL . '/forms/v2/forms';
    $http_headres = array(
        "Authorization: Bearer $hubspot_access_token",
        "Content-Type: application/json"
    );

    $formData='{
    "name": "Order From Bigcommerce",
    "action": "",
    "method": "",
    "cssClass": "",
    "redirect": "",
    "submitText": "Submit",
    "followUpId": "",
    "notifyRecipients": "",
    "leadNurturingCampaignId": "",
    "formFieldGroups": [
        {
            "fields": [
                {
                    "name": "subject",
                    "label": "Subject",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 0,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "order_id",
                    "label": "Order No",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 1,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "email",
                    "label": "Email",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 1,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "status",
                    "label": "Order Status",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "bill_street",
                    "label": "Billing Street",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "bill_city",
                    "label": "Billing City",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "bill_code",
                    "label": "Billing Code",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "bill_country",
                    "label": "Billing Country",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "bill_state",
                    "label": "Billing State",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "ship_city",
                    "label": "Shipping City",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "ship_street",
                    "label": "Shipping Street",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "ship_code",
                    "label": "Shipping Code",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "ship_country",
                    "label": "Shipping Country",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "ship_state",
                    "label": "Shipping State",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "tax",
                    "label": "Tax",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "subtotal",
                    "label": "Subtotal",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "total",
                    "label": "Grand Total",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        }
    ],
    "createdAt": 1318534279910,
    "updatedAt": 1413919291011,
    "performableHtml": "",
    "migratedFrom": "ld",
    "ignoreCurrentValues": false,
    "metaData": [],
    "deletable": true
}';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $formUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $response = curl_exec($ch);
    $responseDecode = json_decode($response, true);
   // print_r($responseDecode);
        if(isset($responseDecode['guid'])) {
            $portal_id = $responseDecode['portalId'];
            $form_id = $responseDecode['guid'];
            $updateArr = array(
                'app2_cred2' => $portal_id,
                'app2_cred3' => $form_id
            );
            $cond = " AND id=$row_id";
            $re = update($userSubscription, $updateArr, $cond);
            if ($re) {
                echo '1';
                //header("Location:https://" . APP_DOMAIN . "/app/subscription/select-plan.php?access_id=$credential");
            } else {
                echo "Not Updated";
            }
        }
        else{
            echo $responseDecode['message'];
        }

}
function magentoForm() {
    include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";
    //form create on hubspot
    $hubspot_access_token = trim($_REQUEST['access_token']);
    $row_id = trim($_REQUEST['credential']);
    $formUrl =HUBAPI_URL . '/forms/v2/forms';
    $http_headres = array(
        "Authorization: Bearer $hubspot_access_token",
        "Content-Type: application/json"
    );

    $formData='{
    "name": "Order From Magento",
    "action": "",
    "method": "",
    "cssClass": "",
    "redirect": "",
    "submitText": "Submit",
    "followUpId": "",
    "notifyRecipients": "",
    "leadNurturingCampaignId": "",
    "formFieldGroups": [
        {
            "fields": [
                {
                    "name": "subject",
                    "label": "Subject",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 0,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "order_id",
                    "label": "Order No",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 1,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "email",
                    "label": "Email",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 1,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "status",
                    "label": "Order Status",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "bill_street",
                    "label": "Billing Street",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "bill_city",
                    "label": "Billing City",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "bill_code",
                    "label": "Billing Code",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "bill_country",
                    "label": "Billing Country",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "bill_state",
                    "label": "Billing State",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "ship_city",
                    "label": "Shipping City",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "ship_street",
                    "label": "Shipping Street",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "ship_code",
                    "label": "Shipping Code",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "ship_country",
                    "label": "Shipping Country",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "ship_state",
                    "label": "Shipping State",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "tax",
                    "label": "Tax",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "subtotal",
                    "label": "Subtotal",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "total",
                    "label": "Grand Total",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        }
    ],
    "createdAt": 1318534279910,
    "updatedAt": 1413919291011,
    "performableHtml": "",
    "migratedFrom": "ld",
    "ignoreCurrentValues": false,
    "metaData": [],
    "deletable": true
}';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $formUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $response = curl_exec($ch);
    $responseDecode = json_decode($response, true);
    // print_r($responseDecode);
    if(isset($responseDecode['guid'])) {
        $portal_id = $responseDecode['portalId'];
        $form_id = $responseDecode['guid'];
        $updateArr = array(
            'app2_cred2' => $portal_id,
            'app2_cred3' => $form_id
        );
        $cond = " AND id=$row_id";
        $re = update($userSubscription, $updateArr, $cond);
        if ($re) {
            echo '1';
            //header("Location:https://" . APP_DOMAIN . "/app/subscription/select-plan.php?access_id=$credential");
        } else {
            echo "Not Updated";
        }
    }
    else{
        echo $responseDecode['message'];
    }

}
function shopifyForm() {
    include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";
    //form create on hubspot
    $hubspot_access_token = trim($_REQUEST['access_token']);
    $row_id = trim($_REQUEST['credential']);
    $formUrl =HUBAPI_URL . '/forms/v2/forms';
    $http_headres = array(
        "Authorization: Bearer $hubspot_access_token",
        "Content-Type: application/json"
    );

    $formData='{
    "name": "Order From Shopify",
    "action": "",
    "method": "",
    "cssClass": "",
    "redirect": "",
    "submitText": "Submit",
    "followUpId": "",
    "notifyRecipients": "",
    "leadNurturingCampaignId": "",
    "formFieldGroups": [
        {
            "fields": [
                {
                    "name": "subject",
                    "label": "Subject",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 0,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "order_id",
                    "label": "Order No",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 1,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "email",
                    "label": "Email",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 1,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "status",
                    "label": "Order Status",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "bill_street",
                    "label": "Billing Street",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "bill_city",
                    "label": "Billing City",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "bill_code",
                    "label": "Billing Code",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "bill_country",
                    "label": "Billing Country",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "bill_state",
                    "label": "Billing State",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "ship_city",
                    "label": "Shipping City",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "ship_street",
                    "label": "Shipping Street",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "ship_code",
                    "label": "Shipping Code",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "ship_country",
                    "label": "Shipping Country",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "ship_state",
                    "label": "Shipping State",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "tax",
                    "label": "Tax",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "subtotal",
                    "label": "Subtotal",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        },
        {
            "fields": [
                {
                    "name": "total",
                    "label": "Grand Total",
                    "type": "string",
                    "fieldType": "text",
                    "description": "",
                    "groupName": "",
                    "displayOrder": 2,
                    "required": false,
                    "selectedOptions": [],
                    "options": [],
                    "validation": {
                        "name": "",
                        "message": "",
                        "data": "",
                        "useDefaultBlockList": false
                    },
                    "enabled": true,
                    "hidden": false,
                    "defaultValue": "",
                    "isSmartField": false,
                    "unselectedLabel": "",
                    "placeholder": ""
                }
            ],
            "default": true,
            "isSmartGroup": false
        }
    ],
    "createdAt": 1318534279910,
    "updatedAt": 1413919291011,
    "performableHtml": "",
    "migratedFrom": "ld",
    "ignoreCurrentValues": false,
    "metaData": [],
    "deletable": true
}';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $formUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $response = curl_exec($ch);
    $responseDecode = json_decode($response, true);
    // print_r($responseDecode);
    if(isset($responseDecode['guid'])) {
        $portal_id = $responseDecode['portalId'];
        $form_id = $responseDecode['guid'];
        $updateArr = array(
            'app2_cred2' => $portal_id,
            'app2_cred3' => $form_id
        );
        $cond = " AND id=$row_id";
        $re = update($userSubscription, $updateArr, $cond);
        if ($re) {
            echo '1';
            //header("Location:https://" . APP_DOMAIN . "/app/subscription/select-plan.php?access_id=$credential");
        } else {
            echo "Not Updated";
        }
    }
    else{
        echo $responseDecode['message'];
    }

}