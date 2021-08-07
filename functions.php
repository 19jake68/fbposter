<?php
// Facebook Multi Page / Group Poster v3
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
die();

$config = array();
$config[ 'fileUpload' ] = true; // optional, Leave as is.
$adminOptions = array();
$successImg = "<img src=\"img/check.png\" />";
$failImg    = "<img src=\"img/error.png\" />";
$warnImg    = "<img src=\"img/warning.png\" />";
$hardDemo   = ( file_exists( '.demo' ) ? true : false );
$__FBAPI__  =  "v2.7";
define( "MAX_BATCH_IDS", "250" );
$plugins = array(); //filter
$components = array();  //action
$allplugins = array();  //merged_filters
$current_plugin = array();  //current_filter
$availablePlugins = array();

// Function Definitions
function showHTML( $message, $heading = "", $title = "", $footer = "", $endExec = true ) {
    global $loggedIn, $adminloggedIn, $adminOptions, $lang, $warn;
    if ( empty( $adminOptions ) ) {
        $adminOptions[ 'theme' ] = 'metro - v2';
        $lang['Script Title'] = 'Facebook Multi Page/Group Poster';
        $adminOptions[ 'scriptFooter' ] = '';
        $lang['Usage Help'] = 'Usage Help';
        $lang['Home'] = 'Home';
        $adminOptions[ 'scriptLogo' ] = 'img/logo.png';
        $adminOptions[ 'modernMBGC' ] = '#FFFFFF';
        $adminOptions[ 'modernCBGC' ] = '#FFFFFF';
        $adminOptions[ 'modernHBGC' ] = '#081E42';
        $adminOptions[ 'version' ] = '3.07';
        $lang['DIR'] = 'LTR';
        $adminOptions[ 'lang' ] = 'en';
    }
    if ( $heading == "" ) $heading = $lang['Heading'];
    if ( $title == "" ) $title = $lang['Script Title'];
    if ( $footer == "" ) $footer = $adminOptions[ 'scriptFooter' ];
    if ( file_exists( 'themes/' . $adminOptions[ 'theme' ] . '.php' ) ) {
        //$showLogin = true;
        require_once( 'themes/' . $adminOptions[ 'theme' ] . '.php' );
        if (isset($themeProcessed))        	
        	if ( $endExec )
        		die( 0 );
    		else
    			return;
    }
    $footer .= " | v." . $adminOptions[ 'version' ];
    $langs = glob( "lang/*.php" );
    foreach ( $langs as $file ) {
        $filename = substr( $file, 5, -9 );
        if ( ( isset( $_COOKIE[ 'FBMPGPLang' ] ) && $_COOKIE[ 'FBMPGPLang' ] == $filename ) || ( !isset( $_COOKIE[ 'FBMPGPLang' ] ) && ( $adminOptions[ 'lang' ] == $filename ) ) )
            $footer .= " | " . strtoupper( $filename );
        else
            $footer .= " | <a class=noul href='?lang=" . $filename . "'>" . strtoupper( $filename ) . "</a>";
    }
    $footer .= " |";
    if ( $loggedIn ) {
        $menu = '<li><a href="?logout">' . $lang['Logout'] . '</a></li>
        <li><a href="?usershowhelp">' . $lang['Usage Help'] . '</a></li>';
        if ( $adminOptions[ 'useCron' ] )
            $menu .= '<li><a href="?crons">' . $lang['My Crons'] . '</a></li>';
        $menu .= '<li><a href="?ucp">' . $lang['User CP'] . '</a></li>
        <li><a href=".">' . $lang['Home'] . '</a></li>';
    } elseif ( $adminloggedIn ) {
        $menu = '<li><a href="?logout">' . $lang['Logout'] . '</a></li>';
        if ( $adminOptions[ 'useCron' ] )
        $menu .= '<li><a href="?crons">' . $lang['View Crons'] . '</a></li>';
        $menu .= '<li><a href="?users">' . $lang['Users List'] . '</a></li>
        <li><a href=".">' . $lang['Admin Panel'] . '</a></li>';
    } else {
        $menu = '<li><a href="?showhelp">' . $lang['Usage Help'] . '</a></li>
        <li><a href=".">' . $lang['Home'] . '</a></li>';
    }
    $template = file_get_contents( 'themes/' . $adminOptions[ 'theme' ] . '.html' );
    if ( $adminOptions[ 'theme' ] != 'fbmpgp' )
    $head = "<style>
    body, .nojqui { background-color: " . $adminOptions[ 'modernMBGC' ] . " !important; }
    .main { background-color: " . $adminOptions[ 'modernCBGC' ] . " !important; }
    .container-full { background-color: " . $adminOptions[ 'modernHBGC' ] . " !important; }
    .ui-state-active,.tabcontrol .tabs li.active a,.ui-widget-content .ui-state-hover,ui-state-default .ui-state-hover,.ui-widget-header { background-color: " . $adminOptions[ 'modernHBGC' ] . " !important;\n background-image: none; !important;\n opacity: 1 !important; }
    .tabcontrol .tabs {border-color: " . $adminOptions[ 'modernHBGC' ] . " !important;}
    .ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default { background-image: none !important;\n background-color: " . $adminOptions[ 'modernHBGC' ] . " !important;\n opacity: 0.7;\n color: white; }
    </style>";
    else
    $head = "";
    if ( $lang['DIR'] == 'RTL' )
    $head .= "<style>body { direction: rtl !important; } .list-inline { direction: ltr !important; } a.visit,.alignright,.place-right,.app-bar .app-bar-menu>li {float: left !important; }</style>";
    if ( isset( $warn ) ) {
        $footer .= "<script>$.notify('Error: $warn', {globalPosition: 'bottom right', className: 'error'});</script>";
    }
    if ( isset( $_GET[ 'notify' ] ) )
    $footer .= "<script>$.notify('" . $_GET[ 'notify' ] . "', {globalPosition: 'top right', className: 'notify'});</script>";
    $template = str_replace( array(
            "%%title%%",
            "%%head%%",
            "%%logo%%",
            "%%menu%%",
            "%%h1%%",
            "%%p%%",
            "%%footer%%"
        ), array(
            $title,
            $head,
            $adminOptions[ 'scriptLogo' ],
            $menu,
            $heading,
            $message,
            $footer
        ), $template);
    echo $template;
    if ( $endExec ) {
        die( 0 );
    }
}

