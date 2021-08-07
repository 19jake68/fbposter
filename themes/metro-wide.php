<?php

if ( count( get_included_files() ) == 1 )
    die();

if ( $showLogin ) {
    if ( !isset( $step ) )
        $step = 0;
    $forgotPassLink = "https://www.facebook.com/" . $GLOBALS[ '__FBAPI__' ] . "/dialog/oauth?client_id=" . $config[ 'appId' ] . "&redirect_uri=" . urlencode( 'http://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'SCRIPT_NAME' ] ) . "&scope=public_profile&state=safX";
    $output = '
    <br /><center>
    <div class="login-form padding20 block-shadow" style="max-width: 500px">
        <div class="tabcontrol" data-role="tabControl">
            <ul class="tabs">
                <li><a href="#frame_login">' . $lang['Login'] . '</a></li>
                <li><a href="#frame_signup">' . $lang['Register'] . '</a></li>
            </ul>
            <div class="frames">
                <div class="frame bg-white" id="frame_login">
                    <form method=post name="loginForm">
                    <input type="hidden" value="' . RestrictCSRF::generateToken( 'loginForm' ) . '" name="loginForm" id="loginForm">
                    <div class="input-control text full-size" data-role="input">
                        <label for="user_login">' . $lang['Username'] . '</label>
                        <input type="text" name="un" id="user_login" style="padding-right: 39px;">
                        <button class="button helper-button clear" tabindex="-1" type="button"><span class="mif-cross"></span></button>
                    </div><br><br>
                    <div class="input-control password full-size" data-role="input">
                        <label for="user_pass">' . $lang['Password'] . '</label>
                        <input type="password" name="pw" id="user_pass" style="padding-right: 39px;">
                        <button class="button helper-button clear" tabindex="-1" type="button"><span class="mif-cross"></span></button>
                    </div>
                    <label class="input-control checkbox small-check">
                        <input type="checkbox" name=rem checked>
                        <span class="check"></span>
                        <span class="caption">' . $lang['Remember Me'] . '</span>
                    </label><br>                    
                    <input type=submit value="' . $lang['Login'] . '"><br><br>
                    <small><a href="' . $forgotPassLink . '">' . $lang['Forgot Your Password'] . '</a></small>
                    </form>
                </div>
                <div class="frame bg-white" id="frame_signup">';
                if ( $adminOptions[ 'enableNUR' ] )
                    $output .= '<form method=post id="signinForm" name="signinForm">
                        <input type="hidden" value="' . RestrictCSRF::generateToken( 'signinForm' ) . '" name="signinForm" id="signinForm">
                        <div class="input-control text full-size" data-role="input">
                            <label for="signup_login">' . $lang['Username'] . '</label>
                            <input type="text" name="suun" id="signup_login" style="padding-right: 39px;">
                            <button class="button helper-button clear" tabindex="-1" type="button"><span class="mif-cross"></span></button>
                        </div><br><br>
                        <div class="input-control text full-size" data-role="input">
                            <label for="signup_email">' . $lang['Email'] . '</label>
                            <input type="text" name="suem" id="signup_email" style="padding-right: 39px;">
                            <button class="button helper-button clear" tabindex="-1" type="button"><span class="mif-cross"></span></button>
                        </div><br><br>
                        <div class="input-control text full-size" data-role="input">
                            <label for="signup_pass">' . $lang['Password'] . '</label>
                            <input type="password" name="supw" id="signup_pass" style="padding-right: 39px;">
                            <button class="button helper-button clear" tabindex="-1" type="button"><span class="mif-cross"></span></button>
                        </div><br><br>
                        <div class="input-control text full-size" data-role="input">
                            <label for="signup_uid" title="Your Facebook numerical User-ID. Click to find out"><a href="http://findmyfbid.com/" target="_new">' . $lang['FB ID'] . '</a></label>
                            <input type="text" name="suuid" id="signup_uid" style="padding-right: 39px;">
                            <button class="button helper-button clear" tabindex="-1" type="button"><span class="mif-cross"></span></button>
                        </div><br><br>                     
                        <input type=button id="Register" value="' . $lang['Register'] . '">
                        </form>';
                $output .= '
                </div>
            </div>
        </div>
    </div></center>';   
    $output .= '
    <script>
    $( "#signinForm" ).tooltip(); 
    $("#Register").click(function (event) {
        event.preventDefault;
        $("#signinForm").block({ 
            message: "<img src=\"img/loading.gif\">", 
            timeout: 10000,
            css: { border: "0px", backgroundColor: "rgba(255, 255, 255, 0)" },
            overlayCSS:  { backgroundColor: "#fff", opacity: 0.7 } 
        }); 
        var options = {
            target:        "#result",
            timeout:   5000,
            success:  function(responseText, statusText, xhr, $form) {
                $("#signinForm").unblock()
                if (responseText == "OK") {
                    location.reload(1);
                }
            },
        };
        $(\'#signinForm\').ajaxSubmit(options);
    });
    </script><div id=result style="display: none"></div>';

    return $output;    
}