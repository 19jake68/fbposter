<?php
// Facebook Multi Page/Group Poster v3
// Created by Novartis (Safwan)

ob_start();
error_reporting( 0 );

if ( file_exists( 'config.php' ) )
    require_once( 'config.php' );
else
    require_once( 'functions.php' );
require_once( 'includes/RestrictCSRF.php' );

//DB existence check, Creates DB files if not present
if ( !file_exists( 'params.php' ) )
    require( 'includes/createdbs.php' );
else
    require_once( 'params.php' );
if ( !file_exists( $dbName . '-settings.db' ) || !file_exists( $dbName . '-logs.db' ) || !file_exists( $dbName . '-crons.db' ) || !file_exists( $dbName . '-users.db' ) || !file_exists( $dbName . '-presets.db' ) )
    require( 'includes/createdbs.php' );

readSettings();

if ( ( isset( $_GET[ 'lang' ] ) || isset( $_COOKIE[ 'FBMPGPLang' ] ) ) && file_exists( 'lang/' . ( isset( $_GET[ 'lang' ] ) ? $_GET[ 'lang' ] : $_COOKIE[ 'FBMPGPLang' ] ) . '-lang.php' ) )
    require_once( 'lang/' . ( isset( $_GET[ 'lang' ] ) ? $_GET[ 'lang' ] : $_COOKIE[ 'FBMPGPLang' ] ) . '-lang.php' );
else
    require_once( 'lang/' . $adminOptions[ 'lang' ] . '-lang.php' );

$plugins = glob( "plugins/" . "*.php" );        
foreach( $plugins as $plugin ) {
    $pluginName = substr( $plugin, 8, -4 );    
    if ( $adminOptions[ 'plug_' . $pluginName ] )
        require_once( $plugin );
}
        
if ( $adminOptions[ 'scriptTitle' ] != "" )
    $lang['Script Title'] = $adminOptions[ 'scriptTitle' ];
if ( $adminOptions[ 'scriptHeading' ] != "" )
    $lang['Heading'] = $adminOptions[ 'scriptHeading' ];

if ( isset( $_GET[ 'lang' ] ) && file_exists( 'lang/' . $_GET[ 'lang' ] . '-lang.php' ) ) {
    setcookie( "FBMPGPLang", $_GET[ 'lang' ], time() + 86400 * 365 );
    $_COOKIE[ 'FBMPGPLang' ] = $_GET[ 'lang' ];
}
if ( isset( $_COOKIE[ 'FBMPGPLang' ] ) && !file_exists( 'lang/' . $_COOKIE[ 'FBMPGPLang' ] . '-lang.php' ) ) {
    setcookie( "FBMPGPLang", '', time() - 50000 );
    unset( $_COOKIE[ 'FBMPGPLang' ] );
}

//Is this a logout request?
if ( isset( $_GET[ 'logout' ] ) ) {
    setcookie( "FBMPGPLogin", '', time() - 50000 );
    setcookie( "FBMPGPUserID", '', time() - 50000 );
    header( "Location: ./" );
    exit;
}

//Is this a logged in user show help/documentation request?
if ( isset( $_GET[ 'showhelp' ] ) ) {
    showHelp();
}

//At this point we check all Input for XSS/SQLInjection attack, terminate execution if found!
xssSqlClean();

//Is this an Image Proxy Request?
if ( isset( $_GET[ 'proxyurl' ] ) ) {
    require_once( 'includes/proxy.php' );
}

// initialize Facebook class using your own Facebook App credentials
require_once( "src/facebook.php" );
$fb = new Facebook( $config );

// Now we must check if the user is authorized. User might be logging in, authorizing the script or it may be a FB redirect request during the authorization process.

