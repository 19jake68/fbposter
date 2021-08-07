<?php

if ( count( get_included_files() ) == 1 )
    die();

if ( $showLogin ) {
    if ( !isset( $step ) )
        $step = 0;
    $forgotPassLink = "https://www.facebook.com/v2.3/dialog/oauth?client_id=" . $config[ 'appId' ] . "&redirect_uri=" . urlencode( 'http://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'SCRIPT_NAME' ] ) . "&scope=public_profile&state=safX";
    $output = '
    <link rel=stylesheet type="text/css" href="themes/spigot.login.css" />
    <br />
    <div id="form_wrapper" class="form_wrapper">
        <form method=post name="loginForm" class="login active">
            <h3 class=ui-widget-header>' . $lang['Login'] . '</h3>            
            <input type="hidden" value="' . RestrictCSRF::generateToken( 'loginForm' ) . '" name="loginForm" id="loginForm">
            <div>
                <label for="user_login" class="uname">' . $lang['Username'] . '</label>
                <input type="text" name="un" id="user_login" required="required">
            </div>
            <div>
                <label for="user_pass" class="youpasswd">' . $lang['Password'] . '<small><a href="' . $forgotPassLink . '" class="forgot">' . $lang['Forgot Your Password'] . '</a></small></label>
                <input type="password" name="pw" id="user_pass" required="required">
            </div>
            <div class="bottom ui-state-default">
                <div class="remember">
                    <input id="loginkeeping" type="checkbox" name=rem checked>
                    <span>' . $lang['Remember Me'] . '</span>
                </div>
                <input type=submit value="' . $lang['Login'] . '">
                <a rel="register" class="linkform">
                    ' . $lang['Register Here'] . '
                </a>
                <div class="clear"></div>
            </div>
        </form>';
    if ( $adminOptions[ 'enableNUR' ] )
        $output .= '
            <form method=post id="signinForm" name="signinForm" class="register">
                <h3 class=ui-widget-header>' . $lang['Register'] . '</h3> 
                <input type="hidden" value="' . RestrictCSRF::generateToken( 'signinForm' ) . '" name="signinForm" id="signinForm">
                <div>
                    <label for="signup_login" class="uname" data-icon="u">' . $lang['Username'] . '</label>
                    <input type="text" name="suun" id="signup_login" srequired="required">
                </div>
                <div>
                    <label for="signup_email" class="youmail" data-icon="e">' . $lang['Email'] . '</label>
                    <input type="text" name="suem" id="signup_email" required="required">
                </div>
                <div>
                    <label for="signup_pass" class="youpasswd" data-icon="p">' . $lang['Password'] . '</label>
                    <input type="password" name="supw" id="signup_pass" required="required">
                </div>
                <div>
                    <label for="signup_uid" title="Your Facebook numerical User-ID. Click to find out"><a href="http://findmyfacebookid.com/" target="_new">' . $lang['FB ID'] . '</a></label>
                    <input type="text" name="suuid" id="signup_uid" required="required">
                </div>                   
                <div class="bottom ui-state-default">
                    <center><input type=button id="signup" value="' . $lang['Register'] . '"></center>
                    <a rel="login" class="linkform">
                        ' . $lang['Login'] . '
                    </a>
                    <div class="clear"></div>
                </div>
            </form>';
    $output .= '
        </div>
    </div>';   
    $output .= '
    <script>
    $( "#signinForm" ).tooltip(); 
    $("#signup").click(function (event) {
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
    //the form wrapper (includes all forms)
    var $form_wrapper	= $("#form_wrapper"),
    //the current form is the one with class "active"
    $currentForm	= $form_wrapper.children("form.active"),        
    //the switch form links
    $linkform		= $form_wrapper.find(".linkform");
    
    $form_wrapper.children("form").each(function(i){
        var $theForm	= $(this);
        //solve the inline display none problem when using fadeIn/fadeOut
        if(!$theForm.hasClass("active"))
            $theForm.hide();
        $theForm.data({
            width	: $theForm.width(),
            height	: $theForm.height()
        });
    });
    
    setWrapperWidth();
    $linkform.bind("click",function(e){
        var $link	= $(this);
        var target	= $link.attr("rel");
        $currentForm.fadeOut(400,function(){
            //remove class "active" from current form
            $currentForm.removeClass("active");
            //new current form
            $currentForm= $form_wrapper.children("form."+target);
            //animate the wrapper
            $form_wrapper.stop()
                         .animate({
                            width	: $currentForm.data("width") + "px",
                            height	: $currentForm.data("height") + "px"
                         },500,function(){
                            //new form gets class "active"
                            $currentForm.addClass("active");
                            //show the new form
                            $currentForm.fadeIn(400);
                         });
        });
        e.preventDefault();
    });
    function setWrapperWidth(){
        $form_wrapper.css({
            width	: $currentForm.data("width") + "px",
            height	: $currentForm.data("height") + "px"
        });
    }
    </script>
    <div id=result style="display: none"></div><br><br>';

    return $output;    
}