function showLogin() {
    global $config, $warn, $step, $adminOptions, $lang;
    if ( file_exists( 'themes/' . $adminOptions[ 'theme' ] . '.php' ) ) {
        $showLogin = true;
        showHTML( include_once( 'themes/' . $adminOptions[ 'theme' ] . '.php' ), $lang['Please Login Register'] );
    } else
    showHTML( include_once( 'includes/login.php' ), $lang['Please Login Register'] );
}

function showHelp() {
    global $adminOptions, $lang;
    if ( isset( $_COOKIE[ 'FBMPGPLang' ] ) && file_exists( 'lang/' . $_COOKIE[ 'FBMPGPLang' ] . '-help.html' ) )
    showHTML( file_get_contents( 'lang/' . $_COOKIE[ 'FBMPGPLang' ] . '-help.html' ), $lang['Using'] . " " . $lang['Heading'] );
    else
    showHTML( file_get_contents( 'lang/' . $adminOptions[ 'lang' ] . '-help.html' ), $lang['Using'] . " " . $lang['Heading'] );
}

function checkLogin( $user, $hashed_pass, $uid = 0 ) {
    global $dbName, $adminloggedIn, $loggedIn, $cookie, $warn, $step, $failImg, $lang;
    global $tempData, $userName, $fullname, $password, $adminPassword, $userId, $userToken, $pageData, $groupData, $userOptions, $userIds;
    global $fb, $hardDemo, $hash;
    if ( $db = new PDO( 'sqlite:' . $dbName . '-settings.db' ) ) {
        $statement = $db->prepare( "SELECT * FROM Settings" );
        if ( $statement ) {
            $statement->execute();
        } else {
            showHTML( "$failImg Error while checking login/cookie information. Settings Database opened OK but statement execution failed." );
        }
        $tempData = $statement->fetchAll();
        if ( ( strcasecmp( $user, $tempData[ 0 ][ 'admin' ] ) == 0 ) && ( $hashed_pass === md5( decrypt( $tempData[ 0 ][ 'adminpass' ] ) ) ) ) {
            $adminloggedIn = true;
            $adminPassword = decrypt( $tempData[ 0 ][ 'adminpass' ] );
            $cookie        = base64_encode( "$user:" . $hashed_pass );
            setcookie( 'FBMPGPLogin', $cookie );
            if ( isset( $_GET[ 'logs' ] ) ) {
                showLogs();
            } elseif ( isset( $_GET[ 'rg' ] ) && !$hardDemo ) {
                authRedirect();
            } elseif ( isset( $_GET[ 'users' ] ) ) {
                require_once( 'includes/showusers.php' );
            } elseif ( isset( $_GET[ 'crons' ] ) ) {
                require_once( 'includes/showcrons.php' );
            } elseif ( isset( $_GET[ 'clogs' ] ) ) {
                if ( file_exists( $dbName . '-logs.db' ) )
                unlink( $dbName . '-logs.db' );
                header( "Location: ./?logs" );
                exit;
            } else {
                showHTML( include_once( 'includes/admin.php' ), $lang['Admin Panel'] );
            }
        }
    } else {
        showHTML( "$failImg Failed to open settings database while checking login information. Exiting..." );
    }
    if ( $db = new PDO( 'sqlite:' . $dbName . '-users.db' ) ) {
        $statement = $db->prepare( "SELECT COUNT(*) FROM FB WHERE username = \"$user\"" );
        if ( $statement ) {
            $statement->execute();
        } else {
            showHTML( "$failImg Error while checking login/cookie information. Users Database opened OK but statement execution failed." );
        }
        if ( $statement->fetchColumn() > 0 ) {
            if ( $uid ) {
                $statement = $db->prepare( "SELECT * FROM FB WHERE username = \"$user\" AND userid = \"$uid\"" );
            } else {
                $statement = $db->prepare( "SELECT * FROM FB WHERE username = \"$user\"" );
            }
            if ( $statement ) {
                $statement->execute();
            } else {
                showHTML( "$failImg Users Database query failed while checking login information" );
            }
            $tempData = $statement->fetchAll();
            if ( !$tempData ) {
                $warn = $lang['User does not exist'];
                showLogin();
            }
            
            $userName    = $tempData[ 0 ][ 'username' ];
            $password    = decrypt( $tempData[ 0 ][ 'password' ] );
            $userToken   = $tempData[ 0 ][ 'usertoken' ];
            $fullname    = $tempData[ 0 ][ 'fullname' ];
            $pageData    = $tempData[ 0 ][ 'pagedata' ];
            $groupData   = $tempData[ 0 ][ 'groupdata' ];
            $userId      = $tempData[ 0 ][ 'userid' ];
            $userOptions = readOptions( $tempData[ 0 ][ 'useroptions' ] );
            $userOptions = checkUserOptions( $userOptions );
            $userOptions[ 'lastActive' ] = time();
            if ( isset($_GET['verify'])) {
				if (($_GET['email']==$userOptions[ 'email' ]) && ($_GET['hash']==$userOptions[ 'hash' ])) {
					$userOptions['emailVerified'] = 1;
					$userOptions['emailSent'] = 0;
					$userId = $userName;
				}			
			}
            saveUserOptions();
            if ( $uid ) {
                $statement = $db->prepare( "SELECT * FROM FB WHERE username = \"$user\"" );
                if ( $statement ) {
                    $statement->execute();
                } else {
                    showHTML( "$failImg Users Database query failed while checking id information" );
                }
                $tempData = $statement->fetchAll();
            }
            foreach ( $tempData as $s )
            $userIds[ $s[ 'fullname' ] ] = $s[ 'userid' ];
        }
    } else {
        showHTML( "$failImg Failed to open users database while checking login information. Exiting..." );
    }
    if ( ( strcasecmp( $user, $userName ) != 0 ) || ( $hashed_pass != md5( $password ) ) || ( $user == '' ) || ( $hashed_pass == '' ) ) {
        if ( isset( $_POST[ 'un' ] ) )
        $warn = $lang['Incorrect login info'];
        showLogin();
    }
    $cookie   = base64_encode( "$userName:" . md5( $password ) );
    $loggedIn = true;
}