// So, first we check if we are on FB redirect during the authorization process.
if ( isset( $_GET[ 'code' ] ) ) {
    require_once( 'includes/fbauth.php' );
} elseif ( isset( $_POST[ 'un' ] ) && isset( $_POST[ 'pw' ] ) ) {
    // User is logging in...    
    $user        = strtolower( $_POST[ 'un' ] );
    $hashed_pass = md5( $_POST[ 'pw' ] );
    checkLogin( $user, $hashed_pass );
    if ( isset( $_POST[ 'rem' ] ) ) { // If user ticked 'Remember Me' while logging in
        $t = time() + 86400 * 365;
    } else {
        $t = 0;
    }
    setcookie( 'FBMPGPLogin', $cookie, $t );
    if ( $loggedIn )
        setcookie( 'FBMPGPUserID', $userId, $t );
} elseif ( isset( $_POST[ 'suun' ] ) ) {
    require_once( 'includes/signup.php' );
} elseif( isset($_GET['verify']) && ($_GET['email']) && !empty($_GET['email']) AND isset($_GET['hash']) && !empty($_GET['hash']) AND isset($_GET['username']) && !empty($_GET['username']) ){
    $email = $_GET['email']; // Set email variable
    $hashString = explode("-",$_GET['hash']);
    $hash = $hashString[0];
    $hashed_pass = $hashString[1];
    $username = $_GET['username'];
    checkLogin( $username, $hashed_pass, 0 );    
} elseif ( isset( $_COOKIE[ 'FBMPGPLogin' ] ) ) {
    // Authorization Check
    $cookie = base64_decode( $_COOKIE[ 'FBMPGPLogin' ] );
    if ( isset( $_COOKIE[ 'FBMPGPUserID' ] ) )
        $uid = $_COOKIE[ 'FBMPGPUserID' ];
    else
        $uid = 0;
    $cookie = base64_decode( $_COOKIE[ 'FBMPGPLogin' ] );
    list( $user, $hashed_pass ) = explode( ':', $cookie );
    checkLogin( $user, $hashed_pass, $uid );
} else {
    // No authorization found. Show login box
    showLogin();
}

// Now the user must be logged in already for the below code to be executed

