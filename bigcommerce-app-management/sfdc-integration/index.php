<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Untitled Document</title>
        <link rel="stylesheet" type="text/css" href="style/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="js/app.js"></script>
    </head>
    <body>
        <header>
            <div class="grid-wrap">
                <div class="logo width-40 float-left">
                    <a href="/admin"><img src="images/logo.png" alt="aquaApi logo"></a>
                </div>
                <div class="admin-config width-60 float-left text-right">
                    <div class="dropdown-box">
                        <span class="name"><img src="images/header-settings.png"></span>
                        <ul class="dropdown-menu list text-left">
                            <li><a href="/Admin/account/changepassword">Payment card update</a></li>
                            <li><a href="credential.html">CRM credentials update</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>
        <div class="grid-wrap">
            <h1 style="margin: 40px 0 60px;">you have chosen <span>ZOHO CRM</span> during installation</h1>
            <div class="box-wrp">
                <h2>subscription plan</h2>
                <div class="btn-wrp">
                    <a class="secondary-btn" href="#">Remove subscription</a>
                    <a class="primary-btn" href="change-plan.html">Change plan</a>
                </div>
                <h3>Starter plan</h3>
                <p><span>rice :</span> $5/mon</p>
                <p><span>Order :  </span>up to 500 orders/months</p>
            </div>
            <a class="primary-btn" href="sfdc-index.php">Sync Data to SFDC</a>
            <div class="box-wrp">
                <h2>Sync details</h2>
                <div class="btn-wrp">
                    <p><span>Last sync time: </span> 12.30am (IST)</p>
                </div>
                <div id="horizontalTab">
                    <ul class="resp-tabs-list">
                        <li><a class="syncLink" href="#" data-name="accounts">Accounts</a></li>
                        <li><a class="syncLink" href="#" data-name="products">Products</a> </li>
                        <li><a class="syncLink" href="#" data-name="orders">Orders</a></li>
                    </ul>
                    <div class="resp-tabs-container">
                        <div>
                            <table class="tab-table">
                                <thead>
                                <th>header</th>
                                <th>header</th>
                                <th>header</th>
                                <th>header</th>
                                <th>header</th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                    </tr>
                                    <tr>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                    </tr>
                                    <tr>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div>
                            <table class="tab-table">
                                <thead>
                                <th>header1</th>
                                <th>header1</th>
                                <th>header1</th>
                                <th>header1</th>
                                <th>header1</th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                    </tr>
                                    <tr>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                    </tr>
                                    <tr>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div>
                            <table class="tab-table">
                                <thead>
                                <th>header2</th>
                                <th>header2</th>
                                <th>header2</th>
                                <th>header2</th>
                                <th>header2</th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                    </tr>
                                    <tr>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                    </tr>
                                    <tr>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                        <td>data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-wrp">
                <h2>Sync error</h2>
                <div class="sync-error">
                    <p>lorem ipsum dollar amet sit </p>
                    <p>lorem ipsum dollar amet sit dollar amet sit</p>
                    <p>Dollar amet lorem ipsum dollar</p>
                    <p>ipsum dollar amet sit </p>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function () {
                //checkCredentials();
                $('#horizontalTab').easyResponsiveTabs({
                    type: 'default', //Types: default, vertical, accordion           
                    width: 'auto', //auto or any width like 600px
                    fit: true, // 100% fit in a container
                    closed: 'accordion', // Start closed if in accordion view
                    activate: function (event) { // Callback function if tab is switched
                        var $tab = $(this);
                        var $info = $('#tabInfo');
                        var $name = $('span', $info);
                        $name.text($tab.text());
                        $info.show();
                    }
                });
                $('#verticalTab').easyResponsiveTabs({
                    type: 'vertical',
                    width: 'auto',
                    fit: true
                });
                $('body').on('click', '.syncLink', function (e) {
                    e.preventDefault();
                    var col_name = $(this).data('name');
                    if (col_name === 'accounts') {
                        account_count();
                    } else if (col_name === 'products') {
                        products();
                    } else if (col_name === 'orders') {
                        orders();
                    }
                });
            });

            function checkCredentials() {
                $.ajax({
                    url: "sfdc-index.php?type=checkIfStandardPriceBook",
                    method: "GET",
                    data: {},
                    dataType: "json"
                }).done(function (result) {
                    console.log(result);
                    alert("Your system configured properly,You can Sync now");
                }).fail(function (jqXHR, textStatus) {
                    alert("Please active Standard Price Book at your salseforce ");
                });
            }

            function account_count() {
                $.ajax({
                    url: "sfdc-index.php?type=countAccounts",
                    method: "GET",
                    data: {},
                    dataType: "json"
                }).done(function (result) {
                    if(parseInt(result.flag)===1){
                        var counter = parseInt(result.msg.count);
                        alert(counter);
                    }
                    console.log(result);
                    //alert("Your system configured properly,You can Sync now");
                }).fail(function (jqXHR, textStatus) {
                    alert("Please active Standard Price Book at your salseforce ");
                });
            }
            
            function accounts(){
                
            }

            function products() {

            }

            function orders() {

            }
        </script>
    </body>
</html>