function readSettings() {
    global $dbName, $config, $adminOptions, $failImg, $availablePlugins;
    if ( $db = new PDO( 'sqlite:' . $dbName . '-settings.db' ) ) {
        $statement = $db->prepare( "SELECT * FROM Settings" );
        if ( $statement ) {
            $statement->execute();
        } else {
            showHTML( "$failImg Settings reading failed!" );
        }
        $tempData = $statement->fetchAll();
        $config[ 'appId' ] = $tempData[ 0 ][ 'appid' ];
        $config[ 'secret' ] = $tempData[ 0 ][ 'secret' ];
        $adminOptions = readOptions( $tempData[ 0 ][ 'adminoptions' ] );
        if ( !isset( $adminOptions[ 'language' ] ) )
        $adminOptions[ 'language' ] = 'en';
        if ( !isset( $adminOptions[ 'enableDemo' ] ) )
        $adminOptions[ 'enableDemo' ] = 0;
        if ( !isset( $adminOptions[ 'enableNUR' ] ) )
        $adminOptions[ 'enableNUR' ] = 1;
        if ( !isset( $adminOptions[ 'enableARA' ] ) )
        $adminOptions[ 'enableARA' ] = 1;
        if ( !isset( $adminOptions[ 'minimumDelay' ] ) )
        $adminOptions[ 'minimumDelay' ] = 1;
        if ( !isset( $adminOptions[ 'defaultDelay' ] ) )
        $adminOptions[ 'defaultDelay' ] = 5;
        if ( !isset( $adminOptions[ 'adminTimeZone' ] ) )
        $adminOptions[ 'adminTimeZone' ] = 'Asia/Karachi';
        if ( !isset( $adminOptions[ 'adminTimeZoneId' ] ) )
        $adminOptions[ 'adminTimeZoneId' ] = 243;
        if ( !isset( $adminOptions[ 'useCron' ] ) )
        $adminOptions[ 'useCron' ] = 0;
        if ( !isset( $adminOptions[ 'lang' ] ) )
        $adminOptions[ 'lang' ] = 'en';
        if ( !isset( $adminOptions[ 'theme' ] ) )
        $adminOptions[ 'theme' ] = 'metro - v2';

        if ( !isset( $adminOptions[ 'scriptTitle' ] ) )
        $adminOptions[ 'scriptTitle' ] = '';
        else
        $adminOptions[ 'scriptTitle' ] = urldecode( $adminOptions[ 'scriptTitle' ] );

        if ( !isset( $adminOptions[ 'scriptHeading' ] ) )
        $adminOptions[ 'scriptHeading' ] = '';
        else
        $adminOptions[ 'scriptHeading' ] = urldecode( $adminOptions[ 'scriptHeading' ] );

        if ( !isset( $adminOptions[ 'scriptFooter' ] ) )
        $adminOptions[ 'scriptFooter' ] = '';
        else
        $adminOptions[ 'scriptFooter' ] = urldecode( $adminOptions[ 'scriptFooter' ] );

        if ( !isset( $adminOptions[ 'scriptLogo' ] ) )
        $adminOptions[ 'scriptLogo' ] = 'img/logo.png';
        else
        $adminOptions[ 'scriptLogo' ] = urldecode( $adminOptions[ 'scriptLogo' ] );

        if ( !isset( $adminOptions[ 'modernMBGC' ] ) )
        $adminOptions[ 'modernMBGC' ] = '#FFFFFF';
        if ( !isset( $adminOptions[ 'modernCBGC' ] ) )
        $adminOptions[ 'modernCBGC' ] = '#FFFFFF';
        if ( !isset( $adminOptions[ 'modernHBGC' ] ) )
        $adminOptions[ 'modernHBGC' ] = '#081E42';
        $adminOptions[ 'version' ] = '3.07';
        if ( !isset( $adminOptions[ 'imgurCID' ] ) )
        $adminOptions[ 'imgurCID' ] = '';
        if ( !isset( $adminOptions[ 'purchaseCode' ] ) )
        $adminOptions[ 'purchaseCode' ] = '';
        if ( !isset( $adminOptions[ 'lastUpdateCheck' ] ) )
        $adminOptions[ 'lastUpdateCheck' ] = '0';
        if ( !isset( $adminOptions[ 'updateVersion' ] ) )
        $adminOptions[ 'updateVersion' ] = '';
        if ( !isset( $adminOptions[ 'cronDelay' ] ) )
        $adminOptions[ 'cronDelay' ] = 3;
        if ( !isset( $adminOptions[ 'maxCronPosts' ] ) )
        $adminOptions[ 'maxCronPosts' ] = 5;
        if ( !isset( $adminOptions[ 'lastCronRun' ] ) )
        $adminOptions[ 'lastCronRun' ] = 0;
        if ( !isset( $adminOptions[ 'lastCronExecution' ] ) )
        $adminOptions[ 'lastCronExecution' ] = $adminOptions[ 'lastCronRun' ];
        if ( !isset( $adminOptions[ 'adminEmail' ] ) )
        $adminOptions[ 'adminEmail' ] = '';
        if ( !isset( $adminOptions[ 'notifySignUp' ] ) )
            $adminOptions[ 'notifySignUp' ] = 0;
        if ( !isset( $adminOptions[ 'notifySettingsChange' ] ) )
            $adminOptions[ 'notifySettingsChange' ] = 0;
        if ( !isset( $adminOptions[ 'emailVerify' ] ) )
            $adminOptions[ 'emailVerify' ] = 0;
        date_default_timezone_set( $adminOptions[ 'adminTimeZone' ] );
        $db = null;
        $plugins = glob( "plugins/" . "*.php" );
        foreach ( $plugins as $plugin ) {
            $pluginName = substr( $plugin, 8, - 4 );
            $availablePlugins[] = $pluginName;
            if ( !isset( $adminOptions[ 'plug_' . $pluginName ] ) )
            $adminOptions[ 'plug_' . $pluginName ] = 0;
        }
    } else {
        showHTML( "$failImg Unable to open settings database. Exiting..." );
    }
}