// Access Token Checking
if ($adminOptions['emailVerify'] && $userOptions['emailSent'] && !$userOption['emailVerified']) {
	showHTML( $lang['Email Not Verified'], $lang['Welcome'] . " $userName" );
} elseif ( $userToken != "" ) {
    require_once( 'includes/fbtoken.php' );
} elseif ( !isset( $_POST[ 'token' ] ) ) {
    $message = '<div>' . $lang['Not Authorized'] . '.<br />
            ' . $lang['Click Authorize'] . '.<br /><br /><center>
            <form method=get id=Authorize action="https://www.facebook.com/' . $GLOBALS[ '__FBAPI__' ] . '/dialog/oauth">
            <input type=hidden name=client_id value="' . $config[ 'appId' ] . '">
            <input type=hidden name=redirect_uri value="http://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'SCRIPT_NAME' ] . '">
            <input type=hidden name=scope value="public_profile,user_photos,user_likes,user_managed_groups,manage_pages,publish_pages,publish_actions">
            <input type=hidden name=state value="' . $userName . '|safInit">    
            <input type=submit value="' . $lang['Authorize'] . '">&nbsp;<input type=button onclick="showToken()" value="' . $lang['Enter'] . ' ' . $lang['Access Token'] . '">&nbsp;<sup><a href="" onclick="showTokenHelp();return false;">[?]</a></sup>
            </form></center>
        </div><br />
        <div style="font-size: x-small"><b>' . $lang['Permissions Required'] . ':</b><br />
            <b><em>' . $lang['Your Profile'] . ' - </em></b> ' . $lang['Profile Description'] . '.<br />
            <b><em>' . $lang['Your Photos'] . ' - </em></b> ' . $lang['Photos Description'] . '.<br />
            <b><em>' . $lang['Your Pages'] . ' - </em></b> ' . $lang['Pages Description'] . '.<br />
            <b><em>' . $lang['Publish Actions'] . ' - </em></b> ' . $lang['Publish Description'] . '.<br />
            <b><em>' . $lang['Groups List'] . ' - </em></b> ' . $lang['Groups Description'] . '.<br />
        </div>
        <div id=token class="lightbox ui-widget-content"><center>
			<form name=Account class="confirm" id=Account method=post action="?ucp">
				<h3 class="lightbox ui-widget-header">' . $lang["Access Token"] . '</h3>
				<br />
				<textarea name=token id=userTokenValue class="textbox" rows=5>' . ( $hardDemo && ( $userName == "Multi" ) ? "*****" : $userToken ) . '</textarea><input type=hidden name="users">
				</table>
				<input id=updateToken type=submit default value="' . $lang["Update"] . '" disabled> <input type=button value="' . $lang["OKay"] . '"  onclick=\"$("#token").trigger("close");\">
			</form><br />							
			</center>
        </div>
        <div id=tokenhelp class="lightbox ui-widget-content">
			<div id="fb-root"></div><script>(function(d, s, id) {  var js, fjs = d.getElementsByTagName(s)[0];  if (d.getElementById(id)) return;  js = d.createElement(s); js.id = id;  js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.3";  fjs.parentNode.insertBefore(js, fjs);}(document, "script", "facebook-jssdk"));</script><div class="fb-video" data-allowfullscreen="1" data-href="/SarirSoftwares/videos/vb.658561290933922/767674873355896/?type=3"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/SarirSoftwares/videos/767674873355896/"><a href="https://www.facebook.com/SarirSoftwares/videos/767674873355896/">Two Methods of Getting Access Tokens</a><p>Tutorial on getting application access tokens.Method One : Graph API Explorer TokenMethod Two: HTC Sense App TokenGraph API token is short lived, and expires after a few hours, or a day at most. HTC token has long expiry times.Graph API Explorer Tool URL:https://developers.facebook.com/tools/explorer/The URL to get HTC Sense Token, as indicated in the video is;https://www.facebook.com/dialog/oauth/?app_id=41158896424&amp;next=http%3A%2F%2Fwww.facebook.com%2Fconnect%2Flogin_success.html&amp;response_type=token&amp;client_id=41158896424&amp;state=y&amp;scope=public_profile,user_photos,user_likes,user_managed_groups,user_groups,manage_pages,publish_pages,publish_actions</p>Posted by <a href="https://www.facebook.com/SarirSoftwares/">Sarir Softwares</a> on Wednesday, 18 November 2015</blockquote></div></div>
		</div>';
    $message .= "<script>            
            function showToken() {
                $('#token').lightbox_me({
                    centered: true, 
                    onLoad: function() { 
                        $('#Account').find('textarea:first').focus()
                    }
                }); 
            }
            function showTokenHelp() {
                $('#tokenhelp').lightbox_me({
                    centered: true,                     
                }); 
            }
            $(document).ready(function() {
                $('#userTokenValue').on('change keydown paste', function(){
                      $('#updateToken').enable();
                });
            });
            $('#Authorize').easyconfirm({
                eventType: 'submit',
                locale: { title: '" . $lang['Important Note'] . "', text: '" . $lang['User Auth Note'] . "', button: ['" . $lang['Cancel'] . "','" . $lang['Proceed'] . "']}
            });
            </script>";
    showHTML( $message, $lang['Welcome'] . " $userName" );
}

// Is this a Page/Groups Refresh Data Request?
if ( isset( $_GET[ 'rg' ] ) || isset( $_POST[ 'upGroups' ] ) ) {
    require_once( 'includes/fbrg.php' );
}

// Is this a Post Preset Save submission?
if ( isset($_POST[ 'pageid' ] ) ) {
    if ( isset( $_POST[ 'savename' ] ) ) {
        if ( ( $_POST[ 'pageid' ] == 0 ) && ( $_POST[ 'savename' ] !== '' ) ) {
            require_once( 'includes/savepost.php' );
        }
    } 
}

// Is this a logged in user show help/documentation request?
if ( isset( $_GET[ 'usershowhelp' ] ) ) {
    showHelp();
} elseif ( isset( $_GET[ 'ucp' ] ) ) {
    //User Control Panel request?    
    require_once( 'includes/usercp.php' );
} elseif ( isset( $_GET[ 'crons' ] ) ) {
    require_once( 'includes/showcrons.php' );
}

if ( $userOptions[ 'userDisabled' ] )
    showHTML( $userOptions[ 'disableReason' ] . "<br />" . $lang['Manual approval'], $lang['Welcome'] . " $userName" );

// Now we have all the data as user is logged into us
$pages       = explode( "\n", urldecode( $pageData ) );
$groups      = explode( "\n", urldecode( $groupData ) );
$isGroupPost = false;

if ( isset( $_POST[ 'pageid' ] ) ) {
    // Is this a Post Preset Save submission?    
    if (isset($_POST['savename'])) {
        if (($_POST['pageid'] == 0) && ($_POST['savename'] !== '') ) {
            savePost();            
        }
    }
    // This is a post submission. Time to actually post this submission to selected account.          
    require_once( 'includes/post.php' );
} else {
    // No pageid means not a post request, just show the fields and forms to fill-up
    require_once( 'includes/mainform.php' );
    require_once( 'includes/class.JavaScriptPacker.php' );
    $message = sanitizeOutput( $message );
    $packer  = new JavaScriptPacker( $script, 10, true, false );
    $script  = $packer->pack(); // We encrypt the javascript output to make copying difficult on public sites
    $message .= $script . '</script> ';
    showHTML( $message, "<img src='http://graph.facebook.com/" . $GLOBALS[ '__FBAPI__' ] . "/$userId/picture?redirect=1&height=64&type=normal&width=64' width=64 height=65 style='vertical-align:middle;'>&nbsp;" . $lang['Welcome'] . " $fullname" );
}
?>