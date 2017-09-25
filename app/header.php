<?php
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");
?>
<script src="https://apis.google.com/js/platform.js?onload=onLoad" async defer></script>
<meta name="google-signin-client_id" content="<?php echo GOOGLE_CLIENT_ID; ?>">
<header>
    <div class="grid-wrap pad">
        <div class="logo width-40 float-left">
            <a href="/"><img src="/app/images/logo.png" alt="aquaApi logo"></a>
        </div>
        <div class="admin-config width-60 float-left text-right">
            <div class="dropdown-box">
                <span class="name"><img src="/app/images/header-profile-icon.png"> <span><?php echo $_SESSION['user_name']; ?></span></span>
                <ul class="dropdown-menu list text-left">
                    <li><a href="https://<?php echo APP_DOMAIN; ?>/app/subscription/user-dashboard.php">Dashboard</a></li>
                    <?php
                    if($_SESSION['app'] == 'AQUAAPI') {
                        echo '<li><a href="https://'.APP_DOMAIN.'/app/install/change-password.php">Change Password</a></li>';
                    }
                    ?>
                    <li><a href="https://<?php echo APP_DOMAIN; ?>/app/install/crm-type.php">New Subscription</a></li>
                    <li><a href="#" id="logout">Log Out</a> </li>
                    <!--li><a href="sync-time-update.php">Change sync time</a></li>
                    <li><a href="support.php">Support</a></li-->
                </ul>
            </div>
        </div>
    </div>
</header>

<script type="text/javascript">
    window.fbAsyncInit = function () {
        FB.init({
            appId: '<?php echo FACEBOOK_APP_ID; ?>',
            cookie: true,  // enable cookies to allow the server to access
                           // the session
            xfbml: true,  // parse social plugins on this page
            version: 'v2.8' // use graph api version 2.8
        });

    };

    // Load the SDK asynchronously
    (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    //For Google Logout
    function onLoad() {
        gapi.load('auth2', function() {
            gapi.auth2.init();
        });
    }
    $('#logout').click(function () {
        var app = '<?php echo $_SESSION["app"]; ?>';
        if(app === "FACEBOOK") {
            FB.getLoginStatus(function (response) {
                if (response.status === 'connected') {
                    FB.logout(function (response) {

                        window.location.href = "https://<?php echo APP_DOMAIN; ?>/app/subscription/user-dashboard.php?logout=1";
                    });
                }
            });
        }
        else if(app === "GOOGLE"){
                var auth2 = gapi.auth2.getAuthInstance();
                auth2.signOut().then(function () {
                    console.log('User signed out.');
                    window.location.href = "https://<?php echo APP_DOMAIN; ?>/app/subscription/user-dashboard.php?logout=1";
                });
        }
        else
        {
            window.location.href = "https://<?php echo APP_DOMAIN; ?>/app/subscription/user-dashboard.php?logout=1";
        }


    })
</script>