function readOptions( $opts ) {
    $opts    = explode( '|', $opts );
    $options = array();
    foreach ( $opts as $option ) {
        @list( $paramName, $paramValue ) = explode( ':', $option );
        if ( ( $paramName != "" ) && ( $paramValue != "" ) )
        $options[ $paramName ] = $paramValue;
    }
    return $options;
}

function saveAdminOptions() {
    global $dbName, $adminOptions, $failImg;
    if ( $db2 = new PDO( 'sqlite:' . $dbName . '-settings.db' ) ) {
        $option = "";
        foreach ( $adminOptions as $key => $value ) {
            if ( ( $key != "" ) && ( $value != "" ) ) {
                if ( $option != "" )
                $option .= "|";
                if ( ( $key == "scriptTitle" ) || ( $key == "scriptHeading" ) || ( $key == "scriptFooter" ) || ( $key == "scriptLogo" ) )
                $option .= $key . ":" . urlencode( $value );
                else
                $option .= $key . ":" . $value;
            }
        }
        $statement = $db2->prepare( "UPDATE Settings SET adminoptions=\"$option\" WHERE appid <> 0" );
        if ( $statement )
        $statement->execute();
        else
        showHTML( "$failImg Failed to save admin options." );
    } else {
        die( "$failImg Database open error while saving Admin Options." );
    }
}

