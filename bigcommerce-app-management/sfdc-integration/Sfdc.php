<?php

/*
 * Description:     
 */
require('sfdc/SforceEnterpriseClient.php');

class Sfdc {
    /* credentials checking */

    static function checkCredentials($SfdcPassword, $SfdcUsername, $SfdcSecurityToken) {
        $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";

        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $response = $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
        return $response;
    }

    /* creating account in sfdc */

    static function createSfdcAccount($sfdcCredentials,$records) {
        $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        $SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        $SfdcPassword = $sfdcCredentials['sfdc_password'];
        $SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];
		//print_r($SfdcUsername);
        //print_r("Testing");
		//exit(1);
        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $result = $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
        $response = $mySforceConnection->create($records, 'Account');
        return $response[0];
    }

    static function updateSfdcAccount($sfdcCredentials,$records) {
        $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        $SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        $SfdcPassword = $sfdcCredentials['sfdc_password'];
        $SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];
        //print_r($SfdcUsername);
        //print_r("Testing");
        //exit(1);
        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $result = $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
        $response = $mySforceConnection->update($records, 'Account');
        return $response[0];
    }

	static function createSfdcContact($sfdcCredentials,$records) {
        $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        $SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        $SfdcPassword = $sfdcCredentials['sfdc_password'];
        $SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];
		//print_r($SfdcUsername);
        //print_r("Testing");
		//exit(1);
        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $result = $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
        $response = $mySforceConnection->create($records, 'Contact');
        return $response[0];
    }
	
    /* creating product in sfdc */

    static function createSfdcProducts($sfdcCredentials,$records) {
        $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        $SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        $SfdcPassword = $sfdcCredentials['sfdc_password'];
        $SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];

        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
        $response = $mySforceConnection->create($records, 'Product2');
        return $response[0];
    }

    /* adding product in sfdc price book */

    static function addToPriceBook($sfdcCredentials,$records) {
        $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        $SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        $SfdcPassword = $sfdcCredentials['sfdc_password'];
        $SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];

        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
        $response = $mySforceConnection->create($records, 'PricebookEntry');
        return $response[0];
    }

    /* adding product in sfdc price book */

    static function updatePriceBook($sfdcCredentials,$records) {
        $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        $SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        $SfdcPassword = $sfdcCredentials['sfdc_password'];
        $SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];

        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
        $response = $mySforceConnection->update($records, 'PricebookEntry');
        return $response[0];
    }

    /* creating price book in sfdc */

    static function createSfdcPricebook($sfdcCredentials,$records) {
        $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        $SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        $SfdcPassword = $sfdcCredentials['sfdc_password'];
        $SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];

        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
        $response = $mySforceConnection->create($records, 'Pricebook2');
        return $response[0];
    }

    static function getPriceBookDetails($sfdcCredentials) {
        $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        $SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        $SfdcPassword = $sfdcCredentials['sfdc_password'];
        $SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];

        $query = "Select Name,Id FROM Pricebook2 WHERE IsActive = true";
        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
        $response = $mySforceConnection->query($query);
		//print_r("PriceBook: ");
		//print_r($response);
        return $response->records[0];
    }

    static function createOpportunity($sfdcCredentials,$records) {
        $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        $SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        $SfdcPassword = $sfdcCredentials['sfdc_password'];
        $SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];
        
        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);

        $response = $mySforceConnection->create($records, 'Opportunity');
        return $response[0];
    }
	
	static function createOrder($sfdcCredentials,$records) {
        $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        $SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        $SfdcPassword = $sfdcCredentials['sfdc_password'];
        $SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];
        
        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
        $response = $mySforceConnection->create($records, 'Order');
        return $response[0];
    }
	static function updateOrder($sfdcCredentials, $records) {
       $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        $SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        $SfdcPassword = $sfdcCredentials['sfdc_password'];
        $SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];

        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
        $response = $mySforceConnection->update($records, 'Order');
        return $response[0];
    }
	static function createOrderItem($sfdcCredentials,$records) {
        $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        $SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        $SfdcPassword = $sfdcCredentials['sfdc_password'];
        $SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];
        
        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
        $response = $mySforceConnection->create($records, 'OrderItem');
        return $response[0];
    }
    static function addProductToOpportunity($sfdcCredentials,$records) {
        $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        $SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        $SfdcPassword = $sfdcCredentials['sfdc_password'];
        $SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];

        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);

        $response = $mySforceConnection->create($records, 'OpportunityLineItem');
        return $response[0];
    }

    static function sfdcQuery($sfdcCredentials,$query) {
        $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        $SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        $SfdcPassword = $sfdcCredentials['sfdc_password'];
        $SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];
        
        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
        $response = $mySforceConnection->query($query);
        return $response->records[0];
    }

    static function sfdcSelectQuery($sfdcCredentials,$query) {
        $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        $SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        $SfdcPassword = $sfdcCredentials['sfdc_password'];
        $SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];
        
        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
        $response = $mySforceConnection->query($query);
        return $response->records;
    }
	static function sfdcFindRecord($sfdcCredentials,$query) {
        $SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
        $SfdcUsername = $sfdcCredentials['sfdc_user_name'];
        $SfdcPassword = $sfdcCredentials['sfdc_password'];
        $SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];

        $mySforceConnection = new SforceEnterpriseClient();
        $mySforceConnection->createConnection($SfdcWsdl);
        $mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);	
		$response = $mySforceConnection->query($query);
		return $response;
    }
}