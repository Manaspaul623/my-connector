<?php
include "/var/www/html/bigcommerce-app-management/sfdc-integration/Sfdc.php"; // Globally include SFDC Class

//Core Function For Magento Sync....
function magentoToZohoCRM($credentials)
{
    /* including database related files */
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;
    $cloudApp = 'ZOHO';

    //

    $subscriptionId = $credentials['subscription_id'];
    $crmType = 'magentoTozoho';//$credentials['crm_type'];
    $contextVal = //$credentials['contextValue'];

    $magentoURL = $credentials['app1Details']['magento_context'];
    $magentoID = $credentials['app1Details']['magento_user_name'];
    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];
    $zohoCRMAuthtoken = $credentials['app2Details']['zoho_auth_id'];
    //$access_token = $credentials['bigCommerceAccessToken'];
    $magentoPassword = $credentials['app1Details']['magento_password'];
    //$accountType = $credentials['account_type'];

    // Get Magento Token
    $magentoURL = $magentoURL . "rest/V1/";

    $total_success = 0;
    $url = $magentoURL . "integration/admin/token";
    $http_headres = array(
        "Accept: application/json",
    );
    $postfields = array('username' => $magentoID,
        'password' => $magentoPassword);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


    if ($http_code === 200) {
        curl_close($ch);
        $magentoToken = json_decode($return, TRUE);


    }


    try {

        //............ORDER LIST COLLECTIONG.................


        $orderUrl = $magentoURL . "orders?searchCriteria[filter_groups][0][filters][0][field]=created_at&searchCriteria[filter_groups][0][filters][0][value]=" . $min_date_created . "&searchCriteria[filter_groups][0][filters][0][condition_type]=from&searchCriteria[filter_groups][1][filters][1][field]=created_at&searchCriteria[filter_groups][1][filters][1][value]=" . $max_date_created . "&searchCriteria[filter_groups][1][filters][1][condition_type]=to";
        $http_headres = array(
            "content-Type: application/json",
            "acccept: application/json",
            "authorization: Bearer " . $magentoToken
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $orderUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code === 200) {
            $orders = json_decode($return, TRUE);
            $count = $orders['total_count'];

            if ($count > 0) {

                for ($orderCount = 1; $orderCount <= $count; $orderCount++) {
                    $key = $orderCount - 1;

                    //Customer Detials Collect
                    $customerId = $orders['items'][$key]['customer_id'];
                    $currencyExchangeRate = $orders['items'][$key]['base_to_global_rate'];
                    $discountAmount = $orders['items'][$key]['discount_amount'];
                    //Order id collect
                    $orderId = $orders['items'][$key]['items'][0]['order_id'];
                    $subject = 'Order Number ' . $orders['items'][$key]['items'][0]['order_id'];

                    $totalProduct = $orders['items'][$key]['total_item_count'];

                    //Status of Order./................
                    $currentOrderStatus = $orders['items'][$key]['status'];

                    if (($currentOrderStatus === 'complete') || ($currentOrderStatus === 'pending')) {
                        $salesOrder = 'TRUE';
                    } else {
                        $salesOrder = 'FALSE';

                    }


                    //Customer  Detail collect..........

                    $customerDetailUrl = $magentoURL . "customers/" . $customerId;
                    $http_headres = array(
                        "content-Type: application/json",
                        "acccept: application/json",
                        "authorization: Bearer " . $magentoToken
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $customerDetailUrl);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                    $return = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);


                    //End of Collecting of Customer Detailas
                    if ($http_code === 200) {

                        $customerDetails = json_decode($return, TRUE);

                        $firstName = $customerDetails['firstname'];
                        $lastName = $customerDetails['lastname'];
                        if(empty($customerDetails['company'])) {
                            $accountName = $firstName . ' ' . $lastName;
                        }else{
                            $accountName = $customerDetails['company'];
                        }
                        $email = $customerDetails['email'];
                        if(!empty($customerDetails['addresses'][0])) {
                            $phone = $customerDetails['addresses'][0]['telephone'];
                        }
                        else{
                            $phone ='';
                        }


                        //Customer Billing Address Collecting
                        $orderUrl = $magentoURL . "customers/" . $customerId . "/billingAddress";
                        $http_headres = array(
                            "content-Type: application/json",
                            "acccept: application/json",
                            "authorization: Bearer " . $magentoToken
                        );
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $orderUrl);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                        $return = curl_exec($ch);
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);
                        //End of Billing Address Collecting
                        $customerBillingAddress = json_decode($return, TRUE);

                        if (!empty($customerBillingAddress)) {
                            $addressDetails = json_decode($return, TRUE);


                            $mailingStreet = $addressDetails['street'][0];
                            $otherStreet = $addressDetails['street'][0];
                            $mailingCity = $addressDetails['city'];
                            $otherCity = $addressDetails['city'];
                            $mailingState = $addressDetails['region']['region'];
                            $otherState = $addressDetails['region']['region'];
                            $mailingZip = $addressDetails['postcode'];
                            $otherZip = $addressDetails['postcode'];
                            $mailingCountry = $addressDetails['country_id'];
                            $otherCountry = $addressDetails['country_id'];
                        } else {
                            $mailingStreet = $orders['items'][$key]['billing_address']['street'][0];
                            $otherStreet = $orders['items'][$key]['billing_address']['street'][0];
                            $mailingCity = $orders['items'][$key]['billing_address']['city'];
                            $otherCity = $orders['items'][$key]['billing_address']['city'];
                            $mailingState = $orders['items'][$key]['billing_address']['region'];
                            $otherState = $orders['items'][$key]['billing_address']['region'];
                            $mailingZip = $orders['items'][$key]['billing_address']['postcode'];
                            $otherZip = $orders['items'][$key]['billing_address']['postcode'];
                            $mailingCountry = $orders['items'][$key]['billing_address']['country_id'];
                            $otherCountry = $orders['items'][$key]['billing_address']['country_id'];
                        }

                        $flag = FALSE;
                        try {
                            // Insert Account details
                            $xml = '<?xml version="1.0" encoding="UTF-8"?>
                            <Accounts>
                                <row no="1">
                                        <FL val="Account Name">' . $accountName . '</FL>
                                        <FL val="Email">' . $email . '</FL>
                                        <FL val="Phone">' . $phone . '</FL>
                                        <FL val="Billing Street">' . $mailingStreet . '</FL>
                                        <FL val="Shipping Street">' . $otherStreet . '</FL>
                                        <FL val="Billing City">' . $mailingCity . '</FL>
                                        <FL val="Shipping City">' . $otherCity . '</FL>
                                        <FL val="Billing State">' . $mailingState . '</FL>
                                        <FL val="Shipping State">' . $otherState . '</FL>
                                        <FL val="Billing Code">' . $mailingZip . '</FL>
                                        <FL val="Shipping Code">' . $otherZip . '</FL>
                                        <FL val="Billing Country">' . $mailingCountry . '</FL>
                                        <FL val="Shipping Country">' . $otherCountry . '</FL>
                                </row>
                             </Accounts>';

                            // For Insert Records with duplicate checking.

                            $url = "https://crm.zoho.com/crm/private/xml/Accounts/insertRecords";
                            $query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$xml";
                            $ch = curl_init();
                            /* set url to send post request */
                            curl_setopt($ch, CURLOPT_URL, $url);
                            /* allow redirects */
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                            /* return a response into a variable */
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            /* times out after 30s */
                            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                            /* set POST method */
                            curl_setopt($ch, CURLOPT_POST, 1);
                            /* add POST fields parameters */
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                            // Execute cUrl session

                            $response = curl_exec($ch);
                            curl_close($ch);;


                            $xml = '<?xml version="1.0" encoding="UTF-8"?>
                            <Contacts>
                                <row no="1">
                                    <FL val="First Name">' . $firstName . '</FL>
                                    <FL val="Last Name">' . $lastName . '</FL>
                                    <FL val="Account Name">' . $accountName . '</FL>
                                    <FL val="Email">' . $email . '</FL>
                                    <FL val="Phone">' . $phone . '</FL>
                                    <FL val="Mailing Street">' . $mailingStreet . '</FL>
                                    <FL val="Other Street">' . $otherStreet . '</FL>
                                    <FL val="Mailing City">' . $mailingCity . '</FL>
                                    <FL val="Other City">' . $otherCity . '</FL>
                                    <FL val="Mailing State">' . $mailingState . '</FL>
                                    <FL val="Other State">' . $otherState . '</FL>
                                    <FL val="Mailing Zip">' . $mailingZip . '</FL>
                                    <FL val="Other Zip">' . $otherZip . '</FL>
                                    <FL val="Mailing Country">' . $mailingCountry . '</FL>
                                    <FL val="Other Country">' . $otherCountry . '</FL>
                                </row>
                            </Contacts>';

                            // For Insert Records with duplicate checking.

                            $url = "https://crm.zoho.com/crm/private/xml/Contacts/insertRecords";
                            $query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$xml";
                            $ch = curl_init();
                            /* set url to send post request */
                            curl_setopt($ch, CURLOPT_URL, $url);
                            /* allow redirects */
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                            /* return a response into a variable */
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            /* times out after 30s */
                            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                            /* set POST method */
                            curl_setopt($ch, CURLOPT_POST, 1);
                            /* add POST fields parameters */
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                            // Execute cUrl session

                            $response = curl_exec($ch);
                            curl_close($ch);
                            $res = simplexml_load_string($response);
                            $resCode = (string)$res->result->row->success->code;

                            if (($resCode === '2000') || ($resCode === '2001') || ($resCode === '2002')) {
                                // $flag = TRUE;

                                if ($resCode === '2000') {
                                    $flagCustomer++;
                                }


                                // Order Details Collect
                                $subject = 'Order Number ' . $orders['items'][$key]['items'][0]['order_id'];
                                $grandTotal = $orders['items'][$key]['grand_total'];
                                $subTotal = $orders['items'][$key]['base_grand_total'];
                                $tax = $orders['items'][$key]['tax_amount'];
                                $adjustment = ($grandTotal - $subTotal)-$tax;

                                $shippingAmounts = $orders['items'][$key]['shipping_amount'];
                                //Billing Address Detail
                                $orderBillingStreet = $orders['items'][$key]['billing_address']['street'][0];
                                $orderBillingCity = $orders['items'][$key]['billing_address']['city'];
                                $orderBillingZip = $orders['items'][$key]['billing_address']['postcode'];
                                $orderBillingCountry = $orders['items'][$key]['billing_address']['country_id'];
                                $orderBillingState = $orders['items'][$key]['billing_address']['region'];
                                //Shipping Address Detail
                                $orderShippingCity = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['city'];
                                $orderShippingState = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['region'];
                                $orderShippingStreet = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['street'][0];
                                $orderShippingZip = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['postcode'];
                                $orderShippingCountry = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['country_id'];

                                if ($salesOrder === 'TRUE') {
                                    $InvoiceXML = '<?xml version="1.0" encoding="UTF-8"?>
                                        <SalesOrders>
                                          <row no="1">
                                            <FL val="Contact Name">' . $accountName . '</FL>
                                            <FL val="Account Name">' . $accountName . '</FL>
                                            <FL val="Customer No">' . $customerId . '</FL>
                                            <FL val="Subject">' . $subject . '</FL>
                                            <FL val="Product Details"></FL>
                                            <FL val="Billing Street">' . $orderBillingStreet . '</FL>
                                            <FL val="Shipping Street">' . $orderShippingStreet . '</FL>
                                            <FL val="Billing City">' . $orderBillingCity . '</FL>
                                            <FL val="Shipping City">' . $orderShippingCity . '</FL>
                                            <FL val="Billing State">' . $orderBillingState . '</FL>
                                            <FL val="Shipping State">' . $orderShippingState . '</FL>
                                            <FL val="Billing Code">' . $orderBillingZip . '</FL>
                                            <FL val="Shipping Code">' . $orderShippingZip . '</FL>
                                            <FL val="Billing Country">' . $orderBillingCountry . '</FL>
                                            <FL val="Shipping Country">' . $orderShippingCountry . '</FL>
                                            <FL val="Excise Duty">' . $shippingAmounts . '</FL>
                                            <FL val="Status">' . $currentOrderStatus . '</FL>
                                            <FL val="Sub Total">' . $subTotal . '</FL>
                                            <FL val="Tax">' . $tax . '</FL>
                                            <FL val="Adjustment">' . $adjustment . '</FL>
                                            <FL val="Grand Total">' . $grandTotal . '</FL>
                                          </row>
                                        </SalesOrders>';
                                } else {
                                    $InvoiceXML = '<?xml version="1.0" encoding="UTF-8"?>
                                      <Quotes>
                                       <row no="1">
                                        <FL val="Contact Name">' . $accountName . '</FL>
                                        <FL val="Account Name">' . $accountName . '</FL>
                                        <FL val="Subject">' . $subject . '</FL>
                                        <FL val="Product Details">
                                        </FL>
                                        <FL val="Billing Street">' . $orderBillingStreet . '</FL>
                                        <FL val="Shipping Street">' . $orderShippingStreet . '</FL>
                                        <FL val="Billing City">' . $orderBillingCity . '</FL>
                                        <FL val="Shipping City">' . $orderShippingCity . '</FL>
                                        <FL val="Billing State">' . $orderBillingState . '</FL>
                                        <FL val="Shipping State">' . $orderShippingState . '</FL>
                                        <FL val="Billing Code">' . $orderBillingZip . '</FL>
                                        <FL val="Shipping Code">' . $orderShippingZip . '</FL>
                                        <FL val="Billing Country">' . $orderBillingCountry . '</FL>
                                        <FL val="Shipping Country">' . $orderShippingCountry . '</FL>
                                        <FL val="Sub Total">' . $subTotal . '</FL>
                                        <FL val="Tax">' . $tax . '</FL>
                                        <FL val="Adjustment">' . $adjustment . '</FL>
                                        <FL val="Grand Total">' . $grandTotal . '</FL>
                                        </row>
                                      </Quotes>';

                                }
                                $sxeInvoiceXML = new SimpleXMLElement($InvoiceXML);

                                //PRODUCT DETAILS COLLECT FROM ORDER
                                $totalProduct = count($orders['items'][$key]['items']);

                                if ($totalProduct > 0) {
                                    $prodNumber = 0;
                                    for ($i = 0; $i < $totalProduct; $i++) {
                                        //PRODUCT ID COLLECTED

                                        $productId = $orders['items'][$key]['items'][$i]['sku'];
                                        $prodCode = $productId;

                                        $m_quantity = $orders['items'][$key]['items'][$i]['qty_ordered'];
                                        $prodListPrice = $orders['items'][$key]['items'][$i]['base_price'];
                                        $prodUnitPrice = $orders['items'][$key]['items'][$i]['price'];
                                        $total = $orders['items'][$key]['items'][$i]['base_row_total'];
                                        $totalAfterDiscount = $orders['items'][$key]['items'][$i]['base_row_total_incl_tax'];
                                        //$discount = $total - $totalAfterDiscount;
                                        $discount = $orders['items'][$key]['items'][$i]['discount_amount'];
                                        $tax = $orders['items'][$key]['items'][$i]['tax_amount'];
                                        $netTotal = $orders['items'][$key]['items'][$i]['row_total'];

                                        //PRODUCT DETAILS COLLECT
                                        $productUrl = $magentoURL . "products/" . $productId;
                                        $http_headres = array(
                                            "content-Type: application/json",
                                            "cccept: application/json",
                                            "authorization: Bearer " . $magentoToken
                                        );
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $productUrl);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                        $return = curl_exec($ch);
                                        curl_close($ch);

                                        $product = json_decode($return, TRUE);


                                        //PRODUCT DETAILS IF EXISTS .................
                                        $prodCode = $product['sku'];
                                        $prodName = $product['name'];
                                        $m_quantity = $product['extension_attributes']['stock_item']['min_sale_qty'];
                                        $stock_quantity = $product['extension_attributes']['stock_item']['qty'];
                                        $price = $product['price'];
                                        $description = $product['custom_attributes'][0]['value'];

                                        $ProductXML = '<?xml version="1.0" encoding="UTF-8"?>
																					 <Products>
																							<row no="1">
																							<FL val="Product Code">' . $prodCode . '</FL>
																							<FL val="Product Name"><![CDATA[' . urlencode($prodName) . ']]></FL>
																							<FL val="Unit Price">' . $prodUnitPrice . '</FL>
																							<FL val="Qty Ordered"><![CDATA[' . urlencode($m_quantity) . ']]></FL>
																							<FL val="Qty in Stock"><![CDATA[' . urlencode($stock_quantity) . ']]></FL>
																							<FL val="Description"><![CDATA[' . urlencode($description) . ']]></FL>
																							</row>
																					</Products>';
                                        $output = simplexml_load_string($ProductXML);

                                        // For Insert Records with duplicate checking.

                                        $url = "https://crm.zoho.com/crm/private/xml/Products/insertRecords";
                                        $query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$ProductXML";
                                        $ch = curl_init();
                                        /* set url to send post request */
                                        curl_setopt($ch, CURLOPT_URL, $url);
                                        /* allow redirects */
                                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                        /* return a response into a variable */
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                        /* times out after 30s */
                                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                        /* set POST method */
                                        curl_setopt($ch, CURLOPT_POST, 1);
                                        /* add POST fields parameters */
                                        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                        // Execute cUrl session

                                        $response = curl_exec($ch);
                                        curl_close($ch);
                                        $res = simplexml_load_string($response);
                                        if ($res->result != FALSE) {

                                            $resCode = (string)$res->result->row->success->code;
                                            $prodId = $res->result->row->success->details->FL[0];
                                        } else {
                                            $resCode = 0;
                                        }


                                        if (($resCode === '2000') || ($resCode === '2001') || ($resCode === '2002')) {

                                            // $flag = TRUE;

                                            if ($resCode === '2000') {
                                                $flagProduct++;
                                            }
                                            if ($resCode === '2002') // duplicate product
                                            {
                                                // Update Product
                                                $url = "https://crm.zoho.com/crm/private/xml/Products/updateRecords";
                                                $query = "authtoken=$zohoCRMAuthtoken&newFormat=1&scope=crmapi&id=$prodId&xmlData=$ProductXML";
                                                $ch = curl_init();
                                                /* set url to send post request */
                                                curl_setopt($ch, CURLOPT_URL, $url);
                                                /* allow redirects */
                                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                                /* return a response into a variable */
                                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                                /* times out after 30s */
                                                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                                /* set POST method */
                                                curl_setopt($ch, CURLOPT_POST, 1);
                                                /* add POST fields parameters */
                                                curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                                // Execute cUrl session

                                                $response = curl_exec($ch);
                                                curl_close($ch);
                                                $res = simplexml_load_string($response);


                                            }



                                            if(!isset($orders['items'][$key]['items'][$i]['parent_item_id'])) {
                                                $prodNumber++;
                                                $productDetails = $sxeInvoiceXML->row->FL[3]->addChild("product");
                                                $productDetails->addAttribute('no', $prodNumber);
                                                $FLsku = $productDetails->addChild("FL", $prodId);
                                                $FLsku->addAttribute('val', 'Product Id');
                                                $FLquantity = $productDetails->addChild("FL", $m_quantity);
                                                $FLquantity->addAttribute('val', 'Quantity');
                                                $FLprice = $productDetails->addChild("FL", $prodUnitPrice);
                                                $FLprice->addAttribute('val', 'Unit Price');
                                                $FLprice = $productDetails->addChild("FL", $prodListPrice);
                                                $FLprice->addAttribute('val', 'List Price');
                                                $FLprice = $productDetails->addChild("FL", $total);
                                                $FLprice->addAttribute('val', 'Total');
                                                $FLprice = $productDetails->addChild("FL", $discount);
                                                $FLprice->addAttribute('val', 'Discount');
                                                $FLprice = $productDetails->addChild("FL", $netTotal);
                                                $FLprice->addAttribute('val', 'Total after Discount');
                                                $FLprice = $productDetails->addChild("FL", $tax);
                                                $FLprice->addAttribute('val', 'Tax');
                                                $FLprice = $productDetails->addChild("FL", $netTotal);
                                                $FLprice->addAttribute('val', 'Net Total');
                                            }
                                        } else {

                                            $flag = FALSE;
                                            $st = 'YES';
                                            $AccountHead = 'PRODUCT';
                                            // SaveErrorDetails fails when $prodName has special characters used in MySQL
                                            SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Product ' . $prodName);
                                        }


                                    }// for each product

                                    $InvoiceXML = $sxeInvoiceXML->asXML();

                                }
                                if ($salesOrder === 'TRUE') {
                                    $url = "https://crm.zoho.com/crm/private/xml/SalesOrders/insertRecords";
                                } else {
                                    $url = "https://crm.zoho.com/crm/private/xml/Quotes/insertRecords";
                                }
                                $query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$InvoiceXML";

                                $ch = curl_init();
                                /* set url to send post request */
                                curl_setopt($ch, CURLOPT_URL, $url);
                                /* allow redirects */
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                /* return a response into a variable */
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                /* times out after 30s */
                                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                /* set POST method */
                                curl_setopt($ch, CURLOPT_POST, 1);
                                /* add POST fields parameters */
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                // Execute cUrl session

                                $response = curl_exec($ch);

                                // $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                                curl_close($ch);

                                // check if it returns 200, or else return false

                                $res = simplexml_load_string($response);
                                if ($res->result != FALSE) {
                                    $resCode = (string)$res->result->row->success->code;
                                } else {
                                    $resCode = 0;
                                }
                                if (($resCode === '2000') || ($resCode === '2001') || ($resCode === '2002')) {

                                    // $flag = TRUE;

                                    if ($resCode === '2000') {
                                        $flagOrder++;
                                    }
                                } else {

                                    $flag = FALSE;
                                    $st = 'YES';
                                    $AccountHead = 'ORDER';
                                    SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Order Number: ' . $orders[$key]['id']);
                                }


                            } else {

                                $AccountHead = 'CUSTOMER';

                                SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Customer: ' . $accountName);
                                $flag = FALSE;
                            }

                        } catch (Exception $e) {
                            $msg = $e;
                            $flag = FALSE;
                        }


                    }

                }
            }


        }

    } catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }

    // Create reports.

    $status = 'YES';
    $totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
    $userDataArrTransaction = array(
        'user_id' => $credentials['userId'],
        'crm_type' => $crmType,
        'no_customer_data' => $flagCustomer,
        'no_product_data' => $flagProduct,
        'no_order_data' => $flagOrder,
        'last_sync_time' => time(),
        'total_transaction' => $totalTransaction,
        'status' => $status,
        'added_on' => time()
    );
    $credentials['counterOrders'] = $flagOrder;
    $credentials['counterProducts'] = $flagProduct;
    $credentials['counterAccounts'] = $flagCustomer;
    //for table transection detials
    tblTransectionDetail($credentials, $crmType, $userDataArrTransaction);

    sendSyncReport($credentials, $crmType);

    return $flagOrder;
} //Done

function magentoToZohoInventoryCRM($credentials)
{
    /* including database related files */
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;
    $cloudApp = 'ZOHO_INVENTORY';

    //

    $subscriptionId = $credentials['subscription_id'];
    $crmType = 'magentoTozohoinventory';//$credentials['crm_type'];
    $contextVal = //$credentials['contextValue'];

    $magentoURL = $credentials['app1Details']['magento_context'];
    $magentoID = $credentials['app1Details']['magento_user_name'];
    $magentoPassword = $credentials['app1Details']['magento_password'];
    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];
    $zohoInventoryAuthToken = $credentials['app2Details']['zoho_auth_id'];
    $zohoInventoryOrgID = $credentials['app2Details']['zoho_organisation_id'];
    //$access_token = $credentials['bigCommerceAccessToken'];

    //$accountType = $credentials['account_type'];

    // Get Magento Token
    $magentoURL = $magentoURL . "rest/V1/";

    $total_success = 0;
    $url = $magentoURL . "integration/admin/token";
    $http_headres = array(
        "Accept: application/json",
    );
    $postfields = array('username' => $magentoID,
        'password' => $magentoPassword);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


    if ($http_code === 200) {
        curl_close($ch);
        $magentoToken = json_decode($return, TRUE);


    }


    try {

        //............ORDER LIST COLLECTIONG.................


        $orderUrl = $magentoURL . "orders?searchCriteria[filter_groups][0][filters][0][field]=created_at&searchCriteria[filter_groups][0][filters][0][value]=" . $min_date_created . "&searchCriteria[filter_groups][0][filters][0][condition_type]=from&searchCriteria[filter_groups][1][filters][1][field]=created_at&searchCriteria[filter_groups][1][filters][1][value]=" . $max_date_created . "&searchCriteria[filter_groups][1][filters][1][condition_type]=to";
        $http_headres = array(
            "content-Type: application/json",
            "acccept: application/json",
            "authorization: Bearer " . $magentoToken
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $orderUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code === 200) {
            $orders = json_decode($return, TRUE);
            $count = $orders['total_count'];

            if ($count > 0) {

                for ($orderCount = 1; $orderCount <= $count; $orderCount++) {
                    $key = $orderCount - 1;

                    //Customer Detials Collect
                    $customerId = $orders['items'][$key]['customer_id'];
                    $currencyExchangeRate = $orders['items'][$key]['base_to_global_rate'];
                    $discountAmount = $orders['items'][$key]['discount_amount'];
                    //Order id collect
                    $orderId = $orders['items'][$key]['items'][0]['order_id'];
                    $subject = 'Order Number ' . $orders['items'][$key]['items'][0]['order_id'];

                    $totalProduct = $orders['items'][$key]['total_item_count'];

                    //Status of Order./................
                    $currentOrderStatus = $orders['items'][$key]['status'];

                    if (($currentOrderStatus === 'complete') || ($currentOrderStatus === 'pending')) {
                        $salesOrder = 'confirmed';

                    } else {

                        $salesOrder = $currentOrderStatus;
                    }


                    //Customer  Detail collect..........

                    $customerDetailUrl = $magentoURL . "customers/" . $customerId;
                    $http_headres = array(
                        "content-Type: application/json",
                        "acccept: application/json",
                        "authorization: Bearer " . $magentoToken
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $customerDetailUrl);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                    $return = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);


                    //End of Collecting of Customer Detailas
                    if ($http_code === 200) {

                        $customerDetails = json_decode($return, TRUE);
                        $firstName = $customerDetails['firstname'];
                        $lastName = $customerDetails['lastname'];
                        if(empty($customerDetails['company'])) {
                            $accountName = $firstName . ' ' . $lastName;
                        }else{
                            $accountName = $customerDetails['company'];
                        }
                        $email = $customerDetails['email'];
                        if(!empty($customerDetails['addresses'][0])) {
                            $phone = $customerDetails['addresses'][0]['telephone'];
                        }
                        else{
                            $phone ='';
                        }

                        //Customer Billing Address Collecting
                        $orderUrl = $magentoURL . "customers/" . $customerId . "/billingAddress";
                        $http_headres = array(
                            "content-Type: application/json",
                            "acccept: application/json",
                            "authorization: Bearer " . $magentoToken
                        );
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $orderUrl);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                        $return = curl_exec($ch);
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);
                        //End of Billing Address Collecting
                        $customerBillingAddress = json_decode($return, TRUE);
                        if (!empty($customerBillingAddress)) {
                            $addressDetails = json_decode($return, TRUE);


                            $mailingStreet = $addressDetails['street'][0];
                            $otherStreet = $addressDetails['street'][0];
                            $mailingCity = $addressDetails['city'];
                            $otherCity = $addressDetails['city'];
                            $mailingState = $addressDetails['region']['region'];
                            $otherState = $addressDetails['region']['region'];
                            $mailingZip = $addressDetails['postcode'];
                            $otherZip = $addressDetails['postcode'];
                            $mailingCountry = $addressDetails['country_id'];
                            $otherCountry = $addressDetails['country_id'];
                            $contactDetails = json_encode(array(
                                "contact_name" => $firstName . " " . $lastName . " ( " . $email . " ) ",
                                "company_name" => $addressDetails['company'],
                                "contact_type" => "customer",
                                "billing_address" => array(
                                    "address" => $mailingStreet,
                                    "city" => $mailingCity,
                                    "state" => $mailingState,
                                    "zip" => $mailingZip,
                                    "country" => $mailingCountry,
                                    "fax" => ""
                                ),
                                "shipping_address" => array(
                                    "address" => $otherStreet,
                                    "city" => $otherCity,
                                    "state" => $otherState,
                                    "zip" => $otherZip,
                                    "country" => $otherCountry,
                                    "fax" => ""
                                ),
                                "contact_persons" => array(
                                    array(
                                        "first_name" => $firstName,
                                        "last_name" => $lastName,
                                        "email" => $email,
                                        "phone" => $phone,
                                    )
                                )
                            ));

                        } else {
                            $mailingStreet = $orders['items'][$key]['billing_address']['street'][0];
                            $otherStreet = $orders['items'][$key]['billing_address']['street'][0];
                            $mailingCity = $orders['items'][$key]['billing_address']['city'];
                            $otherCity = $orders['items'][$key]['billing_address']['city'];
                            $mailingState = $orders['items'][$key]['billing_address']['region'];
                            $otherState = $orders['items'][$key]['billing_address']['region'];
                            $mailingZip = $orders['items'][$key]['billing_address']['postcode'];
                            $otherZip = $orders['items'][$key]['billing_address']['postcode'];
                            $mailingCountry = $orders['items'][$key]['billing_address']['country_id'];
                            $otherCountry = $orders['items'][$key]['billing_address']['country_id'];
                            $contactDetails = json_encode(array(
                                "contact_name" => $firstName . " " . $lastName . " ( " . $email . " ) ",
                                "company_name" => $customerDetails['addresses'][0]['company_name'],
                                "contact_type" => "customer",
                                "billing_address" => array(
                                    "address" => $mailingStreet,
                                    "city" => $mailingCity,
                                    "state" => $mailingState,
                                    "zip" => $mailingZip,
                                    "country" => $mailingCountry,
                                    "fax" => ""
                                ),
                                "shipping_address" => array(
                                    "address" => $otherStreet,
                                    "city" => $otherCity,
                                    "state" => $otherState,
                                    "zip" => $otherZip,
                                    "country" => $otherCountry,
                                    "fax" => ""
                                ),
                                "contact_persons" => array(
                                    array(
                                        "first_name" => $firstName,
                                        "last_name" => $lastName,
                                        "email" => $email,
                                        "phone" => $phone,
                                    )
                                )
                            ));

                        }

                        $flag = FALSE;
                        try {

                            $url = "https://inventory.zoho.com/api/v1/contacts";
                            $query = "organization_id=" . $zohoInventoryOrgID . "&authtoken=" . $zohoInventoryAuthToken . "&JSONString=" . $contactDetails;
                            $ch = curl_init();
                            /* set url to send post request */
                            curl_setopt($ch, CURLOPT_URL, $url);
                            /* allow redirects */
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                            /* return a response into a variable */
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            /* times out after 30s */
                            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                            /* set POST method */
                            curl_setopt($ch, CURLOPT_POST, 1);
                            /* add POST fields parameters */
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                            // Execute cUrl session
                            $response = curl_exec($ch);
                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


                            if ($http_code === 201 || $http_code === 400) {
                                // $flag = TRUE;

                                if ($http_code === 201) {
                                    $msg = json_decode($response, TRUE);
                                    $retVal = $msg['code'];
                                    $customerID = $msg['contact']['contact_id'];
                                    $flagCustomer++;
                                } else {  //For Duplicate Contact Detected
                                    //echo $contactName;
                                    $url = "https://inventory.zoho.com/api/v1/contacts?authtoken=" . $zohoInventoryAuthToken . "&organization_id=" . $zohoInventoryOrgID . "&email=" . $email;
                                    $ch = curl_init();
                                    /* set url to send post request */
                                    curl_setopt($ch, CURLOPT_URL, $url);
                                    /* allow redirects */
                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                    /* return a response into a variable */
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                    /* times out after 30s */
                                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                    /* set POST method */
                                    curl_setopt($ch, CURLOPT_POST, 0);


                                    // Execute cUrl session
                                    $response = curl_exec($ch);
                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                    $msg = json_decode($response, TRUE);
                                    $customerID = $msg['contacts'][0]['contact_id'];

                                    //echo "Old Customer :";
                                    //echo $customerID;
                                    curl_close($ch);

                                }


                                // Order Details Collect
                                $subject = 'Order Number ' . $orders['items'][$key]['items'][0]['order_id'];
                                $grandTotal = $orders['items'][$key]['grand_total'];
                                $subTotal = $orders['items'][$key]['base_grand_total'];
                                $tax = $orders['items'][$key]['tax_amount'];
                                $adjustment = $grandTotal - $subTotal;
                                //Billing Address Detail
                                $orderBillingStreet = $orders['items'][$key]['billing_address']['street'][0];
                                $orderBillingCity = $orders['items'][$key]['billing_address']['city'];
                                $orderBillingZip = $orders['items'][$key]['billing_address']['postcode'];
                                $orderBillingCountry = $orders['items'][$key]['billing_address']['country_id'];
                                $orderBillingState = $orders['items'][$key]['billing_address']['region'];
                                //Shipping Address Detail
                                $orderShippingCity = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['city'];
                                $orderShippingState = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['region'];
                                $orderShippingStreet = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['street'][0];
                                $orderShippingZip = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['postcode'];
                                $orderShippingCountry = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['country_id'];


                                //PRODUCT DETAILS COLLECT FROM ORDER
                                $totalProduct = count($orders['items'][$key]['items']);
                                if ($totalProduct > 0) {
                                    $prodNumber = 0;
                                    for ($i = 0; $i < $totalProduct; $i++) {
                                        //PRODUCT ID COLLECTED

                                        $productId = $orders['items'][$key]['items'][$i]['sku'];
                                        $prodCode = $productId;

                                        $m_quantity = $orders['items'][$key]['items'][$i]['qty_ordered'];
                                        $prodListPrice = $orders['items'][$key]['items'][$i]['base_price'];
                                        $prodUnitPrice = $orders['items'][$key]['items'][$i]['price'];
                                        $total = $orders['items'][$key]['items'][$i]['base_row_total'];
                                        $totalAfterDiscount = $orders['items'][$key]['items'][$i]['base_row_total_incl_tax'];
                                        //$discount = $total - $totalAfterDiscount;
                                        $discount = $orders['items'][$key]['items'][$i]['discount_amount'];
                                        $tax = $orders['items'][$key]['items'][$i]['tax_amount'];
                                        $netTotal = $orders['items'][$key]['items'][$i]['price_incl_tax'];

                                        //PRODUCT DETAILS COLLECT
                                        $productUrl = $magentoURL . "products/" . $productId;
                                        $http_headres = array(
                                            "content-Type: application/json",
                                            "acccept: application/json",
                                            "authorization: Bearer " . $magentoToken
                                        );
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $productUrl);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                        $return = curl_exec($ch);
                                        curl_close($ch);

                                        $product = json_decode($return, TRUE);


                                        //PRODUCT DETAILS IF EXISTS .................
                                        $prodCode = $product['sku'];
                                        $prodName = $product['name'];
                                        //$m_quantity = $product['extension_attributes']['stock_item']['min_sale_qty'];
                                        $stock_quantity = $product['extension_attributes']['stock_item']['qty'];
                                        $price = $product['price'];
                                        $description = $product['custom_attributes'][0]['value'];

                                        /* Insert each product into zoho Inventory */
                                        $itemDetails = json_encode(array(
                                            "item_type" => "inventory",
                                            "name" => $prodName,
                                            "rate" => $prodUnitPrice,
                                            "sku" => $prodCode,
                                            "initial_stock" => '100',
                                            "initial_stock_rate" => '100',
                                            "purchase_rate" => '0',
                                        ));
                                        /* Copy Product details ordered for inserting with Sales Order */
                                        if(!isset($orders['items'][$key]['items'][$i]['parent_item_id'])) {
                                            $orderItems[$prodNumber]['name'] = $prodName;
                                            $orderItems[$prodNumber]['description'] = $prodName; //str_replace(',', '', $description);
                                            $orderItems[$prodNumber]['rate'] = $prodUnitPrice;
                                            $orderItems[$prodNumber]['quantity'] = $m_quantity;
                                            $prodNumber++;
                                        }

                                        $url = "https://inventory.zoho.com/api/v1/items";
                                        $query = "organization_id=" . $zohoInventoryOrgID . "&authtoken=" . $zohoInventoryAuthToken . "&JSONString=" . $itemDetails;
                                        $ch = curl_init();
                                        /* set url to send post request */
                                        curl_setopt($ch, CURLOPT_URL, $url);
                                        /* allow redirects */
                                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                        /* return a response into a variable */
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                        /* times out after 30s */
                                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                        /* set POST method */
                                        curl_setopt($ch, CURLOPT_POST, 1);
                                        /* add POST fields parameters */
                                        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                        // Execute cUrl session

                                        $response = curl_exec($ch);
                                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


                                        if ($http_code === 201) {
                                            curl_close($ch);
                                            $flag = 1;
                                            $flagProduct++;
                                        } else // Product couldn't be added
                                        {
                                            $error = json_decode($response, TRUE);
                                            // Error is not because of duplicate record
                                            if ($error['code'] != 1001) {
                                                // Insert Error Data in Database
                                                $AccountHead = 'PRODUCT';
                                                $st = 'YES';
                                                SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Product ' . $prodCode);
                                            }
                                            curl_close($ch);
                                        }


                                    }// for each product

                                }
                                /* Insert Sales Order into Zoho Inventory */
                                //$salesOrderDate = DateTime::createFromFormat(DATE_RFC2822, $orders['items'][$key]['created_at']);
                                $date = explode(" ", $orders['items'][$key]['created_at']);

                                $salesOrderDetails = json_encode(array(
                                    "salesorder_number" => $orderId,
                                    "date" => $date[0],
                                    "shipment_date" => "",
                                    "delivery_method" => "None",
                                    "discount" => 0,
                                    "discount_type" => "entity_level",
                                    "customer_id" => $customerID,
                                    "status" => $salesOrder,
                                    "line_items" => $orderItems
                                ));


                                $url = "https://inventory.zoho.com/api/v1/salesorders?ignore_auto_number_generation=TRUE";
                                $query = "organization_id=" . $zohoInventoryOrgID . "&authtoken=" . $zohoInventoryAuthToken . "&JSONString=" . $salesOrderDetails;
                                $ch = curl_init();
                                /* set url to send post request */
                                curl_setopt($ch, CURLOPT_URL, $url);
                                /* allow redirects */
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                /* return a response into a variable */
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                /* times out after 30s */
                                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                /* set POST method */
                                curl_setopt($ch, CURLOPT_POST, 1);
                                /* add POST fields parameters */
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                $response = curl_exec($ch);
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                $zohoSalesOrderDetails = json_decode($response, TRUE);
                                curl_close($ch);
                                // Check, if Order has been suceessfully created
                                if ($http_code === 201) {
                                    $flagOrder++;

                                } else /* Order addition unsuccessful */ {
                                    $error = json_decode($response, TRUE);
                                    // Insert Error Data in Database
                                    $AccountHead = 'ORDER';
                                    $st = 'YES';
                                    $flagErr = SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding order: ' . $orderId . ':' . $error['message']);
                                }


                            } else {

                                $AccountHead = 'CUSTOMER';

                                SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Customer: ' . $accountName);
                                $flag = FALSE;
                            }

                        } catch (Exception $e) {
                            $msg = $e;
                            $flag = FALSE;
                        }


                    }

                }
            }


        }

    } catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }

    // Create reports.

    $status = 'YES';
    $totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
    $userDataArrTransaction = array(
        'user_id' => $credentials['userId'],
        'crm_type' => $crmType,
        'no_customer_data' => $flagCustomer,
        'no_product_data' => $flagProduct,
        'no_order_data' => $flagOrder,
        'last_sync_time' => time(),
        'total_transaction' => $totalTransaction,
        'status' => $status,
        'added_on' => time()
    );
    $credentials['counterOrders'] = $flagOrder;
    $credentials['counterProducts'] = $flagProduct;
    $credentials['counterAccounts'] = $flagCustomer;
    //for table transection detials
    tblTransectionDetail($credentials, $crmType, $userDataArrTransaction);

    sendSyncReport($credentials, $crmType);

    return $flagOrder;
}

function magentoToSfdcCRM($credentials)
{
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;

    $subscriptionId = $credentials['subscription_id'];
    $crmType = 'magentoTosfdc';//$credentials['crm_type'];


    $magentoURL = $credentials['app1Details']['magento_context'];
    $magentoID = $credentials['app1Details']['magento_user_name'];
    $magentoPassword = $credentials['app1Details']['magento_password'];
    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];

    //$access_token = $credentials['bigCommerceAccessToken'];
    //$accountType = $credentials['account_type'];
    $sfdcCredentials = $credentials['app2Details'];
    $priceBookInfo = Sfdc::getPriceBookDetails($sfdcCredentials);
    $credentials['stdPriceBookId'] = $priceBookInfo->Id;
    $cloudApp = 'SFDC';

    // Get Magento Token
    $magentoURL = $magentoURL . "rest/V1/";

    $total_success = 0;
    $url = $magentoURL . "integration/admin/token";
    $http_headres = array(
        "Accept: application/json",
    );
    $postfields = array('username' => $magentoID,
        'password' => $magentoPassword);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code === 200) {
        curl_close($ch);
        $magentoToken = json_decode($return, TRUE);

    }

    try {


        //............ORDER LIST COLLECTIONG.................

        $orderUrl = $magentoURL . "orders?searchCriteria[filter_groups][0][filters][0][field]=created_at&searchCriteria[filter_groups][0][filters][0][value]=" . $min_date_created . "&searchCriteria[filter_groups][0][filters][0][condition_type]=from&searchCriteria[filter_groups][1][filters][1][field]=created_at&searchCriteria[filter_groups][1][filters][1][value]=" . $max_date_created . "&searchCriteria[filter_groups][1][filters][1][condition_type]=to";
        $http_headres = array(
            "content-Type: application/json",
            "acccept: application/json",
            "authorization: Bearer " . $magentoToken
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $orderUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);


        if ($http_code === 200) {
            $orders = json_decode($return, TRUE);
            $count = $orders['total_count'];


            // Insert everything for the Order including Contact and Products.

            for ($orderCount = 1; $orderCount <= $count; $orderCount++) {
                $key = $orderCount - 1;


                //Customer Detials Collect
                $customerId = $orders['items'][$key]['customer_id'];
                $currencyExchangeRate = $orders['items'][$key]['base_to_global_rate'];
                $discountAmount = $orders['items'][$key]['discount_amount'];
                $currentOrderID = $orders['items'][$key]['items'][0]['order_id'];
                $subject = 'Order Number ' . $orders['items'][$key]['items'][0]['order_id'];

                $totalProduct = $orders['items'][$key]['total_item_count'];
                //Status of Order./................
                $currentOrderStatus = $orders['items'][$key]['status'];

                $sfdcQuery = "Select Id FROM Order WHERE OrderReferenceNumber = '$currentOrderID'";
                $queryResult = Sfdc::sfdcFindRecord($sfdcCredentials, $sfdcQuery);

                if ($queryResult->size === 0) // order doesn't exist
                {


                    // Search for existing Account

                    $sfdcQuery = "Select Id, Name FROM Account WHERE AccountNumber = '$customerId' ";
                    $queryResult = Sfdc::sfdcFindRecord($sfdcCredentials, $sfdcQuery);

                    if ($queryResult->size === 0) // no existing Account
                    {

                        //Customer  Detail collect..........

                        $customerDetailUrl = $magentoURL . "customers/" . $customerId;
                        $http_headres = array(
                            "content-Type: application/json",
                            "acccept: application/json",
                            "authorization: Bearer " . $magentoToken
                        );
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $customerDetailUrl);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                        $return = curl_exec($ch);
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);

                        if ($http_code === 200) {
                            $customerDetails = json_decode($return, TRUE);
                            $firstName = $customerDetails['firstname'];
                            $lastName = $customerDetails['lastname'];
                            if ($customerDetails['company'] !== NULL) {
                                $accountName = $customerDetails['company'];
                            } else {
                                $accountName = $firstName . ' ' . $lastName;
                            }
                            $email = $customerDetails['email'];
                            if(!empty($customerDetails['addresses'][0])) {
                                $phone = $customerDetails['addresses'][0]['telephone'];
                            }
                            else{
                                $phone ='';
                            }

                            //Customer Billing Address Collecting
                            $orderUrl = $magentoURL . "customers/" . $customerId . "/billingAddress";
                            $http_headres = array(
                                "content-Type: application/json",
                                "acccept: application/json",
                                "authorization: Bearer " . $magentoToken
                            );
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $orderUrl);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                            $return = curl_exec($ch);
                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            curl_close($ch);


                            $customerBillingAddress = json_decode($return, TRUE);


                            //End of Billing Address Collecting


                            $accounts = array();
                            $accounts[0] = new stdclass();
                            if (!empty($customerBillingAddress)) {
                                $addressDetails = json_decode($return, TRUE);
                                $accounts[0]->BillingCity = $addressDetails['city'];
                                $accounts[0]->BillingCountry = $addressDetails['country_id'];
                                $accounts[0]->BillingPostalCode = $addressDetails['postcode'];
                                $accounts[0]->BillingState = $addressDetails['region']['region'];
                                $accounts[0]->BillingStreet = $addressDetails['street'][0];
                                $accounts[0]->ShippingCity = $addressDetails['city'];
                                $accounts[0]->ShippingCountry = $addressDetails['country_id'];
                                $accounts[0]->ShippingPostalCode = $addressDetails['postcode'];
                                $accounts[0]->ShippingState = $addressDetails['region']['region'];
                                $accounts[0]->ShippingStreet = $addressDetails['street'][0];;
                            } else {
                                $accounts[0]->BillingStreet = $orders['items'][$key]['billing_address']['street'][0];
                                $accounts[0]->ShippingStreet = $orders['items'][$key]['billing_address']['street'][0];
                                $accounts[0]->BillingCity = $orders['items'][$key]['billing_address']['city'];
                                $accounts[0]->ShippingCity = $orders['items'][$key]['billing_address']['city'];
                                $accounts[0]->BillingState = $orders['items'][$key]['billing_address']['region'];
                                $accounts[0]->ShippingState = $orders['items'][$key]['billing_address']['region'];
                                $accounts[0]->BillingPostalCode = $orders['items'][$key]['billing_address']['postcode'];
                                $accounts[0]->ShippingPostalCode = $orders['items'][$key]['billing_address']['postcode'];
                                $accounts[0]->BillingCountry = $orders['items'][$key]['billing_address']['country_id'];
                                $accounts[0]->ShippingCountry = $orders['items'][$key]['billing_address']['country_id'];
                            }

                            /* Insert new Account in sfdc */
                            $accounts[0]->Name = $accountName;
                            $accounts[0]->AccountNumber = $customerId;

                            // $accounts[0]->Email = $customerDetails['email'];

                            $accounts[0]->Phone = $customerDetails['addresses'][0]['telephone'];

                            $createAccountRes = Sfdc::createSfdcAccount($sfdcCredentials, $accounts);
                            $oldAccount = new stdclass();


                            $accountId = $createAccountRes->id;
                            $resCode = $createAccountRes->success;
                            if ($resCode == 0) // error in inserting Account
                            {
                                $createAccountError = $createAccountRes->errors[0]->duplicateResult->matchResults[0]->matchRecords[0]->record->Id;
                                // check, if duplicate exists.

                                if ($createAccountRes->errors[0]->statusCode === 'DUPLICATES_DETECTED') {
                                    //update records
                                    $oldAccount->Id = $createAccountError;
                                    $accounts[0]->Id = $oldAccount->Id;
                                    $response = Sfdc::updateSfdcAccount($sfdcCredentials, $accounts);
                                }
                                $st = 'YES';
                                $AccountHead = 'ACCOUNT';
                                // SaveErrorDetails fails when $prodName has special characters used in MySQL
                                SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Account ' . $customerId);

                            }

                            // Create Contacts

                            $contacts = array();
                            $contacts[0] = new stdclass();
                            $contacts[0]->LastName = $lastName;
                            $contacts[0]->FirstName = $firstName;
                            $contacts[0]->AccountId = $accountId;
                            $contacts[0]->Email = $customerDetails['email'];
                            $contacts[0]->Phone = $customerDetails['phone'];
                            $contacts[0]->MailingCity = $accounts[0]->BillingCity;
                            $contacts[0]->MailingCountry = $accounts[0]->BillingCountry;
                            $contacts[0]->MailingPostalCode = $accounts[0]->BillingPostalCode;
                            $contacts[0]->MailingState = $accounts[0]->BillingState;
                            $contacts[0]->MailingStreet = $accounts[0]->BillingStreet;
                            $contacts[0]->OtherCity = $accounts[0]->ShippingStreet;
                            $contacts[0]->OtherCountry = $accounts[0]->ShippingCountry;
                            $contacts[0]->OtherPostalCode = $accounts[0]->ShippingPostalCode;
                            $contacts[0]->OtherState = $accounts[0]->ShippingState;
                            $contacts[0]->OtherStreet = $accounts[0]->ShippingStreet;


                            $createAccountRes = Sfdc::createSfdcContact($sfdcCredentials, $contacts);


                        }
                    } else // Account already exists
                    {

                        $accountId = $queryResult->records[0]->Id;
                        $accountName = $queryResult->records[0]->Name;
                        $resCode = 2; // account exists
                    }
                    if ($resCode > 0) {

                        // $flag = TRUE;

                        if ($resCode == 1) {
                            $flagCustomer++;
                        }

                        // Contact addition successful. Proceed with addtion of Product and Sales Order

                        /* Insert Sales Order  */
                        $sfdcOrders = array();
                        $sfdcOrders[0] = new stdclass();
                        $subject = 'Order Number ' . $orders['items'][$key]['items'][0]['order_id'];
                        $grandTotal = $orders['items'][$key]['grand_total'];
                        $subTotal = $orders['items'][$key]['base_grand_total'];
                        $tax = $orders['items'][$key]['tax_amount'];
                        $adjustment = $grandTotal - $subTotal;

                        //Billing Address Detial
                        $sfdcOrders[0]->BillingStreet = $orders['items'][$key]['billing_address']['street'][0];
                        $sfdcOrders[0]->BillingCity = $orders['items'][$key]['billing_address']['city'];
                        $sfdcOrders[0]->BillingState = $orders['items'][$key]['billing_address']['region'];
                        $sfdcOrders[0]->BillingPostalCode = $orders['items'][$key]['billing_address']['postcode'];
                        $sfdcOrders[0]->BillingCountry = $orders['items'][$key]['billing_address']['country_id'];


                        $sfdcOrders[0]->ShippingStreet = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['street'][0];
                        $sfdcOrders[0]->ShippingCity = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['city'];
                        $sfdcOrders[0]->ShippingState = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['region'];
                        $sfdcOrders[0]->ShippingPostalCode = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['postcode'];
                        $sfdcOrders[0]->ShippingCountry = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['country_id'];

                        // $Orders[0]->Account = $accountName;

                        //$accounts[0]->AccountId = $accountId;
                        $sfdcOrders[0]->AccountId = $accountId;

                        // $sfdcOrders[0]->TotalAmount =  $orders['items'][$key]['grand_total'];;

                        $sfdcOrders[0]->Description = $subject;
                        $sfdcOrders[0]->Name = $subject;
                        $salesOrderDate = DateTime::createFromFormat(DATE_RFC2822, $orders['items'][$key]['created_at']);
                        $sfdcOrders[0]->EffectiveDate = date('Y-m-d', strtotime($salesOrderDate));
                        $sfdcOrders[0]->OrderReferenceNumber = $currentOrderID;
                        $sfdcOrders[0]->Pricebook2Id = $credentials['stdPriceBookId'];
                        $currentOrderStatus = $orders['items'][$key]['status'];
                        $sfdcOpportunity = array();
                        $sfdcOpportunity[0] = new stdclass();
                        if (($currentOrderStatus === 'complete') || ($currentOrderStatus === 'pending')) {

                            // $sfdcOrders[0]->StatusCode = 'Draft';

                            $sfdcOrders[0]->Status = 'Draft';
                            $sfdcOpportunity[0]->StageName = 'Closed Won';
                        } else {

                            // $sfdcOrders[0]->Status = 'Activated';
                            // $sfdcOrders[0]->StatusCode = 'Draft';

                            $sfdcOrders[0]->Status = 'Draft';
                            $sfdcOpportunity[0]->StageName = 'Proposal';
                        }

                        // $Orders[0]->Pricebook2Id = $s_priceBookId;
                        // Create Opportunity fields.
                        $sfdcOpportunity[0]->AccountId = $accountId;
                        $sfdcOpportunity[0]->CloseDate = $sfdcOrders[0]->EffectiveDate;
                        $sfdcOpportunity[0]->Name = $sfdcOrders[0]->Name;
                        //$sfdcOpportunity[0]->StageName = 'Closed Won';
                        $sfdcOpportunity[0]->Amount = $grandTotal;
                        $sfdcOpportunity[0]->Description = $sfdcOrders[0]->Description;
                        $sfdcOpportunity[0]->Pricebook2Id = $sfdcOrders[0]->Pricebook2Id;
                        $opportunityDetails = Sfdc::createOpportunity($sfdcCredentials, $sfdcOpportunity);

                        $orderDetails = Sfdc::createOrder($sfdcCredentials, $sfdcOrders);


                        if ($orderDetails->success == TRUE) {
                            $flagOrder++;
                        } else // error in inserting Order
                        {
                            $st = 'YES';
                            $AccountHead = 'ORDER';
                            // SaveErrorDetails fails when $prodName has special characters used in MySQL
                            SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Order ' . $currentOrderID);
                        }

                        $totalProduct = count($orders['items'][$key]['items']);
                        if ($totalProduct > 0) {
                            $prodNumber = 0;
                            $orderItem = array();
                            $opportunityLineItem = array();
                            for ($i = 0; $i < $totalProduct; $i++) {
                                $prodCode = $orders['items'][$key]['items'][$i]['sku'];
                                $sfdcQuery = "Select Id FROM PricebookEntry WHERE ProductCode = '$prodCode'";
                                $queryResult = Sfdc::sfdcFindRecord($sfdcCredentials, $sfdcQuery);

                                if ($queryResult->size != 0) // Product exists
                                {
                                    $productPricebookEntryId = $queryResult->records[0]->Id;
                                    // Update Product
                                    $priceEntry = array();
                                    $priceEntry[0] = new stdclass();
                                    $priceEntry[0]->Id = $productPricebookEntryId;
                                    $priceEntry[0]->IsActive = TRUE;
                                    $priceEntry[0]->UnitPrice = $orders['items'][$key]['items'][$i]['price'];
                                    $priceEntry[0]->UseStandardPrice = FALSE;
                                    $response = Sfdc::updatePriceBook($sfdcCredentials, $priceEntry);
                                } else {
                                    //PRODUCT DETAILS COLLECT
                                    $productUrl = $magentoURL . "products/" . $prodCode;
                                    $http_headres = array(
                                        "content-Type: application/json",
                                        "cccept: application/json",
                                        "authorization: Bearer " . $magentoToken
                                    );
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $productUrl);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                    $return = curl_exec($ch);
                                    curl_close($ch);

                                    $product = json_decode($return, TRUE);

                                    /* insert into products */
                                    $products = array();
                                    $products[0] = new stdclass();
                                    $products[0]->Name = $product['name'];
                                    $products[0]->IsActive = TRUE;
                                    $products[0]->ProductCode = $product['sku'];
                                    $products[0]->Description = $product['custom_attributes'][0]['value']; // Check description
                                    $productRes = Sfdc::createSfdcProducts($sfdcCredentials, $products);
                                    if ($productRes->success == TRUE) {
                                        $flagProduct++;
                                    } else // error in inserting Product
                                    {
                                        $st = 'YES';
                                        $AccountHead = 'PRODUCT';
                                        // SaveErrorDetails fails when $prodName has special characters used in MySQL
                                        SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Product ' . $product['sku']);
                                    }

                                    $insertedProductId = $productRes->id;
                                    $priceEntry = array();
                                    $priceEntry[0] = new stdclass();
                                    $priceEntry[0]->Pricebook2Id = $credentials['stdPriceBookId'];
                                    $priceEntry[0]->IsActive = TRUE;
                                    $priceEntry[0]->Product2Id = $insertedProductId;
                                    $priceEntry[0]->UnitPrice = $orders['items'][$key]['items'][$i]['price'];
                                    $priceEntry[0]->UseStandardPrice = FALSE;

                                    $addedId = Sfdc::addToPriceBook($sfdcCredentials, $priceEntry);
                                    $productPricebookEntryId = $addedId->id;

                                }

                                // Add Order Item
                                if(!isset($orders['items'][$key]['items'][$i]['parent_item_id'])) {

                                    $orderItem[$prodNumber] = new stdclass();
                                    $orderItem[$prodNumber]->PricebookEntryId = $productPricebookEntryId;
                                    $orderItem[$prodNumber]->OrderId = $orderDetails->id;
                                    $orderItem[$prodNumber]->Quantity = $orders['items'][$key]['items'][$i]['qty_ordered'];
                                    $orderItem[$prodNumber]->UnitPrice = $orders['items'][$key]['items'][$i]['price'];

                                    if ($opportunityDetails->success) {
                                        // create Opportunity line item
                                        $opportunityLineItem[$prodNumber] = new stdclass();
                                        $opportunityLineItem[$prodNumber]->PricebookEntryId = $orderItem[$prodNumber]->PricebookEntryId;
                                        $opportunityLineItem[$prodNumber]->OpportunityId = $opportunityDetails->id;
                                        $opportunityLineItem[$prodNumber]->Quantity = $orderItem[$prodNumber]->Quantity;
                                        $opportunityLineItem[$prodNumber]->UnitPrice = $orderItem[$prodNumber]->UnitPrice;
                                    }
                                    $prodNumber++;
                                }

                            } // for each product
                            $orderItemRes = Sfdc::createOrderItem($sfdcCredentials, $orderItem);
                            $opportunityItemRes = Sfdc::addProductToOpportunity($sfdcCredentials, $opportunityLineItem);

                        } // if Product SKUs available.
                    } else {

                        $AccountHead = 'CUSTOMER';
                        $st = 'YES';
                        SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Customer: ' . $accountName);
                        $flag = FALSE;
                    }
                } /// If customer successfully retrieved from BG.

            }
        }

        // check if any orders exist for the page


    } // Try retrieving orders from BigCommerce
    catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }

    // Create reports.

    $status = 'YES';
    $totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
    $userDataArrTransaction = array(
        'user_id' => $credentials['userId'],
        'crm_type' => $crmType,
        'no_customer_data' => $flagCustomer,
        'no_product_data' => $flagProduct,
        'no_order_data' => $flagOrder,
        'last_sync_time' => time(),
        'total_transaction' => $totalTransaction,
        'status' => $status,
        'added_on' => time()
    );
    $credentials['counterOrders'] = $flagOrder;
    $credentials['counterProducts'] = $flagProduct;
    $credentials['counterAccounts'] = $flagCustomer;
    include "/var/www/html/app/mysql/mysqlconstants.php";

    tblTransectionDetail($credentials, $crmType, $userDataArrTransaction);

    sendSyncReport($credentials, $crmType);
    return $flagOrder;
} //Done

function magentoToVTigerCRM($credentials)
{
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;

    $subscriptionId = $credentials['subscription_id'];
    $crmType = 'magentoTovtiger';//$credentials['crm_type'];
    $contextVal = //$credentials['contextValue'];

    $magentoURL = $credentials['app1Details']['magento_context'];
    $magentoID = $credentials['app1Details']['magento_user_name'];
    $magentoPassword = $credentials['app1Details']['magento_password'];
    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];

    $vTigerUserName = $credentials['app2Details']['vtiger_username'];
    $vTigerAccessKey = $credentials['app2Details']['vtiger_key'];
    $vTigerEndpoint = $credentials['app2Details']['vtiger_endpoint'];

    // Get vTiger access token
    $url = $vTigerEndpoint . "/webservice.php?operation=getchallenge&username=" . $vTigerUserName;
    $http_headres = array(
        "Content-Type: application/json",
        "Accept: application/json",
        "cache-control : no-cache"
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code === 200) {
        curl_close($ch);
        $vTigerAuth = json_decode($return, TRUE);
        $vTigerAuthToken = $vTigerAuth['result']['token'];
    }

    $cloudApp = 'vTigerCRM';
    $url = $vTigerEndpoint . "/webservice.php";
    $generatedKey = md5($vTigerAuthToken . $vTigerAccessKey);
    $postfields = array('operation' => 'login', 'username' => $vTigerUserName,
        'accessKey' => $generatedKey);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    $return = curl_exec($ch);
    //var_dump($return);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($http_code === 200) {
        curl_close($ch);
        $vTigerLogin = json_decode($return, TRUE);
        $vTigerSessionID = $vTigerLogin['result']['sessionName'];
        $vTigerUserID = $vTigerLogin['result']['userId'];

    }

    // Get Magento Token
    $magentoURL = $magentoURL . "rest/V1/";

    $total_success = 0;
    $url = $magentoURL . "integration/admin/token";
    $http_headres = array(
        "Accept: application/json",
    );
    $postfields = array('username' => $magentoID,
        'password' => $magentoPassword);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code === 200) {
        curl_close($ch);
        $magentoToken = json_decode($return, TRUE);

    }

    try {

        //............ORDER LIST COLLECTIONG.................


        $orderUrl = $magentoURL . "orders?searchCriteria[filter_groups][0][filters][0][field]=created_at&searchCriteria[filter_groups][0][filters][0][value]=" . $min_date_created . "&searchCriteria[filter_groups][0][filters][0][condition_type]=from&searchCriteria[filter_groups][1][filters][1][field]=created_at&searchCriteria[filter_groups][1][filters][1][value]=" . $max_date_created . "&searchCriteria[filter_groups][1][filters][1][condition_type]=to";
        $http_headres = array(
            "content-Type: application/json",
            "acccept: application/json",
            "authorization: Bearer " . $magentoToken
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $orderUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code === 200) {
            $orders = json_decode($return, TRUE);
            $count = $orders['total_count'];


            if ($count > 0) {

                for ($orderCount = 1; $orderCount <= $count; $orderCount++) {
                    $key = $orderCount - 1;

                    //Customer Detials Collect
                    $customerId = $orders['items'][$key]['customer_id'];
                    $currencyExchangeRate = $orders['items'][$key]['base_to_global_rate'];
                    $discountAmount = $orders['items'][$key]['discount_amount'];
                    //Order id collect
                    $orderId = $orders['items'][$key]['items'][0]['order_id'];
                    $subject = 'Order Number ' . $orders['items'][$key]['items'][0]['order_id'];

                    $totalProduct = $orders['items'][$key]['total_item_count'];

                    //Status of Order./................
                    $currentOrderStatus = $orders['items'][$key]['status'];

                    if (($currentOrderStatus === 'complete') || ($currentOrderStatus === 'pending')) {
                        $salesOrder = 'TRUE';
                    } else {
                        $salesOrder = 'FALSE';

                    }


                    //ORDER PREVIOUSLY INSERT IN SALESORDER  CHECKING.
                    $orderExistQuery = "select count(*) from SalesOrder where subject='$subject';";

                    $queryOrderParam = urlencode($orderExistQuery);
                    $orderParams = "sessionName=$vTigerSessionID&operation=query&query=$queryOrderParam";
                    $getOrderUrl = $vTigerEndpoint . "/webservice.php?" . $orderParams;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $getOrderUrl);
                    curl_setopt($ch, CURLOPT_POST, 0);
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                    $response = curl_exec($ch);
                    curl_close($ch);
                    $collectedCount = json_decode($response, true);
                    $collectedSalesOrderCountNo = $collectedCount['result'][0]['count'];


                    //ORDER PREVIOUSLY INSERT IN QUOTE  CHECKING.

                    $quoteExistQuery = "select count(*) from Quotes where subject='$subject';";

                    $quoteOrderParam = urlencode($quoteExistQuery);
                    $quoteParams = "sessionName=$vTigerSessionID&operation=query&query=$quoteOrderParam";
                    $getQuoteUrl = $vTigerEndpoint . "/webservice.php?" . $quoteParams;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $getQuoteUrl);
                    curl_setopt($ch, CURLOPT_POST, 0);
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                    $response = curl_exec($ch);
                    curl_close($ch);
                    $collectedCount = json_decode($response, true);
                    $collectedQuoteCountNo = $collectedCount['result'][0]['count'];


                    if ($collectedSalesOrderCountNo == 0 && $collectedQuoteCountNo == 0) {


                        //Customer  Detail collect..........

                        $customerDetailUrl = $magentoURL . "customers/" . $customerId;
                        $http_headres = array(
                            "content-Type: application/json",
                            "acccept: application/json",
                            "authorization: Bearer " . $magentoToken
                        );
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $customerDetailUrl);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                        $return = curl_exec($ch);
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);


                        //End of Collecting of Customer Detailas
                        if ($http_code === 200) {

                            $customerDetails = json_decode($return, TRUE);
                            $firstName = $customerDetails['firstname'];
                            $lastName = $customerDetails['lastname'];
                            if(empty($customerDetails['company'])) {
                                $accountName = $firstName . ' ' . $lastName;
                            }else{
                                $accountName = $customerDetails['company'];
                            }
                            $email = $customerDetails['email'];
                            if(!empty($customerDetails['addresses'][0])) {
                                $phone = $customerDetails['addresses'][0]['telephone'];
                            }
                            else{
                                $phone ='';
                            }

                            //Customer Billing Address Collecting
                            $orderUrl = $magentoURL . "customers/" . $customerId . "/billingAddress";
                            $http_headres = array(
                                "content-Type: application/json",
                                "acccept: application/json",
                                "authorization: Bearer " . $magentoToken
                            );
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $orderUrl);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                            $return = curl_exec($ch);
                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            curl_close($ch);


                            $customerBillingAddress = json_decode($return, TRUE);


                            //End of Billing Address Collecting


                            if (!empty($customerBillingAddress)) {
                                $addressDetails = json_decode($return, TRUE);


                                $mailingStreet = $addressDetails['street'][0];
                                $otherStreet = $addressDetails['street'][0];
                                $mailingCity = $addressDetails['city'];
                                $otherCity = $addressDetails['city'];
                                $mailingState = $addressDetails['region']['region'];
                                $otherState = $addressDetails['region']['region'];
                                $mailingZip = $addressDetails['postcode'];
                                $otherZip = $addressDetails['postcode'];
                                $mailingCountry = $addressDetails['country_id'];
                                $otherCountry = $addressDetails['country_id'];
                            } else {
                                $mailingStreet = $orders['items'][$key]['billing_address']['street'][0];
                                $otherStreet = $orders['items'][$key]['billing_address']['street'][0];
                                $mailingCity = $orders['items'][$key]['billing_address']['city'];
                                $otherCity = $orders['items'][$key]['billing_address']['city'];
                                $mailingState = $orders['items'][$key]['billing_address']['region'];
                                $otherState = $orders['items'][$key]['billing_address']['region'];
                                $mailingZip = $orders['items'][$key]['billing_address']['postcode'];
                                $otherZip = $orders['items'][$key]['billing_address']['postcode'];
                                $mailingCountry = $orders['items'][$key]['billing_address']['country_id'];
                                $otherCountry = $orders['items'][$key]['billing_address']['country_id'];
                            }

                            $flag = FALSE;
                            try {
                                //ACCOUNT EXIST OR NOT CHECKING

                                $accountExistQuery = "select count(*) from Accounts where email1='$email';";


                                $queryAccountParam = urlencode($accountExistQuery);
                                $accountParams = "sessionName=$vTigerSessionID&operation=query&query=$queryAccountParam";
                                $getAccountUrl = $vTigerEndpoint . "/webservice.php?" . $accountParams;
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $getAccountUrl);
                                curl_setopt($ch, CURLOPT_POST, 0);
                                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($ch, CURLOPT_HEADER, 0);
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                                $response = curl_exec($ch);
                                curl_close($ch);
                                $collectedCount = json_decode($response, true);
                                $collectedCountNo = $collectedCount['result'][0]['count'];

                                if ($collectedCountNo == 0) {
                                    // Insert Account details..................
                                    $accountInsertUrl = $vTigerEndpoint . "/webservice.php";
                                    $accountFields = array(
                                        'accountname' => $accountName,
                                        'assigned_user_id' => $vTigerUserID,
                                        'phone' => $phone,
                                        'email1' => $email,

                                        'bill_street' => $mailingStreet,
                                        'bill_city' => $mailingCity,
                                        'bill_state' => $mailingState,
                                        'bill_country' => $mailingCountry,
                                        'bill_code' => $mailingZip,
                                        'ship_street' => $otherStreet,
                                        'ship_city' => $otherCity,
                                        'ship_state' => $otherState,
                                        'ship_country' => $otherCountry,
                                        'ship_code' => $otherZip

                                    );
                                    $accountFieldsJSON = json_encode($accountFields);
                                    //var_dump($accountFieldsJSON);
                                    $moduleName = 'Accounts';
                                    $postFields = array("sessionName" => $vTigerSessionID, "operation" => 'create',
                                        "element" => $accountFieldsJSON, "elementType" => $moduleName);
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $accountInsertUrl);
                                    curl_setopt($ch, CURLOPT_POST, 1);
                                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                                    curl_setopt($ch, CURLOPT_HEADER, 0);
                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                                    //$return = curl_exec($ch);

                                    // Execute cUrl session

                                    $response = curl_exec($ch);
                                    curl_close($ch);

                                    $collectedResponseId = json_decode($response, true);
                                    $collectedAccountId = $collectedResponseId['result']['id'];
                                } else {

                                    //Collection Account Id From Accounts..............
                                    $accountIdQuery = "select id from Accounts where email1='$email';";
                                    //urlencode to as its sent over http.
                                    $queryParam = urlencode($accountIdQuery);
                                    $accountParams = "sessionName=$vTigerSessionID&operation=query&query=$queryParam";


                                    $getAccountUrl = $vTigerEndpoint . "/webservice.php?" . $accountParams;

                                    //var_dump($getAccountUrl);

                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $getAccountUrl);
                                    curl_setopt($ch, CURLOPT_POST, 0);
                                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    curl_setopt($ch, CURLOPT_HEADER, 0);
                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);


                                    $response = curl_exec($ch);
                                    curl_close($ch);


                                    $collectedResponseId = json_decode($response, true);
                                    //Account Id
                                    $collectedAccountId = $collectedResponseId['result'][0]['id'];


                                    //End ofCollection Account Id From Accounts..............
                                }

                                //CONTACT EXIST OR NOT CHECKING
                                $contactExistQuery = "select count(*) from Contacts where account_id='$collectedAccountId';";


                                $queryContactParam = urlencode($contactExistQuery);
                                $contactParams = "sessionName=$vTigerSessionID&operation=query&query=$queryContactParam";
                                $getContactUrl = $vTigerEndpoint . "/webservice.php?" . $contactParams;
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $getContactUrl);
                                curl_setopt($ch, CURLOPT_POST, 0);
                                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($ch, CURLOPT_HEADER, 0);
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

                                $response = curl_exec($ch);
                                curl_close($ch);
                                $collectedCount = json_decode($response, true);


                                $collectedCountNo = $collectedCount['result'][0]['count'];

                                if ($collectedCountNo == 0) {
                                    // Create Contact for this account..................
                                    $accountInsertUrl = $vTigerEndpoint . "/webservice.php";
                                    $contactData = array(
                                        'firstname' => $firstName,
                                        'lastname' => $lastName,
                                        'assigned_user_id' => $vTigerUserID,
                                        'contacttype' => 'Primary',
                                        'account_id' => $collectedAccountId,
                                        'email' => $email,
                                        'mobile' => $phone,
                                        'mailingstreet' => $mailingStreet,
                                        'otherstreet' => $otherStreet,
                                        'mailingcity' => $mailingCity,
                                        'othercity' => $otherCity,
                                        'mailingstate' => $mailingState,
                                        'otherstate' => $otherState,
                                        'mailingzip' => $mailingZip,
                                        'otherzip' => $otherZip,
                                        'mailingcountry' => $mailingCountry,
                                        'othercountry' => $otherCountry
                                    );
                                    $contactFieldsJSON = json_encode($contactData); //Encoding to json formate for communicating with server
                                    //var_dump($contactFieldsJSON);
                                    $moduleName = 'Contacts';

                                    $contactParams = array("sessionName" => $vTigerSessionID, "operation" => 'create',
                                        "element" => $contactFieldsJSON, "elementType" => $moduleName);

                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $accountInsertUrl);
                                    curl_setopt($ch, CURLOPT_POST, 1);
                                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $contactParams);

                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);


                                    $response = curl_exec($ch);
                                    $collectedResponseContactId = json_decode($response, true);


                                    $collectedContactId = $collectedResponseContactId['result']['id'];
                                    curl_close($ch);

                                    $responseValue = json_decode($response, TRUE);
                                    if ($responseValue['success'] == 'true') {
                                        $flagCustomer++;
                                    } else {
                                        $resCode = $responseValue['error']['code'];
                                        $resMsg = $responseValue['error']['message'];
                                        $AccountHead = 'CUSTOMER';
                                        $st = 'YES';
                                        SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Customer: ' . $accountName . ' Error Code: ' . $resCode . 'Reason:' . $resMsg);
                                        //$flag = FALSE;
                                    }
                                    //End of Create Contact for this account..................
                                } else {
                                    // Collection Contact Id From Contacts..............

                                    $contactIdQuery = "select id from Contacts where email='$email';";
                                    $queryParam = urlencode($contactIdQuery);
                                    $contactParams = "sessionName=$vTigerSessionID&operation=query&query=$queryParam";
                                    $getContactUrl = $vTigerEndpoint . "/webservice.php?" . $contactParams;
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $getContactUrl);
                                    curl_setopt($ch, CURLOPT_POST, 0);
                                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    curl_setopt($ch, CURLOPT_HEADER, 0);
                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                                    $response = curl_exec($ch);
                                    curl_close($ch);
                                    $collectedResponseContactId = json_decode($response, true);
                                    $collectedContactId = $collectedResponseContactId['result'][0]['id'];


                                    // End ofCollection Account Id From Accounts..............
                                }


                                // Order Details Collect
                                $subject = 'Order Number ' . $orders['items'][$key]['items'][0]['order_id'];
                                $grandTotal = $orders['items'][$key]['grand_total'];
                                $subTotal = $orders['items'][$key]['base_grand_total'];
                                $tax = $orders['items'][$key]['tax_amount'];
                                $adjustment = ($grandTotal - $subTotal)-$tax;
                                //Billing Address Detial
                                $orderBillingStreet = $orders['items'][$key]['billing_address']['street'][0];
                                $orderBillingCity = $orders['items'][$key]['billing_address']['city'];
                                $orderBillingZip = $orders['items'][$key]['billing_address']['postcode'];
                                $orderBillingCountry = $orders['items'][$key]['billing_address']['country_id'];
                                $orderBillingState = $orders['items'][$key]['billing_address']['region'];
                                //Shipping Address Detial
                                $orderShippingCity = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['city'];
                                $orderShippingState = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['region'];
                                $orderShippingStreet = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['street'][0];
                                $orderShippingZip = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['postcode'];
                                $orderShippingCountry = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['country_id'];


                                $lineitem = [];


                                //PRODUCT DETAILS COLLECT FROM ORDER
                                $totalProduct = count($orders['items'][$key]['items']);
                                if ($totalProduct > 0) {
                                    $prodNumber = 0;
                                    for ($i = 0; $i < $totalProduct; $i++) {
                                        //PRODUCT ID COLLECTED

                                        $productId = $orders['items'][$key]['items'][$i]['sku'];

                                        $m_quantity = $orders['items'][$key]['items'][$i]['qty_ordered'];
                                        $prodListPrice = $orders['items'][$key]['items'][$i]['base_price'];
                                        $prodUnitPrice = $orders['items'][$key]['items'][$i]['price'];
                                        $total = $orders['items'][$key]['items'][$i]['base_row_total'];
                                        $totalAfterDiscount = $orders['items'][$key]['items'][$i]['base_row_total_incl_tax'];
                                        //$discount = $total - $totalAfterDiscount;
                                        $discount = $orders['items'][$key]['items'][$i]['discount_amount'];
                                        $tax = $orders['items'][$key]['items'][$i]['tax_amount'];
                                        $netTotal = $orders['items'][$key]['items'][$i]['price_incl_tax'];

                                        //PRODUCT DETAILS COLLECT
                                        $productUrl = $magentoURL . "products/" . $productId;
                                        $http_headres = array(
                                            "content-Type: application/json",
                                            "cccept: application/json",
                                            "authorization: Bearer " . $magentoToken
                                        );
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $productUrl);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                        $return = curl_exec($ch);
                                        curl_close($ch);

                                        $product = json_decode($return, TRUE);


                                        //PRODUCT DETIALS IF EXISTS .................
                                        $prodCode = $product['sku'];
                                        $prodName = $product['name'];
                                        //$m_quantiy = $product['extension_attributes']['stock_item']['min_qty'];
                                        $stock_quantity = $product['extension_attributes']['stock_item']['qty'];
                                        $price = $product['price'];
                                        $description = $product['custom_attributes'][0]['value'];


                                        //Product exist or Not checking
                                        $productExistQuery = "select count(*) from Products where productcode='$productId';";
                                        //urlencode to as its sent over http.
                                        $queryProductParam = urlencode($productExistQuery);
                                        $accountParams = "sessionName=$vTigerSessionID&operation=query&query=$queryProductParam";
                                        $getAccountUrl = $vTigerEndpoint . "/webservice.php?" . $accountParams;
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $getAccountUrl);
                                        curl_setopt($ch, CURLOPT_POST, 0);
                                        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        curl_setopt($ch, CURLOPT_HEADER, 0);
                                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                                        $response = curl_exec($ch);
                                        curl_close($ch);
                                        $collectedCount = json_decode($response, true);
                                        //Account Id
                                        $collectedCountNo = $collectedCount['result'][0]['count'];


                                        // For Insert Records with duplicate checking.
                                        if ($collectedCountNo == 0) {
                                            // INSERT VTIGER PRODUCT
                                            $productInsertUrl = $vTigerEndpoint . "/webservice.php";
                                            $productData = array(
                                                'productcode' => $prodCode,
                                                'productname' => $prodName,
                                                'discontinued' => 1,
                                                "comment" => '',
                                                "qty_per_unit" => $m_quantity,
                                                "qtyinstock" => $stock_quantity,
                                                "list_price" => $prodListPrice,
                                                "unit_price" => $prodUnitPrice,
                                                "isclosed" => 1,
                                                "currency1" => 0,
                                                "currency_id" => "21x1",
                                                "hdnProductId" => $productId,
                                                "description" => $description,
                                                "assigned_user_id" => $vTigerUserID,
                                                "totalProductCount" => $m_quantity
                                            );

                                            $productFieldsJSON = json_encode($productData);
                                            $moduleName = 'Products';


                                            $productfields = array("operation" => 'create', "sessionName" => $vTigerSessionID,
                                                "element" => $productFieldsJSON, "elementType" => $moduleName);

                                            // Execute cUrl session
                                            $ch = curl_init();
                                            curl_setopt($ch, CURLOPT_URL, $productInsertUrl);
                                            curl_setopt($ch, CURLOPT_POST, 1);
                                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                            curl_setopt($ch, CURLOPT_POSTFIELDS, $productfields);
                                            curl_setopt($ch, CURLOPT_HEADER, 0);
                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);


                                            $response = curl_exec($ch);
                                            curl_close($ch);

                                            //Collected Quote Id
                                            $collectedResponseId = json_decode($response, true);

                                            $collectedProductId = $collectedResponseId['result']['id'];
                                            $collectedProductBasePrice = $collectedResponseId['result']['unit_price'];
                                            $collectedProductQuantity = $collectedResponseId['result']['qty_per_unit'];

                                            $responseValue = json_decode($response, TRUE);
                                            // End of Sales Orders.................


                                            if ($responseValue['success'] == 'true') {
                                                $flagProduct++;
                                            } else {
                                                $resCode = $responseValue['error']['code'];
                                                $resMsg = $responseValue['error']['message'];
                                                $flag = FALSE;
                                                $st = 'YES';
                                                $AccountHead = 'PRODUCT';
                                                SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Product : ' . $prodName . ' Error Code: ' . $resCode . ' Reason:' . $resMsg);

                                            }

                                        } else {


                                            //Collection Product Id From Products..............
                                            $productIdQuery = "select * from Products where productcode='$productId';";
                                            //urlencode to as its sent over http.
                                            $queryProductParam = urlencode($productIdQuery);
                                            $productParams = "sessionName=$vTigerSessionID&operation=query&query=$queryProductParam";


                                            $getProductUrl = $vTigerEndpoint . "/webservice.php?" . $productParams;


                                            $ch = curl_init();
                                            curl_setopt($ch, CURLOPT_URL, $getProductUrl);
                                            curl_setopt($ch, CURLOPT_POST, 0);
                                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                            curl_setopt($ch, CURLOPT_HEADER, 0);
                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);


                                            $response = curl_exec($ch);
                                            curl_close($ch);

                                            $collectedResponseId = json_decode($response, true);
                                            //Account Id
                                            $collectedProductId = $collectedResponseId['result'][0]['id'];
                                            $collectedProductBasePrice = $collectedResponseId['result'][0]['unit_price'];
                                            $collectedProductQuantity = $collectedResponseId['result'][0]['qty_per_unit'];

                                            //End of Collection Product Id From Products..............


                                        }
                                        if(!isset($orders['items'][$key]['items'][$i]['parent_item_id'])) {
                                            $lineitem[$prodNumber] = ['productid' => $collectedProductId, 'listprice' => $collectedProductBasePrice, 'quantity' => $collectedProductQuantity, 'discount_amount' => $discount, 'tax1' => $tax];
                                            $prodNumber++;
                                        }



                                    }// for each product

                                }


                                //Retrieve All List Available From vTiger..............
                                $listTypeParams = "sessionName=$vTigerSessionID&operation=listtypes";


                                $getListTypeUrl = $vTigerEndpoint . "/webservice.php?" . $listTypeParams;

                                //var_dump($getAccountUrl);

                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $getListTypeUrl);
                                curl_setopt($ch, CURLOPT_POST, 0);
                                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($ch, CURLOPT_HEADER, 0);
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);


                                $response = curl_exec($ch);
                                curl_close($ch);

                                $encode = json_decode($response, TRUE);

                                $listType = $encode['result']['types'];


                                $salesOrderExist = array_search("SalesOrder", $listType);


                                if ($salesOrder === 'TRUE') {

                                    if ($salesOrderExist >= 0) {


                                        $orderInsertUrl = $vTigerEndpoint . "/webservice.php";

                                        $salesOrderData = array(
                                            'subject' => $subject,
                                            'sostatus' => 'New',
                                            'account_id' => $collectedAccountId,
                                            'customerno' => $customerId,
                                            //'quote_id'=>$collectedQuoteId,
                                            'bill_street' => $orderBillingStreet,
                                            'bill_city' => $orderBillingCity,
                                            'bill_code' => $orderBillingZip,
                                            'bill_country' => $orderBillingCountry,
                                            'bill_state' => $orderBillingState,
                                            'assigned_user_id' => $vTigerUserID,
                                            'contact_id' => $collectedContactId,
                                            'invoicestatus' => 'New',
                                            'adjustment' => $adjustment,
                                            'exciseduty' => $tax,
                                            'subtotal' => $subTotal,
                                            'total' => $grandTotal,
                                            'ship_city' => $orderShippingCity,
                                            'ship_street' => $orderShippingStreet,
                                            'ship_code' => $orderShippingZip,
                                            'ship_country' => $orderShippingCountry,
                                            'ship_state' => $orderShippingState,
                                            'status' => $currentOrderStatus,
                                            'LineItems' => $lineitem,
                                            'currency_id' => '21x1',
                                            'hdntaxtype' => 'group',
                                            'conversion_rate' => $currencyExchangeRate,
                                            'hdndiscountamount' => $discountAmount,
                                            'hdnGrandTotal' => $grandTotal,
                                            'hdnSubTotal' => $subTotal,
                                        );


                                        $salesOrderFieldsJSON = json_encode($salesOrderData);
                                        $moduleName = 'SalesOrder';

                                        $salesOrderFields = array("operation" => 'create', "sessionName" => $vTigerSessionID,
                                            "element" => $salesOrderFieldsJSON, "elementType" => $moduleName);

                                        // Execute cUrl session
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $orderInsertUrl);
                                        curl_setopt($ch, CURLOPT_POST, 1);
                                        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        curl_setopt($ch, CURLOPT_POSTFIELDS, $salesOrderFields);
                                        curl_setopt($ch, CURLOPT_HEADER, 0);
                                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);


                                        $response = curl_exec($ch);
                                        curl_close($ch);


                                        $responseValue = json_decode($response, TRUE);
                                        if ($responseValue['success'] == 'true') {
                                            $flagOrder++;
                                        } else {

                                            $resCode = $responseValue['error']['code'];
                                            $resMsg = $responseValue['error']['message'];
                                            //$flag = FALSE;
                                            $st = 'YES';
                                            $AccountHead = 'ORDER';
                                            SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Order Number: ' . $orders[$key]['id'] . ' Error Code: ' . $resCode . ' Reason:' . $resMsg);

                                        }

                                    }

                                } else {


                                    $quoteInsertUrl = $vTigerEndpoint . "/webservice.php";
                                    $productQuote = array(
                                        'subject' => $subject,
                                        'account_id' => $collectedAccountId,
                                        'quotestage' => 'created',
                                        'contact_id' => $collectedContactId,
                                        'bill_street' => $orderBillingStreet,
                                        'bill_city' => $orderBillingCity,
                                        'bill_code' => $orderBillingZip,
                                        'bill_country' => $orderBillingCountry,
                                        'bill_state' => $orderBillingState,
                                        'ship_city' => $orderShippingCity,
                                        'ship_street' => $orderShippingStreet,
                                        'ship_code' => $orderShippingZip,
                                        'ship_country' => $orderShippingCountry,
                                        'ship_state' => $orderShippingState,
                                        'assigned_user_id' => $vTigerUserID,
                                        'productid' => $collectedProductId,
                                        'LineItems' => $lineitem
                                    );


                                    $productQuoteFieldsJSON = json_encode($productQuote);
                                    $moduleName = 'Quotes';

                                    $productQuoteFields = array("operation" => 'create', "sessionName" => $vTigerSessionID,
                                        "element" => $productQuoteFieldsJSON, "elementType" => $moduleName);

                                    // Execute cUrl session
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $quoteInsertUrl);
                                    curl_setopt($ch, CURLOPT_POST, 1);
                                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $productQuoteFields);
                                    curl_setopt($ch, CURLOPT_HEADER, 0);
                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);


                                    $response = curl_exec($ch);
                                    curl_close($ch);

                                    $responseValue = json_decode($response, TRUE);
                                    if ($responseValue['success'] == 'true') {
                                        $flagOrder++;
                                    } else {

                                        $resCode = $responseValue['error']['code'];
                                        $resMsg = $responseValue['error']['message'];
                                        //$flag = FALSE;
                                        $st = 'YES';
                                        $AccountHead = 'ORDER';
                                        SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Quote Number: ' . $orders[$key]['id'] . ' Error Code: ' . $resCode . ' Reason:' . $resMsg);

                                    }
                                    //Collected Quote Id
                                    //$collectedResponseId = json_decode($response, true);

                                    //$collectedQuoteId = $collectedResponseId['result']['id'];


                                    //End of Quotes Insert .................

                                }


                            } catch (Exception $e) {
                                $msg = $e;
                                $flag = FALSE;
                            }


                        }
                    }
                }
            }


        }

    } catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }


    // Create reports.

    $status = 'YES';
    $totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
    $userDataArrTransaction = array(
        'user_id' => $credentials['userId'],
        'crm_type' => $crmType,
        'no_customer_data' => $flagCustomer,
        'no_product_data' => $flagProduct,
        'no_order_data' => $flagOrder,
        'last_sync_time' => time(),
        'total_transaction' => $totalTransaction,
        'status' => $status,
        'added_on' => time()
    );
    $credentials['counterOrders'] = $flagOrder;
    $credentials['counterProducts'] = $flagProduct;
    $credentials['counterAccounts'] = $flagCustomer;
    include "/var/www/html/app/mysql/mysqlconstants.php";

    tblTransectionDetail($credentials, $crmType, $userDataArrTransaction);

    sendSyncReport($credentials, $crmType);

    return $flagOrder;
    /* if( sync != 'RunSync') {
          if ($flagOrder > 0) {
              $orders = urlencode(base64_encode($flagOrder));
              if (isset($_REQUEST['page'])) {
                  $orders = base64_decode(urldecode($orders));
                  echo $orders;
              } else {
                  header("Location:https://" . APP_DOMAIN . "/app/subscription/user-dashboard.php?order=" . $orders);
              }
          } else {
              echo '0';
          }
      } */

} //Done

function magentoToHubspotCRM($credentials)
{

    include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;

    $subscriptionId = $credentials['subscription_id'];
    $crmType = 'magentoTohubspot';//$credentials['crm_type'];
    $contextVal = //$credentials['contextValue'];

    $magentoURL = $credentials['app1Details']['magento_context'];
    $magentoID = $credentials['app1Details']['magento_user_name'];
    $magentoPassword = $credentials['app1Details']['magento_password'];
    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];


    $hubspot_refresh_token = $credentials['app2Details']['hubspot_refresh_token'];
    $hubspot_portal_id = $credentials['app2Details']['hubspot_portal_id'];
    $hubspot_form_id = $credentials['app2Details']['hubspot_form_id'];
    $hubspot_last_order = $credentials['app2Details']['hubspot_last_order'];

    //........... Get HubSpot access token if already Access Permission Given.......

    //For Latest Access Token Collect From Refresh Token
    $url = HUBAPI_URL . '/oauth/v1/token';
    $headers = array(
        'Content-Type: application/x-www-form-urlencoded;charset=utf-8'
    );

    $postfields = array(
        'grant_type' => 'refresh_token',
        'client_id' => HUBSPOT_CLIENT_ID,
        'client_secret' => HUBSPOT_CLIENT_SECRET,
        'redirect_uri' => 'https://' . APP_DOMAIN . '/app/install/hubspot/app-login.php',
        'refresh_token' => $hubspot_refresh_token
    );
    $contactFieldsJSON = http_build_query($postfields);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $contactFieldsJSON);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $response = curl_exec($ch);
    $responseDecode = json_decode($response, true);
    //Access Token Collected For a User
    $hubspot_access_token = $responseDecode['access_token'];
    curl_close($ch);

    // Get Magento Token
    $magentoURL = $magentoURL . "rest/V1/";

    $total_success = 0;
    $url = $magentoURL . "integration/admin/token";
    $http_headres = array(
        "Accept: application/json",
    );
    $postfields = array('username' => $magentoID,
        'password' => $magentoPassword);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code === 200) {
        curl_close($ch);
        $magentoToken = json_decode($return, TRUE);

    }

    try {

        //............ORDER LIST COLLECTIONG.................


        $orderUrl = $magentoURL . "orders?searchCriteria[filter_groups][0][filters][0][field]=created_at&searchCriteria[filter_groups][0][filters][0][value]=" . $min_date_created . "&searchCriteria[filter_groups][0][filters][0][condition_type]=from&searchCriteria[filter_groups][1][filters][1][field]=created_at&searchCriteria[filter_groups][1][filters][1][value]=" . $max_date_created . "&searchCriteria[filter_groups][1][filters][1][condition_type]=to";
        $http_headres = array(
            "content-Type: application/json",
            "acccept: application/json",
            "authorization: Bearer " . $magentoToken
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $orderUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code === 200) {
            $orders = json_decode($return, TRUE);
            $count = $orders['total_count'];


            if ($count > 0) {

                for ($orderCount = 1; $orderCount <= $count; $orderCount++) {
                    $key = $orderCount - 1;

                    //Customer Detials Collect
                    $customerId = $orders['items'][$key]['customer_id'];
                    $currencyExchangeRate = $orders['items'][$key]['base_to_global_rate'];
                    $discountAmount = $orders['items'][$key]['discount_amount'];
                    //Order id collect
                    $orderId = $orders['items'][$key]['items'][0]['order_id'];

                    $subject = 'Order Number ' . $orders['items'][$key]['items'][0]['order_id'];

                    $totalProduct = $orders['items'][$key]['total_item_count'];

                    //Status of Order./................
                    $currentOrderStatus = $orders['items'][$key]['status'];


                    //Order Previously Exist or not
                    if ($orderId > $hubspot_last_order || $hubspot_last_order == '') {

                        //Customer  Detail collect..........

                        $customerDetailUrl = $magentoURL . "customers/" . $customerId;
                        $http_headres = array(
                            "content-Type: application/json",
                            "acccept: application/json",
                            "authorization: Bearer " . $magentoToken
                        );
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $customerDetailUrl);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                        $return = curl_exec($ch);
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);


                        //End of Collecting of Customer Detailas
                        if ($http_code === 200) {

                            $customerDetails = json_decode($return, TRUE);
                            $firstName = $customerDetails['firstname'];
                            $lastName = $customerDetails['lastname'];
                            if(empty($customerDetails['company'])) {
                                $accountName = $firstName . ' ' . $lastName;
                            }else{
                                $accountName = $customerDetails['company'];
                            }
                            $email = $customerDetails['email'];
                            if(!empty($customerDetails['addresses'][0])) {
                                $phone = $customerDetails['addresses'][0]['telephone'];
                            }
                            else{
                                $phone ='';
                            }

                            //Customer Billing Address Collecting
                            $orderUrl = $magentoURL . "customers/" . $customerId . "/billingAddress";
                            $http_headres = array(
                                "content-Type: application/json",
                                "acccept: application/json",
                                "authorization: Bearer " . $magentoToken
                            );
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $orderUrl);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                            $return = curl_exec($ch);
                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            curl_close($ch);


                            $customerBillingAddress = json_decode($return, TRUE);


                            //End of Billing Address Collecting


                            if (!empty($customerBillingAddress)) {
                                $addressDetails = json_decode($return, TRUE);


                                $mailingStreet = $addressDetails['street'][0];
                                $otherStreet = $addressDetails['street'][0];
                                $mailingCity = $addressDetails['city'];
                                $otherCity = $addressDetails['city'];
                                $mailingState = $addressDetails['region']['region'];
                                $otherState = $addressDetails['region']['region'];
                                $mailingZip = $addressDetails['postcode'];
                                $otherZip = $addressDetails['postcode'];
                                $mailingCountry = $addressDetails['country_id'];
                                $otherCountry = $addressDetails['country_id'];
                            } else {
                                $mailingStreet = $orders['items'][$key]['billing_address']['street'][0];
                                $otherStreet = $orders['items'][$key]['billing_address']['street'][0];
                                $mailingCity = $orders['items'][$key]['billing_address']['city'];
                                $otherCity = $orders['items'][$key]['billing_address']['city'];
                                $mailingState = $orders['items'][$key]['billing_address']['region'];
                                $otherState = $orders['items'][$key]['billing_address']['region'];
                                $mailingZip = $orders['items'][$key]['billing_address']['postcode'];
                                $otherZip = $orders['items'][$key]['billing_address']['postcode'];
                                $mailingCountry = $orders['items'][$key]['billing_address']['country_id'];
                                $otherCountry = $orders['items'][$key]['billing_address']['country_id'];
                            }

                            $flag = FALSE;
                            try {

                                // Create Contact for this Order ..................

                                $contactInsertUrl = HUBAPI_URL . '/contacts/v1/contact/createOrUpdate/email/' . $email;
                                $headers = array(
                                    "Authorization: Bearer $hubspot_access_token"
                                    //"Content-Type: application/json"
                                );

                                $contactData = array(
                                    'properties' => array(
                                        array(
                                            'property' => 'email',
                                            'value' => $email
                                        ),
                                        array(
                                            'property' => 'firstname',
                                            'value' => $firstName
                                        ),
                                        array(
                                            'property' => 'lastname',
                                            'value' => $lastName
                                        ),
                                        array(
                                            'property' => 'phone',
                                            'value' => $phone
                                        ),
                                        array(
                                            'property' => 'address',
                                            'value' => $mailingStreet
                                        ),
                                        array(
                                            'property' => 'city',
                                            'value' => $mailingCity
                                        ),
                                        array(
                                            'property' => 'state',
                                            'value' => $mailingState
                                        ),
                                        array(
                                            'property' => 'zip',
                                            'value' => $mailingZip
                                        ),
                                        array(
                                            'property' => 'country',
                                            'value' => $mailingCountry
                                        ),
                                        array(
                                            'property' => 'jobtitle',
                                            'value' => 'New Contact'
                                        ),
                                        array(
                                            'property' => 'lifecyclestage',
                                            'value' => 'customer'
                                        )
                                    )
                                );
                                $contactFieldsJSON = json_encode($contactData);

                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $contactInsertUrl);
                                curl_setopt($ch, CURLOPT_POST, 1);
                                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $contactFieldsJSON);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                $response = curl_exec($ch);
                                $responseDecode = json_decode($response, true);
                                curl_close($ch);
                                if (isset($responseDecode['vid'])) {
                                    $flagCustomer++;
                                }

                                // Order Details Collect
                                $subject = 'Order Number ' . $orders['items'][$key]['items'][0]['order_id'];
                                $grandTotal = $orders['items'][$key]['grand_total'];
                                $subTotal = $orders['items'][$key]['base_grand_total'];
                                $tax = $orders['items'][$key]['tax_amount'];
                                $adjustment = $grandTotal - $subTotal;
                                //Billing Address Detial
                                $orderBillingStreet = $orders['items'][$key]['billing_address']['street'][0];
                                $orderBillingCity = $orders['items'][$key]['billing_address']['city'];
                                $orderBillingZip = $orders['items'][$key]['billing_address']['postcode'];
                                $orderBillingCountry = $orders['items'][$key]['billing_address']['country_id'];
                                $orderBillingState = $orders['items'][$key]['billing_address']['region'];
                                //Shipping Address Detial
                                $orderShippingCity = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['city'];
                                $orderShippingState = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['region'];
                                $orderShippingStreet = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['street'][0];
                                $orderShippingZip = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['postcode'];
                                $orderShippingCountry = $orders['items'][$key]['extension_attributes']['shipping_assignments'][0]['shipping']['address']['country_id'];

                                //Form Data Submit
                                $formInsertUrl = "https://forms.hubspot.com/uploads/form/v2/" . $hubspot_portal_id . "/" . $hubspot_form_id;

                                $headers = array(
                                    "Content-Type: application/x-www-form-urlencoded"
                                );
                                $hubspotutk = $_COOKIE['hubspotutk']; //grab the cookie from the visitors browser.
                                $ip_addr = $_SERVER['REMOTE_ADDR']; //IP address too.
                                $hs_context = array(
                                    'hutk' => $hubspotutk,
                                    'ipAddress' => $ip_addr
                                );
                                $hs_context_json = json_encode($hs_context);

                                $str_post = "subject=" . urlencode($subject)
                                    . "&order_id=" . urlencode($orderId)
                                    . "&email=" . urlencode($email)
                                    . "&status=" . urlencode($currentOrderStatus)
                                    . "&bill_street=" . urlencode($orderBillingStreet)
                                    . "&bill_city=" . urlencode($orderBillingCity)
                                    . "&bill_code=" . urlencode($orderBillingZip)
                                    . "&bill_country=" . urlencode($orderBillingCountry)
                                    . "&bill_state=" . urlencode($orderBillingState)
                                    . "&ship_street=" . urlencode($orderShippingStreet)
                                    . "&ship_city=" . urlencode($orderShippingCity)
                                    . "&ship_code=" . urlencode($orderShippingZip)
                                    . "&ship_country=" . urlencode($orderShippingCountry)
                                    . "&ship_state=" . urlencode($orderShippingState)
                                    . "&tax=" . urlencode($tax)
                                    . "&subtotal=" . urlencode($subTotal)
                                    . "&total=" . urlencode($grandTotal)
                                    . "&hs_context=" . urlencode($hs_context_json); //Leave this one be
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $formInsertUrl);
                                curl_setopt($ch, CURLOPT_POST, 1);
                                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $str_post);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                $response = curl_exec($ch);
                                $responseDecode = json_decode($response, true);
                                curl_close($ch);

                                if (!isset($responseDecode['error'])) {
                                    $flagOrder++;
                                    //update table subscription
                                    $cond = " AND app2_cred1='$hubspot_refresh_token' AND app2_cred2='$hubspot_portal_id' AND app2_cred3='$hubspot_form_id'";
                                    $updateArr = array(
                                        'hub_order' => $orderId
                                    );
                                    //Subscription Table update
                                    update($userSubscription, $updateArr, $cond);
                                    //Final Subscription Table update
                                    update($userSubscriptionFinal, $updateArr, $cond);

                                }
                                //End of Form Submission
                            } catch (Exception $e) {
                                $msg = $e;
                                $flag = FALSE;
                            }


                        }

                    }//order exist or not
                }//end or order list
            }


        }

    } catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }
    // Create reports.

    $status = 'YES';
    $totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
    $userDataArrTransaction = array(
        'user_id' => $credentials['userId'],
        'crm_type' => $crmType,
        'no_customer_data' => $flagCustomer,
        'no_product_data' => $flagProduct,
        'no_order_data' => $flagOrder,
        'last_sync_time' => time(),
        'total_transaction' => $totalTransaction,
        'status' => $status,
        'added_on' => time()
    );
    $credentials['counterOrders'] = $flagOrder;
    $credentials['counterProducts'] = $flagProduct;
    $credentials['counterAccounts'] = $flagCustomer;
    include "/var/www/html/app/mysql/mysqlconstants.php";

    tblTransectionDetail($credentials, $crmType, $userDataArrTransaction);

    sendSyncReport($credentials, $crmType);
    return $flagOrder;
} //Done


// Core Function For Bigcommerce Sync....
function bigcommerceToZohoCRM($credentials)
{
    /* including database related files */
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;
    $totalTransaction = 0;
    $zohoCRMAuthtoken = '';
    $access_token = '';
    $cloudApp = 'ZOHO';
    //

    $subscriptionId = $credentials['subscription_id'];
    $crmType = 'bigcommerceTozoho';
    $bigcommURL = $credentials['app1Details']['bigcommerce_context'] . "/api";
    $bigcommerceUsername = $credentials['app1Details']['bigcommerce_user_name'];
    $bigcommerceAccessKey = $credentials['app1Details']['bigcommerce_password'];
    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];
    $zohoCRMAuthtoken = $credentials['app2Details']['zoho_auth_id'];
    //$access_token = $credentials['bigCommerceAccessToken'];
    //$accountType = $credentials['account_type'];

    try {

        $retVal = FALSE;
        $total_success = 0;
        $access_token = base64_encode($bigcommerceUsername . ":" . $bigcommerceAccessKey);
        $url = $bigcommURL . "/v2/orders/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
        $http_headres = array(
            "Authorization: Basic " . trim($access_token),
            "Content-Type: application/json",
            "Accept: application/json",

        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        if ($http_code === 200) {
            curl_close($ch);
            $count_arr = json_decode($return, TRUE);
            $count = $count_arr['count'];
            if ($count > 0) {
                $page = ceil($count / MAX_PAGE_SIZE);


                for ($i = 1; $i <= $page; $i++) {
                    $url = $bigcommURL . "/v2/orders.json?page=" . $i . "&limit=" . MAX_PAGE_SIZE . "&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                    $return = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    // check if any orders exist for the page

                    if ($http_code === 200) {
                        $orders = json_decode($return, TRUE);

                        // Insert everything for the Order including Contact and Products.

                        foreach ($orders as $key => $value) {
                            // if ($orders[$key]['id'] === 276){
                            $currentOrderID = $orders[$key]['id'];
                            /* Retrieve Customer Contact for the Order and insert into Zoho Inventory */
                            $customer_id = $orders[$key]['customer_id'];
                            //echo ("Order ID: ".$orders[$key]['id']);
                            //echo ("Status ID: ".$orders[$key]['status']);
                            $currentOrderStatus = $orders[$key]['status'];
                            if (($currentOrderStatus === 'Incomplete') || ($currentOrderStatus === 'Pending') || ($currentOrderStatus === 'Declined')) {
                                $salesOrder = 'FALSE';
                            } else {
                                $salesOrder = 'TRUE';

                            }
                            //echo ("Sales Order: ".$salesOrder);
                            //echo "\r\n";

                            $customer_url = $bigcommURL . "/v2/customers/$customer_id.json";
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $customer_url);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                            $return = curl_exec($ch);
                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                            curl_close($ch);
                            if ($http_code === 200) {
                                $customerDetails = json_decode($return, TRUE);
                                $firstName = $customerDetails['first_name'];
                                $lastName = $customerDetails['last_name'];
                                $accountName = $firstName . ' ' . $lastName;
                                $email = $customerDetails['email'];
                                $phone = $customerDetails['phone'];
                                /* retrieve address for the customer */
                                $address_url = $customerDetails['addresses']['url'];
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $address_url);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                $return = curl_exec($ch);
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                // curl_close($ch);
                                // echo("Address: ");
                                // echo $return;

                                if ($http_code === 200) {
                                    $addressDetails = json_decode($return, TRUE);
                                    $mailingStreet = $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'];
                                    $otherStreet = $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'];
                                    $mailingCity = $addressDetails[0]['city'];
                                    $otherCity = $addressDetails[0]['city'];
                                    $mailingState = $addressDetails[0]['state'];
                                    $otherState = $addressDetails[0]['state'];
                                    $mailingZip = $addressDetails[0]['zip'];
                                    $otherZip = $addressDetails[0]['zip'];
                                    $mailingCountry = $addressDetails[0]['country'];
                                    $otherCountry = $addressDetails[0]['country'];
                                } else {
                                    $mailingStreet = $orders[$key]['billing_address']['street_1'] . " " . $orders[$key]['billing_address']['street_2'];
                                    $otherStreet = $orders[$key]['billing_address']['street_1'] . " " . $orders[$key]['billing_address']['street_2'];
                                    $mailingCity = $orders[$key]['billing_address']['city'];
                                    $otherCity = $orders[$key]['billing_address']['city'];
                                    $mailingState = $orders[$key]['billing_address']['state'];
                                    $otherState = $orders[$key]['billing_address']['state'];
                                    $mailingZip = $orders[$key]['billing_address']['zip'];
                                    $otherZip = $orders[$key]['billing_address']['zip'];
                                    $mailingCountry = $orders[$key]['billing_address']['country'];
                                    $otherCountry = $orders[$key]['billing_address']['country'];
                                }

                                $flag = FALSE;
                                try {
                                    // Insert Account details
                                    $xml = '<?xml version="1.0" encoding="UTF-8"?>
                                                        <Accounts>
                                                        <row no="1">
                                                                <FL val="Account Name">' . $accountName . '</FL>
                                                                <FL val="Email">' . $email . '</FL>
                                                                <FL val="Phone">' . $phone . '</FL>
                                                                <FL val="Billing Street">' . $mailingStreet . '</FL>
                                                                <FL val="Shipping Street">' . $otherStreet . '</FL>
                                                                <FL val="Billing City">' . $mailingCity . '</FL>
                                                                <FL val="Shipping City">' . $otherCity . '</FL>
                                                                <FL val="Billing State">' . $mailingState . '</FL>
                                                                <FL val="Shipping State">' . $otherState . '</FL>
                                                                <FL val="Billing Code">' . $mailingZip . '</FL>
                                                                <FL val="Shipping Code">' . $otherZip . '</FL>
                                                                <FL val="Billing Country">' . $mailingCountry . '</FL>
                                                                <FL val="Shipping Country">' . $otherCountry . '</FL>
                                                        </row>
                                                         </Accounts>';

                                    // For Insert Records with duplicate checking.

                                    $url = "https://crm.zoho.com/crm/private/xml/Accounts/insertRecords";
                                    $query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$xml";
                                    $ch = curl_init();
                                    /* set url to send post request */
                                    curl_setopt($ch, CURLOPT_URL, $url);
                                    /* allow redirects */
                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                    /* return a response into a variable */
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                    /* times out after 30s */
                                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                    /* set POST method */
                                    curl_setopt($ch, CURLOPT_POST, 1);
                                    /* add POST fields parameters */
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                    // Execute cUrl session

                                    $response = curl_exec($ch);
                                    curl_close($ch);


                                    $xml = '<?xml version="1.0" encoding="UTF-8"?>
        						<Contacts>
            						<row no="1">
                						<FL val="First Name">' . $firstName . '</FL>
                						<FL val="Last Name">' . $lastName . '</FL>
                						<FL val="Account Name">' . $accountName . '</FL>
                						<FL val="Email">' . $email . '</FL>
                						<FL val="Phone">' . $phone . '</FL>
                						<FL val="Mailing Street">' . $mailingStreet . '</FL>
                						<FL val="Other Street">' . $otherStreet . '</FL>
                						<FL val="Mailing City">' . $mailingCity . '</FL>
                						<FL val="Other City">' . $otherCity . '</FL>
                						<FL val="Mailing State">' . $mailingState . '</FL>
                						<FL val="Other State">' . $otherState . '</FL>
                						<FL val="Mailing Zip">' . $mailingZip . '</FL>
                						<FL val="Other Zip">' . $otherZip . '</FL>
                						<FL val="Mailing Country">' . $mailingCountry . '</FL>
                						<FL val="Other Country">' . $otherCountry . '</FL>
            						</row>
       							 </Contacts>';

                                    // For Insert Records with duplicate checking.

                                    $url = "https://crm.zoho.com/crm/private/xml/Contacts/insertRecords";
                                    $query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$xml";
                                    $ch = curl_init();
                                    /* set url to send post request */
                                    curl_setopt($ch, CURLOPT_URL, $url);
                                    /* allow redirects */
                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                    /* return a response into a variable */
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                    /* times out after 30s */
                                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                    /* set POST method */
                                    curl_setopt($ch, CURLOPT_POST, 1);
                                    /* add POST fields parameters */
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                    // Execute cUrl session

                                    $response = curl_exec($ch);
                                    curl_close($ch);
                                    $res = simplexml_load_string($response);
                                    $resCode = (string)$res->result->row->success->code;

                                    // error_log("XML & Response".$error."    ".$response, 1, "support@aquaapi.com");
                                    // echo $res->axXML();
                                    // $error['code'] = $resCode;

                                    if (($resCode === '2000') || ($resCode === '2001') || ($resCode === '2002')) {

                                        // $flag = TRUE;

                                        if ($resCode === '2000') {
                                            $flagCustomer++;
                                        }

                                        // Contact addition successful. Proceed with addtion of Product and Sales Order
                                        $subject = 'Order Number ' . $orders[$key]['id'];
                                        $grandTotal = $orders[$key]['total_inc_tax'];
                                        $subTotal = $orders[$key]['subtotal_ex_tax'];
                                        $tax = $orders[$key]['total_tax'];
                                        $adjustment = ($orders[$key]['total_inc_tax'] - $orders[$key]['subtotal_inc_tax'])-$tax;
                                        $orderBillingStreet = $orders[$key]['billing_address']['street_1'] . $orders[$key]['billing_address']['street_2'];
                                        $orderBillingCity = $orders[$key]['billing_address']['city'];
                                        $orderBillingState = $orders[$key]['billing_address']['state'];
                                        $orderBillingZip = $orders[$key]['billing_address']['zip'];
                                        $orderBillingCountry = $orders[$key]['billing_address']['country'];
                                        // retrieve shipping address
                                        $orderShippingURL = $orders[$key]['shipping_addresses']['url'];
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $orderShippingURL);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                        $return = curl_exec($ch);
                                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                        curl_close($ch);
                                        $orderShippingAddresses = json_decode($return, TRUE);
                                        $orderShippingStreet = $orderShippingAddresses[0]['street_1'] . $orderShippingAddresses[0]['street_2'];
                                        $orderShippingCity = $orderShippingAddresses[0]['city'];
                                        $orderShippingState = $orderShippingAddresses[0]['state'];
                                        $orderShippingZip = $orderShippingAddresses[0]['zip'];
                                        $orderShippingCountry = $orderShippingAddresses[0]['country'];

                                        if ($salesOrder === 'TRUE') {
                                            $InvoiceXML = '<?xml version="1.0" encoding="UTF-8"?>
									<SalesOrders><row no="1">
                                                                        <FL val="Contact Name">' . $accountName . '</FL>
                                                                        <FL val="Account Name">' . $accountName . '</FL>
                                                                        <FL val="Subject">' . $subject . '</FL>
									<FL val="Product Details">
									</FL>
                                                                        <FL val="Billing Street">' . $orderBillingStreet . '</FL>
                                                                        <FL val="Shipping Street">' . $orderShippingStreet . '</FL>
                                                                        <FL val="Billing City">' . $orderBillingCity . '</FL>
                                                                        <FL val="Shipping City">' . $orderShippingCity . '</FL>
                                                                        <FL val="Billing State">' . $orderBillingState . '</FL>
                                                                        <FL val="Shipping State">' . $orderShippingState . '</FL>
                                                                        <FL val="Billing Code">' . $orderBillingZip . '</FL>
                                                                        <FL val="Shipping Code">' . $orderShippingZip . '</FL>
                                                                        <FL val="Billing Country">' . $orderBillingCountry . '</FL>
                                                                        <FL val="Shipping Country">' . $orderShippingCountry . '</FL>
									<FL val="Sub Total">' . $subTotal . '</FL>
									<FL val="Tax">' . $tax . '</FL>
									<FL val="Adjustment">' . $adjustment . '</FL>
									<FL val="Grand Total">' . $grandTotal . '</FL>
                                                                        </row></SalesOrders>';
                                        } else {
                                            $InvoiceXML = '<?xml version="1.0" encoding="UTF-8"?>
                                                                        <Quotes><row no="1">
                                                                        <FL val="Contact Name">' . $accountName . '</FL>
                                                                        <FL val="Account Name">' . $accountName . '</FL>
                                                                        <FL val="Subject">' . $subject . '</FL>
                                                                        <FL val="Product Details">
                                                                        </FL>
                                                                        <FL val="Billing Street">' . $orderBillingStreet . '</FL>
                                                                        <FL val="Shipping Street">' . $orderShippingStreet . '</FL>
                                                                        <FL val="Billing City">' . $orderBillingCity . '</FL>
                                                                        <FL val="Shipping City">' . $orderShippingCity . '</FL>
                                                                        <FL val="Billing State">' . $orderBillingState . '</FL>
                                                                        <FL val="Shipping State">' . $orderShippingState . '</FL>
                                                                        <FL val="Billing Code">' . $orderBillingZip . '</FL>
                                                                        <FL val="Shipping Code">' . $orderShippingZip . '</FL>
                                                                        <FL val="Billing Country">' . $orderBillingCountry . '</FL>
                                                                        <FL val="Shipping Country">' . $orderShippingCountry . '</FL>
                                                                        <FL val="Sub Total">' . $subTotal . '</FL>
                                                                        <FL val="Tax">' . $tax . '</FL>
                                                                        <FL val="Adjustment">' . $adjustment . '</FL>
                                                                        <FL val="Grand Total">' . $grandTotal . '</FL>
                                                                        </row></Quotes>';

                                        }
                                        /* Insert Products in Zoho CRM, if not already present */
                                        $product_url = $orders[$key]['products']['url'];
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $product_url);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                        $return = curl_exec($ch);
                                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                        curl_close($ch);
                                        $recordSet = json_decode($return, TRUE);


                                        $sxeInvoiceXML = new SimpleXMLElement($InvoiceXML);
                                        /* if multiple sku available */
                                        if (count($recordSet) > 0) {
                                            $prodNumber = 0;
                                            foreach ($recordSet as $k => $val) {
                                                $m_sku_id = $val['sku'];
                                                $prodCode = $m_sku_id;
                                                $m_adjusted_price = $val['price_inc_tax'];
                                                $prodName = $val['name'];
                                                $m_quantity = $val['quantity'];
                                                $prodListPrice = $val['base_price'];
                                                $prodUnitPrice = $val['price_ex_tax'];
                                                $total = $val['base_total'];
                                                $totalAfterDiscount = $val['total_ex_tax'];
                                                $discount = $total - $totalAfterDiscount;
                                                $tax = $val['total_tax'];
                                                $netTotal = $val['total_inc_tax'];
                                                $ProductXML = '<?xml version="1.0" encoding="UTF-8"?>
       											 <Products>
            											<row no="1">
                										<FL val="Product Code">' . $prodCode . '</FL>
                										<FL val="Product Name"><![CDATA[' . urlencode($prodName) . ']]></FL>
                										<FL val="Unit Price">' . $prodUnitPrice . '</FL>
														<FL val="Qty Ordered"><![CDATA[' . urlencode($m_quantity) . ']]></FL>
            											</row>
        										</Products>';
                                                $output = simplexml_load_string($ProductXML);
                                                //echo $output->asXML();

                                                // For Insert Records with duplicate checking.

                                                $url = "https://crm.zoho.com/crm/private/xml/Products/insertRecords";
                                                $query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$ProductXML";
                                                $ch = curl_init();
                                                /* set url to send post request */
                                                curl_setopt($ch, CURLOPT_URL, $url);
                                                /* allow redirects */
                                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                                /* return a response into a variable */
                                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                                /* times out after 30s */
                                                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                                /* set POST method */
                                                curl_setopt($ch, CURLOPT_POST, 1);
                                                /* add POST fields parameters */
                                                curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                                // Execute cUrl session

                                                $response = curl_exec($ch);
                                                curl_close($ch);
                                                $res = simplexml_load_string($response);
                                                if ($res->result != FALSE) {

                                                    $resCode = (string)$res->result->row->success->code;
                                                    $prodId = $res->result->row->success->details->FL[0];
                                                } else {
                                                    $resCode = 0;
                                                }


                                                if (($resCode === '2000') || ($resCode === '2001') || ($resCode === '2002')) {

                                                    // $flag = TRUE;

                                                    if ($resCode === '2000') {
                                                        $flagProduct++;
                                                    }
                                                    if ($resCode === '2002') // duplicate product
                                                    {
                                                        // Update Product
                                                        $url = "https://crm.zoho.com/crm/private/xml/Products/updateRecords";
                                                        $query = "authtoken=$zohoCRMAuthtoken&newFormat=1&scope=crmapi&id=$prodId&xmlData=$ProductXML";
                                                        $ch = curl_init();
                                                        /* set url to send post request */
                                                        curl_setopt($ch, CURLOPT_URL, $url);
                                                        /* allow redirects */
                                                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                                        /* return a response into a variable */
                                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                                        /* times out after 30s */
                                                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                                        /* set POST method */
                                                        curl_setopt($ch, CURLOPT_POST, 1);
                                                        /* add POST fields parameters */
                                                        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                                        // Execute cUrl session

                                                        $response = curl_exec($ch);
                                                        curl_close($ch);
                                                        $res = simplexml_load_string($response);
                                                        //echo("Product update");
                                                        //echo $response;

                                                    }


                                                    $prodNumber++;
                                                    $productDetails = $sxeInvoiceXML->row->FL[3]->addChild("product");
                                                    $productDetails->addAttribute('no', $prodNumber);
                                                    $FLsku = $productDetails->addChild("FL", $prodId);
                                                    $FLsku->addAttribute('val', 'Product Id');
                                                    $FLquantity = $productDetails->addChild("FL", $m_quantity);
                                                    $FLquantity->addAttribute('val', 'Quantity');
                                                    $FLprice = $productDetails->addChild("FL", $prodUnitPrice);
                                                    $FLprice->addAttribute('val', 'Unit Price');
                                                    $FLprice = $productDetails->addChild("FL", $prodListPrice);
                                                    $FLprice->addAttribute('val', 'List Price');
                                                    $FLprice = $productDetails->addChild("FL", $total);
                                                    $FLprice->addAttribute('val', 'Total');
                                                    $FLprice = $productDetails->addChild("FL", $discount);
                                                    $FLprice->addAttribute('val', 'Discount');
                                                    $FLprice = $productDetails->addChild("FL", $totalAfterDiscount);
                                                    $FLprice->addAttribute('val', 'Total after Discount');
                                                    $FLprice = $productDetails->addChild("FL", $tax);
                                                    $FLprice->addAttribute('val', 'Tax');
                                                    $FLprice = $productDetails->addChild("FL", $netTotal);
                                                    $FLprice->addAttribute('val', 'Net Total');
                                                } else {

                                                    $flag = FALSE;
                                                    $st = 'YES';
                                                    $AccountHead = 'PRODUCT';
                                                    // SaveErrorDetails fails when $prodName has special characters used in MySQL
                                                    SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Product ' . $prodName);
                                                }
                                            } // for each product

                                            // echo $sxeInvoiceXML->asXML();

                                            $InvoiceXML = $sxeInvoiceXML->asXML();

                                            // error_log($InvoiceXML, 1, "debashishp@gmail.com");

                                        } // if Product SKUs available.
                                        /* Insert Sales Order or Quote in Zoho CRM if not aleady present */
                                        if ($salesOrder === 'TRUE') {
                                            $url = "https://crm.zoho.com/crm/private/xml/SalesOrders/insertRecords";
                                        } else {
                                            $url = "https://crm.zoho.com/crm/private/xml/Quotes/insertRecords";
                                        }
                                        $query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$InvoiceXML";

                                        // $query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=2&version=4&newFormat=1&xmlData=$InvoiceXML";

                                        $ch = curl_init();
                                        /* set url to send post request */
                                        curl_setopt($ch, CURLOPT_URL, $url);
                                        /* allow redirects */
                                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                        /* return a response into a variable */
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                        /* times out after 30s */
                                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                        /* set POST method */
                                        curl_setopt($ch, CURLOPT_POST, 1);
                                        /* add POST fields parameters */
                                        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                        // Execute cUrl session

                                        $response = curl_exec($ch);

                                        // $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                                        curl_close($ch);

                                        // check if it returns 200, or else return false

                                        $res = simplexml_load_string($response);
                                        if ($res->result != FALSE) {
                                            $resCode = (string)$res->result->row->success->code;
                                        } else {
                                            $resCode = 0;
                                        }
                                        //echo $res->asXML();
                                        // echo "</br>";
                                        // $error['code'] = $resCode;

                                        if (($resCode === '2000') || ($resCode === '2001') || ($resCode === '2002')) {

                                            // $flag = TRUE;

                                            if ($resCode === '2000') {
                                                $flagOrder++;
                                            }
                                        } else {

                                            $flag = FALSE;
                                            $st = 'YES';
                                            $AccountHead = 'ORDER';
                                            SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Order Number: ' . $orders[$key]['id']);
                                        }
                                    } else {

                                        $AccountHead = 'CUSTOMER';
                                        $st = 'YES';
                                        SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Customer: ' . $accountName);
                                        $flag = FALSE;
                                    }
                                } catch (Exception $e) {

                                    $msg = $e;
                                    $flag = FALSE;
                                }
                            } /// If customer successfully retrieved from BG.
                            // } // Temporary code. To be removed.
                        } // for each order on the page
                    }
                } // for each page of orders
            }
        } // if count of orders returned
    } // Try retrieving orders from BigCommerce
    catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }

    // Create reports.

    $status = 'YES';
    $totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
    $userDataArrTransaction = array(
        'user_id' => $credentials['userId'],
        'crm_type' => $crmType,
        'no_customer_data' => $flagCustomer,
        'no_product_data' => $flagProduct,
        'no_order_data' => $flagOrder,
        'last_sync_time' => time(),
        'total_transaction' => $totalTransaction,
        'status' => $status,
        'added_on' => time()
    );
    $credentials['counterOrders'] = $flagOrder;
    $credentials['counterProducts'] = $flagProduct;
    $credentials['counterAccounts'] = $flagCustomer;
    //for table transection detials
    tblTransectionDetail($credentials, $crmType, $userDataArrTransaction);

    sendSyncReport($credentials, $crmType);

    return $flagOrder;
} //Done

function bigcommerceToZohoInventoryCRM($credentials)
{
    /* including database related files */
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;
    $totalTransaction = 0;
    $zohoCRMAuthtoken = '';
    $access_token = '';
    $cloudApp = 'ZOHO_INVENTORY';
    //

    $subscriptionId = $credentials['subscription_id'];
    $crmType = 'bigcommerceTozohoinventory';
    $bigcommURL = $credentials['app1Details']['bigcommerce_context'] . "/api";
    $bigcommerceUsername = $credentials['app1Details']['bigcommerce_user_name'];
    $bigcommerceAccessKey = $credentials['app1Details']['bigcommerce_password'];
    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];
    $zohoInventoryAuthToken = $credentials['app2Details']['zoho_auth_id'];
    $zohoInventoryOrgID = $credentials['app2Details']['zoho_organisation_id'];
    //$access_token = $credentials['bigCommerceAccessToken'];
    //$accountType = $credentials['account_type'];

    try {

        $retVal = FALSE;
        $total_success = 0;
        $access_token = base64_encode($bigcommerceUsername . ":" . $bigcommerceAccessKey);
        $url = $bigcommURL . "/v2/orders/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
        $http_headres = array(
            "Authorization: Basic " . trim($access_token),
            "Content-Type: application/json",
            "Accept: application/json",

        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        if ($http_code === 200) {
            curl_close($ch);
            $count_arr = json_decode($return, TRUE);
            $count = $count_arr['count'];

            if ($count > 0) {
                $page = ceil($count / MAX_PAGE_SIZE);

                for ($i = 1; $i <= $page; $i++) {
                    $url = $bigcommURL . "/v2/orders.json?page=" . $i . "&limit=" . MAX_PAGE_SIZE . "&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                    $return = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    // check if any orders exist for the page

                    if ($http_code === 200) {
                        $orders = json_decode($return, TRUE);

                        foreach ($orders as $key => $value) {

                            $currentOrderID = $orders[$key]['id'];
                            /* Retrieve Customer Contact for the Order and insert into Zoho Inventory */
                            $customer_id = $orders[$key]['customer_id'];
                            $currentOrderStatus = $orders[$key]['status'];
                            $subTotal = $orders[$key]['total_ex_tax'];
                            $tax = $orders[$key]['total_tax'];
                            $grandTotal = $orders[$key]['total_inc_tax'];
                            if (($currentOrderStatus === 'Incomplete') || ($currentOrderStatus === 'Pending') || ($currentOrderStatus === 'Declined')) {
                                $salesOrder = $currentOrderStatus;
                            } else {
                                $salesOrder = 'confirmed';

                            }
                            //echo ("Sales Order: ".$salesOrder);
                            //echo "\r\n";

                            $customer_url = $bigcommURL . "/v2/customers/$customer_id.json";
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $customer_url);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                            $return = curl_exec($ch);
                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


                            curl_close($ch);
                            if ($http_code === 200) {
                                $customerDetails = json_decode($return, TRUE);
                                $firstName = $customerDetails['first_name'];
                                $lastName = $customerDetails['last_name'];
                                $contactName = $firstName . ' ' . $lastName;
                                $email = $customerDetails['email'];
                                $phone = $customerDetails['phone'];
                                /* retrieve address for the customer */
                                $address_url = $customerDetails['addresses']['url'];
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $address_url);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                $return = curl_exec($ch);
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                // curl_close($ch);
                                // echo("Address: ");
                                // echo $return;
                                curl_close($ch);
                                if ($http_code === 200) {
                                    $addressDetails = json_decode($return, TRUE);
                                    $contactDetails = json_encode(array(
                                        "contact_name" => $customerDetails['first_name'] . " " . $customerDetails['last_name'] . " ( " . $customerDetails['email'] . " ) ",
                                        "company_name" => $customerDetails['company'],
                                        "contact_type" => "customer",
                                        "billing_address" => array(
                                            "address" => $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'],
                                            "city" => $addressDetails[0]['city'],
                                            "state" => $addressDetails[0]['state'],
                                            "zip" => $addressDetails[0]['zip'],
                                            "country" => $addressDetails[0]['country'],
                                            "fax" => ""
                                        ),
                                        "shipping_address" => array(
                                            "address" => $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'],
                                            "city" => $addressDetails[0]['city'],
                                            "state" => $addressDetails[0]['state'],
                                            "zip" => $addressDetails[0]['zip'],
                                            "country" => $addressDetails[0]['country'],
                                            "fax" => ""
                                        ),
                                        "contact_persons" => array(
                                            array(
                                                "first_name" => $customerDetails['first_name'],
                                                "last_name" => $customerDetails['last_name'],
                                                "email" => $customerDetails['email'],
                                                "phone" => $customerDetails['phone'],
                                            )
                                        )
                                    ));
                                } else {
                                    $mailingStreet = $orders[$key]['billing_address']['street_1'] . " " . $orders[$key]['billing_address']['street_2'];
                                    $otherStreet = $orders[$key]['billing_address']['street_1'] . " " . $orders[$key]['billing_address']['street_2'];
                                    $mailingCity = $orders[$key]['billing_address']['city'];
                                    $otherCity = $orders[$key]['billing_address']['city'];
                                    $mailingState = $orders[$key]['billing_address']['state'];
                                    $otherState = $orders[$key]['billing_address']['state'];
                                    $mailingZip = $orders[$key]['billing_address']['zip'];
                                    $otherZip = $orders[$key]['billing_address']['zip'];
                                    $mailingCountry = $orders[$key]['billing_address']['country'];
                                    $otherCountry = $orders[$key]['billing_address']['country'];
                                    $contactDetails = json_encode(array(
                                        "contact_name" => $customerDetails['first_name'] . " " . $customerDetails['last_name'] . " ( " . $customerDetails['email'] . " ) ",
                                        "company_name" => $customerDetails['company'],
                                        "contact_type" => "customer",
                                        "billing_address" => array(
                                            "address" => $mailingStreet,
                                            "city" => $mailingCity,
                                            "state" => $mailingState,
                                            "zip" => $mailingZip,
                                            "country" => $mailingCountry,
                                            "fax" => ""
                                        ),
                                        "shipping_address" => array(
                                            "address" => $otherStreet,
                                            "city" => $otherCity,
                                            "state" => $otherState,
                                            "zip" => $otherZip,
                                            "country" => $otherCountry,
                                            "fax" => ""
                                        ),
                                        "contact_persons" => array(
                                            array(
                                                "first_name" => $customerDetails['first_name'],
                                                "last_name" => $customerDetails['last_name'],
                                                "email" => $customerDetails['email'],
                                                "phone" => $customerDetails['phone'],
                                            )
                                        )
                                    ));
                                }
                                $url = "https://inventory.zoho.com/api/v1/contacts";
                                $query = "organization_id=" . $zohoInventoryOrgID . "&authtoken=" . $zohoInventoryAuthToken . "&JSONString=" . $contactDetails;
                                $ch = curl_init();
                                /* set url to send post request */
                                curl_setopt($ch, CURLOPT_URL, $url);
                                /* allow redirects */
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                /* return a response into a variable */
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                /* times out after 30s */
                                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                /* set POST method */
                                curl_setopt($ch, CURLOPT_POST, 1);
                                /* add POST fields parameters */
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                // Execute cUrl session
                                $response = curl_exec($ch);
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                                curl_close($ch);
                                $flag = FALSE;
                                if ($http_code === 201 || $http_code === 400) {

                                    if ($http_code === 201) { //For New Contact Detected
                                        $msg = json_decode($response, TRUE);
                                        $retVal = $msg['code'];
                                        $customerID = $msg['contact']['contact_id'];
                                        // echo "New Customer :";
                                        //echo $customerID;
                                        $flagCustomer++;
                                    } else {  //For Duplicate Contact Detected
                                        //echo $contactName;
                                        $url = "https://inventory.zoho.com/api/v1/contacts?authtoken=" . $zohoInventoryAuthToken . "&organization_id=" . $zohoInventoryOrgID . "&email=" . $email;
                                        $ch = curl_init();
                                        /* set url to send post request */
                                        curl_setopt($ch, CURLOPT_URL, $url);
                                        /* allow redirects */
                                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                        /* return a response into a variable */
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                        /* times out after 30s */
                                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                        /* set POST method */
                                        curl_setopt($ch, CURLOPT_POST, 0);


                                        // Execute cUrl session
                                        $response = curl_exec($ch);
                                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                        $msg = json_decode($response, TRUE);
                                        $customerID = $msg['contacts'][0]['contact_id'];

                                        //echo "Old Customer :";
                                        //echo $customerID;
                                        curl_close($ch);

                                    }
                                    /* Insert Products in Zoho Inventory, if not already present */
                                    $product_url = $orders[$key]['products']['url'];
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $product_url);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                    $return = curl_exec($ch);
                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                                    // check if it returns 200, or else return false
                                    curl_close($ch);
                                    if ($http_code === 200) {
                                        $orderProductDetails = json_decode($return, TRUE);

                                        $productCount = count($orderProductDetails);

                                        for ($i = 0; $i < $productCount; $i++) {

                                            //Produ
                                            $product_url = $bigcommURL . "/v2/products/" . $orderProductDetails[$i]['product_id'];
                                            $ch = curl_init();
                                            curl_setopt($ch, CURLOPT_URL, $product_url);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                            $return = curl_exec($ch);
                                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                            $productDetail = json_decode($return);
                                            curl_close($ch);

                                            /* Insert each product into zoho Inventory */
                                            $itemDetails = json_encode(array(
                                                "item_type" => "inventory",
                                                "name" => $orderProductDetails[$i]['name'],
                                                "rate" => $orderProductDetails[$i]['base_total'],
                                                "sku" => $orderProductDetails[$i]['sku'],
                                                "description" => $orderProductDetails[$i]['name'],
                                                "initial_stock" => '100',
                                                "initial_stock_rate" => '100',
                                                "purchase_rate" => '0',
                                            ));
                                            /* Copy Product details ordered for inserting with Sales Order */
                                            $orderItems[$i]['name'] = $orderProductDetails[$i]['name'];
                                            $orderItems[$i]['description'] = $orderProductDetails[$i]['name'];
                                            $orderItems[$i]['rate'] = $orderProductDetails[$i]['price_ex_tax'];
                                            $orderItems[$i]['quantity'] = $orderProductDetails[$i]['quantity'];
                                            $url = "https://inventory.zoho.com/api/v1/items";
                                            $query = "organization_id=" . $zohoInventoryOrgID . "&authtoken=" . $zohoInventoryAuthToken . "&JSONString=" . $itemDetails;
                                            $ch = curl_init();
                                            /* set url to send post request */
                                            curl_setopt($ch, CURLOPT_URL, $url);
                                            /* allow redirects */
                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                            /* return a response into a variable */
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                            /* times out after 30s */
                                            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                            /* set POST method */
                                            curl_setopt($ch, CURLOPT_POST, 1);
                                            /* add POST fields parameters */
                                            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                            // Execute cUrl session

                                            $response = curl_exec($ch);
                                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                            // check if it returns 200, or else return false
                                            if ($http_code === 201) {
                                                curl_close($ch);
                                                $flag = 1;
                                                $flagProduct++;
                                            } else // Product couldn't be added
                                            {
                                                $error = json_decode($response, TRUE);
                                                // Error is not because of duplicate record
                                                if ($error['code'] != 1001) {
                                                    // Insert Error Data in Database
                                                    $AccountHead = 'PRODUCT';
                                                    $st = 'YES';
                                                    SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Product ' . $orderProductDetails[$i]['sku']);
                                                }
                                                curl_close($ch);
                                            }
                                        }
                                    }

                                    /* Insert Sales Order into Zoho Inventory */
                                    $salesOrderDate = DateTime::createFromFormat(DATE_RFC2822, $orders[$key]['date_created']);
                                    $salesOrderDetails = json_encode(array(
                                        "salesorder_number" => $orders[$key]['id'],
                                        "date" => $salesOrderDate->format('Y-m-d'),
                                        "shipment_date" => "",
                                        "delivery_method" => "None",
                                        "discount" => 0,
                                        "discount_type" => "entity_level",
                                        "customer_id" => $customerID,
                                        "status" => $salesOrder,
                                        "line_items" => $orderItems,
                                        "sub_total" => $subTotal,
                                        "tax_total" => $tax,
                                        "total" => $grandTotal,
                                    ));
                                    $url = "https://inventory.zoho.com/api/v1/salesorders?ignore_auto_number_generation=TRUE";
                                    $query = "organization_id=" . $zohoInventoryOrgID . "&authtoken=" . $zohoInventoryAuthToken . "&JSONString=" . $salesOrderDetails;
                                    $ch = curl_init();
                                    /* set url to send post request */
                                    curl_setopt($ch, CURLOPT_URL, $url);
                                    /* allow redirects */
                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                    /* return a response into a variable */
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                    /* times out after 30s */
                                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                    /* set POST method */
                                    curl_setopt($ch, CURLOPT_POST, 1);
                                    /* add POST fields parameters */
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                    $response = curl_exec($ch);
                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                    $zohoSalesOrderDetails = json_decode($response, TRUE);
                                    curl_close($ch);
                                    // Check, if Order has been suceessfully created
                                    if ($http_code === 201) {
                                        $flagOrder++;

                                    } else /* Order addition unsuccessful */ {
                                        $error = json_decode($response, TRUE);
                                        // Insert Error Data in Database
                                        $AccountHead = 'ORDER';
                                        $st = 'YES';
                                        $flagErr = SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding order: ' . $orders[$key]['id'] . ':' . $error['message']);
                                    }
                                } else {
                                    $error = json_decode($response, TRUE);
                                    // Error is not because of duplicate record
                                    if ($error['code'] != 3062) {
                                        // Insert Error Data in Database
                                        $AccountHead = 'CUSTOMER';
                                        $st = 'YES';
                                        $flagErr = SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Customer for Order: ' . $orders[$key]['id'] . ':' . $error['message']);
                                    }

                                }

                            } /// If customer successfully retrieved from BG.
                            // } // Temporary code. To be removed.
                        } // for each order on the page
                    }
                } // for each page of orders
            }
        } // if count of orders returned
    } // Try retrieving orders from BigCommerce
    catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }

    // Create reports.

    $status = 'YES';
    $totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
    $userDataArrTransaction = array(
        'user_id' => $credentials['userId'],
        'crm_type' => $crmType,
        'no_customer_data' => $flagCustomer,
        'no_product_data' => $flagProduct,
        'no_order_data' => $flagOrder,
        'last_sync_time' => time(),
        'total_transaction' => $totalTransaction,
        'status' => $status,
        'added_on' => time()
    );
    $credentials['counterOrders'] = $flagOrder;
    $credentials['counterProducts'] = $flagProduct;
    $credentials['counterAccounts'] = $flagCustomer;
    //for table transection detials
    tblTransectionDetail($credentials, $crmType, $userDataArrTransaction);

    sendSyncReport($credentials, $crmType);

    return $flagOrder;
}

function bigcommerceToVTigerCRM($credentials)
{
    /* including database related files */
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;
    $totalTransaction = 0;
    $zohoCRMAuthtoken = '';
    $access_token = '';
    $cloudApp = 'ZOHO';
    //

    $subscriptionId = $credentials['subscription_id'];
    $crmType = 'bigcommerceTovtiger';
    $bigcommURL = $credentials['app1Details']['bigcommerce_context'] . "/api";
    $bigcommerceUsername = $credentials['app1Details']['bigcommerce_user_name'];
    $bigcommerceAccessKey = $credentials['app1Details']['bigcommerce_password'];
    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];
    $vTigerUserName = $credentials['app2Details']['vtiger_username'];
    $vTigerAccessKey = $credentials['app2Details']['vtiger_key'];
    $vTigerEndpoint = $credentials['app2Details']['vtiger_endpoint'];

    // Get vTiger access token

    $url = $vTigerEndpoint . "/webservice.php?operation=getchallenge&username=" . $vTigerUserName;
    $http_headres = array(
        "Content-Type: application/json",
        "Accept: application/json",
        "cache-control : no-cache"
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code === 200) {
        curl_close($ch);
        $vTigerAuth = json_decode($return, TRUE);
        $vTigerAuthToken = $vTigerAuth['result']['token'];
    }

    $url = $vTigerEndpoint . "/webservice.php";
    $generatedKey = md5($vTigerAuthToken . $vTigerAccessKey);
    $postfields = array(
        'operation' => 'login',
        'username' => $vTigerUserName,
        'accessKey' => $generatedKey
    );

    // $postfieldsstring = json_encode($postfields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($http_code === 200) {
        curl_close($ch);
        $vTigerLogin = json_decode($return, TRUE);
        $vTigerSessionID = $vTigerLogin['result']['sessionName'];
        $vTigerUserID = $vTigerLogin['result']['userId'];
    }

    try {

        $retVal = FALSE;
        $total_success = 0;
        $access_token = base64_encode($bigcommerceUsername . ":" . $bigcommerceAccessKey);
        $url = $bigcommURL . "/v2/orders/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
        $http_headres = array(
            "Authorization: Basic " . trim($access_token),
            "Content-Type: application/json",
            "Accept: application/json",

        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        if ($http_code === 200) {
            curl_close($ch);
            $count_arr = json_decode($return, TRUE);
            $count = $count_arr['count'];

            if ($count > 0) {
                // Retrieve All List Available From vTiger..............

                $listTypeParams = "sessionName=$vTigerSessionID&operation=listtypes";
                $getListTypeUrl = $vTigerEndpoint . "/webservice.php?" . $listTypeParams;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $getListTypeUrl);
                curl_setopt($ch, CURLOPT_POST, 0);
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                $response = curl_exec($ch);
                curl_close($ch);
                $encode = json_decode($response, TRUE);
                $listType = $encode['result']['types'];

                $salesOrderExist = array_search("SalesOrder", $listType);

                $page = ceil($count / MAX_PAGE_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $url = $bigcommURL . "/v2/orders.json?page=" . $i . "&limit=" . MAX_PAGE_SIZE . "&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                    $return = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    // check if any orders exist for the page

                    if ($http_code === 200) {
                        $orders = json_decode($return, TRUE);


                        // Insert everything for the Order including Contact and Products.

                        foreach ($orders as $key => $value) {

                            $customer_id = $orders[$key]['customer_id'];

                            $order_id = $orders[$key]['id'];
                            $subject = 'Order Number ' . $orders[$key]['id'];
                            $currency_id = $orders[$key]['currency_id'];
                            $currencyExchangeRate = $orders[$key]['currency_exchange_rate'];
                            $discountAmount = $orders[$key]['discount_amount'];


                            $currentOrderStatus = $orders[$key]['status'];
                            if (($currentOrderStatus === 'Incomplete') || ($currentOrderStatus === 'Pending') || ($currentOrderStatus === 'Declined')) {
                                $salesOrder = 'FALSE';
                            } else {
                                $salesOrder = 'TRUE';
                            }

                            //ORDER PREVIOUSLY INSERT IN SALESORDER  CHECKING.
                            $orderExistQuery = "select count(*) from SalesOrder where subject='$subject';";

                            $queryOrderParam = urlencode($orderExistQuery);
                            $orderParams = "sessionName=$vTigerSessionID&operation=query&query=$queryOrderParam";
                            $getOrderUrl = $vTigerEndpoint . "/webservice.php?" . $orderParams;
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $getOrderUrl);
                            curl_setopt($ch, CURLOPT_POST, 0);
                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                            $response = curl_exec($ch);
                            curl_close($ch);
                            $collectedCount = json_decode($response, true);
                            $collectedSalesOrderCountNo = $collectedCount['result'][0]['count'];


                            //ORDER PREVIOUSLY INSERT IN QUOTE  CHECKING.

                            $quoteExistQuery = "select count(*) from Quotes where subject='$subject';";

                            $quoteOrderParam = urlencode($quoteExistQuery);
                            $quoteParams = "sessionName=$vTigerSessionID&operation=query&query=$quoteOrderParam";
                            $getQuoteUrl = $vTigerEndpoint . "/webservice.php?" . $quoteParams;
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $getQuoteUrl);
                            curl_setopt($ch, CURLOPT_POST, 0);
                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                            $response = curl_exec($ch);
                            curl_close($ch);
                            $collectedCount = json_decode($response, true);
                            $collectedQuoteCountNo = $collectedCount['result'][0]['count'];


                            if ($collectedSalesOrderCountNo == 0 && $collectedQuoteCountNo == 0) {
                                $customer_url = $bigcommURL . "/v2/customers/$customer_id.json";
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $customer_url);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                $return = curl_exec($ch);
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                                curl_close($ch);
                                if ($http_code === 200) {
                                    $customerDetails = json_decode($return, TRUE);
                                    $firstName = $customerDetails['first_name'];
                                    $lastName = $customerDetails['last_name'];
                                    $accountName = $firstName . ' ' . $lastName;
                                    $email = $customerDetails['email'];
                                    $phone = $customerDetails['phone'];


                                    /* retrieve address for the customer */
                                    $address_url = $customerDetails['addresses']['url'];
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $address_url);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                    $return = curl_exec($ch);
                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                    curl_close($ch);
                                    if ($http_code === 200) {
                                        $addressDetails = json_decode($return, TRUE);
                                        $mailingStreet = $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'];
                                        $otherStreet = $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'];
                                        $mailingCity = $addressDetails[0]['city'];
                                        $otherCity = $addressDetails[0]['city'];
                                        $mailingState = $addressDetails[0]['state'];
                                        $otherState = $addressDetails[0]['state'];
                                        $mailingZip = $addressDetails[0]['zip'];
                                        $otherZip = $addressDetails[0]['zip'];
                                        $mailingCountry = $addressDetails[0]['country'];
                                        $otherCountry = $addressDetails[0]['country'];
                                    } else {
                                        $mailingStreet = $orders[$key]['billing_address']['street_1'] . " " . $orders[$key]['billing_address']['street_2'];
                                        $otherStreet = $orders[$key]['billing_address']['street_1'] . " " . $orders[$key]['billing_address']['street_2'];
                                        $mailingCity = $orders[$key]['billing_address']['city'];
                                        $otherCity = $orders[$key]['billing_address']['city'];
                                        $mailingState = $orders[$key]['billing_address']['state'];
                                        $otherState = $orders[$key]['billing_address']['state'];
                                        $mailingZip = $orders[$key]['billing_address']['zip'];
                                        $otherZip = $orders[$key]['billing_address']['zip'];
                                        $mailingCountry = $orders[$key]['billing_address']['country'];
                                        $otherCountry = $orders[$key]['billing_address']['country'];
                                    }

                                    $flag = FALSE;
                                    try {

                                        //ACCOUNT EXIST OR NOT CHECKING

                                        $accountExistQuery = "select count(*) from Accounts where email1='$email';";


                                        $queryAccountParam = urlencode($accountExistQuery);
                                        $accountParams = "sessionName=$vTigerSessionID&operation=query&query=$queryAccountParam";
                                        $getAccountUrl = $vTigerEndpoint . "/webservice.php?" . $accountParams;
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $getAccountUrl);
                                        curl_setopt($ch, CURLOPT_POST, 0);
                                        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        curl_setopt($ch, CURLOPT_HEADER, 0);
                                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                                        $response = curl_exec($ch);
                                        curl_close($ch);
                                        $collectedCount = json_decode($response, true);
                                        $collectedCountNo = $collectedCount['result'][0]['count'];
                                        if ($collectedCountNo == 0) {
                                            // Insert Account details..................

                                            $accountInsertUrl = $vTigerEndpoint . "/webservice.php";
                                            $accountFields = array(
                                                'accountname' => $accountName,
                                                'assigned_user_id' => $vTigerUserID,
                                                'phone' => $phone,
                                                'email1' => $email,
                                                'bill_street' => $mailingStreet,
                                                'bill_city' => $mailingCity,
                                                'bill_state' => $mailingState,
                                                'bill_country' => $mailingCountry,
                                                'bill_code' => $mailingZip,
                                                'ship_street' => $otherStreet,
                                                'ship_city' => $otherCity,
                                                'ship_state' => $otherState,
                                                'ship_country' => $otherCountry,
                                                'ship_code' => $otherZip
                                            );
                                            $accountFieldsJSON = json_encode($accountFields);
                                            $moduleName = 'Accounts';
                                            $postfields = array(
                                                "sessionName" => $vTigerSessionID,
                                                "operation" => 'create',
                                                "element" => $accountFieldsJSON,
                                                "elementType" => $moduleName
                                            );
                                            $ch = curl_init();
                                            curl_setopt($ch, CURLOPT_URL, $accountInsertUrl);
                                            curl_setopt($ch, CURLOPT_POST, 1);
                                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                                            curl_setopt($ch, CURLOPT_HEADER, 0);
                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);


                                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                            $response = curl_exec($ch);
                                            curl_close($ch);


                                            $collectedResponseId = json_decode($response, true);
                                            $collectedAccountId = $collectedResponseId['result']['id'];

                                            // End of  Insert Account details..................

                                        } else {
                                            // Collection Account Id From Accounts..............

                                            $accountIdQuery = "select id from Accounts where email1='$email';";
                                            $queryParam = urlencode($accountIdQuery);
                                            $accountParams = "sessionName=$vTigerSessionID&operation=query&query=$queryParam";
                                            $getAccountUrl = $vTigerEndpoint . "/webservice.php?" . $accountParams;
                                            $ch = curl_init();
                                            curl_setopt($ch, CURLOPT_URL, $getAccountUrl);
                                            curl_setopt($ch, CURLOPT_POST, 0);
                                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                            curl_setopt($ch, CURLOPT_HEADER, 0);
                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                            $response = curl_exec($ch);

                                            curl_close($ch);
                                            $collectedResponseId = json_decode($response, true);
                                            $collectedAccountId = $collectedResponseId['result'][0]['id'];

                                            // End ofCollection Account Id From Accounts..............
                                        }


                                        //CONTACT EXIST OR NOT CHECKING
                                        $contactExistQuery = "select count(*) from Contacts where email='$email';";


                                        $queryContactParam = urlencode($contactExistQuery);
                                        $contactParams = "sessionName=$vTigerSessionID&operation=query&query=$queryContactParam";
                                        $getContactUrl = $vTigerEndpoint . "/webservice.php?" . $contactParams;
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $getContactUrl);
                                        curl_setopt($ch, CURLOPT_POST, 0);
                                        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        curl_setopt($ch, CURLOPT_HEADER, 0);
                                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

                                        $response = curl_exec($ch);
                                        curl_close($ch);
                                        $collectedCount = json_decode($response, true);

                                        $collectedCountNo = $collectedCount['result'][0]['count'];
                                        if ($collectedCountNo == 0) {
                                            // Create Contact for this account..................
                                            $contactInsertUrl = $vTigerEndpoint . "/webservice.php";
                                            $contactData = array(
                                                'firstname' => $firstName,
                                                'lastname' => $lastName,
                                                'assigned_user_id' => $vTigerUserID,
                                                'contacttype' => 'Primary',
                                                'account_id' => $collectedAccountId,
                                                'email' => $email,
                                                'mobile' => $phone,
                                                'mailingstreet' => $mailingStreet,
                                                'otherstreet' => $otherStreet,
                                                'mailingcity' => $mailingCity,
                                                'othercity' => $otherCity,
                                                'mailingstate' => $mailingState,
                                                'otherstate' => $otherState,
                                                'mailingzip' => $mailingZip,
                                                'otherzip' => $otherZip,
                                                'mailingcountry' => $mailingCountry,
                                                'othercountry' => $otherCountry
                                            );
                                            $contactFieldsJSON = json_encode($contactData);


                                            $moduleName = 'Contacts';
                                            $contactParams = array(
                                                "sessionName" => $vTigerSessionID,
                                                "operation" => 'create',
                                                "element" => $contactFieldsJSON,
                                                "elementType" => $moduleName
                                            );
                                            $ch = curl_init();
                                            curl_setopt($ch, CURLOPT_URL, $contactInsertUrl);
                                            curl_setopt($ch, CURLOPT_POST, 1);
                                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                            curl_setopt($ch, CURLOPT_POSTFIELDS, $contactParams);
                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                            $response = curl_exec($ch);
                                            $collectedResponseContactId = json_decode($response, true);

                                            $collectedContactId = $collectedResponseContactId['result']['id'];
                                            curl_close($ch);
                                            $responseValue = json_decode($response, TRUE);
                                            if ($responseValue['success'] == 'true') {
                                                $flagCustomer++;
                                            } else {
                                                $resCode = $responseValue['error']['code'];
                                                $resMsg = $responseValue['error']['message'];
                                                $AccountHead = 'CUSTOMER';
                                                $st = 'YES';
                                                SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Customer: ' . $accountName . ' Error Code: ' . $resCode . 'Reason:' . $resMsg);
                                                //$flag = FALSE;
                                            }
                                        } else {
                                            // Collection Contact Id From Contacts..............

                                            $contactIdQuery = "select id from Contacts where email='$email';";
                                            $queryParam = urlencode($contactIdQuery);
                                            $contactParams = "sessionName=$vTigerSessionID&operation=query&query=$queryParam";
                                            $getContactUrl = $vTigerEndpoint . "/webservice.php?" . $contactParams;
                                            $ch = curl_init();
                                            curl_setopt($ch, CURLOPT_URL, $getContactUrl);
                                            curl_setopt($ch, CURLOPT_POST, 0);
                                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                            curl_setopt($ch, CURLOPT_HEADER, 0);
                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                            $response = curl_exec($ch);
                                            curl_close($ch);
                                            $collectedResponseContactId = json_decode($response, true);
                                            $collectedContactId = $collectedResponseContactId['result'][0]['id'];
                                            // End ofCollection Account Id From Accounts..............
                                        }

                                        // Contact addition successful. Proceed with addtion of Product and Sales Order

                                        $subject = 'Order Number ' . $orders[$key]['id'];
                                        $grandTotal = $orders[$key]['total_inc_tax'];
                                        $subTotal = $orders[$key]['subtotal_ex_tax'];
                                        $tax = $orders[$key]['total_tax'];
                                        $adjustment = ($orders[$key]['total_inc_tax'] - $orders[$key]['subtotal_inc_tax'])-$tax;
                                        $orderBillingStreet = $orders[$key]['billing_address']['street_1'] . $orders[$key]['billing_address']['street_2'];
                                        $orderBillingCity = $orders[$key]['billing_address']['city'];
                                        $orderBillingState = $orders[$key]['billing_address']['state'];
                                        $orderBillingZip = $orders[$key]['billing_address']['zip'];
                                        $orderBillingCountry = $orders[$key]['billing_address']['country'];

                                        // retrieve shipping address

                                        $orderShippingURL = $orders[$key]['shipping_addresses']['url'];
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $orderShippingURL);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                        $return = curl_exec($ch);
                                        curl_close($ch);
                                        $orderShippingAddresses = json_decode($return, TRUE);
                                        $orderShippingStreet = $orderShippingAddresses[0]['street_1'] . $orderShippingAddresses[0]['street_2'];
                                        $orderShippingCity = $orderShippingAddresses[0]['city'];
                                        $orderShippingState = $orderShippingAddresses[0]['state'];
                                        $orderShippingZip = $orderShippingAddresses[0]['zip'];
                                        $orderShippingCountry = $orderShippingAddresses[0]['country'];

                                        /* Insert Products in vTiger CRM, if not already present */
                                        $product_url = $orders[$key]['products']['url'];
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $product_url);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                        $return = curl_exec($ch);
                                        curl_close($ch);
                                        $recordSet = json_decode($return, TRUE);


                                        $lineitem = [];
                                        $items = [];
                                        /* if multiple sku available */
                                        if (count($recordSet) > 0) {
                                            $prodNumber = 0;
                                            foreach ($recordSet as $k => $val) {
                                                $prodSku = $val['sku'];

                                                $m_sku_id = $val['product_id'];
                                                $prodCode = $m_sku_id;
                                                $m_adjusted_price = $val['price_inc_tax'];
                                                $prodName = $val['name'];
                                                $m_quantity = $val['quantity'];
                                                $prodListPrice = $val['base_price'];
                                                $prodUnitPrice = $val['price_ex_tax'];
                                                $total = $val['base_total'];
                                                $totalAfterDiscount = $val['total_ex_tax'];
                                                $discount = $total - $totalAfterDiscount;
                                                $tax = $val['total_tax'];
                                                $netTotal = $val['total_inc_tax'];

                                                // Product exist or Not checking
                                                $productExistQuery = "select count(*) from Products where productcode='$prodCode';";

                                                $queryProductParam = urlencode($productExistQuery);
                                                $productParams = "sessionName=$vTigerSessionID&operation=query&query=$queryProductParam";
                                                $getProductUrl = $vTigerEndpoint . "/webservice.php?" . $productParams;
                                                $ch = curl_init();
                                                curl_setopt($ch, CURLOPT_URL, $getProductUrl);
                                                curl_setopt($ch, CURLOPT_POST, 0);
                                                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                                curl_setopt($ch, CURLOPT_HEADER, 0);
                                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                                                $response = curl_exec($ch);
                                                curl_close($ch);
                                                $collectedCount = json_decode($response, true);


                                                $collectedCountNo = $collectedCount['result'][0]['count'];

                                                // For Insert Records with duplicate checking.

                                                if ($collectedCountNo == 0) {

                                                    //Product details collect from bigcommerce
                                                    $product_url = $bigcommURL . "/v2/products?sku={$prodSku}";
                                                    $ch = curl_init();
                                                    curl_setopt($ch, CURLOPT_URL, $product_url);
                                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                                    $return = curl_exec($ch);

                                                    $bigCommerceProduct = json_decode($return, TRUE);

                                                    $description = $bigCommerceProduct[0]['description'];


                                                    // INSERT VTIGER PRODUCT
                                                    $productInsertUrl = $vTigerEndpoint . "/webservice.php";
                                                    $productData = array(
                                                        'productcode' => $prodCode,
                                                        'productname' => $prodName,
                                                        'discontinued' => 1,
                                                        "comment" => '',
                                                        "qty_per_unit" => $m_quantity,
                                                        "list_price" => $prodListPrice,
                                                        "unit_price" => $prodUnitPrice,
                                                        "isclosed" => 1,
                                                        "currency1" => 0,
                                                        'description' => $description,
                                                        "currency_id" => "21x1",
                                                        "hdnTaxType" => "individual",
                                                        "hdnProductId" => $prodSku,
                                                        "assigned_user_id" => $vTigerUserID,
                                                        "totalProductCount" => $m_quantity
                                                    );
                                                    $productFieldsJSON = json_encode($productData);
                                                    $moduleName = 'Products';
                                                    $productFields = array(
                                                        "operation" => 'create',
                                                        "sessionName" => $vTigerSessionID,
                                                        "element" => $productFieldsJSON,
                                                        "elementType" => $moduleName
                                                    );


                                                    $ch = curl_init();
                                                    curl_setopt($ch, CURLOPT_URL, $productInsertUrl);
                                                    curl_setopt($ch, CURLOPT_POST, 1);
                                                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $productFields);
                                                    curl_setopt($ch, CURLOPT_HEADER, 0);
                                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                                    $response = curl_exec($ch);
                                                    curl_close($ch);


                                                    $collectedResponseId = json_decode($response, true);
                                                    $collectedProductId = $collectedResponseId['result']['id'];
                                                    $collectedProductBasePrice = $collectedResponseId['result']['unit_price'];
                                                    $collectedProductQuantity = $collectedResponseId['result']['qty_per_unit'];


                                                    $responseValue = json_decode($response, TRUE);
                                                    // End of Sales Orders.................


                                                    if ($responseValue['success'] == 'true') {
                                                        $flagProduct++;
                                                    } else {
                                                        $resCode = $responseValue['error']['code'];
                                                        $resMsg = $responseValue['error']['message'];
                                                        $flag = FALSE;
                                                        $st = 'YES';
                                                        $AccountHead = 'PRODUCT';
                                                        SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Product : ' . $prodName . ' Error Code: ' . $resCode . ' Reason:' . $resMsg);

                                                    }
                                                } else {

                                                    // Collection Product Id From Products..............

                                                    $productIdQuery = "select * from Products where productcode='$prodCode';";

                                                    $queryProductParam = urlencode($productIdQuery);
                                                    $accountParams = "sessionName=$vTigerSessionID&operation=query&query=$queryProductParam";
                                                    $getAccountUrl = $vTigerEndpoint . "/webservice.php?" . $accountParams;

                                                    $ch = curl_init();
                                                    curl_setopt($ch, CURLOPT_URL, $getAccountUrl);
                                                    curl_setopt($ch, CURLOPT_POST, 0);
                                                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                                    curl_setopt($ch, CURLOPT_HEADER, 0);
                                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                                                    $response = curl_exec($ch);
                                                    $collectedResponseId = json_decode($response, true);

                                                    $collectedProductId = $collectedResponseId['result'][0]['id'];
                                                    $collectedProductBasePrice = $collectedResponseId['result'][0]['unit_price'];
                                                    $collectedProductQuantity = $collectedResponseId['result'][0]['qty_per_unit'];

                                                    // End of Collection Product Id From Products..............
                                                }

                                                $lineitem[$prodNumber] = ['productid' => $collectedProductId, 'listprice' => $collectedProductBasePrice, 'quantity' => $collectedProductQuantity, 'discount_amount' => $discount, 'tax1' => $tax, 'netprice' => $netTotal];
                                                $prodNumber++;
                                            } // for each product


                                        } // if Product SKUs available.


                                        if ($salesOrder === 'TRUE') {

                                            // Start of Sales Order ..............

                                            if ($salesOrderExist >= 0) {

                                                $orderInsertUrl = $vTigerEndpoint . "/webservice.php";
                                                $salesOrderData = array(
                                                    'subject' => $subject,
                                                    'sostatus' => 'Created',
                                                    'account_id' => $collectedAccountId,
                                                    'bill_street' => $orderBillingStreet,
                                                    'bill_city' => $orderBillingCity,
                                                    'bill_code' => $orderBillingZip,
                                                    'bill_country' => $orderBillingCountry,
                                                    'bill_state' => $orderBillingState,
                                                    'assigned_user_id' => $vTigerUserID,
                                                    'contact_id' => $collectedContactId,
                                                    'adjustment' => $adjustment,
                                                    'exciseduty' => $tax,
                                                    'subtotal' => $subTotal,
                                                    'total' => $grandTotal,
                                                    'ship_city' => $orderShippingCity,
                                                    'ship_street' => $orderShippingStreet,
                                                    'ship_code' => $orderShippingZip,
                                                    'ship_country' => $orderShippingCountry,
                                                    'ship_state' => $orderShippingState,
                                                    'invoicestatus' => 'AutoCreated',
                                                    'status' => $currentOrderStatus,
                                                    'LineItems' => $lineitem,
                                                    'currency_id' => '21x1',
                                                    'hdntaxtype' => 'group',
                                                    'conversion_rate' => $currencyExchangeRate,
                                                    'hdndiscountamount' => $discountAmount,
                                                    'hdnGrandTotal' => $grandTotal,
                                                    'hdnSubTotal' => $subTotal,
                                                );


                                                $salesOrderFieldsJSON = json_encode($salesOrderData);
                                                $moduleName = 'SalesOrder';


                                                $salesOrderFields = array(
                                                    "operation" => 'create',
                                                    "sessionName" => $vTigerSessionID,
                                                    "element" => $salesOrderFieldsJSON,
                                                    "elementType" => $moduleName
                                                );

                                                $ch = curl_init();
                                                curl_setopt($ch, CURLOPT_URL, $orderInsertUrl);
                                                curl_setopt($ch, CURLOPT_POST, 1);
                                                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                                curl_setopt($ch, CURLOPT_POSTFIELDS, $salesOrderFields);
                                                curl_setopt($ch, CURLOPT_HEADER, 0);
                                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

                                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                                $response = curl_exec($ch);
                                                curl_close($ch);

                                                $responseValue = json_decode($response, TRUE);
                                                // End of Sales Orders.................


                                                if ($responseValue['success'] == 'true') {
                                                    $flagOrder++;
                                                } else {

                                                    $resCode = $responseValue['error']['code'];
                                                    $resMsg = $responseValue['error']['message'];
                                                    $flag = FALSE;
                                                    $st = 'YES';
                                                    $AccountHead = 'ORDER';
                                                    SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Order Number: ' . $orders[$key]['id'] . ' Error Code: ' . $resCode . ' Reason:' . $resMsg);

                                                }

                                            }
                                        } else {
                                            $quoteInsertUrl = $vTigerEndpoint . "/webservice.php";
                                            $productQuote = array(
                                                "subject" => $subject,
                                                'account_id' => $collectedAccountId,
                                                "quotestage" => 'created',
                                                "contact_id" => $collectedContactId,
                                                'bill_street' => $orderBillingStreet,
                                                'bill_city' => $orderBillingCity,
                                                'bill_code' => $orderBillingZip,
                                                'bill_country' => $orderBillingCountry,
                                                'bill_state' => $orderBillingState,
                                                'ship_city' => $orderShippingCity,
                                                'ship_street' => $orderShippingStreet,
                                                'ship_code' => $orderShippingZip,
                                                'ship_country' => $orderShippingCountry,
                                                'ship_state' => $orderShippingState,
                                                'assigned_user_id' => $vTigerUserID,
                                                "LineItems" => $lineitem
                                            );
                                            $productQuoteFieldsJSON = json_encode($productQuote);
                                            $moduleName = 'Quotes';
                                            $productQuotefields = array(
                                                "operation" => 'create',
                                                "sessionName" => $vTigerSessionID,
                                                "element" => $productQuoteFieldsJSON,
                                                "elementType" => $moduleName
                                            );

                                            // Execute cUrl session

                                            $ch = curl_init();
                                            curl_setopt($ch, CURLOPT_URL, $quoteInsertUrl);
                                            curl_setopt($ch, CURLOPT_POST, 1);
                                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                            curl_setopt($ch, CURLOPT_POSTFIELDS, $productQuotefields);
                                            curl_setopt($ch, CURLOPT_HEADER, 0);
                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                                            $response = curl_exec($ch);
                                            curl_close($ch);


                                            $collectedResponse = json_decode($response, true);
                                            $collectedQuoteId = $collectedResponse['result']['id'];

                                            if ($collectedResponse['success'] == 'true') {
                                                $flagOrder++;
                                            } else {

                                                $resCode = $collectedResponse['error']['code'];
                                                $resMsg = $collectedResponse['error']['message'];
                                                $flag = FALSE;
                                                $st = 'YES';
                                                $AccountHead = 'ORDER';
                                                SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Order Number: ' . $orders[$key]['id'] . ' Error Code: ' . $resCode . ' Reason:' . $resMsg);

                                            }
                                            // End of Quotes Insert .................

                                        }


                                    } catch (Exception $e) {


                                        $msg = $e;
                                        $flag = FALSE;
                                    }
                                } /// If customer successfully retrieved from BG.


                            }//previously quote or salesorder if not insert

                        } // for each order on the page
                    }
                } // for each page of orders
            }


        } // if count of orders returned

    } // Try retrieving orders from BigCommerce
    catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }

    // Create reports.

    $status = 'YES';
    $totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
    $userDataArrTransaction = array(
        'user_id' => $credentials['userId'],
        'crm_type' => $crmType,
        'no_customer_data' => $flagCustomer,
        'no_product_data' => $flagProduct,
        'no_order_data' => $flagOrder,
        'last_sync_time' => time(),
        'total_transaction' => $totalTransaction,
        'status' => $status,
        'added_on' => time()
    );
    $credentials['counterOrders'] = $flagOrder;
    $credentials['counterProducts'] = $flagProduct;
    $credentials['counterAccounts'] = $flagCustomer;
    include "/var/www/html/app/mysql/mysqlconstants.php";

    tblTransectionDetail($credentials, $crmType, $userDataArrTransaction);

    sendSyncReport($credentials, $crmType);

    return $flagOrder;
} //Done

function bigcommerceToSfdcCRM($credentials)
{


    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;
    $totalTransaction = 0;
    //$zohoCRMAuthtoken = '';
    $access_token = '';

    //$uID = $credentials['user_id'];
    $subscriptionId = $credentials['subscription_id'];
    $crmType = 'bigcommerceTosfdc';//$credentials['crm_type'];
    //$crmType = $credentials['crm_type'];
    //$contextVal = $credentials['contextValue'];
    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];
    //$access_token = $credentials['bigCommerceAccessToken'];
    //$accountType = $credentials['account_type'];
    $sfdcCredentials = $credentials['app2Details'];
    $priceBookInfo = Sfdc::getPriceBookDetails($sfdcCredentials);
    $credentials['stdPriceBookId'] = $priceBookInfo->Id;
    $cloudApp = 'SFDC';

    $bigcommURL = $credentials['app1Details']['bigcommerce_context'] . "/api";
    $bigcommerceUsername = $credentials['app1Details']['bigcommerce_user_name'];
    $bigcommerceAccessKey = $credentials['app1Details']['bigcommerce_password'];
    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];


    try {

        $retVal = FALSE;
        $total_success = 0;
        $access_token = base64_encode($bigcommerceUsername . ":" . $bigcommerceAccessKey);
        $url = $bigcommURL . "/v2/orders/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
        $http_headres = array(
            "Authorization: Basic " . trim($access_token),
            "Content-Type: application/json",
            "Accept: application/json",

        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code === 200) {
            curl_close($ch);
            $count_arr = json_decode($return, TRUE);
            $count = $count_arr['count'];

            if ($count > 0) {
                $page = ceil($count / MAX_PAGE_SIZE);

                for ($i = 1; $i <= $page; $i++) {
                    $url = $bigcommURL . "/v2/orders.json?page=" . $i . "&limit=" . MAX_PAGE_SIZE . "&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                    $return = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    // check if any orders exist for the page

                    if ($http_code === 200) {
                        $orders = json_decode($return, TRUE);

                        // Insert everything for the Order including Contact and Products.

                        foreach ($orders as $key => $value) {

                            $currentOrderID = $orders[$key]['id'];

                            // $sfdcQuery = "Select Id FROM Account WHERE AccountNumber = '$customer_id' ";

                            $sfdcQuery = "Select Id FROM Order WHERE OrderReferenceNumber = '$currentOrderID' ";
                            $queryResult = Sfdc::sfdcFindRecord($sfdcCredentials, $sfdcQuery);
                            if ($queryResult->size === 0) // order doesn't exist
                            {
                                $customer_id = $orders[$key]['customer_id'];

                                $currentOrderStatus = $orders[$key]['status'];

                                // Search for existing Account

                                $sfdcQuery = "Select Id, Name FROM Account WHERE AccountNumber = '$customer_id' ";
                                $queryResult = Sfdc::sfdcFindRecord($sfdcCredentials, $sfdcQuery);
                                if ($queryResult->size === 0) // no existing Account
                                {
                                    $customer_url = $bigcommURL . "/v2/customers/$customer_id.json";
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $customer_url);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                    $return = curl_exec($ch);
                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


                                    curl_close($ch);
                                    if ($http_code === 200) {
                                        $customerDetails = json_decode($return, TRUE);
                                        $firstName = $customerDetails['first_name'];
                                        $lastName = $customerDetails['last_name'];
                                        if ($customerDetails['company'] !== NULL) {
                                            $accountName = $customerDetails['company'];
                                        } else {
                                            $accountName = $firstName . ' ' . $lastName;
                                        }
                                        /* retrieve address for the customer */
                                        $address_url = $customerDetails['addresses']['url'];
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $address_url);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                        $return = curl_exec($ch);
                                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                        curl_close($ch);

                                        // echo("Address: ");
                                        // echo $return;

                                        $accounts = array();
                                        $accounts[0] = new stdclass();
                                        if ($http_code === 200) {
                                            $addressDetails = json_decode($return, TRUE);
                                            $accounts[0]->BillingCity = $addressDetails[0]['city'];
                                            $accounts[0]->BillingCountry = $addressDetails[0]['country'];
                                            $accounts[0]->BillingPostalCode = $addressDetails[0]['zip'];
                                            $accounts[0]->BillingState = $addressDetails[0]['state'];
                                            $accounts[0]->BillingStreet = $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'];
                                            $accounts[0]->ShippingCity = $addressDetails[0]['city'];
                                            $accounts[0]->ShippingCountry = $addressDetails[0]['country'];
                                            $accounts[0]->ShippingPostalCode = $addressDetails[0]['zip'];
                                            $accounts[0]->ShippingState = $addressDetails[0]['state'];
                                            $accounts[0]->ShippingStreet = $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'];
                                        } else {
                                            $accounts[0]->BillingStreet = $orders[$key]['billing_address']['street_1'] . " " . $orders[$key]['billing_address']['street_2'];
                                            $accounts[0]->ShippingStreet = $orders[$key]['billing_address']['street_1'] . " " . $orders[$key]['billing_address']['street_2'];
                                            $accounts[0]->BillingCity = $orders[$key]['billing_address']['city'];
                                            $accounts[0]->ShippingCity = $orders[$key]['billing_address']['city'];
                                            $accounts[0]->BillingState = $orders[$key]['billing_address']['state'];
                                            $accounts[0]->ShippingState = $orders[$key]['billing_address']['state'];
                                            $accounts[0]->BillingPostalCode = $orders[$key]['billing_address']['zip'];
                                            $accounts[0]->ShippingPostalCode = $orders[$key]['billing_address']['zip'];
                                            $accounts[0]->BillingCountry = $orders[$key]['billing_address']['country'];
                                            $accounts[0]->ShippingCountry = $orders[$key]['billing_address']['country'];
                                        }

                                        /* Insert new Account in sfdc */
                                        $accounts[0]->Name = $accountName;
                                        $accounts[0]->AccountNumber = $customer_id;

                                        // $accounts[0]->Email = $customerDetails['email'];

                                        $accounts[0]->Phone = $customerDetails['phone'];
                                        $createAccountRes = Sfdc::createSfdcAccount($sfdcCredentials, $accounts);
                                        $accountId = $createAccountRes->id;
                                        $oldAccount = new stdclass();
                                        $resCode = $createAccountRes->success;
                                        if ($resCode == 0) // error in inserting Account
                                        {
                                            $createAccountError = $createAccountRes->errors[0]->duplicateResult->matchResults[0]->matchRecords[0]->record->Id;
                                            // check, if duplicate exists.

                                            if ($createAccountRes->errors[0]->statusCode === 'DUPLICATES_DETECTED') {
                                                //update records
                                                $oldAccount->Id = $createAccountError;
                                                $accounts[0]->Id = $oldAccount->Id; /*
											$SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
                                                        				$SfdcUsername = $sfdcCredentials['sfdc_user_name'];
                                                        				$SfdcPassword = $sfdcCredentials['sfdc_password'];
                                                        				$SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];
                                                        				$mySforceConnection = new SforceEnterpriseClient();
                                                        				$mySforceConnection->createConnection($SfdcWsdl);
                                                        				$mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
                                                        				$response = $mySforceConnection->update(array($oldAccount,$accounts[0]), 'Account');
											*/
                                                $response = Sfdc::updateSfdcAccount($sfdcCredentials, $accounts);
                                            }
                                            $st = 'YES';
                                            $AccountHead = 'ACCOUNT';
                                            // SaveErrorDetails fails when $prodName has special characters used in MySQL
                                            SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Account ' . $customer_id);

                                        }

                                        // Create Contacts

                                        $contacts = array();
                                        $contacts[0] = new stdclass();
                                        $contacts[0]->LastName = $lastName;
                                        $contacts[0]->FirstName = $firstName;
                                        $contacts[0]->AccountId = $accountId;
                                        $contacts[0]->Email = $customerDetails['email'];
                                        $contacts[0]->Phone = $customerDetails['phone'];
                                        $contacts[0]->MailingCity = $accounts[0]->BillingCity;
                                        $contacts[0]->MailingCountry = $accounts[0]->BillingCountry;
                                        $contacts[0]->MailingPostalCode = $accounts[0]->BillingPostalCode;
                                        $contacts[0]->MailingState = $accounts[0]->BillingState;
                                        $contacts[0]->MailingStreet = $accounts[0]->BillingStreet;
                                        $contacts[0]->OtherCity = $accounts[0]->ShippingStreet;
                                        $contacts[0]->OtherCountry = $accounts[0]->ShippingCountry;
                                        $contacts[0]->OtherPostalCode = $accounts[0]->ShippingPostalCode;
                                        $contacts[0]->OtherState = $accounts[0]->ShippingState;
                                        $contacts[0]->OtherStreet = $accounts[0]->ShippingStreet;

                                        $createAccountRes = Sfdc::createSfdcContact($sfdcCredentials, $contacts);
                                    }
                                } else // Account already exists

                                {
                                    $accountId = $queryResult->records[0]->Id;
                                    $accountName = $queryResult->records[0]->Name;
                                    $resCode = 2; // account exists
                                }
                                if ($resCode > 0) {

                                    // $flag = TRUE;

                                    if ($resCode == 1) {
                                        $flagCustomer++;
                                    }

                                    // Contact addition successful. Proceed with addtion of Product and Sales Order

                                    /* Insert Sales Order  */
                                    $sfdcOrders = array();
                                    $sfdcOrders[0] = new stdclass();
                                    $subject = 'Order Number ' . $orders[$key]['id'];
                                    $grandTotal = $orders[$key]['total_inc_tax'];
                                    $subTotal = $orders[$key]['subtotal_ex_tax'];
                                    $tax = $orders[$key]['total_tax'];
                                    $adjustment = $orders[$key]['total_inc_tax'] - $orders[$key]['subtotal_inc_tax'];


                                    $sfdcOrders[0]->BillingStreet = $orders[$key]['billing_address']['street_1'] . $orders[$key]['billing_address']['street_2'];
                                    $sfdcOrders[0]->BillingCity = $orders[$key]['billing_address']['city'];
                                    $sfdcOrders[0]->BillingState = $orders[$key]['billing_address']['state'];
                                    $sfdcOrders[0]->BillingPostalCode = $orders[$key]['billing_address']['zip'];
                                    $sfdcOrders[0]->BillingCountry = $orders[$key]['billing_address']['country'];

                                    // retrieve shipping address

                                    $orderShippingURL = $orders[$key]['shipping_addresses']['url'];
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $orderShippingURL);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                    $return = curl_exec($ch);
                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                    curl_close($ch);
                                    $orderShippingAddresses = json_decode($return, TRUE);
                                    $sfdcOrders[0]->ShippingStreet = $orderShippingAddresses[0]['street_1'] . $orderShippingAddresses[0]['street_2'];
                                    $sfdcOrders[0]->ShippingCity = $orderShippingAddresses[0]['city'];
                                    $sfdcOrders[0]->ShippingState = $orderShippingAddresses[0]['state'];
                                    $sfdcOrders[0]->ShippingPostalCode = $orderShippingAddresses[0]['zip'];
                                    $sfdcOrders[0]->ShippingCountry = $orderShippingAddresses[0]['country'];

                                    // $Orders[0]->Account = $accountName;

                                    //$accounts[0]->AccountId = $accountId;
                                    $sfdcOrders[0]->AccountId = $accountId;

                                    // $sfdcOrders[0]->TotalAmount =  $orders[$key]['total_inc_tax'];

                                    $sfdcOrders[0]->Description = $subject;
                                    $sfdcOrders[0]->Name = $subject;
                                    $salesOrderDate = DateTime::createFromFormat(DATE_RFC2822, $orders[$key]['date_created']);
                                    $sfdcOrders[0]->EffectiveDate = $salesOrderDate->format('Y-m-d');
                                    $sfdcOrders[0]->OrderReferenceNumber = $orders[$key]['id'];
                                    $sfdcOrders[0]->Pricebook2Id = $credentials['stdPriceBookId'];
                                    $currentOrderStatus = $orders[$key]['status'];
                                    $sfdcOpportunity = array();
                                    $sfdcOpportunity[0] = new stdclass();
                                    if (($currentOrderStatus === 'Incomplete') || ($currentOrderStatus === 'Pending') || ($currentOrderStatus === 'Declined')) {

                                        // $sfdcOrders[0]->StatusCode = 'Draft';

                                        $sfdcOrders[0]->Status = 'Draft';
                                        $sfdcOpportunity[0]->StageName = 'Proposal';
                                    } else {

                                        // $sfdcOrders[0]->Status = 'Activated';
                                        // $sfdcOrders[0]->StatusCode = 'Draft';

                                        $sfdcOrders[0]->Status = 'Draft';
                                        $sfdcOpportunity[0]->StageName = 'Closed Won';
                                    }

                                    // $Orders[0]->Pricebook2Id = $s_priceBookId;
                                    // Create Opportunity fields.
                                    $sfdcOpportunity[0]->AccountId = $accountId;
                                    $sfdcOpportunity[0]->CloseDate = $sfdcOrders[0]->EffectiveDate;
                                    $sfdcOpportunity[0]->Name = $sfdcOrders[0]->Name;
                                    //$sfdcOpportunity[0]->StageName = 'Closed Won';
                                    $sfdcOpportunity[0]->Amount = $grandTotal;
                                    $sfdcOpportunity[0]->Description = $sfdcOrders[0]->Description;
                                    $sfdcOpportunity[0]->Pricebook2Id = $sfdcOrders[0]->Pricebook2Id;
                                    $opportunityDetails = Sfdc::createOpportunity($sfdcCredentials, $sfdcOpportunity);
                                    $orderDetails = Sfdc::createOrder($sfdcCredentials, $sfdcOrders);
                                    if ($orderDetails->success == TRUE) {
                                        $flagOrder++;
                                    } else // error in inserting Order
                                    {
                                        $st = 'YES';
                                        $AccountHead = 'ORDER';
                                        // SaveErrorDetails fails when $prodName has special characters used in MySQL
                                        SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Order ' . $orders[$key]['id']);
                                    }
                                    /* Insert Products in Zoho CRM, if not already present */
                                    $product_url = $orders[$key]['products']['url'];
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $product_url);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                    $return = curl_exec($ch);
                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                    curl_close($ch);
                                    $recordSet = json_decode($return, TRUE);
                                    /* if multiple sku available */
                                    if (count($recordSet) > 0) {
                                        $prodNumber = 0;
                                        $orderItem = array();
                                        $opportunityLineItem = array();
                                        foreach ($recordSet as $k => $val) {
                                            $prodCode = $val['sku'];
                                            $sfdcQuery = "Select Id FROM PricebookEntry WHERE ProductCode = '$prodCode'";
                                            $queryResult = Sfdc::sfdcFindRecord($sfdcCredentials, $sfdcQuery);
                                            if ($queryResult->size != 0) // Product exists
                                            {
                                                $productPricebookEntryId = $queryResult->records[0]->Id;
                                                // Update Product
                                                $priceEntry = array();
                                                $priceEntry[0] = new stdclass();
                                                $priceEntry[0]->Id = $productPricebookEntryId;
                                                $priceEntry[0]->IsActive = TRUE;
                                                $priceEntry[0]->UnitPrice = $val['base_price'];
                                                $priceEntry[0]->UseStandardPrice = FALSE;
                                                $response = Sfdc::updatePriceBook($sfdcCredentials, $priceEntry);
                                            } else {
                                                /* insert into products */
                                                $products = array();
                                                $products[0] = new stdclass();
                                                $products[0]->Name = $val['name'];;
                                                $products[0]->IsActive = TRUE;
                                                $products[0]->ProductCode = $val['sku'];
                                                $products[0]->Description = $val['name']; // Check description
                                                $productRes = Sfdc::createSfdcProducts($sfdcCredentials, $products);
                                                if ($productRes->success == TRUE) {
                                                    $flagProduct++;
                                                } else // error in inserting Product
                                                {
                                                    $AccountHead = 'PRODUCT';
                                                    // SaveErrorDetails fails when $prodName has special characters used in MySQL
                                                    SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Product ' . $val['sku']);
                                                }

                                                $insertedProductId = $productRes->id;
                                                $priceEntry = array();
                                                $priceEntry[0] = new stdclass();
                                                $priceEntry[0]->Pricebook2Id = $credentials['stdPriceBookId'];
                                                $priceEntry[0]->IsActive = TRUE;
                                                $priceEntry[0]->Product2Id = $insertedProductId;
                                                $priceEntry[0]->UnitPrice = $val['base_total'];
                                                $priceEntry[0]->UseStandardPrice = FALSE;
                                                $addedId = Sfdc::addToPriceBook($sfdcCredentials, $priceEntry);
                                                $productPricebookEntryId = $addedId->id;
                                            }

                                            // Add Order Item

                                            $orderItem[$k] = new stdclass();
                                            $orderItem[$k]->PricebookEntryId = $productPricebookEntryId;
                                            $orderItem[$k]->OrderId = $orderDetails->id;
                                            $orderItem[$k]->Quantity = $val['quantity'];
                                            $orderItem[$k]->UnitPrice = $val['base_price'];
                                            if ($opportunityDetails->success) {
                                                // create Opportunity line item
                                                $opportunityLineItem[$k] = new stdclass();
                                                $opportunityLineItem[$k]->PricebookEntryId = $orderItem[$k]->PricebookEntryId;
                                                $opportunityLineItem[$k]->OpportunityId = $opportunityDetails->id;
                                                $opportunityLineItem[$k]->Quantity = $orderItem[$k]->Quantity;
                                                $opportunityLineItem[$k]->UnitPrice = $orderItem[$k]->UnitPrice;
                                            }
                                        } // for each product
                                        $orderItemRes = Sfdc::createOrderItem($sfdcCredentials, $orderItem);
                                        $opportunityItemRes = Sfdc::addProductToOpportunity($sfdcCredentials, $opportunityLineItem);


                                    } // if Product SKUs available.
                                } else {

                                    $AccountHead = 'CUSTOMER';

                                    SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Customer: ' . $accountName);
                                    $flag = FALSE;
                                }
                            } /// If customer successfully retrieved from BG.
                        }
                    }
                } // for each page of orders
            }
        } // if count of orders returned
    } // Try retrieving orders from BigCommerce
    catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }

    // Create reports.

    $status = 'YES';
    $totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
    $userDataArrTransaction = array(
        'user_id' => $credentials['userId'],
        'crm_type' => $crmType,
        'no_customer_data' => $flagCustomer,
        'no_product_data' => $flagProduct,
        'no_order_data' => $flagOrder,
        'last_sync_time' => time(),
        'total_transaction' => $totalTransaction,
        'status' => $status,
        'added_on' => time()
    );
    $credentials['counterOrders'] = $flagOrder;
    $credentials['counterProducts'] = $flagProduct;
    $credentials['counterAccounts'] = $flagCustomer;
    include "/var/www/html/app/mysql/mysqlconstants.php";

    tblTransectionDetail($credentials, $crmType, $userDataArrTransaction);

    sendSyncReport($credentials, $crmType);

    return $flagOrder;
} //Done

function bigcommerceToHubspotCRM($credentials)
{
    include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;

    $subscriptionId = $credentials['subscription_id'];
    $crmType = 'bigcommerceTohubspot';//$credentials['crm_type'];
    $contextVal = //$credentials['contextValue'];


        /* $subscriptionId = $credentials['subscription_id'];
         $crmType = 'bigcommerceTovtiger'; */
    $bigcommURL = $credentials['app1Details']['bigcommerce_context'] . "/api";
    $bigcommerceUsername = $credentials['app1Details']['bigcommerce_user_name'];
    $bigcommerceAccessKey = $credentials['app1Details']['bigcommerce_password'];
    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];


    $hubspot_refresh_token = $credentials['app2Details']['hubspot_refresh_token'];
    $hubspot_portal_id = $credentials['app2Details']['hubspot_portal_id'];
    $hubspot_form_id = $credentials['app2Details']['hubspot_form_id'];
    $hubspot_last_order = $credentials['app2Details']['hubspot_last_order'];
    //For Latest Access Token Collect From Refresh Token
    $url = HUBAPI_URL . '/oauth/v1/token';
    $headers = array(
        'Content-Type: application/x-www-form-urlencoded;charset=utf-8'
    );

    $postfields = array(
        'grant_type' => 'refresh_token',
        'client_id' => HUBSPOT_CLIENT_ID,
        'client_secret' => HUBSPOT_CLIENT_SECRET,
        'redirect_uri' => 'https://' . APP_DOMAIN . '/app/install/hubspot/app-login.php',
        'refresh_token' => $hubspot_refresh_token
    );
    $contactFieldsJSON = http_build_query($postfields);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $contactFieldsJSON);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $response = curl_exec($ch);
    $responseDecode = json_decode($response, true);

    //Access Token Collected For a User
    $hubspot_access_token = $responseDecode['access_token'];
    curl_close($ch);

    try {

        $retVal = FALSE;
        $total_success = 0;
        $access_token = base64_encode($bigcommerceUsername . ":" . $bigcommerceAccessKey);
        $url = $bigcommURL . "/v2/orders/count.json?min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
        $http_headres = array(
            "Authorization: Basic " . trim($access_token),
            "Content-Type: application/json",
            "Accept: application/json",

        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        if ($http_code === 200) {
            curl_close($ch);
            $count_arr = json_decode($return, TRUE);
            $count = $count_arr['count'];

            if ($count > 0) {
                $page = ceil($count / MAX_PAGE_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $url = $bigcommURL . "/v2/orders.json?page=" . $i . "&limit=" . MAX_PAGE_SIZE . "&min_date_created=" . $min_date_created . "&max_date_created=" . $max_date_created;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                    $return = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    // check if any orders exist for the page

                    if ($http_code === 200) {
                        $orders = json_decode($return, TRUE);
                        // Insert everything for the Order including Contact and Products.

                        foreach ($orders as $key => $value) {
                            $order_id = $orders[$key]['id'];
                            $customer_id = $orders[$key]['customer_id'];
                            $currentOrderStatus = $orders[$key]['status'];
                            //Customer Details collect from BigCommerce order
                            $customer_url = $bigcommURL . "/v2/customers/$customer_id.json";
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $customer_url);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                            $return = curl_exec($ch);
                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            curl_close($ch);
                            if ($order_id > $hubspot_last_order || $hubspot_last_order == '') {
                                if ($http_code === 200) {
                                    $customerDetails = json_decode($return, TRUE);
                                    $firstName = $customerDetails['first_name'];
                                    $lastName = $customerDetails['last_name'];
                                    $accountName = $firstName . ' ' . $lastName;
                                    $email = $customerDetails['email'];
                                    $phone = $customerDetails['phone'];


                                    /* retrieve address for the customer */
                                    $address_url = $customerDetails['addresses']['url'];
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $address_url);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                    $return = curl_exec($ch);
                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                    curl_close($ch);
                                    if ($http_code === 200) {
                                        $addressDetails = json_decode($return, TRUE);
                                        $mailingStreet = $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'];
                                        $otherStreet = $addressDetails[0]['street_1'] . " " . $addressDetails[0]['street_2'];
                                        $mailingCity = $addressDetails[0]['city'];
                                        $otherCity = $addressDetails[0]['city'];
                                        $mailingState = $addressDetails[0]['state'];
                                        $otherState = $addressDetails[0]['state'];
                                        $mailingZip = $addressDetails[0]['zip'];
                                        $otherZip = $addressDetails[0]['zip'];
                                        $mailingCountry = $addressDetails[0]['country'];
                                        $otherCountry = $addressDetails[0]['country'];
                                    } else {
                                        $mailingStreet = $orders[$key]['billing_address']['street_1'] . " " . $orders[$key]['billing_address']['street_2'];
                                        $otherStreet = $orders[$key]['billing_address']['street_1'] . " " . $orders[$key]['billing_address']['street_2'];
                                        $mailingCity = $orders[$key]['billing_address']['city'];
                                        $otherCity = $orders[$key]['billing_address']['city'];
                                        $mailingState = $orders[$key]['billing_address']['state'];
                                        $otherState = $orders[$key]['billing_address']['state'];
                                        $mailingZip = $orders[$key]['billing_address']['zip'];
                                        $otherZip = $orders[$key]['billing_address']['zip'];
                                        $mailingCountry = $orders[$key]['billing_address']['country'];
                                        $otherCountry = $orders[$key]['billing_address']['country'];
                                    }

                                    $flag = FALSE;
                                    try {
                                        // Create Contact for this Order ..................

                                        $contactInsertUrl = HUBAPI_URL . '/contacts/v1/contact/createOrUpdate/email/' . $email;
                                        $headers = array(
                                            "Authorization: Bearer $hubspot_access_token"
                                            //"Content-Type: application/json"
                                        );

                                        $contactData = array(
                                            'properties' => array(
                                                array(
                                                    'property' => 'email',
                                                    'value' => $email
                                                ),
                                                array(
                                                    'property' => 'firstname',
                                                    'value' => $firstName
                                                ),
                                                array(
                                                    'property' => 'lastname',
                                                    'value' => $lastName
                                                ),
                                                array(
                                                    'property' => 'phone',
                                                    'value' => $phone
                                                ),
                                                array(
                                                    'property' => 'address',
                                                    'value' => $mailingStreet
                                                ),
                                                array(
                                                    'property' => 'city',
                                                    'value' => $mailingCity
                                                ),
                                                array(
                                                    'property' => 'state',
                                                    'value' => $mailingState
                                                ),
                                                array(
                                                    'property' => 'zip',
                                                    'value' => $mailingZip
                                                ),
                                                array(
                                                    'property' => 'country',
                                                    'value' => $mailingCountry
                                                ),
                                                array(
                                                    'property' => 'jobtitle',
                                                    'value' => 'New Contact'
                                                ),
                                                array(
                                                    'property' => 'lifecyclestage',
                                                    'value' => 'customer'
                                                )
                                            )
                                        );
                                        $contactFieldsJSON = json_encode($contactData);

                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $contactInsertUrl);
                                        curl_setopt($ch, CURLOPT_POST, 1);
                                        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        curl_setopt($ch, CURLOPT_POSTFIELDS, $contactFieldsJSON);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                        $response = curl_exec($ch);
                                        $responseDecode = json_decode($response, true);

                                        if (isset($responseDecode['vid'])) {
                                            $flagCustomer++;
                                        }
                                        $order_id = $orders[$key]['id'];
                                        $subject = 'Order Number ' . $orders[$key]['id'];
                                        $grandTotal = $orders[$key]['total_inc_tax'];
                                        $subTotal = $orders[$key]['subtotal_ex_tax'];
                                        $tax = $orders[$key]['total_tax'];
                                        $adjustment = $orders[$key]['total_inc_tax'] - $orders[$key]['subtotal_inc_tax'];

                                        $orderBillingStreet = $orders[$key]['billing_address']['street_1'] . $orders[$key]['billing_address']['street_2'];
                                        $orderBillingCity = $orders[$key]['billing_address']['city'];
                                        $orderBillingState = $orders[$key]['billing_address']['state'];
                                        $orderBillingZip = $orders[$key]['billing_address']['zip'];
                                        $orderBillingCountry = $orders[$key]['billing_address']['country'];

                                        // retrieve shipping address

                                        $orderShippingURL = $orders[$key]['shipping_addresses']['url'];
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $orderShippingURL);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
                                        $return = curl_exec($ch);
                                        curl_close($ch);
                                        $orderShippingAddresses = json_decode($return, TRUE);
                                        $orderShippingStreet = $orderShippingAddresses[0]['street_1'] . $orderShippingAddresses[0]['street_2'];
                                        $orderShippingCity = $orderShippingAddresses[0]['city'];
                                        $orderShippingState = $orderShippingAddresses[0]['state'];
                                        $orderShippingZip = $orderShippingAddresses[0]['zip'];
                                        $orderShippingCountry = $orderShippingAddresses[0]['country'];

                                        //Form Data Submit
                                        $formInsertUrl = "https://forms.hubspot.com/uploads/form/v2/" . $hubspot_portal_id . "/" . $hubspot_form_id;

                                        $headers = array(
                                            "Content-Type: application/x-www-form-urlencoded"
                                        );
                                        $hubspotutk = $_COOKIE['hubspotutk']; //grab the cookie from the visitors browser.
                                        $ip_addr = $_SERVER['REMOTE_ADDR']; //IP address too.
                                        $hs_context = array(
                                            'hutk' => $hubspotutk,
                                            'ipAddress' => $ip_addr
                                        );
                                        $hs_context_json = json_encode($hs_context);

                                        $str_post = "subject=" . urlencode($subject)
                                            . "&order_id=" . urlencode($order_id)
                                            . "&email=" . urlencode($email)
                                            . "&status=" . urlencode($currentOrderStatus)
                                            . "&bill_street=" . urlencode($orderBillingStreet)
                                            . "&bill_city=" . urlencode($orderBillingCity)
                                            . "&bill_code=" . urlencode($orderBillingZip)
                                            . "&bill_country=" . urlencode($orderBillingCountry)
                                            . "&bill_state=" . urlencode($orderBillingState)
                                            . "&ship_street=" . urlencode($orderShippingStreet)
                                            . "&ship_city=" . urlencode($orderShippingCity)
                                            . "&ship_code=" . urlencode($orderShippingZip)
                                            . "&ship_country=" . urlencode($orderShippingCountry)
                                            . "&ship_state=" . urlencode($orderShippingState)
                                            . "&tax=" . urlencode($tax)
                                            . "&subtotal=" . urlencode($subTotal)
                                            . "&total=" . urlencode($grandTotal)
                                            . "&hs_context=" . urlencode($hs_context_json); //Leave this one be
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $formInsertUrl);
                                        curl_setopt($ch, CURLOPT_POST, 1);
                                        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        curl_setopt($ch, CURLOPT_POSTFIELDS, $str_post);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                        $response = curl_exec($ch);
                                        $responseDecode = json_decode($response, true);
                                        curl_close($ch);
                                        if (!isset($responseDecode['error'])) {
                                            $flagOrder++;
                                            //update table subscription
                                            $cond = " AND app2_cred1='$hubspot_refresh_token' AND app2_cred2='$hubspot_portal_id' AND app2_cred3='$hubspot_form_id'";
                                            $updateArr = array(
                                                'hub_order' => $order_id
                                            );
                                            //Subscription Table update
                                            update($userSubscription, $updateArr, $cond);
                                            //Final Subscription Table update
                                            update($userSubscriptionFinal, $updateArr, $cond);
                                        }

                                    } catch (Exception $e) {
                                        $msg = $e;
                                        $flag = FALSE;
                                    }// for each order on the page
                                }
                            }
                        } // for each page of orders
                    }


                } // if count of orders returned
            }
        }
    } // Try retrieving orders from BigCommerce
    catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }
    // Create reports.

    $status = 'YES';
    $totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
    $userDataArrTransaction = array(
        'user_id' => $credentials['userId'],
        'crm_type' => $crmType,
        'no_customer_data' => $flagCustomer,
        'no_product_data' => $flagProduct,
        'no_order_data' => $flagOrder,
        'last_sync_time' => time(),
        'total_transaction' => $totalTransaction,
        'status' => $status,
        'added_on' => time()
    );
    $credentials['counterOrders'] = $flagOrder;
    $credentials['counterProducts'] = $flagProduct;
    $credentials['counterAccounts'] = $flagCustomer;
    include "/var/www/html/app/mysql/mysqlconstants.php";

    tblTransectionDetail($credentials, $crmType, $userDataArrTransaction);

    sendSyncReport($credentials, $crmType);

    return $flagOrder;
} //Done

// Core Function For Shopify Sync From Aquaapi App....
function shopifyToHubspotCRM($credentials)
{

    include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;
    $cloudApp = 'HUBSPOT';
    $subscriptionId = $credentials['subscription_id'];
    $crmType = 'shopifyTohubspot';//$credentials['crm_type'];
    $contextVal = //$credentials['contextValue'];

    $shopifyURL = $credentials['app1Details']['shopify_url'];

    $shopifyAPI = $credentials['app1Details']['shopify_api'];

    $shopifyPassword = $credentials['app1Details']['shopify_password'];

    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];


    $hubspot_refresh_token = $credentials['app2Details']['hubspot_refresh_token'];
    $hubspot_portal_id = $credentials['app2Details']['hubspot_portal_id'];
    $hubspot_form_id = $credentials['app2Details']['hubspot_form_id'];
    $hubspot_last_order = $credentials['app2Details']['hubspot_last_order'];

    //........... Get HubSpot access token if already Access Permission Given.......

    //For Latest Access Token Collect From Refresh Token
    $url = HUBAPI_URL . '/oauth/v1/token';
    $headers = array(
        'Content-Type: application/x-www-form-urlencoded;charset=utf-8'
    );

    $postfields = array(
        'grant_type' => 'refresh_token',
        'client_id' => HUBSPOT_CLIENT_ID,
        'client_secret' => HUBSPOT_CLIENT_SECRET,
        'redirect_uri' => 'https://' . APP_DOMAIN . '/app/install/hubspot/app-login.php',
        'refresh_token' => $hubspot_refresh_token
    );
    $contactFieldsJSON = http_build_query($postfields);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $contactFieldsJSON);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $response = curl_exec($ch);
    $responseDecode = json_decode($response, true);
    //Access Token Collected For a User
    $hubspot_access_token = $responseDecode['access_token'];
    curl_close($ch);


    try {
        //............ORDER LIST COLLECTIONG.................
        $orderUrl = $shopifyURL . "/admin/orders.json?created_at_min=" . $min_date_created . "&created_at_max=" . $max_date_created . "&direction=asc";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $orderUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code === 200) {
            $orders = json_decode($return, TRUE);
            $count = count($orders);

            if ($count > 0) {

                foreach ($orders as $num => $getOrders) {

                    foreach ($getOrders as $key => $value) {

                        //Customer Detials Collect
                        $customerId = $value['customer']['id'];

                        $orderId = $value['order_number'];

                        $subject = 'Order Number ' . $orderId;

                        //Status of Order./................
                        $currentOrderStatus = $value['financial_status'];


                        //Order Previously Exist or not
                        if ($orderId > $hubspot_last_order || $hubspot_last_order == '') {

                            $email = $value['email'];
                            $phone = $value['phone'];
                            //End of Billing Address Collecting

                            if (!empty($value['shipping_address'])) {
                                $firstName = $value['shipping_address']['first_name'];
                                $lastName = $value['shipping_address']['last_name'];
                                $accountName = $firstName . ' ' . $lastName;
                                $phone = $value['shipping_address']['phone'];

                                $mailingStreet = $value['shipping_address']['address1'] . "," . $value['shipping_address']['address2'];
                                $otherStreet = $value['shipping_address']['address1'] . "," . $value['shipping_address']['address2'];
                                $mailingCity = $value['shipping_address']['city'];
                                $otherCity = $value['shipping_address']['city'];
                                $mailingState = $value['shipping_address']['province'];
                                $otherState = $value['shipping_address']['province'];
                                $mailingZip = $value['shipping_address']['zip'];
                                $otherZip = $value['shipping_address']['zip'];
                                $mailingCountry = $value['shipping_address']['country'];
                                $otherCountry = $value['shipping_address']['country'];
                            } else if (!empty($value['billing_address'])) {
                                $firstName = $value['billing_address']['first_name'];
                                $lastName = $value['billing_address']['last_name'];
                                $accountName = $firstName . ' ' . $lastName;
                                $phone = $value['billing_address']['phone'];

                                $mailingStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                                $otherStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                                $mailingCity = $value['billing_address']['city'];
                                $otherCity = $value['billing_address']['city'];
                                $mailingState = $value['billing_address']['province'];
                                $otherState = $value['billing_address']['province'];
                                $mailingZip = $value['billing_address']['zip'];
                                $otherZip = $value['billing_address']['zip'];
                                $mailingCountry = $value['billing_address']['country'];
                                $otherCountry = $value['billing_address']['country'];
                            } else {
                                $firstName = $value['customer']['first_name'];
                                $lastName = $value['customer']['last_name'];
                                $accountName = $firstName . ' ' . $lastName;
                                $email = $value['email'];
                                $phone = $value['customer']['phone'];

                                $mailingStreet = $value['customer']['default_address']['address1'] . "," . $value['customer']['default_address']['address2'];
                                $otherStreet = $value['customer']['default_address']['address1'] . "," . $value['customer']['default_address']['address2'];
                                $mailingCity = $value['customer']['default_address']['city'];
                                $otherCity = $value['customer']['default_address']['city'];
                                $mailingState = $value['customer']['default_address']['province'];
                                $otherState = $value['customer']['default_address']['province'];
                                $mailingZip = $value['customer']['default_address']['zip'];
                                $otherZip = $value['customer']['default_address']['zip'];
                                $mailingCountry = $value['customer']['default_address']['country'];
                                $otherCountry = $value['customer']['default_address']['country'];
                            }

                            $flag = FALSE;
                            try {

                                // Create Contact for this Order ..................
                                $contactInsertUrl = HUBAPI_URL . '/contacts/v1/contact/createOrUpdate/email/' . $email;
                                $headers = array(
                                    "Authorization: Bearer $hubspot_access_token"
                                    //"Content-Type: application/json"
                                );

                                $contactData = array(
                                    'properties' => array(
                                        array(
                                            'property' => 'email',
                                            'value' => $email
                                        ),
                                        array(
                                            'property' => 'firstname',
                                            'value' => $firstName
                                        ),
                                        array(
                                            'property' => 'lastname',
                                            'value' => $lastName
                                        ),
                                        array(
                                            'property' => 'phone',
                                            'value' => $phone
                                        ),
                                        array(
                                            'property' => 'address',
                                            'value' => $mailingStreet
                                        ),
                                        array(
                                            'property' => 'city',
                                            'value' => $mailingCity
                                        ),
                                        array(
                                            'property' => 'state',
                                            'value' => $mailingState
                                        ),
                                        array(
                                            'property' => 'zip',
                                            'value' => $mailingZip
                                        ),
                                        array(
                                            'property' => 'country',
                                            'value' => $mailingCountry
                                        ),
                                        array(
                                            'property' => 'jobtitle',
                                            'value' => 'New Contact'
                                        ),
                                        array(
                                            'property' => 'lifecyclestage',
                                            'value' => 'customer'
                                        )
                                    )
                                );
                                $contactFieldsJSON = json_encode($contactData);

                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $contactInsertUrl);
                                curl_setopt($ch, CURLOPT_POST, 1);
                                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $contactFieldsJSON);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                $response = curl_exec($ch);
                                $responseDecode = json_decode($response, true);
                                curl_close($ch);
                                if (isset($responseDecode['vid'])) {
                                    $flagCustomer++;
                                }

                                // Order Details Collect
                                $subject = 'Order Number ' . $orderId;
                                $grandTotal = $value['total_price'];
                                $subTotal = $value['subtotal_price'];
                                $tax = $value['total_tax'];
                                $adjustment = $grandTotal - $subTotal;
                                //Billing Address Detial

                                $orderBillingStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                                $orderBillingCity = $value['billing_address']['city'];
                                $orderBillingZip = $value['billing_address']['province'];
                                $orderBillingCountry = $value['billing_address']['zip'];
                                $orderBillingState = $value['billing_address']['country'];
                                //Shipping Address Detial
                                $orderShippingStreet = $value['shipping_address']['address1'] . "," . $value['billing_address']['address2'];
                                $orderShippingCity = $value['shipping_address']['city'];
                                $orderShippingZip = $value['shipping_address']['province'];
                                $orderShippingCountry = $value['shipping_address']['zip'];
                                $orderShippingState = $value['shipping_address']['country'];

                                //Form Data Submit
                                $formInsertUrl = "https://forms.hubspot.com/uploads/form/v2/" . $hubspot_portal_id . "/" . $hubspot_form_id;

                                $headers = array(
                                    "Content-Type: application/x-www-form-urlencoded"
                                );

                                $ip_addr = $_SERVER['REMOTE_ADDR']; //IP address too.
                                $hs_context = array(
                                    'ipAddress' => $ip_addr
                                );
                                $hs_context_json = json_encode($hs_context);

                                $str_post = "subject=" . urlencode($subject)
                                    . "&order_id=" . urlencode($orderId)
                                    . "&email=" . urlencode($email)
                                    . "&status=" . urlencode($currentOrderStatus)
                                    . "&bill_street=" . urlencode($orderBillingStreet)
                                    . "&bill_city=" . urlencode($orderBillingCity)
                                    . "&bill_code=" . urlencode($orderBillingZip)
                                    . "&bill_country=" . urlencode($orderBillingCountry)
                                    . "&bill_state=" . urlencode($orderBillingState)
                                    . "&ship_street=" . urlencode($orderShippingStreet)
                                    . "&ship_city=" . urlencode($orderShippingCity)
                                    . "&ship_code=" . urlencode($orderShippingZip)
                                    . "&ship_country=" . urlencode($orderShippingCountry)
                                    . "&ship_state=" . urlencode($orderShippingState)
                                    . "&tax=" . urlencode($tax)
                                    . "&subtotal=" . urlencode($subTotal)
                                    . "&total=" . urlencode($grandTotal)
                                    . "&hs_context=" . urlencode($hs_context_json); //Leave this one be
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $formInsertUrl);
                                curl_setopt($ch, CURLOPT_POST, 1);
                                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $str_post);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                $response = curl_exec($ch);
                                $responseDecode = json_decode($response, true);
                                curl_close($ch);

                                if (!isset($responseDecode['error'])) {
                                    $flagOrder++;
                                    //update table subscription
                                    $cond = " AND app2_cred1='$hubspot_refresh_token' AND app2_cred2='$hubspot_portal_id' AND app2_cred3='$hubspot_form_id'";
                                    $updateArr = array(
                                        'hub_order' => $orderId
                                    );
                                    //Subscription Table update
                                    update($userSubscription, $updateArr, $cond);
                                    //Final Subscription Table update
                                    update($userSubscriptionFinal, $updateArr, $cond);

                                }
                                //End of Form Submission
                            } catch (Exception $e) {
                                $msg = $e;
                                $flag = FALSE;
                            }


                        }//order exist or not
                    }//end or order list
                }
            }


        }

    } catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }
    // Create reports.

    $status = 'YES';
    $totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
    $userDataArrTransaction = array(
        'user_id' => $credentials['userId'],
        'crm_type' => $crmType,
        'no_customer_data' => $flagCustomer,
        'no_product_data' => $flagProduct,
        'no_order_data' => $flagOrder,
        'last_sync_time' => time(),
        'total_transaction' => $totalTransaction,
        'status' => $status,
        'added_on' => time()
    );
    $credentials['counterOrders'] = $flagOrder;
    $credentials['counterProducts'] = $flagProduct;
    $credentials['counterAccounts'] = $flagCustomer;
    include "/var/www/html/app/mysql/mysqlconstants.php";

    tblTransectionDetail($credentials, $crmType, $userDataArrTransaction);

    sendSyncReport($credentials, $crmType);
    return $flagOrder;
} //Done

function shopifyToZohoCRM($credentials)
{

    include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;
    $cloudApp = 'ZOHO';

    $subscriptionId = $credentials['subscription_id'];
    $crmType = 'shopifyTozoho';//$credentials['crm_type'];
    $contextVal = //$credentials['contextValue'];

    $shopifyURL = $credentials['app1Details']['shopify_url'];
    $shopifyAPI = $credentials['app1Details']['shopify_api'];
    $shopifyPassword = $credentials['app1Details']['shopify_password'];

    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];


    $zohoCRMAuthtoken = $credentials['app2Details']['zoho_auth_id'];

    //........... Get HubSpot access token if already Access Permission Given.......

    try {
        //............ORDER LIST COLLECTIONG.................

        $orderUrl = $shopifyURL . "/admin/orders.json?created_at_min=" . $min_date_created . "&created_at_max=" . $max_date_created . "&direction=asc";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $orderUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code === 200) {
            $orders = json_decode($return, TRUE);

            $count = count($orders);
            if ($count > 0) {

                foreach ($orders as $num => $getOrders) {

                    foreach ($getOrders as $key => $value) {

                        //Customer Detials Collect
                        $customerId = $value['customer']['id'];
                        $email = $value['email'];
                        $phone = $value['phone'];
                        $orderId = $value['order_number'];
                        $subject = 'Order Number ' . $orderId;

                        //Status of Order./................
                        $currentOrderStatus = $value['financial_status'];
                        //Status of Order./................

                        if (($currentOrderStatus === 'complete') || ($currentOrderStatus === 'pending')) {
                            $salesOrder = 'TRUE';
                        } else {
                            $salesOrder = 'FALSE';

                        }

                        if (!empty($value['shipping_address'])) {
                            $firstName = $value['shipping_address']['first_name'];
                            $lastName = $value['shipping_address']['last_name'];
                            $accountName = $firstName . ' ' . $lastName;
                            $phone = $value['shipping_address']['phone'];

                            $mailingStreet = $value['shipping_address']['address1'] . "," . $value['shipping_address']['address2'];
                            $otherStreet = $value['shipping_address']['address1'] . "," . $value['shipping_address']['address2'];
                            $mailingCity = $value['shipping_address']['city'];
                            $otherCity = $value['shipping_address']['city'];
                            $mailingState = $value['shipping_address']['province'];
                            $otherState = $value['shipping_address']['province'];
                            $mailingZip = $value['shipping_address']['zip'];
                            $otherZip = $value['shipping_address']['zip'];
                            $mailingCountry = $value['shipping_address']['country'];
                            $otherCountry = $value['shipping_address']['country'];
                        } else if (!empty($value['billing_address'])) {
                            $firstName = $value['billing_address']['first_name'];
                            $lastName = $value['billing_address']['last_name'];
                            $accountName = $firstName . ' ' . $lastName;
                            $phone = $value['billing_address']['phone'];

                            $mailingStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                            $otherStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                            $mailingCity = $value['billing_address']['city'];
                            $otherCity = $value['billing_address']['city'];
                            $mailingState = $value['billing_address']['province'];
                            $otherState = $value['billing_address']['province'];
                            $mailingZip = $value['billing_address']['zip'];
                            $otherZip = $value['billing_address']['zip'];
                            $mailingCountry = $value['billing_address']['country'];
                            $otherCountry = $value['billing_address']['country'];
                        } else {
                            $firstName = $value['customer']['first_name'];
                            $lastName = $value['customer']['last_name'];
                            $accountName = $firstName . ' ' . $lastName;
                            $email = $value['email'];
                            $phone = $value['customer']['phone'];

                            $mailingStreet = $value['customer']['default_address']['address1'] . "," . $value['customer']['default_address']['address2'];
                            $otherStreet = $value['customer']['default_address']['address1'] . "," . $value['customer']['default_address']['address2'];
                            $mailingCity = $value['customer']['default_address']['city'];
                            $otherCity = $value['customer']['default_address']['city'];
                            $mailingState = $value['customer']['default_address']['province'];
                            $otherState = $value['customer']['default_address']['province'];
                            $mailingZip = $value['customer']['default_address']['zip'];
                            $otherZip = $value['customer']['default_address']['zip'];
                            $mailingCountry = $value['customer']['default_address']['country'];
                            $otherCountry = $value['customer']['default_address']['country'];
                        }

                        $flag = FALSE;
                        try {

                            // Insert Account details
                            $xml = '<?xml version="1.0" encoding="UTF-8"?>
                            <Accounts>
                                <row no="1">
                                        <FL val="Account Name">' . $accountName . '</FL>
                                        <FL val="Email">' . $email . '</FL>
                                        <FL val="Phone">' . $phone . '</FL>
                                        <FL val="Billing Street">' . $mailingStreet . '</FL>
                                        <FL val="Shipping Street">' . $otherStreet . '</FL>
                                        <FL val="Billing City">' . $mailingCity . '</FL>
                                        <FL val="Shipping City">' . $otherCity . '</FL>
                                        <FL val="Billing State">' . $mailingState . '</FL>
                                        <FL val="Shipping State">' . $otherState . '</FL>
                                        <FL val="Billing Code">' . $mailingZip . '</FL>
                                        <FL val="Shipping Code">' . $otherZip . '</FL>
                                        <FL val="Billing Country">' . $mailingCountry . '</FL>
                                        <FL val="Shipping Country">' . $otherCountry . '</FL>
                                </row>
                             </Accounts>';

                            // For Insert Records with duplicate checking.

                            $url = "https://crm.zoho.com/crm/private/xml/Accounts/insertRecords";
                            $query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$xml";
                            $ch = curl_init();
                            /* set url to send post request */
                            curl_setopt($ch, CURLOPT_URL, $url);
                            /* allow redirects */
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                            /* return a response into a variable */
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            /* times out after 30s */
                            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                            /* set POST method */
                            curl_setopt($ch, CURLOPT_POST, 1);
                            /* add POST fields parameters */
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                            // Execute cUrl session

                            $response = curl_exec($ch);
                            curl_close($ch);;


                            $xml = '<?xml version="1.0" encoding="UTF-8"?>
                            <Contacts>
                                <row no="1">
                                    <FL val="First Name">' . $firstName . '</FL>
                                    <FL val="Last Name">' . $lastName . '</FL>
                                    <FL val="Account Name">' . $accountName . '</FL>
                                    <FL val="Email">' . $email . '</FL>
                                    <FL val="Phone">' . $phone . '</FL>
                                    <FL val="Mailing Street">' . $mailingStreet . '</FL>
                                    <FL val="Other Street">' . $otherStreet . '</FL>
                                    <FL val="Mailing City">' . $mailingCity . '</FL>
                                    <FL val="Other City">' . $otherCity . '</FL>
                                    <FL val="Mailing State">' . $mailingState . '</FL>
                                    <FL val="Other State">' . $otherState . '</FL>
                                    <FL val="Mailing Zip">' . $mailingZip . '</FL>
                                    <FL val="Other Zip">' . $otherZip . '</FL>
                                    <FL val="Mailing Country">' . $mailingCountry . '</FL>
                                    <FL val="Other Country">' . $otherCountry . '</FL>
                                </row>
                            </Contacts>';

                            // For Insert Records with duplicate checking.

                            $url = "https://crm.zoho.com/crm/private/xml/Contacts/insertRecords";
                            $query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$xml";
                            $ch = curl_init();
                            /* set url to send post request */
                            curl_setopt($ch, CURLOPT_URL, $url);
                            /* allow redirects */
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                            /* return a response into a variable */
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            /* times out after 30s */
                            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                            /* set POST method */
                            curl_setopt($ch, CURLOPT_POST, 1);
                            /* add POST fields parameters */
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                            // Execute cUrl session

                            $response = curl_exec($ch);
                            curl_close($ch);
                            $res = simplexml_load_string($response);
                            $resCode = (string)$res->result->row->success->code;


                            if (($resCode === '2000') || ($resCode === '2001') || ($resCode === '2002')) {
                                // $flag = TRUE;

                                if ($resCode === '2000') {
                                    $flagCustomer++;
                                }


                                // Order Details Collect
                                $subject = 'Order Number ' . $orderId;
                                $grandTotal = $value['total_price'];
                                $subTotal = $value['subtotal_price'];
                                $tax = $value['total_tax'];
                                $adjustment = ($grandTotal - $subTotal)-$tax;
                                //Billing Address Detial

                                $orderBillingStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                                $orderBillingCity = $value['billing_address']['city'];
                                $orderBillingZip = $value['billing_address']['province'];
                                $orderBillingCountry = $value['billing_address']['zip'];
                                $orderBillingState = $value['billing_address']['country'];
                                //Shipping Address Detial
                                $orderShippingStreet = $value['shipping_address']['address1'] . "," . $value['billing_address']['address2'];
                                $orderShippingCity = $value['shipping_address']['city'];
                                $orderShippingZip = $value['shipping_address']['province'];
                                $orderShippingCountry = $value['shipping_address']['zip'];
                                $orderShippingState = $value['shipping_address']['country'];


                                if ($salesOrder === 'TRUE') {
                                    $InvoiceXML = '<?xml version="1.0" encoding="UTF-8"?>
                                        <SalesOrders>
                                          <row no="1">
                                            <FL val="Contact Name">' . $accountName . '</FL>
                                            <FL val="Account Name">' . $accountName . '</FL>
                                            <FL val="Subject">' . $subject . '</FL>
                                            <FL val="Product Details"></FL>
                                            <FL val="Billing Street">' . $orderBillingStreet . '</FL>
                                            <FL val="Shipping Street">' . $orderShippingStreet . '</FL>
                                            <FL val="Billing City">' . $orderBillingCity . '</FL>
                                            <FL val="Shipping City">' . $orderShippingCity . '</FL>
                                            <FL val="Billing State">' . $orderBillingState . '</FL>
                                            <FL val="Shipping State">' . $orderShippingState . '</FL>
                                            <FL val="Billing Code">' . $orderBillingZip . '</FL>
                                            <FL val="Shipping Code">' . $orderShippingZip . '</FL>
                                            <FL val="Billing Country">' . $orderBillingCountry . '</FL>
                                            <FL val="Shipping Country">' . $orderShippingCountry . '</FL>
                                            <FL val="Sub Total">' . $subTotal . '</FL>
                                            <FL val="Tax">' . $tax . '</FL>
                                            <FL val="Adjustment">' . $adjustment . '</FL>
                                            <FL val="Grand Total">' . $grandTotal . '</FL>
                                          </row>
                                        </SalesOrders>';
                                } else {
                                    $InvoiceXML = '<?xml version="1.0" encoding="UTF-8"?>
                                      <Quotes>
                                       <row no="1">
                                        <FL val="Contact Name">' . $accountName . '</FL>
                                        <FL val="Account Name">' . $accountName . '</FL>
                                        <FL val="Subject">' . $subject . '</FL>
                                        <FL val="Product Details">
                                        </FL>
                                        <FL val="Billing Street">' . $orderBillingStreet . '</FL>
                                        <FL val="Shipping Street">' . $orderShippingStreet . '</FL>
                                        <FL val="Billing City">' . $orderBillingCity . '</FL>
                                        <FL val="Shipping City">' . $orderShippingCity . '</FL>
                                        <FL val="Billing State">' . $orderBillingState . '</FL>
                                        <FL val="Shipping State">' . $orderShippingState . '</FL>
                                        <FL val="Billing Code">' . $orderBillingZip . '</FL>
                                        <FL val="Shipping Code">' . $orderShippingZip . '</FL>
                                        <FL val="Billing Country">' . $orderBillingCountry . '</FL>
                                        <FL val="Shipping Country">' . $orderShippingCountry . '</FL>
                                        <FL val="Sub Total">' . $subTotal . '</FL>
                                        <FL val="Tax">' . $tax . '</FL>
                                        <FL val="Adjustment">' . $adjustment . '</FL>
                                        <FL val="Grand Total">' . $grandTotal . '</FL>
                                        </row>
                                      </Quotes>';

                                }
                                $sxeInvoiceXML = new SimpleXMLElement($InvoiceXML);


                                //PRODUCT DETAILS COLLECT FROM ORDER
                                $totalProduct = count($value['line_items']);
                                $prodNumber = 0;
                                for ($i = 0; $i < $totalProduct; $i++) {
                                    //PRODUCT ID COLLECTED

                                    $productId = $value['line_items'][$i]['product_id'];
                                    $prodCode = $value['line_items'][$i]['sku'];

                                    $m_quantity = $value['line_items'][$i]['quantity'];
                                    $prodListPrice = $value['line_items'][$i]['price'];
                                    $prodUnitPrice = $value['line_items'][$i]['price'];
                                    $total = $value['line_items'][$i]['price'];
                                    $totalLineItems = $m_quantity * $total;

                                    //$discount = $total - $totalAfterDiscount;
                                    $discount = $value['line_items'][$i]['total_discount'];
                                    $totalAfterDiscount = $totalLineItems - $discount;

                                    if ($value['line_items'][$i]['taxable'] == 1) {
                                        $tax = $value['line_items'][$i]['tax_lines'][0]['price'];
                                    } else {
                                        $tax = 0;
                                    }

                                    $netTotal = $totalAfterDiscount + $tax;

                                    //Product Detail Collect

                                    $productUrl = $shopifyURL . "/admin/products/" . $productId . ".json";
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $orderUrl);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    $return = curl_exec($ch);
                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                    curl_close($ch);
                                    $productDetail = json_decode($return, TRUE);

                                    //End of Product Details


                                    //PRODUCT DETAILS IF EXISTS .................
                                    $prodName = $value['line_items'][$i]['name'];
                                    $stock_quantity = '100';
                                    $price = $value['line_items'][$i]['price'];
                                    $description = $value['line_items'][$i]['name'];

                                    $ProductXML = '<?xml version="1.0" encoding="UTF-8"?>
                                                 <Products>
                                                        <row no="1">
                                                        <FL val="Product Code">' . $prodCode . '</FL>
                                                        <FL val="Product Name"><![CDATA[' . urlencode($prodName) . ']]></FL>
                                                        <FL val="Unit Price">' . $prodUnitPrice . '</FL>
                                                        <FL val="Qty Ordered"><![CDATA[' . urlencode($m_quantity) . ']]></FL>
                                                        <FL val="Qty in Stock"><![CDATA[' . urlencode($stock_quantity) . ']]></FL>
                                                        <FL val="Description"><![CDATA[' . urlencode($description) . ']]></FL>
                                                        </row>
                                                </Products>';
                                    $output = simplexml_load_string($ProductXML);

                                    // For Insert Records with duplicate checking.

                                    $url = "https://crm.zoho.com/crm/private/xml/Products/insertRecords";
                                    $query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$ProductXML";
                                    $ch = curl_init();
                                    /* set url to send post request */
                                    curl_setopt($ch, CURLOPT_URL, $url);
                                    /* allow redirects */
                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                    /* return a response into a variable */
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                    /* times out after 30s */
                                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                    /* set POST method */
                                    curl_setopt($ch, CURLOPT_POST, 1);
                                    /* add POST fields parameters */
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                    // Execute cUrl session

                                    $response = curl_exec($ch);
                                    curl_close($ch);
                                    $res = simplexml_load_string($response);
                                    if ($res->result != FALSE) {

                                        $resCode = (string)$res->result->row->success->code;
                                        $prodId = $res->result->row->success->details->FL[0];
                                    } else {
                                        $resCode = 0;
                                    }


                                    if (($resCode === '2000') || ($resCode === '2001') || ($resCode === '2002')) {

                                        // $flag = TRUE;

                                        if ($resCode === '2000') {
                                            $flagProduct++;
                                        }
                                        if ($resCode === '2002') // duplicate product
                                        {
                                            // Update Product
                                            $url = "https://crm.zoho.com/crm/private/xml/Products/updateRecords";
                                            $query = "authtoken=$zohoCRMAuthtoken&newFormat=1&scope=crmapi&id=$prodId&xmlData=$ProductXML";
                                            $ch = curl_init();
                                            /* set url to send post request */
                                            curl_setopt($ch, CURLOPT_URL, $url);
                                            /* allow redirects */
                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                            /* return a response into a variable */
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                            /* times out after 30s */
                                            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                            /* set POST method */
                                            curl_setopt($ch, CURLOPT_POST, 1);
                                            /* add POST fields parameters */
                                            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                            // Execute cUrl session

                                            $response = curl_exec($ch);
                                            curl_close($ch);
                                            $res = simplexml_load_string($response);


                                        }


                                        $prodNumber++;
                                        $productDetails = $sxeInvoiceXML->row->FL[3]->addChild("product");
                                        $productDetails->addAttribute('no', $prodNumber);
                                        $FLsku = $productDetails->addChild("FL", $prodId);
                                        $FLsku->addAttribute('val', 'Product Id');
                                        $FLquantity = $productDetails->addChild("FL", $m_quantity);
                                        $FLquantity->addAttribute('val', 'Quantity');
                                        $FLprice = $productDetails->addChild("FL", $prodUnitPrice);
                                        $FLprice->addAttribute('val', 'Unit Price');
                                        $FLprice = $productDetails->addChild("FL", $prodListPrice);
                                        $FLprice->addAttribute('val', 'List Price');
                                        $FLprice = $productDetails->addChild("FL", $totalLineItems);
                                        $FLprice->addAttribute('val', 'Total');
                                        $FLprice = $productDetails->addChild("FL", $discount);
                                        $FLprice->addAttribute('val', 'Discount');
                                        $FLprice = $productDetails->addChild("FL", $totalAfterDiscount);
                                        $FLprice->addAttribute('val', 'Total after Discount');
                                        $FLprice = $productDetails->addChild("FL", $tax);
                                        $FLprice->addAttribute('val', 'Tax');
                                        $FLprice = $productDetails->addChild("FL", $netTotal);
                                        $FLprice->addAttribute('val', 'Net Total');
                                    } else {

                                        $flag = FALSE;
                                        $st = 'YES';
                                        $AccountHead = 'PRODUCT';
                                        // SaveErrorDetails fails when $prodName has special characters used in MySQL
                                        SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Product ' . $prodName);
                                    }


                                }// for each product

                                $InvoiceXML = $sxeInvoiceXML->asXML();


                                if ($salesOrder === 'TRUE') {
                                    $url = "https://crm.zoho.com/crm/private/xml/SalesOrders/insertRecords";
                                } else {
                                    $url = "https://crm.zoho.com/crm/private/xml/Quotes/insertRecords";
                                }
                                $query = "authtoken=$zohoCRMAuthtoken&scope=crmapi&duplicateCheck=1&version=4&xmlData=$InvoiceXML";

                                $ch = curl_init();
                                /* set url to send post request */
                                curl_setopt($ch, CURLOPT_URL, $url);
                                /* allow redirects */
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                /* return a response into a variable */
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                /* times out after 30s */
                                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                /* set POST method */
                                curl_setopt($ch, CURLOPT_POST, 1);
                                /* add POST fields parameters */
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                // Execute cUrl session

                                $response = curl_exec($ch);

                                // $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                                curl_close($ch);

                                // check if it returns 200, or else return false
                                $res = simplexml_load_string($response);
                                if ($res->result != FALSE) {
                                    $resCode = (string)$res->result->row->success->code;
                                } else {
                                    $resCode = 0;
                                }
                                if (($resCode === '2000') || ($resCode === '2001') || ($resCode === '2002')) {

                                    // $flag = TRUE;

                                    if ($resCode === '2000') {
                                        $flagOrder++;
                                    }
                                } else {

                                    $flag = FALSE;
                                    $st = 'YES';
                                    $AccountHead = 'ORDER';
                                    SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Order Number: ' . $orders[$key]['id']);
                                }

                                //End of Form Submission
                            } else {
                                $AccountHead = 'CUSTOMER';

                                SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Customer: ' . $accountName);
                                $flag = FALSE;
                            }
                        } catch (Exception $e) {
                            $msg = $e;
                            $flag = FALSE;
                        }
                    }//end or order list
                }
            }
        }
    } catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }
    // Create reports.

    $status = 'YES';
    $totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
    $userDataArrTransaction = array(
        'user_id' => $credentials['userId'],
        'crm_type' => $crmType,
        'no_customer_data' => $flagCustomer,
        'no_product_data' => $flagProduct,
        'no_order_data' => $flagOrder,
        'last_sync_time' => time(),
        'total_transaction' => $totalTransaction,
        'status' => $status,
        'added_on' => time()
    );
    $credentials['counterOrders'] = $flagOrder;
    $credentials['counterProducts'] = $flagProduct;
    $credentials['counterAccounts'] = $flagCustomer;
    include "/var/www/html/app/mysql/mysqlconstants.php";

    tblTransectionDetail($credentials, $crmType, $userDataArrTransaction);

    sendSyncReport($credentials, $crmType);
    return $flagOrder;
} //Done

function shopifyToZohoInventoryCRM($credentials)
{

    include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;
    $cloudApp = 'ZOHO_INVENTORY';

    $subscriptionId = $credentials['subscription_id'];
    $crmType = 'shopifyTozohoinventory';//$credentials['crm_type'];
    $contextVal = //$credentials['contextValue'];

    $shopifyURL = $credentials['app1Details']['shopify_url'];
    $shopifyAPI = $credentials['app1Details']['shopify_api'];
    $shopifyPassword = $credentials['app1Details']['shopify_password'];

    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];


    $zohoInventoryAuthToken = $credentials['app2Details']['zoho_auth_id'];
    $zohoInventoryOrgID = $credentials['app2Details']['zoho_organisation_id'];

    //........... Get HubSpot access token if already Access Permission Given.......

    try {
        //............ORDER LIST COLLECTIONG.................

        $orderUrl = $shopifyURL . "/admin/orders.json?created_at_min=" . $min_date_created . "&created_at_max=" . $max_date_created . "&direction=asc";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $orderUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code === 200) {
            $orders = json_decode($return, TRUE);

            $count = count($orders);
            if ($count > 0) {
                foreach ($orders as $num => $getOrders) {
                    foreach ($getOrders as $key => $value) {
                        //Customer Detials Collect
                        $customerId = $value['customer']['id'];
                        $email = $value['email'];
                        $phone = $value['phone'];
                        $orderId = $value['order_number'];
                        $subject = 'Order Number ' . $orderId;

                        //Status of Order./................
                        $currentOrderStatus = $value['financial_status'];
                        //Status of Order./................

                        if (($currentOrderStatus === 'complete') || ($currentOrderStatus === 'pending')) {
                            $salesOrder = 'confirmed';
                        } else {
                            $salesOrder = $currentOrderStatus;

                        }

                        if (!empty($value['shipping_address'])) {
                            $firstName = $value['shipping_address']['first_name'];
                            $lastName = $value['shipping_address']['last_name'];
                            $accountName = $firstName . ' ' . $lastName;
                            $phone = $value['shipping_address']['phone'];

                            $mailingStreet = $value['shipping_address']['address1'] . "," . $value['shipping_address']['address2'];
                            $otherStreet = $value['shipping_address']['address1'] . "," . $value['shipping_address']['address2'];
                            $mailingCity = $value['shipping_address']['city'];
                            $otherCity = $value['shipping_address']['city'];
                            $mailingState = $value['shipping_address']['province'];
                            $otherState = $value['shipping_address']['province'];
                            $mailingZip = $value['shipping_address']['zip'];
                            $otherZip = $value['shipping_address']['zip'];
                            $mailingCountry = $value['shipping_address']['country'];
                            $otherCountry = $value['shipping_address']['country'];
                            $contactDetails = json_encode(array(
                                "contact_name" => $firstName . " " . $lastName . " ( " . $email . " ) ",
                                "company_name" => $value['shipping_address']['company'],
                                "contact_type" => "customer",
                                "billing_address" => array(
                                    "address" => $mailingStreet,
                                    "city" => $mailingCity,
                                    "state" => $mailingState,
                                    "zip" => $mailingZip,
                                    "country" => $mailingCountry,
                                    "fax" => ""
                                ),
                                "shipping_address" => array(
                                    "address" => $otherStreet,
                                    "city" => $otherCity,
                                    "state" => $otherState,
                                    "zip" => $otherZip,
                                    "country" => $otherCountry,
                                    "fax" => ""
                                ),
                                "contact_persons" => array(
                                    array(
                                        "first_name" => $firstName,
                                        "last_name" => $lastName,
                                        "email" => $email,
                                        "phone" => $phone,
                                    )
                                )
                            ));
                        } else if (!empty($value['billing_address'])) {
                            $firstName = $value['billing_address']['first_name'];
                            $lastName = $value['billing_address']['last_name'];
                            $accountName = $firstName . ' ' . $lastName;
                            $phone = $value['billing_address']['phone'];

                            $mailingStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                            $otherStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                            $mailingCity = $value['billing_address']['city'];
                            $otherCity = $value['billing_address']['city'];
                            $mailingState = $value['billing_address']['province'];
                            $otherState = $value['billing_address']['province'];
                            $mailingZip = $value['billing_address']['zip'];
                            $otherZip = $value['billing_address']['zip'];
                            $mailingCountry = $value['billing_address']['country'];
                            $otherCountry = $value['billing_address']['country'];
                            $contactDetails = json_encode(array(
                                "contact_name" => $firstName . " " . $lastName . " ( " . $email . " ) ",
                                "company_name" => $value['billing_address']['company'],
                                "contact_type" => "customer",
                                "billing_address" => array(
                                    "address" => $mailingStreet,
                                    "city" => $mailingCity,
                                    "state" => $mailingState,
                                    "zip" => $mailingZip,
                                    "country" => $mailingCountry,
                                    "fax" => ""
                                ),
                                "shipping_address" => array(
                                    "address" => $otherStreet,
                                    "city" => $otherCity,
                                    "state" => $otherState,
                                    "zip" => $otherZip,
                                    "country" => $otherCountry,
                                    "fax" => ""
                                ),
                                "contact_persons" => array(
                                    array(
                                        "first_name" => $firstName,
                                        "last_name" => $lastName,
                                        "email" => $email,
                                        "phone" => $phone,
                                    )
                                )
                            ));
                        } else {
                            $firstName = $value['customer']['first_name'];
                            $lastName = $value['customer']['last_name'];
                            $accountName = $firstName . ' ' . $lastName;
                            $email = $value['email'];
                            $phone = $value['customer']['phone'];

                            $mailingStreet = $value['customer']['default_address']['address1'] . "," . $value['customer']['default_address']['address2'];
                            $otherStreet = $value['customer']['default_address']['address1'] . "," . $value['customer']['default_address']['address2'];
                            $mailingCity = $value['customer']['default_address']['city'];
                            $otherCity = $value['customer']['default_address']['city'];
                            $mailingState = $value['customer']['default_address']['province'];
                            $otherState = $value['customer']['default_address']['province'];
                            $mailingZip = $value['customer']['default_address']['zip'];
                            $otherZip = $value['customer']['default_address']['zip'];
                            $mailingCountry = $value['customer']['default_address']['country'];
                            $otherCountry = $value['customer']['default_address']['country'];
                            $contactDetails = json_encode(array(
                                "contact_name" => $firstName . " " . $lastName . " ( " . $email . " ) ",
                                "company_name" => $value['customer']['default_address']['company'],
                                "contact_type" => "customer",
                                "billing_address" => array(
                                    "address" => $mailingStreet,
                                    "city" => $mailingCity,
                                    "state" => $mailingState,
                                    "zip" => $mailingZip,
                                    "country" => $mailingCountry,
                                    "fax" => ""
                                ),
                                "shipping_address" => array(
                                    "address" => $otherStreet,
                                    "city" => $otherCity,
                                    "state" => $otherState,
                                    "zip" => $otherZip,
                                    "country" => $otherCountry,
                                    "fax" => ""
                                ),
                                "contact_persons" => array(
                                    array(
                                        "first_name" => $firstName,
                                        "last_name" => $lastName,
                                        "email" => $email,
                                        "phone" => $phone,
                                    )
                                )
                            ));
                        }

                        $flag = FALSE;
                        try {

                            $url = "https://inventory.zoho.com/api/v1/contacts";
                            $query = "organization_id=" . $zohoInventoryOrgID . "&authtoken=" . $zohoInventoryAuthToken . "&JSONString=" . $contactDetails;
                            $ch = curl_init();
                            /* set url to send post request */
                            curl_setopt($ch, CURLOPT_URL, $url);
                            /* allow redirects */
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                            /* return a response into a variable */
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            /* times out after 30s */
                            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                            /* set POST method */
                            curl_setopt($ch, CURLOPT_POST, 1);
                            /* add POST fields parameters */
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                            // Execute cUrl session
                            $response = curl_exec($ch);
                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


                            if ($http_code === 201 || $http_code === 400) {
                                // $flag = TRUE;

                                if ($http_code === 201) {
                                    $msg = json_decode($response, TRUE);
                                    $retVal = $msg['code'];
                                    $customerID = $msg['contact']['contact_id'];
                                    $flagCustomer++;
                                } else {  //For Duplicate Contact Detected
                                    //echo $contactName;
                                    $url = "https://inventory.zoho.com/api/v1/contacts?authtoken=" . $zohoInventoryAuthToken . "&organization_id=" . $zohoInventoryOrgID . "&email=" . $email;
                                    $ch = curl_init();
                                    /* set url to send post request */
                                    curl_setopt($ch, CURLOPT_URL, $url);
                                    /* allow redirects */
                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                    /* return a response into a variable */
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                    /* times out after 30s */
                                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                    /* set POST method */
                                    curl_setopt($ch, CURLOPT_POST, 0);


                                    // Execute cUrl session
                                    $response = curl_exec($ch);
                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                    $msg = json_decode($response, TRUE);
                                    $customerID = $msg['contacts'][0]['contact_id'];

                                    //echo "Old Customer :";
                                    //echo $customerID;
                                    curl_close($ch);
                                }

                                // Order Details Collect
                                $subject = 'Order Number ' . $orderId;
                                $grandTotal = $value['total_price'];
                                $subTotal = $value['subtotal_price'];
                                $tax = $value['total_tax'];
                                $adjustment = $grandTotal - $subTotal;
                                //Billing Address Detial

                                $orderBillingStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                                $orderBillingCity = $value['billing_address']['city'];
                                $orderBillingZip = $value['billing_address']['province'];
                                $orderBillingCountry = $value['billing_address']['zip'];
                                $orderBillingState = $value['billing_address']['country'];
                                //Shipping Address Detial
                                $orderShippingStreet = $value['shipping_address']['address1'] . "," . $value['billing_address']['address2'];
                                $orderShippingCity = $value['shipping_address']['city'];
                                $orderShippingZip = $value['shipping_address']['province'];
                                $orderShippingCountry = $value['shipping_address']['zip'];
                                $orderShippingState = $value['shipping_address']['country'];


                                //PRODUCT DETAILS COLLECT FROM ORDER
                                $totalProduct = count($value['line_items']);
                                $prodNumber = 0;
                                for ($i = 0; $i < $totalProduct; $i++) {
                                    //PRODUCT ID COLLECTED

                                    $productId = $value['line_items'][$i]['product_id'];
                                    $prodCode = $value['line_items'][$i]['sku'];

                                    $m_quantity = $value['line_items'][$i]['quantity'];
                                    $prodListPrice = $value['line_items'][$i]['price'];
                                    $prodUnitPrice = $value['line_items'][$i]['price'];
                                    $total = $value['line_items'][$i]['price'];
                                    $totalLineItems = $m_quantity * $total;
                                    //$discount = $total - $totalAfterDiscount;
                                    $discount = $value['line_items'][$i]['total_discount'];
                                    $totalAfterDiscount = $totalLineItems - $discount;

                                    if ($value['line_items'][$i]['taxable'] == 1) {
                                        $tax = $value['line_items'][$i]['tax_lines'][0]['price'];
                                    } else {
                                        $tax = 0;
                                    }

                                    $netTotal = $totalAfterDiscount + $tax;

                                    //Product Detail Collect

                                    $productUrl = $shopifyURL . "/admin/products/" . $productId . ".json";
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $orderUrl);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    $return = curl_exec($ch);
                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                    curl_close($ch);
                                    $productDetail = json_decode($return, TRUE);

                                    //End of Product Details


                                    //PRODUCT DETAILS IF EXISTS .................
                                    $prodName = $value['line_items'][$i]['name'];
                                    $stock_quantity = '100';
                                    $price = $value['line_items'][$i]['price'];
                                    $description = $value['line_items'][$i]['name'];


                                    /* Insert each product into zoho Inventory */
                                    $itemDetails = json_encode(array(
                                        "item_type" => "inventory",
                                        "name" => $prodName,
                                        "rate" => $prodUnitPrice,
                                        "sku" => $prodCode,
                                        "initial_stock" => '100',
                                        "initial_stock_rate" => '100',
                                        "purchase_rate" => '0',
                                    ));
                                    /* Copy Product details ordered for inserting with Sales Order */
                                    $orderItems[$i]['name'] = $prodName;
                                    $orderItems[$i]['description'] = $prodName; //str_replace(',', '', $description);
                                    $orderItems[$i]['rate'] = $prodUnitPrice;
                                    $orderItems[$i]['quantity'] = $m_quantity;
                                    $orderItems[$i]['unit'] = "qty";
                                    $orderItems[$i]['item_total'] = $netTotal;
                                    $url = "https://inventory.zoho.com/api/v1/items";
                                    $query = "organization_id=" . $zohoInventoryOrgID . "&authtoken=" . $zohoInventoryAuthToken . "&JSONString=" . $itemDetails;
                                    $ch = curl_init();
                                    /* set url to send post request */
                                    curl_setopt($ch, CURLOPT_URL, $url);
                                    /* allow redirects */
                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                    /* return a response into a variable */
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                    /* times out after 30s */
                                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                    /* set POST method */
                                    curl_setopt($ch, CURLOPT_POST, 1);
                                    /* add POST fields parameters */
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                    // Execute cUrl session

                                    $response = curl_exec($ch);
                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


                                    if ($http_code === 201) {
                                        curl_close($ch);
                                        $flag = 1;
                                        $flagProduct++;
                                    } else // Product couldn't be added
                                    {
                                        $error = json_decode($response, TRUE);
                                        // Error is not because of duplicate record
                                        if ($error['code'] != 1001) {
                                            // Insert Error Data in Database
                                            $AccountHead = 'PRODUCT';
                                            $st = 'YES';
                                            SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Product ' . $prodCode);
                                        }
                                        curl_close($ch);
                                    }


                                }// for each product

                                /* Insert Sales Order into Zoho Inventory */
                                //$salesOrderDate = DateTime::createFromFormat(DATE_RFC2822, $orders['items'][$key]['created_at']);
                                $date = explode("T", $value['created_at']);

                                $salesOrderDetails = json_encode(array(
                                    "salesorder_number" => $orderId,
                                    "date" => $date[0],
                                    "shipment_date" => "",
                                    "delivery_method" => "None",
                                    "discount" => 0,
                                    "discount_type" => "entity_level",
                                    "customer_id" => $customerID,
                                    "status" => $salesOrder,
                                    "line_items" => $orderItems,
                                    "sub_total" => $subTotal,
                                    "tax_total" => $tax,
                                    "total" => $grandTotal
                                ));

                                $url = "https://inventory.zoho.com/api/v1/salesorders?ignore_auto_number_generation=TRUE";
                                $query = "organization_id=" . $zohoInventoryOrgID . "&authtoken=" . $zohoInventoryAuthToken . "&JSONString=" . $salesOrderDetails;
                                $ch = curl_init();
                                /* set url to send post request */
                                curl_setopt($ch, CURLOPT_URL, $url);
                                /* allow redirects */
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                /* return a response into a variable */
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                /* times out after 30s */
                                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                /* set POST method */
                                curl_setopt($ch, CURLOPT_POST, 1);
                                /* add POST fields parameters */
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

                                $response = curl_exec($ch);
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                $zohoSalesOrderDetails = json_decode($response, TRUE);
                                curl_close($ch);
                                if ($http_code === 201) {
                                    $flagOrder++;

                                } else /* Order addition unsuccessful */ {
                                    $error = json_decode($response, TRUE);
                                    // Insert Error Data in Database
                                    $AccountHead = 'ORDER';
                                    $st = 'YES';
                                    $flagErr = SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding order: ' . $orderId . ':' . $error['message']);
                                }

                                //End of Form Submission
                            } else {
                                $AccountHead = 'CUSTOMER';

                                SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Customer: ' . $accountName);
                                $flag = FALSE;
                            }
                        } catch (Exception $e) {
                            $msg = $e;
                            $flag = FALSE;
                        }
                    }//end or order list
                }
            }
        }
    } catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }
    // Create reports.

    $status = 'YES';
    $totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
    $userDataArrTransaction = array(
        'user_id' => $credentials['userId'],
        'crm_type' => $crmType,
        'no_customer_data' => $flagCustomer,
        'no_product_data' => $flagProduct,
        'no_order_data' => $flagOrder,
        'last_sync_time' => time(),
        'total_transaction' => $totalTransaction,
        'status' => $status,
        'added_on' => time()
    );
    $credentials['counterOrders'] = $flagOrder;
    $credentials['counterProducts'] = $flagProduct;
    $credentials['counterAccounts'] = $flagCustomer;
    include "/var/www/html/app/mysql/mysqlconstants.php";

    tblTransectionDetail($credentials, $crmType, $userDataArrTransaction);

    sendSyncReport($credentials, $crmType);
    return $flagOrder;
} //Done

function shopifyToVTigerCRM($credentials)
{

    include "$_SERVER[DOCUMENT_ROOT]/app/mysql/mysqlconstants.php";
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;
    $cloudApp = 'ZOHO_INVENTORY';

    $subscriptionId = $credentials['subscription_id'];
    $crmType = 'shopifyTovtiger';//$credentials['crm_type'];
    $contextVal = //$credentials['contextValue'];

    $shopifyURL = $credentials['app1Details']['shopify_url'];
    $shopifyAPI = $credentials['app1Details']['shopify_api'];
    $shopifyPassword = $credentials['app1Details']['shopify_password'];

    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];


    $vTigerUserName = $credentials['app2Details']['vtiger_username'];
    $vTigerAccessKey = $credentials['app2Details']['vtiger_key'];
    $vTigerEndpoint = $credentials['app2Details']['vtiger_endpoint'];

// Get vTiger access token
    $url = $vTigerEndpoint . "/webservice.php?operation=getchallenge&username=" . $vTigerUserName;
    $http_headres = array(
        "Content-Type: application/json",
        "Accept: application/json",
        "cache-control : no-cache"
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
    $return = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code === 200) {

        $vTigerAuth = json_decode($return, TRUE);
        $vTigerAuthToken = $vTigerAuth['result']['token'];
    }

    $cloudApp = 'vTigerCRM';
    $url = $vTigerEndpoint . "/webservice.php";
    $generatedKey = md5($vTigerAuthToken . $vTigerAccessKey);
    $postfields = array('operation' => 'login', 'username' => $vTigerUserName,
        'accessKey' => $generatedKey);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    $return = curl_exec($ch);
    //var_dump($return);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($http_code === 200) {

        $vTigerLogin = json_decode($return, TRUE);
        $vTigerSessionID = $vTigerLogin['result']['sessionName'];
        $vTigerUserID = $vTigerLogin['result']['userId'];

    }
    //........... Get HubSpot access token if already Access Permission Given.......

    try {
        //............ORDER LIST COLLECTIONG.................
        $orderUrl = $shopifyURL . "/admin/orders.json?created_at_min=" . $min_date_created . "&created_at_max=" . $max_date_created . "&direction=asc";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $orderUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code === 200) {
            $orders = json_decode($return, TRUE);
            $count = count($orders);

            foreach ($orders as $num => $getOrders) {
                foreach ($getOrders as $key => $value) {
                    //Customer Detials Collect
                    $customerId = $value['customer']['id'];
                    $email = $value['email'];
                    $phone = $value['phone'];
                    $orderId = $value['order_number'];
                    $subject = 'Order Number ' . $orderId;

                    //Status of Order./................
                    $currentOrderStatus = $value['financial_status'];
                    //Status of Order./................

                    if (($currentOrderStatus === 'complete') || ($currentOrderStatus === 'pending')) {
                        $salesOrder = 'TRUE';
                    } else {

                        $salesOrder = 'FALSE';

                    }
                    //ORDER PREVIOUSLY INSERT IN SALESORDER  CHECKING.
                    $orderExistQuery = "select count(*) from SalesOrder where subject='$subject';";

                    $queryOrderParam = urlencode($orderExistQuery);
                    $orderParams = "sessionName=$vTigerSessionID&operation=query&query=$queryOrderParam";
                    $getOrderUrl = $vTigerEndpoint . "/webservice.php?" . $orderParams;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $getOrderUrl);
                    curl_setopt($ch, CURLOPT_POST, 0);
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                    $response = curl_exec($ch);

                    $collectedCount = json_decode($response, true);
                    $collectedSalesOrderCountNo = $collectedCount['result'][0]['count'];

                    //ORDER PREVIOUSLY INSERT IN QUOTE  CHECKING.

                    $quoteExistQuery = "select count(*) from Quotes where subject='$subject';";

                    $quoteOrderParam = urlencode($quoteExistQuery);
                    $quoteParams = "sessionName=$vTigerSessionID&operation=query&query=$quoteOrderParam";
                    $getQuoteUrl = $vTigerEndpoint . "/webservice.php?" . $quoteParams;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $getQuoteUrl);
                    curl_setopt($ch, CURLOPT_POST, 0);
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                    $response = curl_exec($ch);

                    $collectedCount = json_decode($response, true);
                    $collectedQuoteCountNo = $collectedCount['result'][0]['count'];
                    if ($collectedSalesOrderCountNo == 0 && $collectedQuoteCountNo == 0) {
                        if (!empty($value['shipping_address'])) {
                            $firstName = $value['shipping_address']['first_name'];
                            $lastName = $value['shipping_address']['last_name'];
                            $accountName = $firstName . ' ' . $lastName;
                            $phone = $value['shipping_address']['phone'];

                            $mailingStreet = $value['shipping_address']['address1'] . "," . $value['shipping_address']['address2'];
                            $otherStreet = $value['shipping_address']['address1'] . "," . $value['shipping_address']['address2'];
                            $mailingCity = $value['shipping_address']['city'];
                            $otherCity = $value['shipping_address']['city'];
                            $mailingState = $value['shipping_address']['province'];
                            $otherState = $value['shipping_address']['province'];
                            $mailingZip = $value['shipping_address']['zip'];
                            $otherZip = $value['shipping_address']['zip'];
                            $mailingCountry = $value['shipping_address']['country'];
                            $otherCountry = $value['shipping_address']['country'];

                        } else if (!empty($value['billing_address'])) {
                            $firstName = $value['billing_address']['first_name'];
                            $lastName = $value['billing_address']['last_name'];
                            $accountName = $firstName . ' ' . $lastName;
                            $phone = $value['billing_address']['phone'];

                            $mailingStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                            $otherStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                            $mailingCity = $value['billing_address']['city'];
                            $otherCity = $value['billing_address']['city'];
                            $mailingState = $value['billing_address']['province'];
                            $otherState = $value['billing_address']['province'];
                            $mailingZip = $value['billing_address']['zip'];
                            $otherZip = $value['billing_address']['zip'];
                            $mailingCountry = $value['billing_address']['country'];
                            $otherCountry = $value['billing_address']['country'];

                        } else {
                            $firstName = $value['customer']['first_name'];
                            $lastName = $value['customer']['last_name'];
                            $accountName = $firstName . ' ' . $lastName;
                            $email = $value['email'];
                            $phone = $value['customer']['phone'];

                            $mailingStreet = $value['customer']['default_address']['address1'] . "," . $value['customer']['default_address']['address2'];
                            $otherStreet = $value['customer']['default_address']['address1'] . "," . $value['customer']['default_address']['address2'];
                            $mailingCity = $value['customer']['default_address']['city'];
                            $otherCity = $value['customer']['default_address']['city'];
                            $mailingState = $value['customer']['default_address']['province'];
                            $otherState = $value['customer']['default_address']['province'];
                            $mailingZip = $value['customer']['default_address']['zip'];
                            $otherZip = $value['customer']['default_address']['zip'];
                            $mailingCountry = $value['customer']['default_address']['country'];
                            $otherCountry = $value['customer']['default_address']['country'];

                        }

                        $flag = FALSE;

                        //ACCOUNT EXIST OR NOT CHECKING
                        $accountExistQuery = "select count(*) from Accounts where email1='$email';";
                        $queryAccountParam = urlencode($accountExistQuery);
                        $accountParams = "sessionName=$vTigerSessionID&operation=query&query=$queryAccountParam";
                        $getAccountUrl = $vTigerEndpoint . "/webservice.php?" . $accountParams;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $getAccountUrl);
                        curl_setopt($ch, CURLOPT_POST, 0);
                        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                        $response = curl_exec($ch);

                        $collectedCount = json_decode($response, true);
                        $collectedCountNo = $collectedCount['result'][0]['count'];

                        if ($collectedCountNo == 0) {
                            // Insert Account details..................
                            $accountInsertUrl = $vTigerEndpoint . "/webservice.php";
                            $accountFields = array(
                                'accountname' => $accountName,
                                'assigned_user_id' => $vTigerUserID,
                                'phone' => $phone,
                                'email1' => $email,

                                'bill_street' => $mailingStreet,
                                'bill_city' => $mailingCity,
                                'bill_state' => $mailingState,
                                'bill_country' => $mailingCountry,
                                'bill_code' => $mailingZip,
                                'ship_street' => $otherStreet,
                                'ship_city' => $otherCity,
                                'ship_state' => $otherState,
                                'ship_country' => $otherCountry,
                                'ship_code' => $otherZip

                            );
                            $accountFieldsJSON = json_encode($accountFields);
                            //var_dump($accountFieldsJSON);
                            $moduleName = 'Accounts';
                            $postFields = array("sessionName" => $vTigerSessionID, "operation" => 'create',
                                "element" => $accountFieldsJSON, "elementType" => $moduleName);
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $accountInsertUrl);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                            //$return = curl_exec($ch);

                            // Execute cUrl session

                            $response = curl_exec($ch);


                            $collectedResponseId = json_decode($response, true);
                            $collectedAccountId = $collectedResponseId['result']['id'];
                        } else {

                            //Collection Account Id From Accounts..............
                            $accountIdQuery = "select id from Accounts where email1='$email';";
                            //urlencode to as its sent over http.
                            $queryParam = urlencode($accountIdQuery);
                            $accountParams = "sessionName=$vTigerSessionID&operation=query&query=$queryParam";


                            $getAccountUrl = $vTigerEndpoint . "/webservice.php?" . $accountParams;

                            //var_dump($getAccountUrl);

                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $getAccountUrl);
                            curl_setopt($ch, CURLOPT_POST, 0);
                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);


                            $response = curl_exec($ch);


                            $collectedResponseId = json_decode($response, true);
                            //Account Id
                            $collectedAccountId = $collectedResponseId['result'][0]['id'];


                            //End ofCollection Account Id From Accounts..............
                        }

                        //End of Accounts Opereation

                        //CONTACT EXIST OR NOT CHECKING
                        $contactExistQuery = "select count(*) from Contacts where account_id='$collectedAccountId';";


                        $queryContactParam = urlencode($contactExistQuery);
                        $contactParams = "sessionName=$vTigerSessionID&operation=query&query=$queryContactParam";
                        $getContactUrl = $vTigerEndpoint . "/webservice.php?" . $contactParams;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $getContactUrl);
                        curl_setopt($ch, CURLOPT_POST, 0);
                        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

                        $response = curl_exec($ch);

                        $collectedCount = json_decode($response, true);


                        $collectedCountNo = $collectedCount['result'][0]['count'];

                        if ($collectedCountNo == 0) {
                            // Create Contact for this account..................
                            $accountInsertUrl = $vTigerEndpoint . "/webservice.php";
                            $contactData = array(
                                'firstname' => $firstName,
                                'lastname' => $lastName,
                                'assigned_user_id' => $vTigerUserID,
                                'contacttype' => 'Primary',
                                'account_id' => $collectedAccountId,
                                'email' => $email,
                                'mobile' => $phone,
                                'mailingstreet' => $mailingStreet,
                                'otherstreet' => $otherStreet,
                                'mailingcity' => $mailingCity,
                                'othercity' => $otherCity,
                                'mailingstate' => $mailingState,
                                'otherstate' => $otherState,
                                'mailingzip' => $mailingZip,
                                'otherzip' => $otherZip,
                                'mailingcountry' => $mailingCountry,
                                'othercountry' => $otherCountry
                            );
                            $contactFieldsJSON = json_encode($contactData); //Encoding to json formate for communicating with server
                            //var_dump($contactFieldsJSON);
                            $moduleName = 'Contacts';

                            $contactParams = array("sessionName" => $vTigerSessionID, "operation" => 'create',
                                "element" => $contactFieldsJSON, "elementType" => $moduleName);

                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $accountInsertUrl);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $contactParams);

                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);


                            $response = curl_exec($ch);
                            $collectedResponseContactId = json_decode($response, true);


                            $collectedContactId = $collectedResponseContactId['result']['id'];


                            $responseValue = json_decode($response, TRUE);
                            if ($responseValue['success'] == 'true') {
                                $flagCustomer++;
                            } else {
                                $resCode = $responseValue['error']['code'];
                                $resMsg = $responseValue['error']['message'];
                                $AccountHead = 'CUSTOMER';
                                $st = 'YES';
                                SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Customer: ' . $accountName . ' Error Code: ' . $resCode . 'Reason:' . $resMsg);
                                //$flag = FALSE;
                            }
                            //End of Create Contact for this account..................
                        } else {
                            // Collection Contact Id From Contacts..............

                            $contactIdQuery = "select id from Contacts where email='$email';";
                            $queryParam = urlencode($contactIdQuery);
                            $contactParams = "sessionName=$vTigerSessionID&operation=query&query=$queryParam";
                            $getContactUrl = $vTigerEndpoint . "/webservice.php?" . $contactParams;
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $getContactUrl);
                            curl_setopt($ch, CURLOPT_POST, 0);
                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                            $response = curl_exec($ch);

                            $collectedResponseContactId = json_decode($response, true);
                            $collectedContactId = $collectedResponseContactId['result'][0]['id'];


                            // End ofCollection Account Id From Accounts..............
                        }

                        // Order Details Collect
                        $subject = 'Order Number ' . $orderId;
                        $grandTotal = $value['total_price'];
                        $subTotal = $value['subtotal_price'];
                        $tax = $value['total_tax'];
                        $adjustment = ($grandTotal - $subTotal)-$tax;
                        //Billing Address Detial

                        $orderBillingStreet = $mailingStreet;
                        $orderBillingCity = $mailingCity;
                        $orderBillingZip = $mailingZip;
                        $orderBillingCountry = $mailingCountry;
                        $orderBillingState = $mailingState;
                        //Shipping Address Detial
                        $orderShippingStreet = $otherStreet;
                        $orderShippingCity = $otherCity;
                        $orderShippingZip = $otherZip;
                        $orderShippingCountry = $otherCountry;
                        $orderShippingState = $otherState;

                        $lineitem = [];
                        //PRODUCT DETAILS COLLECT FROM ORDER
                        $totalProduct = count($value['line_items']);
                        $prodNumber = 0;

                        for ($i = 0; $i < $totalProduct; $i++) {
                            //PRODUCT ID COLLECTED

                            $productId = $value['line_items'][$i]['product_id'];
                            $prodCode = $value['line_items'][$i]['sku'];

                            $m_quantity = $value['line_items'][$i]['quantity'];
                            $prodListPrice = $value['line_items'][$i]['price'];
                            $prodUnitPrice = $value['line_items'][$i]['price'];
                            $total = $value['line_items'][$i]['price'];

                            //$discount = $total - $totalAfterDiscount;
                            $discount = $value['line_items'][$i]['total_discount'];
                            $totalAfterDiscount = $total - $discount;

                            if ($value['line_items'][$i]['taxable'] == 1) {
                                $tax = $value['line_items'][$i]['tax_lines'][0]['price'];
                            } else {
                                $tax = 0;
                            }

                            $netTotal = $totalAfterDiscount + $tax;

                            //Product Detail Collect

                            $productUrl = $shopifyURL . "/admin/products/" . $productId . ".json";
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $orderUrl);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            $return = curl_exec($ch);
                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                            $productDetail = json_decode($return, TRUE);
                            //End of Product Details


                            //PRODUCT DETAILS IF EXISTS .................
                            $prodName = $value['line_items'][$i]['name'];
                            $stock_quantity = '100';
                            $price = $value['line_items'][$i]['price'];
                            $description = $value['line_items'][$i]['name'];


                            //Product exist or Not checking
                            $productExistQuery = "select count(*) from Products where productcode='$prodCode';";
                            //urlencode to as its sent over http.
                            $queryProductParam = urlencode($productExistQuery);
                            $accountParams = "sessionName=$vTigerSessionID&operation=query&query=$queryProductParam";
                            $getAccountUrl = $vTigerEndpoint . "/webservice.php?" . $accountParams;
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $getAccountUrl);
                            curl_setopt($ch, CURLOPT_POST, 0);
                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                            $response = curl_exec($ch);

                            $collectedCount = json_decode($response, true);
                            //Account Id
                            $collectedCountNo = $collectedCount['result'][0]['count'];
// For Insert Records with duplicate checking.
                            if ($collectedCountNo == 0) {
                                // INSERT VTIGER PRODUCT
                                $productInsertUrl = $vTigerEndpoint . "/webservice.php";
                                $productData = array(
                                    'productcode' => $prodCode,
                                    'productname' => $prodName,
                                    'discontinued' => 1,
                                    "comment" => '',
                                    "qty_per_unit" => $m_quantity,
                                    "qtyinstock" => $stock_quantity,
                                    "list_price" => $prodListPrice,
                                    "unit_price" => $prodUnitPrice,
                                    "isclosed" => 1,
                                    "currency1" => 0,
                                    "currency_id" => "21x1",
                                    "hdnProductId" => $productId,
                                    "description" => $description,
                                    "assigned_user_id" => $vTigerUserID,
                                    "totalProductCount" => $m_quantity
                                );

                                $productFieldsJSON = json_encode($productData);
                                $moduleName = 'Products';


                                $productfields = array("operation" => 'create', "sessionName" => $vTigerSessionID,
                                    "element" => $productFieldsJSON, "elementType" => $moduleName);

                                // Execute cUrl session
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $productInsertUrl);
                                curl_setopt($ch, CURLOPT_POST, 1);
                                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $productfields);
                                curl_setopt($ch, CURLOPT_HEADER, 0);
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);


                                $response = curl_exec($ch);


                                //Collected Quote Id
                                $collectedResponseId = json_decode($response, true);

                                $collectedProductId = $collectedResponseId['result']['id'];
                                $collectedProductBasePrice = $collectedResponseId['result']['unit_price'];
                                $collectedProductQuantity = $collectedResponseId['result']['qty_per_unit'];

                                $responseValue = json_decode($response, TRUE);
                                // End of Sales Orders.................


                                if ($responseValue['success'] == 'true') {
                                    $flagProduct++;
                                } else {
                                    $resCode = $responseValue['error']['code'];
                                    $resMsg = $responseValue['error']['message'];
                                    $flag = FALSE;
                                    $st = 'YES';
                                    $AccountHead = 'PRODUCT';
                                    SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Product : ' . $prodName . ' Error Code: ' . $resCode . ' Reason:' . $resMsg);

                                }

                            } else {


                                //Collection Product Id From Products..............
                                $productIdQuery = "select * from Products where productcode='$prodCode';";
                                //urlencode to as its sent over http.
                                $queryProductParam = urlencode($productIdQuery);
                                $productParams = "sessionName=$vTigerSessionID&operation=query&query=$queryProductParam";


                                $getProductUrl = $vTigerEndpoint . "/webservice.php?" . $productParams;


                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $getProductUrl);
                                curl_setopt($ch, CURLOPT_POST, 0);
                                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($ch, CURLOPT_HEADER, 0);
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);


                                $response = curl_exec($ch);


                                $collectedResponseId = json_decode($response, true);
                                //Account Id
                                $collectedProductId = $collectedResponseId['result'][0]['id'];
                                $collectedProductBasePrice = $collectedResponseId['result'][0]['unit_price'];
                                $collectedProductQuantity = $collectedResponseId['result'][0]['qty_per_unit'];

                                //End of Collection Product Id From Products..............


                            }

                            $lineitem[$prodNumber] = ['productid' => $collectedProductId, 'listprice' => $collectedProductBasePrice, 'quantity' => $m_quantity, 'discount_amount' => $discount];

                            $prodNumber++;
                        }// for each product

                        //Retrieve All List Available From vTiger..............
                        $listTypeParams = "sessionName=$vTigerSessionID&operation=listtypes";


                        $getListTypeUrl = $vTigerEndpoint . "/webservice.php?" . $listTypeParams;

                        //var_dump($getAccountUrl);

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $getListTypeUrl);
                        curl_setopt($ch, CURLOPT_POST, 0);
                        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);


                        $response = curl_exec($ch);


                        $encode = json_decode($response, TRUE);

                        $listType = $encode['result']['types'];


                        $salesOrderExist = array_search("SalesOrder", $listType);

                        if ($salesOrder === 'TRUE') {

                            if ($salesOrderExist > 0) {


                                $orderInsertUrl = $vTigerEndpoint . "/webservice.php";

                                $salesOrderData = array(
                                    'subject' => $subject,
                                    'sostatus' => 'New',
                                    'account_id' => $collectedAccountId,
                                    'customerno' => $customerId,
                                    //'quote_id'=>$collectedQuoteId,
                                    'bill_street' => $orderBillingStreet,
                                    'bill_city' => $orderBillingCity,
                                    'bill_code' => $orderBillingZip,
                                    'bill_country' => $orderBillingCountry,
                                    'bill_state' => $orderBillingState,
                                    'assigned_user_id' => $vTigerUserID,
                                    'contact_id' => $collectedContactId,
                                    'invoicestatus' => 'New',
                                    'adjustment' => $adjustment,
                                    'exciseduty' => $tax,
                                    'subtotal' => $subTotal,
                                    'total' => $grandTotal,
                                    'ship_city' => $orderShippingCity,
                                    'ship_street' => $orderShippingStreet,
                                    'ship_code' => $orderShippingZip,
                                    'ship_country' => $orderShippingCountry,
                                    'ship_state' => $orderShippingState,
                                    'status' => $currentOrderStatus,
                                    'LineItems' => $lineitem,
                                    'currency_id' => '21x1',
                                    'hdntaxtype' => 'group',
                                    'hdndiscountamount' => $totalAfterDiscount,
                                    'hdnGrandTotal' => $grandTotal,
                                    'hdnSubTotal' => $subTotal,
                                );


                                $salesOrderFieldsJSON = json_encode($salesOrderData);
                                $moduleName = 'SalesOrder';

                                $salesOrderFields = array("operation" => 'create', "sessionName" => $vTigerSessionID,
                                    "element" => $salesOrderFieldsJSON, "elementType" => $moduleName);

                                // Execute cUrl session
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $orderInsertUrl);
                                curl_setopt($ch, CURLOPT_POST, 1);
                                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $salesOrderFields);
                                curl_setopt($ch, CURLOPT_HEADER, 0);
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);


                                $response = curl_exec($ch);


                                $responseValue = json_decode($response, TRUE);
                                if ($responseValue['success'] == 'true') {
                                    $flagOrder++;
                                } else {

                                    $resCode = $responseValue['error']['code'];
                                    $resMsg = $responseValue['error']['message'];
                                    //$flag = FALSE;
                                    $st = 'YES';
                                    $AccountHead = 'ORDER';
                                    SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Order Number: ' . $orders[$key]['id'] . ' Error Code: ' . $resCode . ' Reason:' . $resMsg);

                                }

                            }

                        } else {


                            $quoteInsertUrl = $vTigerEndpoint . "/webservice.php";
                            $productQuote = array(
                                'subject' => $subject,
                                'account_id' => $collectedAccountId,
                                'quotestage' => 'created',
                                'contact_id' => $collectedContactId,
                                'bill_street' => $orderBillingStreet,
                                'bill_city' => $orderBillingCity,
                                'bill_code' => $orderBillingZip,
                                'bill_country' => $orderBillingCountry,
                                'bill_state' => $orderBillingState,
                                'ship_city' => $orderShippingCity,
                                'ship_street' => $orderShippingStreet,
                                'ship_code' => $orderShippingZip,
                                'ship_country' => $orderShippingCountry,
                                'ship_state' => $orderShippingState,
                                'assigned_user_id' => $vTigerUserID,
                                'productid' => $collectedProductId,
                                'LineItems' => $lineitem,
                                'adjustment' => $adjustment,
                                'exciseduty' => $tax,
                                'subtotal' => $subTotal,
                                'total' => $grandTotal,
                            );


                            $productQuoteFieldsJSON = json_encode($productQuote);
                            $moduleName = 'Quotes';

                            $productQuoteFields = array("operation" => 'create', "sessionName" => $vTigerSessionID,
                                "element" => $productQuoteFieldsJSON, "elementType" => $moduleName);

                            // Execute cUrl session
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $quoteInsertUrl);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $productQuoteFields);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);


                            $response = curl_exec($ch);


                            $responseValue = json_decode($response, TRUE);
                            if ($responseValue['success'] == 'true') {
                                $flagOrder++;
                            } else {

                                $resCode = $responseValue['error']['code'];
                                $resMsg = $responseValue['error']['message'];
                                //$flag = FALSE;
                                $st = 'YES';
                                $AccountHead = 'ORDER';
                                SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Quote Number: ' . $orders[$key]['id'] . ' Error Code: ' . $resCode . ' Reason:' . $resMsg);

                            }
                            //Collected Quote Id
                            //$collectedResponseId = json_decode($response, true);

                            //$collectedQuoteId = $collectedResponseId['result']['id'];


                            //End of Quotes Insert .................

                        }

                        //End of Form Submission


                    }
                }//end or order list
            }

        }
    } catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }
    // Create reports.

    $status = 'YES';
    $totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
    $userDataArrTransaction = array(
        'user_id' => $credentials['userId'],
        'crm_type' => $crmType,
        'no_customer_data' => $flagCustomer,
        'no_product_data' => $flagProduct,
        'no_order_data' => $flagOrder,
        'last_sync_time' => time(),
        'total_transaction' => $totalTransaction,
        'status' => $status,
        'added_on' => time()
    );
    $credentials['counterOrders'] = $flagOrder;
    $credentials['counterProducts'] = $flagProduct;
    $credentials['counterAccounts'] = $flagCustomer;
    include "/var/www/html/app/mysql/mysqlconstants.php";

    tblTransectionDetail($credentials, $crmType, $userDataArrTransaction);

    sendSyncReport($credentials, $crmType);
    return $flagOrder;
} //Done

function shopifyToSfdcCRM($credentials)
{
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;

    $subscriptionId = $credentials['subscription_id'];
    $crmType = 'shopifyTosfdc';//$credentials['crm_type'];
    $contextVal = //$credentials['contextValue'];

    $shopifyURL = $credentials['app1Details']['shopify_url'];
    $shopifyAPI = $credentials['app1Details']['shopify_api'];
    $shopifyPassword = $credentials['app1Details']['shopify_password'];

    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];

    $sfdcCredentials = $credentials['app2Details'];
    $priceBookInfo = Sfdc::getPriceBookDetails($sfdcCredentials);
    $credentials['stdPriceBookId'] = $priceBookInfo->Id;
    $cloudApp = 'SFDC';
    //........... Get HubSpot access token if already Access Permission Given.......

    try {
        //............ORDER LIST COLLECTIONG.................

        $orderUrl = $shopifyURL . "/admin/orders.json?created_at_min=" . $min_date_created . "&created_at_max=" . $max_date_created . "&direction=asc";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $orderUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code === 200) {
            $orders = json_decode($return, TRUE);
            $count = count($orders);


            if ($count > 0) {

                foreach ($orders as $num => $getOrders) {

                    foreach ($getOrders as $key => $value) {

                        //Customer Detials Collect
                        if (!empty($value['customer'])) {
                            $customerId = $value['customer']['id'];
                            $accountQuery = "Select Id, Name FROM Account WHERE AccountNumber = '$customerId'";
                        } else {
                            $phoneNo = $value['phone'];
                            $accountQuery = "Select Id, Name FROM Account WHERE Phone = '$phoneNo'";
                        }
                        $currencyExchangeRate = $value['currency'];
                        $discountAmount = $value['total_discounts'];
                        $email = $value['email'];
                        $phone = $value['phone'];
                        $orderId = $value['order_number'];
                        $subject = 'Order Number ' . $orderId;

                        //Status of Order./................
                        $currentOrderStatus = $value['financial_status'];
                        //Status of Order./................

                        $sfdcQuery = "Select Id FROM Order WHERE OrderReferenceNumber = '$orderId'";
                        $queryResult = Sfdc::sfdcFindRecord($sfdcCredentials, $sfdcQuery);

                        if ($queryResult->size === 0) // order doesn't exist
                        {


                            // Search for existing Account

                            $sfdcQuery = $accountQuery;
                            $queryResult = Sfdc::sfdcFindRecord($sfdcCredentials, $sfdcQuery);
                            if ($queryResult->size === 0) // no existing Account
                            {
                                $accounts = array();
                                $accounts[0] = new stdclass();
                                if (!empty($value['shipping_address'])) {
                                    $firstName = $value['shipping_address']['first_name'];
                                    $lastName = $value['shipping_address']['last_name'];
                                    if ($value['shipping_address']['company'] !== null) {
                                        $accountName = $value['shipping_address']['company'];
                                    } else {
                                        $accountName = $firstName . ' ' . $lastName;
                                    }
                                    $phone = $value['shipping_address']['phone'];

                                    $mailingStreet = $value['shipping_address']['address1'] . "," . $value['shipping_address']['address2'];
                                    $otherStreet = $value['shipping_address']['address1'] . "," . $value['shipping_address']['address2'];
                                    $mailingCity = $value['shipping_address']['city'];
                                    $otherCity = $value['shipping_address']['city'];
                                    $mailingState = $value['shipping_address']['province'];
                                    $otherState = $value['shipping_address']['province'];
                                    $mailingZip = $value['shipping_address']['zip'];
                                    $otherZip = $value['shipping_address']['zip'];
                                    $mailingCountry = $value['shipping_address']['country'];
                                    $otherCountry = $value['shipping_address']['country'];

                                    $accounts[0]->BillingCity = $mailingCity;
                                    $accounts[0]->BillingCountry = $mailingCountry;
                                    $accounts[0]->BillingPostalCode = $mailingZip;
                                    $accounts[0]->BillingState = $mailingState;
                                    $accounts[0]->BillingStreet = $mailingStreet;
                                    $accounts[0]->ShippingCity = $otherCity;
                                    $accounts[0]->ShippingCountry = $otherCountry;
                                    $accounts[0]->ShippingPostalCode = $otherZip;
                                    $accounts[0]->ShippingState = $otherState;
                                    $accounts[0]->ShippingStreet = $otherStreet;
                                }
                                else if (!empty($value['billing_address'])) {
                                    $firstName = $value['billing_address']['first_name'];
                                    $lastName = $value['billing_address']['last_name'];
                                    if ($value['shipping_address']['company'] !== null) {
                                        $accountName = $value['shipping_address']['company'];
                                    } else {
                                        $accountName = $firstName . ' ' . $lastName;
                                    }
                                    $phone = $value['billing_address']['phone'];

                                    $mailingStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                                    $otherStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                                    $mailingCity = $value['billing_address']['city'];
                                    $otherCity = $value['billing_address']['city'];
                                    $mailingState = $value['billing_address']['province'];
                                    $otherState = $value['billing_address']['province'];
                                    $mailingZip = $value['billing_address']['zip'];
                                    $otherZip = $value['billing_address']['zip'];
                                    $mailingCountry = $value['billing_address']['country'];
                                    $otherCountry = $value['billing_address']['country'];

                                    $accounts[0]->BillingCity = $mailingCity;
                                    $accounts[0]->BillingCountry = $mailingCountry;
                                    $accounts[0]->BillingPostalCode = $mailingZip;
                                    $accounts[0]->BillingState = $mailingState;
                                    $accounts[0]->BillingStreet = $mailingStreet;
                                    $accounts[0]->ShippingCity = $otherCity;
                                    $accounts[0]->ShippingCountry = $otherCountry;
                                    $accounts[0]->ShippingPostalCode = $otherZip;
                                    $accounts[0]->ShippingState = $otherState;
                                    $accounts[0]->ShippingStreet = $otherStreet;
                                }
                                else {
                                    $firstName = $value['customer']['first_name'];
                                    $lastName = $value['customer']['last_name'];
                                    if ($value['customer']['default_address']['company'] !== null) {
                                        $accountName = $value['customer']['default_address']['company'];
                                    } else {
                                        $accountName = $firstName . ' ' . $lastName;
                                    }
                                    $email = $value['email'];
                                    $phone = $value['customer']['phone'];

                                    $mailingStreet = $value['customer']['default_address']['address1'] . "," . $value['customer']['default_address']['address2'];
                                    $otherStreet = $value['customer']['default_address']['address1'] . "," . $value['customer']['default_address']['address2'];
                                    $mailingCity = $value['customer']['default_address']['city'];
                                    $otherCity = $value['customer']['default_address']['city'];
                                    $mailingState = $value['customer']['default_address']['province'];
                                    $otherState = $value['customer']['default_address']['province'];
                                    $mailingZip = $value['customer']['default_address']['zip'];
                                    $otherZip = $value['customer']['default_address']['zip'];
                                    $mailingCountry = $value['customer']['default_address']['country'];
                                    $otherCountry = $value['customer']['default_address']['country'];

                                    $accounts[0]->BillingCity = $mailingCity;
                                    $accounts[0]->BillingCountry = $mailingCountry;
                                    $accounts[0]->BillingPostalCode = $mailingZip;
                                    $accounts[0]->BillingState = $mailingState;
                                    $accounts[0]->BillingStreet = $mailingStreet;
                                    $accounts[0]->ShippingCity = $otherCity;
                                    $accounts[0]->ShippingCountry = $otherCountry;
                                    $accounts[0]->ShippingPostalCode = $otherZip;
                                    $accounts[0]->ShippingState = $otherState;
                                    $accounts[0]->ShippingStreet = $otherStreet;
                                }

                                /* Insert new Account in sfdc */
                                $accounts[0]->Name = $accountName;
                                $accounts[0]->AccountNumber = $customerId;

                                // $accounts[0]->Email = $customerDetails['email'];
                                $accounts[0]->Phone = $phone;
                                $createAccountRes = Sfdc::createSfdcAccount($sfdcCredentials, $accounts);
                                $oldAccount = new stdclass();

                                $accountId = $createAccountRes->id;
                                $resCode = $createAccountRes->success;
                                if ($resCode == 0) // error in inserting Account
                                {
                                    $createAccountError = $createAccountRes->errors[0]->duplicateResult->matchResults[0]->matchRecords[0]->record->Id;
                                    // check, if duplicate exists.

                                    if ($createAccountRes->errors[0]->statusCode === 'DUPLICATES_DETECTED') {
                                        //update records
                                        $oldAccount->Id = $createAccountError;
                                        $accounts[0]->Id = $oldAccount->Id; /*
											$SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
                                                        				$SfdcUsername = $sfdcCredentials['sfdc_user_name'];
                                                        				$SfdcPassword = $sfdcCredentials['sfdc_password'];
                                                        				$SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];
                                                        				$mySforceConnection = new SforceEnterpriseClient();
                                                        				$mySforceConnection->createConnection($SfdcWsdl);
                                                        				$mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
                                                        				$response = $mySforceConnection->update(array($oldAccount,$accounts[0]), 'Account');
											*/
                                        $response = Sfdc::updateSfdcAccount($sfdcCredentials, $accounts);
                                    }
                                    $st = 'YES';
                                    $AccountHead = 'ACCOUNT';
                                    // SaveErrorDetails fails when $prodName has special characters used in MySQL
                                    SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Account ' . $customerId);
                                }
                                // Create Contacts

                                $contacts = array();
                                $contacts[0] = new stdclass();
                                $contacts[0]->LastName = $lastName;
                                $contacts[0]->FirstName = $firstName;
                                $contacts[0]->AccountId = $accountId;
                                $contacts[0]->Email = $email;
                                $contacts[0]->Phone = $phone;
                                $contacts[0]->MailingCity = $accounts[0]->BillingCity;
                                $contacts[0]->MailingCountry = $accounts[0]->BillingCountry;
                                $contacts[0]->MailingPostalCode = $accounts[0]->BillingPostalCode;
                                $contacts[0]->MailingState = $accounts[0]->BillingState;
                                $contacts[0]->MailingStreet = $accounts[0]->BillingStreet;
                                $contacts[0]->OtherCity = $accounts[0]->ShippingStreet;
                                $contacts[0]->OtherCountry = $accounts[0]->ShippingCountry;
                                $contacts[0]->OtherPostalCode = $accounts[0]->ShippingPostalCode;
                                $contacts[0]->OtherState = $accounts[0]->ShippingState;
                                $contacts[0]->OtherStreet = $accounts[0]->ShippingStreet;


                                $createAccountRes = Sfdc::createSfdcContact($sfdcCredentials, $contacts);

                            }
                            else // Account already exists
                            {
                                $accountId = $queryResult->records[0]->Id;
                                $accountName = $queryResult->records[0]->Name;
                                $resCode = 2; // account exists
                            }

                            if ($resCode > 0) {

                                // $flag = TRUE;

                                if ($resCode == 1) {
                                    $flagCustomer++;
                                }

                                /* Insert Sales Order  */
                                $sfdcOrders = array();
                                $sfdcOrders[0] = new stdclass();

                                // Order Details Collect
                                $subject = 'Order Number ' . $orderId;
                                $grandTotal = $value['total_price'];
                                $subTotal = $value['subtotal_price'];
                                $tax = $value['total_tax'];
                                $adjustment = $grandTotal - $subTotal;

                                //Shipping Address Detail
                                $orderBillingStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                                $orderBillingCity = $value['billing_address']['city'];
                                $orderBillingZip = $value['billing_address']['province'];
                                $orderBillingCountry = $value['billing_address']['zip'];
                                $orderBillingState = $value['billing_address']['country'];
                                //Shipping Address Detail
                                $orderShippingStreet = $value['shipping_address']['address1'] . "," . $value['shipping_address']['address2'];
                                $orderShippingCity = $value['shipping_address']['city'];
                                $orderShippingZip = $value['shipping_address']['province'];
                                $orderShippingCountry = $value['shipping_address']['zip'];
                                $orderShippingState = $value['shipping_address']['country'];
                                //Billing Address Detial
                                $sfdcOrders[0]->BillingStreet = $orderBillingStreet;
                                $sfdcOrders[0]->BillingCity = $orderBillingCity;
                                $sfdcOrders[0]->BillingState = $orderBillingState;
                                $sfdcOrders[0]->BillingPostalCode = $orderBillingZip;
                                $sfdcOrders[0]->BillingCountry = $orderBillingCountry;


                                $sfdcOrders[0]->ShippingStreet = $orderShippingStreet;
                                $sfdcOrders[0]->ShippingCity = $orderShippingCity;
                                $sfdcOrders[0]->ShippingState = $orderShippingState;
                                $sfdcOrders[0]->ShippingPostalCode = $orderShippingZip;
                                $sfdcOrders[0]->ShippingCountry = $orderShippingCountry;

                                // $Orders[0]->Account = $accountName;

                                //$accounts[0]->AccountId = $accountId;
                                $sfdcOrders[0]->AccountId = $accountId;

                                // $sfdcOrders[0]->TotalAmount =  $orders['items'][$key]['grand_total'];;

                                $sfdcOrders[0]->Description = $subject;
                                $sfdcOrders[0]->Name = $subject;
                                $salesOrderDate = DateTime::createFromFormat(DATE_RFC2822, $value['created_at']);
                                $sfdcOrders[0]->EffectiveDate = date('Y-m-d', strtotime($salesOrderDate));
                                $sfdcOrders[0]->OrderReferenceNumber = $orderId;
                                $sfdcOrders[0]->Pricebook2Id = $credentials['stdPriceBookId'];
                                $currentOrderStatus = $value['financial_status'];
                                $sfdcOpportunity = array();
                                $sfdcOpportunity[0] = new stdclass();
                                if (($currentOrderStatus === 'complete') || ($currentOrderStatus === 'processing')) {
                                    // $sfdcOrders[0]->StatusCode = 'Draft';
                                    $sfdcOrders[0]->Status = 'Draft';
                                    $sfdcOpportunity[0]->StageName = 'Closed Won';
                                } else {
                                    // $sfdcOrders[0]->Status = 'Activated';
                                    // $sfdcOrders[0]->StatusCode = 'Draft';
                                    $sfdcOrders[0]->Status = 'Draft';
                                    $sfdcOpportunity[0]->StageName = 'Proposal';
                                }

                                // Create Opportunity fields.
                                $sfdcOpportunity[0]->AccountId = $accountId;
                                $sfdcOpportunity[0]->CloseDate = $sfdcOrders[0]->EffectiveDate;
                                $sfdcOpportunity[0]->Name = $sfdcOrders[0]->Name;
                                //$sfdcOpportunity[0]->StageName = 'Closed Won';
                                $sfdcOpportunity[0]->Amount = $grandTotal;
                                $sfdcOpportunity[0]->Description = $sfdcOrders[0]->Description;
                                $sfdcOpportunity[0]->Pricebook2Id = $sfdcOrders[0]->Pricebook2Id;
                                $opportunityDetails = Sfdc::createOpportunity($sfdcCredentials, $sfdcOpportunity);
                                $orderDetails = Sfdc::createOrder($sfdcCredentials, $sfdcOrders);
                                if ($orderDetails->success == TRUE) {
                                    $flagOrder++;
                                } else // error in inserting Order
                                {
                                    $st = 'YES';
                                    $AccountHead = 'ORDER';
                                    // SaveErrorDetails fails when $prodName has special characters used in MySQL
                                    SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Order ' . $orderId);
                                }

                                //PRODUCT DETAILS COLLECT FROM ORDER
                                $totalProduct = count($value['line_items']);
                                $prodNumber = 0;
                                $orderItem = array();
                                $opportunityLineItem = array();
                                for ($i = 0; $i < $totalProduct; $i++) {
                                    //PRODUCT ID COLLECTED
                                    $prodCode = $value['line_items'][$i]['sku'];
                                    $productId = $value['line_items'][$i]['product_id'];
                                    $m_quantity = $value['line_items'][$i]['quantity'];
                                    $prodListPrice = $value['line_items'][$i]['price'];
                                    $prodUnitPrice = $value['line_items'][$i]['price'];
                                    $total = $value['line_items'][$i]['price'];

                                    $sfdcQuery = "Select Id FROM PricebookEntry WHERE ProductCode = '$prodCode'";
                                    $queryResult = Sfdc::sfdcFindRecord($sfdcCredentials, $sfdcQuery);
                                    if ($queryResult->size != 0) // Product exists
                                    {
                                        $productPricebookEntryId = $queryResult->records[0]->Id;
                                        // Update Product
                                        $priceEntry = array();
                                        $priceEntry[0] = new stdclass();
                                        $priceEntry[0]->Id = $productPricebookEntryId;
                                        $priceEntry[0]->IsActive = TRUE;
                                        $priceEntry[0]->UnitPrice = $value['line_items'][$i]['price'];
                                        $priceEntry[0]->UseStandardPrice = FALSE;
                                        $response = Sfdc::updatePriceBook($sfdcCredentials, $priceEntry);
                                    } else {


                                        //$discount = $total - $totalAfterDiscount;
                                        $discount = $value['line_items'][$i]['total_discount'];
                                        $totalAfterDiscount = $total - $discount;

                                        if ($value['line_items'][$i]['taxable'] == 1) {
                                            $tax = $value['line_items'][$i]['tax_lines'][0]['price'];
                                        } else {
                                            $tax = 0;
                                        }

                                        $netTotal = $totalAfterDiscount + $tax;

                                        //Product Detail Collect

                                        $productUrl = $shopifyURL . "/admin/products/" . $productId . ".json";
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $orderUrl);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        $return = curl_exec($ch);
                                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                        curl_close($ch);
                                        $productDetail = json_decode($return, TRUE);
                                        //End of Product Details
                                        //PRODUCT DETAILS IF EXISTS .................
                                        $prodName = $value['line_items'][$i]['name'];
                                        $stock_quantity = '100';
                                        $price = $value['line_items'][$i]['price'];
                                        $description = $value['line_items'][$i]['name'];

                                        /* insert into products */
                                        $products = array();
                                        $products[0] = new stdclass();
                                        $products[0]->Name = $prodName;
                                        $products[0]->IsActive = TRUE;
                                        $products[0]->ProductCode = $prodCode;
                                        $products[0]->Description = $description; // Check description
                                        $productRes = Sfdc::createSfdcProducts($sfdcCredentials, $products);
                                        if ($productRes->success == TRUE) {
                                            $flagProduct++;
                                        } else // error in inserting Product
                                        {
                                            $st = 'YES';
                                            $AccountHead = 'PRODUCT';
                                            // SaveErrorDetails fails when $prodName has special characters used in MySQL
                                            SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Product ' . $prodCode);
                                        }

                                        $insertedProductId = $productRes->id;
                                        $priceEntry = array();
                                        $priceEntry[0] = new stdclass();
                                        $priceEntry[0]->Pricebook2Id = $credentials['stdPriceBookId'];
                                        $priceEntry[0]->IsActive = TRUE;
                                        $priceEntry[0]->Product2Id = $insertedProductId;
                                        $priceEntry[0]->UnitPrice = $prodUnitPrice;
                                        $priceEntry[0]->UseStandardPrice = FALSE;

                                        $addedId = Sfdc::addToPriceBook($sfdcCredentials, $priceEntry);
                                        $productPricebookEntryId = $addedId->id;
                                    }
                                    // Add Order Item

                                    $orderItem[$i] = new stdclass();
                                    $orderItem[$i]->PricebookEntryId = $productPricebookEntryId;
                                    $orderItem[$i]->OrderId = $orderDetails->id;
                                    $orderItem[$i]->Quantity = $m_quantity;
                                    $orderItem[$i]->UnitPrice = $prodUnitPrice;

                                    if ($opportunityDetails->success) {
                                        // create Opportunity line item
                                        $opportunityLineItem[$i] = new stdclass();
                                        $opportunityLineItem[$i]->PricebookEntryId = $orderItem[$i]->PricebookEntryId;
                                        $opportunityLineItem[$i]->OpportunityId = $opportunityDetails->id;
                                        $opportunityLineItem[$i]->Quantity = $orderItem[$i]->Quantity;
                                        $opportunityLineItem[$i]->UnitPrice = $orderItem[$i]->UnitPrice;
                                    }

                                }
                                $orderItemRes = Sfdc::createOrderItem($sfdcCredentials, $orderItem);
                                $opportunityItemRes = Sfdc::addProductToOpportunity($sfdcCredentials, $opportunityLineItem);

                            } else {

                                $AccountHead = 'CUSTOMER';
                                $st = 'YES';
                                SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Customer: ' . $accountName);
                                $flag = FALSE;
                            }

                        }
                    }//end or order list
                }
            }
        }
    } catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }
    // Create reports.

    $status = 'YES';
    $totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
    $userDataArrTransaction = array(
        'user_id' => $credentials['userId'],
        'crm_type' => $crmType,
        'no_customer_data' => $flagCustomer,
        'no_product_data' => $flagProduct,
        'no_order_data' => $flagOrder,
        'last_sync_time' => time(),
        'total_transaction' => $totalTransaction,
        'status' => $status,
        'added_on' => time()
    );
    $credentials['counterOrders'] = $flagOrder;
    $credentials['counterProducts'] = $flagProduct;
    $credentials['counterAccounts'] = $flagCustomer;
    include "/var/www/html/app/mysql/mysqlconstants.php";

    tblTransectionDetail($credentials, $crmType, $userDataArrTransaction);

    sendSyncReport($credentials, $crmType);
    return $flagOrder;
} //Done

// Core Function For Shopify Sync From Shopify App....
function shopifyappToSfdcCRM($credentials)
{
    $flagCustomer = 0;
    $flagProduct = 0;
    $flagOrder = 0;

    $subscriptionId = $credentials['subscription_id'];
    $crmType = 'shopifyTosfdc';//$credentials['crm_type'];
    $contextVal = //$credentials['contextValue'];

    $shopifyURL = $credentials['app1Details']['shopify_url'];
    $shopifyToken = $credentials['app1Details']['shopify_token'];

    $min_date_created = $credentials['min_date_created'];
    $max_date_created = $credentials['max_date_created'];

    $sfdcCredentials = $credentials['app2Details'];
    $priceBookInfo = Sfdc::getPriceBookDetails($sfdcCredentials);
    $credentials['stdPriceBookId'] = $priceBookInfo->Id;
    $cloudApp = 'SFDC';
    //........... Get HubSpot access token if already Access Permission Given.......

    try {
        //............ORDER LIST COLLECTIONG.................

        $orderUrl = $shopifyURL . "/admin/orders.json?created_at_min=" . $min_date_created . "&created_at_max=" . $max_date_created . "&direction=asc";
        $http_headres = array(
            "X-Shopify-Access-Token: ".trim($shopifyToken)
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $orderUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headres);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code === 200) {
            $orders = json_decode($return, TRUE);
            $count = count($orders);

            print_r($orders);
            exit(1);

            if ($count > 0) {

                foreach ($orders as $num => $getOrders) {

                    foreach ($getOrders as $key => $value) {

                        //Customer Detials Collect
                        if (!empty($value['customer'])) {
                            $customerId = $value['customer']['id'];
                            $accountQuery = "Select Id, Name FROM Account WHERE AccountNumber = '$customerId'";
                        } else {
                            $phoneNo = $value['phone'];
                            $accountQuery = "Select Id, Name FROM Account WHERE Phone = '$phoneNo'";
                        }
                        $currencyExchangeRate = $value['currency'];
                        $discountAmount = $value['total_discounts'];
                        $email = $value['email'];
                        $phone = $value['phone'];
                        $orderId = $value['order_number'];
                        $subject = 'Order Number ' . $orderId;

                        //Status of Order./................
                        $currentOrderStatus = $value['financial_status'];
                        //Status of Order./................

                        $sfdcQuery = "Select Id FROM Order WHERE OrderReferenceNumber = '$orderId'";
                        $queryResult = Sfdc::sfdcFindRecord($sfdcCredentials, $sfdcQuery);

                        if ($queryResult->size === 0) // order doesn't exist
                        {


                            // Search for existing Account

                            $sfdcQuery = $accountQuery;
                            $queryResult = Sfdc::sfdcFindRecord($sfdcCredentials, $sfdcQuery);
                            if ($queryResult->size === 0) // no existing Account
                            {
                                $accounts = array();
                                $accounts[0] = new stdclass();
                                if (!empty($value['shipping_address'])) {
                                    $firstName = $value['shipping_address']['first_name'];
                                    $lastName = $value['shipping_address']['last_name'];
                                    if ($value['shipping_address']['company'] !== null) {
                                        $accountName = $value['shipping_address']['company'];
                                    } else {
                                        $accountName = $firstName . ' ' . $lastName;
                                    }
                                    $phone = $value['shipping_address']['phone'];

                                    $mailingStreet = $value['shipping_address']['address1'] . "," . $value['shipping_address']['address2'];
                                    $otherStreet = $value['shipping_address']['address1'] . "," . $value['shipping_address']['address2'];
                                    $mailingCity = $value['shipping_address']['city'];
                                    $otherCity = $value['shipping_address']['city'];
                                    $mailingState = $value['shipping_address']['province'];
                                    $otherState = $value['shipping_address']['province'];
                                    $mailingZip = $value['shipping_address']['zip'];
                                    $otherZip = $value['shipping_address']['zip'];
                                    $mailingCountry = $value['shipping_address']['country'];
                                    $otherCountry = $value['shipping_address']['country'];

                                    $accounts[0]->BillingCity = $mailingCity;
                                    $accounts[0]->BillingCountry = $mailingCountry;
                                    $accounts[0]->BillingPostalCode = $mailingZip;
                                    $accounts[0]->BillingState = $mailingState;
                                    $accounts[0]->BillingStreet = $mailingStreet;
                                    $accounts[0]->ShippingCity = $otherCity;
                                    $accounts[0]->ShippingCountry = $otherCountry;
                                    $accounts[0]->ShippingPostalCode = $otherZip;
                                    $accounts[0]->ShippingState = $otherState;
                                    $accounts[0]->ShippingStreet = $otherStreet;
                                }
                                else if (!empty($value['billing_address'])) {
                                    $firstName = $value['billing_address']['first_name'];
                                    $lastName = $value['billing_address']['last_name'];
                                    if ($value['shipping_address']['company'] !== null) {
                                        $accountName = $value['shipping_address']['company'];
                                    } else {
                                        $accountName = $firstName . ' ' . $lastName;
                                    }
                                    $phone = $value['billing_address']['phone'];

                                    $mailingStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                                    $otherStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                                    $mailingCity = $value['billing_address']['city'];
                                    $otherCity = $value['billing_address']['city'];
                                    $mailingState = $value['billing_address']['province'];
                                    $otherState = $value['billing_address']['province'];
                                    $mailingZip = $value['billing_address']['zip'];
                                    $otherZip = $value['billing_address']['zip'];
                                    $mailingCountry = $value['billing_address']['country'];
                                    $otherCountry = $value['billing_address']['country'];

                                    $accounts[0]->BillingCity = $mailingCity;
                                    $accounts[0]->BillingCountry = $mailingCountry;
                                    $accounts[0]->BillingPostalCode = $mailingZip;
                                    $accounts[0]->BillingState = $mailingState;
                                    $accounts[0]->BillingStreet = $mailingStreet;
                                    $accounts[0]->ShippingCity = $otherCity;
                                    $accounts[0]->ShippingCountry = $otherCountry;
                                    $accounts[0]->ShippingPostalCode = $otherZip;
                                    $accounts[0]->ShippingState = $otherState;
                                    $accounts[0]->ShippingStreet = $otherStreet;
                                }
                                else {
                                    $firstName = $value['customer']['first_name'];
                                    $lastName = $value['customer']['last_name'];
                                    if ($value['customer']['default_address']['company'] !== null) {
                                        $accountName = $value['customer']['default_address']['company'];
                                    } else {
                                        $accountName = $firstName . ' ' . $lastName;
                                    }
                                    $email = $value['email'];
                                    $phone = $value['customer']['phone'];

                                    $mailingStreet = $value['customer']['default_address']['address1'] . "," . $value['customer']['default_address']['address2'];
                                    $otherStreet = $value['customer']['default_address']['address1'] . "," . $value['customer']['default_address']['address2'];
                                    $mailingCity = $value['customer']['default_address']['city'];
                                    $otherCity = $value['customer']['default_address']['city'];
                                    $mailingState = $value['customer']['default_address']['province'];
                                    $otherState = $value['customer']['default_address']['province'];
                                    $mailingZip = $value['customer']['default_address']['zip'];
                                    $otherZip = $value['customer']['default_address']['zip'];
                                    $mailingCountry = $value['customer']['default_address']['country'];
                                    $otherCountry = $value['customer']['default_address']['country'];

                                    $accounts[0]->BillingCity = $mailingCity;
                                    $accounts[0]->BillingCountry = $mailingCountry;
                                    $accounts[0]->BillingPostalCode = $mailingZip;
                                    $accounts[0]->BillingState = $mailingState;
                                    $accounts[0]->BillingStreet = $mailingStreet;
                                    $accounts[0]->ShippingCity = $otherCity;
                                    $accounts[0]->ShippingCountry = $otherCountry;
                                    $accounts[0]->ShippingPostalCode = $otherZip;
                                    $accounts[0]->ShippingState = $otherState;
                                    $accounts[0]->ShippingStreet = $otherStreet;
                                }

                                /* Insert new Account in sfdc */
                                $accounts[0]->Name = $accountName;
                                $accounts[0]->AccountNumber = $customerId;

                                // $accounts[0]->Email = $customerDetails['email'];
                                $accounts[0]->Phone = $phone;
                                $createAccountRes = Sfdc::createSfdcAccount($sfdcCredentials, $accounts);
                                $oldAccount = new stdclass();

                                $accountId = $createAccountRes->id;
                                $resCode = $createAccountRes->success;
                                if ($resCode == 0) // error in inserting Account
                                {
                                    $createAccountError = $createAccountRes->errors[0]->duplicateResult->matchResults[0]->matchRecords[0]->record->Id;
                                    // check, if duplicate exists.

                                    if ($createAccountRes->errors[0]->statusCode === 'DUPLICATES_DETECTED') {
                                        //update records
                                        $oldAccount->Id = $createAccountError;
                                        $accounts[0]->Id = $oldAccount->Id; /*
											$SfdcWsdl = "/var/www/html/bigcommerce-app-management/sfdc-integration/sfdc/enterprise.wsdl.xml";
                                                        				$SfdcUsername = $sfdcCredentials['sfdc_user_name'];
                                                        				$SfdcPassword = $sfdcCredentials['sfdc_password'];
                                                        				$SfdcSecurityToken = $sfdcCredentials['sfdc_security_password'];
                                                        				$mySforceConnection = new SforceEnterpriseClient();
                                                        				$mySforceConnection->createConnection($SfdcWsdl);
                                                        				$mySforceConnection->login($SfdcUsername, $SfdcPassword . $SfdcSecurityToken);
                                                        				$response = $mySforceConnection->update(array($oldAccount,$accounts[0]), 'Account');
											*/
                                        $response = Sfdc::updateSfdcAccount($sfdcCredentials, $accounts);
                                    }
                                    $st = 'YES';
                                    $AccountHead = 'ACCOUNT';
                                    // SaveErrorDetails fails when $prodName has special characters used in MySQL
                                    SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Account ' . $customerId);
                                }
                                // Create Contacts

                                $contacts = array();
                                $contacts[0] = new stdclass();
                                $contacts[0]->LastName = $lastName;
                                $contacts[0]->FirstName = $firstName;
                                $contacts[0]->AccountId = $accountId;
                                $contacts[0]->Email = $email;
                                $contacts[0]->Phone = $phone;
                                $contacts[0]->MailingCity = $accounts[0]->BillingCity;
                                $contacts[0]->MailingCountry = $accounts[0]->BillingCountry;
                                $contacts[0]->MailingPostalCode = $accounts[0]->BillingPostalCode;
                                $contacts[0]->MailingState = $accounts[0]->BillingState;
                                $contacts[0]->MailingStreet = $accounts[0]->BillingStreet;
                                $contacts[0]->OtherCity = $accounts[0]->ShippingStreet;
                                $contacts[0]->OtherCountry = $accounts[0]->ShippingCountry;
                                $contacts[0]->OtherPostalCode = $accounts[0]->ShippingPostalCode;
                                $contacts[0]->OtherState = $accounts[0]->ShippingState;
                                $contacts[0]->OtherStreet = $accounts[0]->ShippingStreet;


                                $createAccountRes = Sfdc::createSfdcContact($sfdcCredentials, $contacts);

                            }
                            else // Account already exists
                            {
                                $accountId = $queryResult->records[0]->Id;
                                $accountName = $queryResult->records[0]->Name;
                                $resCode = 2; // account exists
                            }

                            if ($resCode > 0) {

                                // $flag = TRUE;

                                if ($resCode == 1) {
                                    $flagCustomer++;
                                }

                                /* Insert Sales Order  */
                                $sfdcOrders = array();
                                $sfdcOrders[0] = new stdclass();

                                // Order Details Collect
                                $subject = 'Order Number ' . $orderId;
                                $grandTotal = $value['total_price'];
                                $subTotal = $value['subtotal_price'];
                                $tax = $value['total_tax'];
                                $adjustment = $grandTotal - $subTotal;

                                //Shipping Address Detail
                                $orderBillingStreet = $value['billing_address']['address1'] . "," . $value['billing_address']['address2'];
                                $orderBillingCity = $value['billing_address']['city'];
                                $orderBillingZip = $value['billing_address']['province'];
                                $orderBillingCountry = $value['billing_address']['zip'];
                                $orderBillingState = $value['billing_address']['country'];
                                //Shipping Address Detail
                                $orderShippingStreet = $value['shipping_address']['address1'] . "," . $value['shipping_address']['address2'];
                                $orderShippingCity = $value['shipping_address']['city'];
                                $orderShippingZip = $value['shipping_address']['province'];
                                $orderShippingCountry = $value['shipping_address']['zip'];
                                $orderShippingState = $value['shipping_address']['country'];
                                //Billing Address Detial
                                $sfdcOrders[0]->BillingStreet = $orderBillingStreet;
                                $sfdcOrders[0]->BillingCity = $orderBillingCity;
                                $sfdcOrders[0]->BillingState = $orderBillingState;
                                $sfdcOrders[0]->BillingPostalCode = $orderBillingZip;
                                $sfdcOrders[0]->BillingCountry = $orderBillingCountry;


                                $sfdcOrders[0]->ShippingStreet = $orderShippingStreet;
                                $sfdcOrders[0]->ShippingCity = $orderShippingCity;
                                $sfdcOrders[0]->ShippingState = $orderShippingState;
                                $sfdcOrders[0]->ShippingPostalCode = $orderShippingZip;
                                $sfdcOrders[0]->ShippingCountry = $orderShippingCountry;

                                // $Orders[0]->Account = $accountName;

                                //$accounts[0]->AccountId = $accountId;
                                $sfdcOrders[0]->AccountId = $accountId;

                                // $sfdcOrders[0]->TotalAmount =  $orders['items'][$key]['grand_total'];;

                                $sfdcOrders[0]->Description = $subject;
                                $sfdcOrders[0]->Name = $subject;
                                $salesOrderDate = DateTime::createFromFormat(DATE_RFC2822, $value['created_at']);
                                $sfdcOrders[0]->EffectiveDate = date('Y-m-d', strtotime($salesOrderDate));
                                $sfdcOrders[0]->OrderReferenceNumber = $orderId;
                                $sfdcOrders[0]->Pricebook2Id = $credentials['stdPriceBookId'];
                                $currentOrderStatus = $value['financial_status'];
                                $sfdcOpportunity = array();
                                $sfdcOpportunity[0] = new stdclass();
                                if (($currentOrderStatus === 'complete') || ($currentOrderStatus === 'processing')) {
                                    // $sfdcOrders[0]->StatusCode = 'Draft';
                                    $sfdcOrders[0]->Status = 'Draft';
                                    $sfdcOpportunity[0]->StageName = 'Closed Won';
                                } else {
                                    // $sfdcOrders[0]->Status = 'Activated';
                                    // $sfdcOrders[0]->StatusCode = 'Draft';
                                    $sfdcOrders[0]->Status = 'Draft';
                                    $sfdcOpportunity[0]->StageName = 'Proposal';
                                }

                                // Create Opportunity fields.
                                $sfdcOpportunity[0]->AccountId = $accountId;
                                $sfdcOpportunity[0]->CloseDate = $sfdcOrders[0]->EffectiveDate;
                                $sfdcOpportunity[0]->Name = $sfdcOrders[0]->Name;
                                //$sfdcOpportunity[0]->StageName = 'Closed Won';
                                $sfdcOpportunity[0]->Amount = $grandTotal;
                                $sfdcOpportunity[0]->Description = $sfdcOrders[0]->Description;
                                $sfdcOpportunity[0]->Pricebook2Id = $sfdcOrders[0]->Pricebook2Id;
                                $opportunityDetails = Sfdc::createOpportunity($sfdcCredentials, $sfdcOpportunity);
                                $orderDetails = Sfdc::createOrder($sfdcCredentials, $sfdcOrders);
                                if ($orderDetails->success == TRUE) {
                                    $flagOrder++;
                                } else // error in inserting Order
                                {
                                    $st = 'YES';
                                    $AccountHead = 'ORDER';
                                    // SaveErrorDetails fails when $prodName has special characters used in MySQL
                                    SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Order ' . $orderId);
                                }

                                //PRODUCT DETAILS COLLECT FROM ORDER
                                $totalProduct = count($value['line_items']);
                                $prodNumber = 0;
                                $orderItem = array();
                                $opportunityLineItem = array();
                                for ($i = 0; $i < $totalProduct; $i++) {
                                    //PRODUCT ID COLLECTED
                                    $prodCode = $value['line_items'][$i]['sku'];
                                    $productId = $value['line_items'][$i]['product_id'];
                                    $m_quantity = $value['line_items'][$i]['quantity'];
                                    $prodListPrice = $value['line_items'][$i]['price'];
                                    $prodUnitPrice = $value['line_items'][$i]['price'];
                                    $total = $value['line_items'][$i]['price'];

                                    $sfdcQuery = "Select Id FROM PricebookEntry WHERE ProductCode = '$prodCode'";
                                    $queryResult = Sfdc::sfdcFindRecord($sfdcCredentials, $sfdcQuery);
                                    if ($queryResult->size != 0) // Product exists
                                    {
                                        $productPricebookEntryId = $queryResult->records[0]->Id;
                                        // Update Product
                                        $priceEntry = array();
                                        $priceEntry[0] = new stdclass();
                                        $priceEntry[0]->Id = $productPricebookEntryId;
                                        $priceEntry[0]->IsActive = TRUE;
                                        $priceEntry[0]->UnitPrice = $value['line_items'][$i]['price'];
                                        $priceEntry[0]->UseStandardPrice = FALSE;
                                        $response = Sfdc::updatePriceBook($sfdcCredentials, $priceEntry);
                                    } else {


                                        //$discount = $total - $totalAfterDiscount;
                                        $discount = $value['line_items'][$i]['total_discount'];
                                        $totalAfterDiscount = $total - $discount;

                                        if ($value['line_items'][$i]['taxable'] == 1) {
                                            $tax = $value['line_items'][$i]['tax_lines'][0]['price'];
                                        } else {
                                            $tax = 0;
                                        }

                                        $netTotal = $totalAfterDiscount + $tax;

                                        //Product Detail Collect

                                        $productUrl = $shopifyURL . "/admin/products/" . $productId . ".json";
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $orderUrl);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                        $return = curl_exec($ch);
                                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                        curl_close($ch);
                                        $productDetail = json_decode($return, TRUE);
                                        //End of Product Details
                                        //PRODUCT DETAILS IF EXISTS .................
                                        $prodName = $value['line_items'][$i]['name'];
                                        $stock_quantity = '100';
                                        $price = $value['line_items'][$i]['price'];
                                        $description = $value['line_items'][$i]['name'];

                                        /* insert into products */
                                        $products = array();
                                        $products[0] = new stdclass();
                                        $products[0]->Name = $prodName;
                                        $products[0]->IsActive = TRUE;
                                        $products[0]->ProductCode = $prodCode;
                                        $products[0]->Description = $description; // Check description
                                        $productRes = Sfdc::createSfdcProducts($sfdcCredentials, $products);
                                        if ($productRes->success == TRUE) {
                                            $flagProduct++;
                                        } else // error in inserting Product
                                        {
                                            $st = 'YES';
                                            $AccountHead = 'PRODUCT';
                                            // SaveErrorDetails fails when $prodName has special characters used in MySQL
                                            SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Product ' . $prodCode);
                                        }

                                        $insertedProductId = $productRes->id;
                                        $priceEntry = array();
                                        $priceEntry[0] = new stdclass();
                                        $priceEntry[0]->Pricebook2Id = $credentials['stdPriceBookId'];
                                        $priceEntry[0]->IsActive = TRUE;
                                        $priceEntry[0]->Product2Id = $insertedProductId;
                                        $priceEntry[0]->UnitPrice = $prodUnitPrice;
                                        $priceEntry[0]->UseStandardPrice = FALSE;

                                        $addedId = Sfdc::addToPriceBook($sfdcCredentials, $priceEntry);
                                        $productPricebookEntryId = $addedId->id;
                                    }
                                    // Add Order Item

                                    $orderItem[$i] = new stdclass();
                                    $orderItem[$i]->PricebookEntryId = $productPricebookEntryId;
                                    $orderItem[$i]->OrderId = $orderDetails->id;
                                    $orderItem[$i]->Quantity = $m_quantity;
                                    $orderItem[$i]->UnitPrice = $prodUnitPrice;

                                    if ($opportunityDetails->success) {
                                        // create Opportunity line item
                                        $opportunityLineItem[$i] = new stdclass();
                                        $opportunityLineItem[$i]->PricebookEntryId = $orderItem[$i]->PricebookEntryId;
                                        $opportunityLineItem[$i]->OpportunityId = $opportunityDetails->id;
                                        $opportunityLineItem[$i]->Quantity = $orderItem[$i]->Quantity;
                                        $opportunityLineItem[$i]->UnitPrice = $orderItem[$i]->UnitPrice;
                                    }

                                }
                                $orderItemRes = Sfdc::createOrderItem($sfdcCredentials, $orderItem);
                                $opportunityItemRes = Sfdc::addProductToOpportunity($sfdcCredentials, $opportunityLineItem);

                            } else {

                                $AccountHead = 'CUSTOMER';
                                $st = 'YES';
                                SaveErrorDetails($subscriptionId, $AccountHead, 'Error adding Customer: ' . $accountName);
                                $flag = FALSE;
                            }

                        }
                    }//end or order list
                }
            }
        }
    } catch (Exception $e) {
        $msg = $e;
        $flag = FALSE;
    }
    // Create reports.

    $status = 'YES';
    $totalTransaction = $flagCustomer + $flagProduct + $flagOrder;
    $userDataArrTransaction = array(
        'user_id' => $credentials['userId'],
        'crm_type' => $crmType,
        'no_customer_data' => $flagCustomer,
        'no_product_data' => $flagProduct,
        'no_order_data' => $flagOrder,
        'last_sync_time' => time(),
        'total_transaction' => $totalTransaction,
        'status' => $status,
        'added_on' => time()
    );
    $credentials['counterOrders'] = $flagOrder;
    $credentials['counterProducts'] = $flagProduct;
    $credentials['counterAccounts'] = $flagCustomer;
    include "/var/www/html/app/mysql/mysqlconstants.php";

    tblTransectionDetail($credentials, $crmType, $userDataArrTransaction);

    sendSyncReport($credentials, $crmType);
    return $flagOrder;
}

// Reporting Function For Sync........
function sendSyncReport($credentials, $crmType)
{
    //Default Time Zone
    date_default_timezone_set('UTC');
    $currentSyncHrs = date("G");
    $currentMinute = date("i");
    if ($currentSyncHrs == 23 && $currentMinute >= 30) {

        include "/var/www/html/app/mysql/mysqlconstants.php";
        //for updating or inserting order no ,product no, customer no
        $user_id = $credentials['userId'];
        $subscription_id = $credentials['subscription_id'];
        $cond = " AND user_id='$user_id' AND crm_type='$crmType'";
        $fetch_sync = fetch($zohoTransactionDetails, $cond);
        $orderCount = $fetch_sync[0]['no_order_data'];
        $productCount = $fetch_sync[0]['no_product_data'];
        $customerCount = $fetch_sync[0]['no_customer_data'];
        $functionName = ucwords($fetch_sync[0]['crm_type']);
        $userName = $credentials['userEmail'];
        $email = $credentials['userEmail'];
        $syncDateTime = date("m/d/Y H:i:s");

        if ($orderCount > 0) {
            $orderText = $orderCount . " order(s) retrieved";
        } else {
            $orderText = "No relevant changes to retrieve.";
        }
        if ($productCount > 0) {
            $productText = $productCount . " product(s) retrieved";
        } else {
            $productText = "No relevant changes to retrieve.";
        }
        if ($customerCount > 0) {
            $customerText = $customerCount . " customer(s) retrieved";
        } else {
            $customerText = "No relevant changes to retrieve.";
        }
        $subject = "AquaAPI -".$functionName." Cloud Connector : Sync report";

        $message = <<<EOF
	<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Untitled Document</title>
    </head>
    <body>
        <div style="padding:0 15px;font-family: Arial,'Helvetica Neue',Helvetica,sans-serif; font-size:13px;">
            <div style="background:#384556;">
                <div style="background:#1c2c3c; text-align:center; padding:10px 5px;">
                    <img src="https://aquaapi.com/images/logo-sml.png">
                </div>
                <div style="background:#384556; padding:15px 25px; color:#fff;">
                    <p style=" margin-bottom:40px;">Hi <a style="color:#fff; font-weight:bold;" href="mailto:$userName">$userName</a></p>
                    <p style="margin-bottom:25px;">Here is the integration report run on $syncDateTime for <a style="color:#fff;">AquaAPI - $functionName Cloud Connector</a></p>
                    <h2 style="font-size:18px;">AquaAPI - $functionName Cloud Connector : Sync report</h2>
                    <ul>
                        <li style="margin-bottom:10px;">Orders: $orderText</li>
                        <li style="margin-bottom:10px;">Products: $productText</li>
                        <li style="margin-bottom:10px;">Customers: $customerText</li>
                    </ul>
                </div>
                <div style="background:#1c2c3c; text-align:center; padding:25px 10px;">
                    <a target="_blank" style="text-decoration:none; color:#fff; margin-right:15px;" href="http://aquaapi.com/">Home</a>
                    <a target="_blank" style="text-decoration:none; color:#fff;" href="http://aquaapi.com/contact.html">Contact Us</a>
                </div>
            </div>
        </div>
    </body>
</html>
EOF;

        $from = $functionName . " Cloud Connector (support@aquaapi.com)";
        // $to = $userName;
        $to = "manas.paul@aquaapi.com";
        $headers = "From: $from\r\n";
        $headers .= "Cc:info@aquaapi.com \r\n";
        $headers .= "Content-type: text/html\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        $flag = mail($to, $subject, $message, $headers);
        if ($flag) {
            $cond = " AND user_id='$user_id' AND crm_type='$crmType'";

            $userData = array(

                'no_customer_data' => 0,
                'no_product_data' => 0,
                'no_order_data' => 0,
                'total_transaction' => 0,
                'last_sync_time' => time()
            );
            $update_sync = update($zohoTransactionDetails, $userData, $cond);
        }
    }
}

function SaveErrorDetails($subsId, $transHead, $errMsg)
{
    include "/var/www/html/app/mysql/mysqlconstants.php";
    $flag = FALSE;
    try {
        //Connect Database and open the connection.
//        $connection_status = Connectdb();
//        $open_status = Opendb();
        //add data in the database
        $userDataArrError = array(
            'subscription_id' => $subsId,
            'transaction_head' => $transHead,
            'error_message' => $errMsg,
            'added_on' => time()
        );
        $insertErrorData = insert($zohoSyncErrorDetails, $userDataArrError);
        if ($insertErrorData > 0) {
            $flag = TRUE;
        } else {
            $flag = FALSE;
        }
    } catch (Exception $ex) {
        //echo 'error : ' . $ex;
    }
    return $flag;
}

function tblTransectionDetail($credentials, $crmType, $userData)
{

    include "/var/www/html/app/mysql/mysqlconstants.php";
    //for updating or inserting order no ,product no, customer no
    $user_id = $credentials['userId'];
    $subscription_id = $credentials['subscription_id'];
    $cond = " AND user_id='$user_id' AND crm_type='$crmType'";
    $count = count_row($zohoTransactionDetails, $cond);
    if ($count == 0) {
        $insertData = insert($zohoTransactionDetails, $userData);
    } else {
        $cond = " AND user_id='$user_id' AND crm_type='$crmType'";
        $fetch = fetch($zohoTransactionDetails, $cond);
        foreach ($fetch as $key => $value) {
            $customer = $value['no_customer_data'] + $credentials['counterAccounts'];
            $order = $value['no_order_data'] + $credentials['counterOrders'];
            $product = $value['no_product_data'] + $credentials['counterProducts'];
            $totalTransaction = $customer + $order + $product;
            $userData = array(
                'subscription_id' => $subscription_id,
                'no_customer_data' => $customer,
                'no_product_data' => $product,
                'no_order_data' => $order,
                'total_transaction' => $totalTransaction,
                'last_sync_time' => time()
            );

        }
        $updateTable = update($zohoTransactionDetails, $userData, $cond);

    }
}