function saveUserOptions() {
    global $dbName, $userId, $userOptions, $failImg;
    $pv = "";
    foreach ( $userOptions as $pk => $ps ) {
        if ( $pv != "" )
        $pv .= "|";
        $pv .= $pk . ":" . $ps;
    }
    if ( $db2 = new PDO( 'sqlite:' . $dbName . '-users.db' ) ) {
        if ( is_numeric( $userId ) )
        $statement = $db2->prepare( "UPDATE FB SET useroptions=\"$pv\" WHERE userid = \"$userId\"" );
        else
        $statement = $db2->prepare( "UPDATE FB SET useroptions=\"$pv\" WHERE username = \"$userId\"" );
        if ( $statement ) {
            $statement->execute();
        } else {
            showHTML( "$failImg Saving user options failed" );
        }
    } else {
        die( "$failImg Database open error while saving User Options." );
    }
}

function checkUserOptions( $opt ) {
    global $adminOptions;
    if ( !isset( $opt[ 'userDisabled' ] ) )
    $opt[ 'userDisabled' ] = 0;
    if ( !isset( $opt[ 'signupDate' ] ) )
    $opt[ 'signupDate' ] = 0;
    if ( !isset( $opt[ 'lastActive' ] ) )
    $opt[ 'lastActive' ] = 0;
    if ( !isset( $opt[ 'disableReason' ] ) )
    $opt[ 'disableReason' ] = '';
    if ( !isset( $opt[ 'autoClearForm' ] ) )
    $opt[ 'autoClearForm' ] = 1;
    if ( !isset( $opt[ 'delayHandling' ] ) )
    $opt[ 'delayHandling' ] = 1;
    if ( $opt[ 'delayHandling' ] && !$adminOptions[ "useCron" ] )
    $opt[ 'delayHandling' ] = 0;
    if ( !isset( $opt[ 'autoRemoveGroups' ] ) )
    $opt[ 'autoRemoveGroups' ] = 0;
    if ( !isset( $opt[ 'autoPause' ] ) )
    $opt[ 'autoPause' ] = 1;
    if ( $opt[ 'autoPause' ] && !$adminOptions[ "useCron" ] )
    $opt[ 'autoPause' ] = 0;
    if ( !isset( $opt[ 'autoPauseDelay' ] ) )
    $opt[ 'autoPauseDelay' ] = 20;
    if ( !isset( $opt[ 'autoPauseAfter' ] ) )
    $opt[ 'autoPauseAfter' ] = 50;
    if ( !isset( $opt[ 'lastCronPostTime' ] ) )
    $opt[ 'lastCronPostTime' ] = 0;
    if ( !isset( $opt[ 'totalCronPosts' ] ) )
    $opt[ 'totalCronPosts' ] = 0;
    if ( !isset( $opt[ 'fbapi' ] ) )
    	$opt[ 'fbapi' ] = 'none';
    elseif ( $opt[ 'fbapi' ] != 'none' )
    	$GLOBALS[ '__FBAPI__' ] = $opt[ 'fbapi' ];
    return $opt;
}

function readSavedPosts() {
    global $dbName, $userName;
    $posts = array();
    if ( $db = new PDO( 'sqlite:' . $dbName . '-presets.db' ) ) {
        if ( isset( $_GET['preset'] ) && isset( $_GET['delete'] ) ) {
            if ( $_GET['delete'] === '1' ) {
                $presetname = $_GET[ 'preset' ];
                $statement  = $db->prepare( "DELETE FROM Presets WHERE username = \"$userName\" AND presetname = \"$presetname\"" );
                if ($statement) {
                    $statement->execute();
                }
            }
        }
        $statement = $db->prepare( "SELECT * FROM Presets WHERE username = \"$userName\"" );
        if ( $statement )
        $statement->execute();
        else
        return false;
        $tempData = $statement->fetchAll();
        foreach ( $tempData as $s )
        $posts[] = $s[ 'presetname' ];
        return $posts;
    }
}

function authRedirect() {
    global $config, $userName;
    $redirect = "https://www.facebook.com/" . $GLOBALS[ '__FBAPI__' ] . "/dialog/oauth?auth_type=rerequest";
    $redirect .= "&client_id=" . $config[ 'appId' ];
    $redirect .= "&redirect_uri=http://" . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'SCRIPT_NAME' ];
    $redirect .= "&scope=public_profile,user_photos,user_likes,user_managed_groups,manage_pages,publish_pages,publish_actions";
    if ( isset( $userName ) )
    $redirect .= "&state=" . $userName . "|safInit";
    else
    $redirect .= "&state=adminToken";
    header( "Location: $redirect" );
    exit;
}

function readURL( $url ) {
    //return false;
    $ch      = curl_init();
    $timeout = 60; // set to zero for no timeout
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    $file_contents = curl_exec( $ch );
    curl_close( $ch );
    return $file_contents;
}

function plug( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
    global $plugins, $allplugins;
    static $idx = 0;
    ++$idx;
    $plugins[$tag][$priority][$idx] = array('function'     => $function_to_add,'accepted_args'=> $accepted_args);
    unset( $allplugins[ $tag ] );
    return true;
}

function doPlug( $tag, $value ) {
    global $plugins, $allplugins, $current_plugin;
    $args = array();
    if ( isset($plugins['all']) ) {
        $current_plugin[] = $tag;
        $args = func_get_args();
        reset( $plugins['all'] );
        do {
            foreach ( (array) current($plugins['all']) as $the_ )
            if ( !is_null($the_['function']) )
            call_user_func_array($the_['function'], $args);
        } while ( next($plugins['all']) !== false );
    }
    if ( !isset($plugins[$tag]) ) {
        if ( isset($plugins['all']) )
        array_pop($current_plugin);
        return $value;
    }
    if ( !isset($plugins['all']) )
    $current_plugin[] = $tag;
    if ( !isset( $allplugins[ $tag ] ) ) {
        ksort($plugins[$tag]);
        $allplugins[ $tag ] = true;
    }
    reset( $plugins[ $tag ] );
    if ( empty($args) )
    $args = func_get_args();
    do {
        foreach ( (array) current($plugins[$tag]) as $the_ )
        if ( !is_null($the_['function']) ) {
            $args[1] = $value;
            $value = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
        }
    } while ( next($plugins[$tag]) !== false );
    array_pop( $current_plugin );
    return $value;
}

function execComponent($tag, $arg = '') {
    global $plugins, $components, $allplugins, $current_plugin;
    if ( ! isset($components[$tag]) )
    $components[$tag] = 1;
    else
    ++$components[$tag];
    // Do 'all' actions first
    if ( isset($plugins['all']) ) {
        $current_plugin[] = $tag;
        $all_args = func_get_args();
        reset( $plugins['all'] );
        do {
            foreach ( (array) current($plugins['all']) as $the_ )
            if ( !is_null($the_['function']) )
            call_user_func_array($the_['function'], $all_args);
        } while ( next($plugins['all']) !== false );
    }
    if ( !isset($plugins[$tag]) ) {
        if ( isset($plugins['all']) )
        array_pop($current_plugin);
        return;
    }
    if ( !isset($plugins['all']) )
    $current_plugin[] = $tag;
    $args = array();
    if ( is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) )
    $args[] =& $arg[0];
    else
    $args[] = $arg;
    for ( $a = 2, $num = func_num_args(); $a < $num; $a++ )
    $args[] = func_get_arg($a);
    // Sort
    if ( !isset( $allplugins[ $tag ] ) ) {
        ksort($plugins[$tag]);
        $allplugins[ $tag ] = true;
    }
    reset( $plugins[ $tag ] );
    do {
        foreach ( (array) current($plugins[$tag]) as $the_ )
        if ( !is_null($the_['function']) )
        call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));

    } while ( next($plugins[$tag]) !== false );
    array_pop($current_plugin);
}

class Spintax
{
    public function process( $text ) {
        return preg_replace_callback(
            '/\{(((?>[^\{\}]+)|(?R))*)\}/x',
            array($this,'replace' ),
            $text
        );
    }

    public function replace( $text ) {
        $text = $this -> process( $text[1] );
        $parts= explode( '|', $text );
        return $parts[ array_rand( $parts ) ];
    }
}

function encrypt( $pure_string ) {
    $iv_size          = mcrypt_get_iv_size( MCRYPT_BLOWFISH, MCRYPT_MODE_ECB );
    $iv               = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
    $encrypted_string = mcrypt_encrypt( MCRYPT_BLOWFISH, ENCRYPTION_KEY, utf8_encode( $pure_string ), MCRYPT_MODE_ECB, $iv );
    return base64_encode( $encrypted_string );
}

function decrypt( $encrypted_string ) {
    $iv_size          = mcrypt_get_iv_size( MCRYPT_BLOWFISH, MCRYPT_MODE_ECB );
    $iv               = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
    $decrypted_string = mcrypt_decrypt( MCRYPT_BLOWFISH, ENCRYPTION_KEY, base64_decode( $encrypted_string ), MCRYPT_MODE_ECB, $iv );
    return rtrim( $decrypted_string, "\0\4" );
}

function parseYtUrl( $url ) {
    $pattern = '#^(?:https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?:.+)?$#x';
    preg_match( $pattern, $url, $matches );
    return ( isset( $matches[ 1 ] ) ) ? $matches[ 1 ] : false;
}

function getStringBetween( $string, $start, $end, $useStatic = false ) {
    static $startpos = 0;    
    if ( $useStatic )
    $ini = strpos( $string, $start, $startpos );
    else
    $ini = strpos( $string, $start );
    if ( $ini === FALSE )
    return "";
    $ini += strlen( $start );
    $endpos = strpos( $string, $end, $ini );
    $len    = $endpos - $ini;
    if ( $useStatic )
    $startpos = $endpos;
    return substr( $string, $ini, $len );
}

function sanitizeOutput( $buffer ) {
    $search = array(
        '/\>[^\S ]+/s',// strip whitespaces after tags, except space
        '/[^\S ]+\</s',// strip whitespaces before tags, except space
        '/(\s)+/s' // shorten multiple whitespace sequences
    );
    $replace = array(
        '>',
        '<',
        '\\1'
    );
    $buffer = preg_replace( $search, $replace, $buffer );
    return $buffer;
}

function xssSqlClean() {
	foreach ( $_POST as $key => $data )
		$_POST[$key] = trim($data);
    foreach ( $_REQUEST as $key => $data ) {
        $data = strtolower( $data );

        if ( strpos( $data, "base64_" ) !== false )
        die( "Possible XSS / SQL Injection Attack" );

        if ( strpos( $data, "union" ) !== false && strpos( $data, "select" ) !== false )
        die( "Possible XSS / SQL Injection Attack" );
    }
}
?>