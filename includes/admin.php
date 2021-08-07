<?php
// Facebook Multi Page/Group Poster v3
// Created by Novartis (Safwan)

// Admin Panel
if ( count( get_included_files() ) == 1 )
    die();    
if ( $adminloggedIn ) {
    global $config, $dbName, $adminOptions, $successImg, $failImg, $hardDemo, $availablePlugins;
    if ( isset( $_GET[ 'doUpdate' ] ) && $adminOptions[ 'updateVersion' ] ) {
        if ( !function_exists( "zip_open" ) )
            return "<h4>$failImg Update Error</h4><p>Your server is missing PHP ZIP support. <br>It is part of default PHP package.<br>Please ask your hosting to enable ZIP support for PHP.<br>Updating cannot be done until this issue is resolved.</p>";
        $output = "<p>Initiating update procedure</p>";
        $updateFileName = $dbName . "-" . $adminOptions[ 'updateVersion' ] . '.zip';
        if ( !file_exists( $updateFileName ) ) {
            $output .= '<p>Downloading New Update</p>';
            $updateFile = readURL( 'http://sarirsoftwares.com/fbmpgp/update.php?doUpdate&purchaseCode=' . $adminOptions[ 'purchaseCode' ] . '&version=' . $adminOptions[ 'version' ] );
            if ( !$updateFile )
                return "$output <br>$failImg Error Downloading Update";
            $dlHandler = fopen( $updateFileName, 'w' );
            if ( !fwrite( $dlHandler, $updateFile ) )
                return "$output<p>$failImg Could not save new update. Operation aborted.</p>";
            fclose($dlHandler);
            $output .= '<p>Update Downloaded And Saved</p>';            
        } else
            $output .= '<p>Update already downloaded.</p>';
        if ( !is_dir( 'backups' ) )
            mkdir( 'backups' );
        $zipHandle = zip_open( $updateFileName );
        if ( !is_resource( $zipHandle ) )
            return "$output<p>$failImg Invalid update file. Please try again later</p>";
        $output .= '<ul>';
        while ($aF = zip_read( $zipHandle ) ) {
            $thisFileName = zip_entry_name( $aF );
            $thisFileDir = dirname( $thisFileName );
            if ( substr( $thisFileName, -1, 1 ) == '/' )
                continue;
            if ( !is_dir( $thisFileDir ) ) {
                 mkdir( $thisFileDir );
                 $output .= '<li>Created Directory ' . $thisFileDir . '</li>';
            }
            if ( !is_dir( $thisFileName ) ) {
                $output .= '<li>' . $thisFileName . '...........';
                $contents = zip_entry_read( $aF, zip_entry_filesize( $aF ) );
                $updateThis = '';               
                if ( $thisFileName == 'upgrade.php' ) {
                    $upgradeExec = fopen( 'upgrade.php','w' );
                    fwrite( $upgradeExec, $contents );
                    fclose( $upgradeExec );
                    include( 'upgrade.php' );
                    unlink( 'upgrade.php' );
                    $output .= ' EXECUTED</li>';
                } else {
                    if ( file_exists( $thisFileName ) )
                        rename( $thisFileName, 'backups/' . basename( $thisFileName ) );
                    $updateThis = fopen( $thisFileName, 'w' );
                    fwrite( $updateThis, $contents );
                    fclose( $updateThis );
                    unset( $contents );
                    $output .= ' UPDATED</li>';
                }
            }        
        }
        $output .= '</ul><p>All existing files were backed up to <strong>backups</strong> folder before being updated</p><p class="success">' . $successImg . ' FBMPGP Updated to v'. $adminOptions[ 'updateVersion' ] . '</p>';
        $adminOptions[ 'version' ] = $adminOptions[ 'updateVersion' ];
        $adminOptions[ 'updateVersion' ] = '';
        saveAdminOptions();
        unlink( $updateFileName );
        return $output;        
    }
    if ( ( isset( $_POST[ 'adminOptions' ] ) || isset( $_POST[ 'appID' ] ) || isset( $_POST[ 'purchaseCode' ] ) ) && $hardDemo ) {
        $warn = "This is online Demo, therefore, settings cannot be changed";
        if ( isset( $_POST[ 'purchaseCode' ] ) )
            die( "Disabled in Demo" );
    } elseif ( isset( $_POST[ 'purchaseCode' ] ) ) {
        $adminOptions[ 'purchaseCode' ] = $_POST[ 'purchaseCode' ];
        $adminOptions[ 'lastUpdateCheck' ] = time();
        saveAdminOptions(); //Should prevent too-fast checks
        $updateCheckResult = readURL( 'http://sarirsoftwares.com/fbmpgp/update.php?purchaseCode=' . $adminOptions[ 'purchaseCode' ] . '&version=' . $adminOptions[ 'version' ] ) or die ( "$failImg $updateCheckResult" . $lang['Error Checking Update'] );
        if ( $updateCheckResult != '.' ) {
            $updateCheckResult = json_decode( $updateCheckResult );
            $adminOptions[ 'updateVersion' ] = $updateCheckResult->{ 'version' };
            saveAdminOptions();
            die( $updateCheckResult->{ 'html' } );
        } else
            die($successImg . " " . $lang[ 'Latest Version' ]);
    } elseif ( isset( $_POST[ 'adminOptions' ] ) ) {    	
        foreach ( $_POST as $key => $data ) {
            if ( $key != "adminOptions" )
                $adminOptions[ $key ] = $data;
        }
        if ( is_numeric( $_POST[ 'adminTimeZone' ] ) ) {
            $timezone = 'Etc/GMT' . ( $_POST[ 'adminTimeZone' ] > 0 ? '-' : '+' );
            $timezone .= abs( $_POST[ 'adminTimeZone' ] );
        } else {
            $timezone = $_POST[ 'adminTimeZone' ];
        }
        $adminOptions[ 'adminTimeZone' ] = $timezone;        
        execComponent( 'adminPanelSave' );
        saveAdminOptions();        
        setcookie( "FBMPGPLang", $adminOptions[ 'lang' ], time() + 86400 * 365 );        
        header( "Location: ./?notify=" . $lang['Settings Saved'] );
        exit;
    } elseif ( isset( $_POST[ 'appID' ] ) && isset( $_POST[ 'appSecret' ] ) ) {
        if ( $db = new PDO( 'sqlite:' . $dbName . '-settings.db' ) ) {
            $statement = $db->prepare( "UPDATE Settings SET appid = \"" . $_POST[ 'appID' ] . "\", secret = \"" . $_POST[ 'appSecret' ] . "\" WHERE admin <> 0" );
            if ( $statement ) {
                $statement->execute();                
            } else {
                showHTML( "Application changing failed while executing database statement." );
            }
            if ( $db = new PDO( 'sqlite:' . $dbName . '-users.db' ) ) {
                $statement = $db->prepare( "UPDATE FB SET usertoken = \"\" WHERE username <> 0" );
                if ( $statement ) {
                    $statement->execute();                
                }
            }
            $adminOptions[ "admintoken" ] = "";
            saveAdminOptions();
            header( "Location: ./" );
            exit;
        }        
    }
    $app    = json_decode( readURL( 'https://graph.facebook.com/' . $GLOBALS[ '__FBAPI__' ] . '/' . $config[ 'appId' ] . '?access_token=' . $config[ 'appId' ] . '|' . $config[ 'secret' ] ) );
    $output = "<div style='float: right' class='align-center'><strong>" . $lang['Using'] . " FBMPGP v" . $adminOptions[ 'version' ] . "</strong><br><small id='checkUpdateResult'><a id='checkUpdate' onclick='checkUpdateClick()'>(" . $lang[ 'check for updates' ] . ")</a><br><span style='font-size: x-small'>" . $lang['Last Checked'] . ": " . ( $adminOptions[ 'lastUpdateCheck' ] ? date( 'd-M-Y G:i', $adminOptions[ 'lastUpdateCheck' ] ) : "-" ) . "</span></small></div><br clear=all>
        <div id=purchaseCode class='lightbox ui-widget-content'><center>
          <h3 class='lightbox ui-widget-header'>" . $lang['Enter'] . " " . $lang['Purchase Code'] . "</h3>
          <form name=purchaseCodeForm id='purchaseCodeForm' class='lightbox' method=post>
          <input type=text size=10 name=purchaseCode class='textbox' value='" . $adminOptions[ 'purchaseCode' ] . "'><br />
          <input type=submit id='updateCheckSubmit' value='" . $lang['Proceed'] . "'></form></center></div>
        
        <div id=whatsNew class='lightbox ui-widget-content'><center>
            <h3 class='lightbox ui-widget-header'>" . $lang['Whats new'] . "</h3></center>
            <div id=whatsNewResult style='padding: 1rem;'>.</div>
            </div>
          
        <div id='admindiv'><h3>". $lang['Settings'] . " " . $lang['Information'] . ":</h3>";
    if ( isset( $app->id ) )
        $output .= "$successImg <strong>" . $lang['Application'] . " " . $lang['ID'] . "</strong>: " . $app->id;
    else
        $output .= "$failImg <strong>" . $lang['Application'] . " " . $lang['ID'] . "</strong>: <span title='App ID and/or App Secret is Invalid'>" . $config[ 'appId' ] . "</span>";
    
    $output .= "&nbsp;&nbsp;<a id=changeAppText><span title='" . $lang['Change App ID'] . "'>(" . $lang['Change'] . ")</span></a><br />
        <div id=changeApp class='lightbox ui-widget-content'><center>
          <h3 class='lightbox ui-widget-header'>" . $lang['Enter New Application'] . "</h3>
          <form name=changeAppForm id='changeAppForm' class='lightbox' method=post>
          <table>
          <tr><td>" . $lang['Application'] . " " . $lang['ID'] . "<td><input type=text size=10 name=appID class='textbox'><br />
          <tr><td>" . $lang['Application'] . " " . $lang['Secret'] . "<td><input type=text size=10 name=appSecret class='textbox'><br />
          </table><input type=submit id='changeAppSubmit' value='" . $lang['Save Settings'] . "'></form></center></div>";
    if ( isset( $app->name ) ) {
        $output .= "$successImg <strong>" . $lang['Application'] . " " . $lang['Name'] ."</strong>: " . $app->name . "<br />";
        if ( isset( $adminOptions[ "admintoken" ] ) && $adminOptions[ "admintoken" ] != "" ) {
            try {    
                $permissions = $fb->api( "/" . $GLOBALS[ '__FBAPI__' ] . "/me/permissions", array( "access_token" => $adminOptions[ "admintoken" ] ) );
                foreach ( $permissions[ 'data' ] as $perm ) {
                    if ( $perm[ 'status' ] == 'granted' ) {
                        $valid = true;
                        break;
                    }
                }    
                if ( isset( $valid ) )
                    $output .= "$successImg <strong>" . $lang['Application'] . " " . $lang['Administrator Token'] ."</strong>: " . $lang['Installed'] . " " . $lang['and'] . " " . $lang['valid'];
                else
                    $output .= "$failImg <strong>" . $lang['Application'] . " " . $lang['Administrator Token'] ."</strong>: " . $lang['Installed'] . " " . $lang['and'] . " " . $lang['invalid'] . "<form name=refresh id=adminToken method=get><input type=hidden name=rg value=1><input type=submit value='" . $lang['Reinstall Token'] . "'></form>";          
            } catch ( Exception $e ) {
                $output .= "$failImg <strong>" . $lang['Application'] . " " . $lang['Administrator Token'] ."</strong>: <span title='" . $e->getMessage() . "'>" . $lang['Installed'] . " " . $lang['and'] . " " . $lang['invalid'] . "</span><form name=refresh id=adminToken method=get><input type=hidden name=rg value=1><input type=submit value='" . $lang['Reinstall Token'] . "'></form>";
            }       
        } else {
            $output .= "$failImg <strong>" . $lang['Application'] . " " . $lang['Administrator Token'] ."</strong>:
            <span title='You may alternatively, logout and signup + authorize as a user who is an administrator of the configured facebook application to install this token'>
                " . $lang['Not Installed'] . "
            </span>&nbsp;&nbsp; <form name=refresh id=adminToken method=get><input type=hidden name=rg value=1><input type=submit value='" . $lang['Install Token'] . "'></form><br />";
        }
    }
    else {
        $output .= "$failImg <strong>" . $lang['Application'] . " " . $lang['Name'] ."</strong>: <br />";    
    }
    //Admin Options
    $output .= "<br /><div>
        <hr><h4>" . $lang['Change'] . " " . $lang['password'] . ": </h4>
        <form name=adminCP method=post>
        <table><tr><td>" . $lang['Enter'] . " " . $lang['current'] . " " . $lang['password'] . ":<td> <input type=password name=oldP><br />
            <tr><td>" . $lang['Enter'] . " " . $lang['new'] . " " . $lang['password'] . ":<td> <input type=password name=newP><br />
            <tr><td>" . $lang['Repeat'] . " " . $lang['new'] . " " . $lang['password'] . ":<td> <input type=password name=renewP><br />
            <tr><td colspan=2 class='text-center'><input type=submit value='" .$lang['Submit'] . "'></table><br>";
    if ( isset( $_POST[ 'oldP' ] ) && isset( $_POST[ 'newP' ] ) && isset( $_POST[ 'renewP' ] ) ) {
        if ( $_POST[ 'oldP' ] != $adminPassword ) {
            $output .= "<span class='notice'>" . $lang['Incorrect'] . " " . $lang['password'] . "</span>";
        } elseif ( $_POST[ 'newP' ] != $_POST[ 'renewP' ] ) {
            $output .= "<span class='notice'>" . $lang['Passwords'] . " " . $lang['do not match'] . "</span>";
        } elseif ( strlen( $_POST[ 'newP' ] ) < 5 ) {
            $output .= "<span class='notice'>" . $lang['Password'] . " " . $lang['length'] . "</span>";
        } elseif ( $hardDemo ) {
            $output .= "<span class='notice'>Password cannot be changed in Demo!</span>";
        } else {
            $newP = encrypt( $_POST[ 'newP' ] );
            if ( $db = new PDO( 'sqlite:' . $dbName . '-settings.db' ) ) {
                $statement = $db->prepare( "UPDATE Settings SET adminpass = \"$newP\" WHERE appid <> 0" );
                if ( $statement ) {
                    $statement->execute();
                    $output .= "<span class='success'>" . $lang['Password'] . " " . $lang['Changed'] . " " . $lang['Successfully'] . "</span>";
                } else {
                    $output .= "<span class='notice'>" . $lang['Critical Error'] . " " . $lang['while changeing'] . " " . $lang['Password'] . "</span>";
                }
            } else {
                $output .= "<span class='notice'>Error opening database!</span>";
            }
        }
    }
    $output .= "</form><hr>
        <form method=POST id=adminForm name=adminForm><input type=hidden name=adminOptions value=1>
        <table class=user>
        <colgroup>
	       <col span='1' style='width: 40%;'>
	       <col span='1' style='width: 60%;'>
	    </colgroup>
        <tr><th colspan=2>" . $lang['Admin'] . " " . $lang['Options'] . "
        <tr><td>" . $lang['Enable'] . " " . $lang['New User Registration'] . ":<td><input type=radio name=enableNUR value=1 " . ( $adminOptions[ 'enableNUR' ] == 1 ? "checked" : "" ) . ">" . $lang['Yes'] . "
            &nbsp;<input type=radio name=enableNUR value=2 " . ( $adminOptions[ 'enableNUR' ] == 2 ? "checked" : "" ) . ">" . $lang['Require Approval'] . "
            &nbsp;<input type=radio name=enableNUR value=0 " . ( $adminOptions[ 'enableNUR' ] == 0 ? "checked" : "" ) . ">" . $lang['No'] . "
        <tr><td>" . $lang['Email Verification'] . ":<td><input type=checkbox name=emailVerify " . ( $adminOptions[ 'emailVerify' ] == 0 ? "" : "checked" ) . ">
        <tr><td>" . $lang['Automatic Role Assignments'] . ":<td><input type=checkbox name=enableARA " . ( $adminOptions[ 'enableARA' ] == 0 ? "" : "checked" ) . ">
        <tr><td>";
        $output .= $lang['Enable'] . " " . $lang['CRON Scheduling'] . ":<td><input type=checkbox name=useCron " . ( $adminOptions[ 'useCron' ] == 0 ? "" : "checked" ) . "><br>";
        if ( $adminOptions[ 'useCron' ] ) {
			if ( ( time() - $adminOptions[ 'lastCronExecution' ] ) > ( $adminOptions[ 'cronDelay' ] * 60 * 2 ) )
     			$output .= "<img src=img/warning.png>";
     		else
     			$output .= "<img src=img/check.png>";
     		$output .= " <small>( " . $lang['Last'] . " " . $lang['CRON Run'] . ": " . date( 'd-M-Y G:i', $adminOptions[ 'lastCronExecution' ] ) . ")</small>"; 
     	}      
        $output .= "<tr><td>" . $lang['Demo Mode'] . ":<td><input type=checkbox name=enableDemo " . ( $adminOptions[ 'enableDemo' ] == 0 ? "" : "checked" ) . ">";
    $output .= "<tr><td>" . $lang['Interface Language'] . ":<td><select name='lang'>";
    $langs = glob( "lang/*.php" );
    foreach ( $langs as $file ) {
        $filename = substr( $file, 5, -9 );
        $output .= "<option value='$filename'" . ( $filename == $adminOptions[ 'lang' ] ? " selected" : "" ) . ">" . strtoupper( $filename );
    }
    $output .= "</select>
    <tr><td>" . $lang['Admin'] . " " . $lang['Time Zone'] . ":<td><select name='adminTimeZone' id='adminTimeZone' class='textbox'>
                " . file_get_contents( 'includes/timezones.html' ) . "
          </select><input type=hidden name=adminTimeZoneId></table>
    	<table class=user>
    	<colgroup>
	       <col span='1' style='width: 40%;'>
	       <col span='1' style='width: 60%;'>
	    </colgroup>
    	<tr><th colspan=2>" . $lang['Email'] . " " . $lang['Options'] . "
    	<tr><td>" . $lang['Admin'] . " " . $lang['Email'] . ":<td><input type=text name='adminEmail' class='textbox' placeholder='' value='" . ( $hardDemo ? "" : $adminOptions[ 'adminEmail' ] ) . "'>
        <tr><td>" . $lang['Notifications'] . ": " . $lang['New User Registration'] . "<td><input type=checkbox name=notifySignUp " . ( $adminOptions[ 'notifySignUp' ] == 0 ? "" : "checked" ) . ">";
    //Posting Options
    $output .= "</table>
        <table class=user>
        <colgroup>
	       <col span='1' style='width: 40%;'>
	       <col span='1' style='width: 60%;'>
	    </colgroup>
        <tr><th colspan=2>" . $lang['Posting'] . " " . $lang['Options'];    
    $output .= "<tr><td>" . $lang['Minimum'] . " " . $lang['Delay'] . ":<td><select name=minimumDelay>";
    for ( $i = 1; $i <= 1800; $i += 5 ) {
        $i = $i - ( $i % 5 );
        if ( $i == 0 )
            $i = 1;
        if ( $i == $adminOptions[ 'minimumDelay' ] )
            $output .= "<option value=$i selected>$i " . $lang['sec'] . "</option>";
        else
            $output .= "<option value=$i>$i " . $lang['sec'] . "</option>";
    }
    $output .= "</select>
        <tr><td>" . $lang['Default'] . " " . $lang['Delay'] . ":<td><select name=defaultDelay>";
    for ( $i = 1; $i <= 1800; $i += 5 ) {
        $i = $i - ( $i % 5 );
        if ( $i == 0 )
            $i = 1;
        if ( $i == $adminOptions[ 'defaultDelay' ] )
            $output .= "<option value=$i selected>$i " . $lang['sec'] . "</option>";
        else
            $output .= "<option value=$i>$i " . $lang['sec'] . "</option>";
    }
    $output .= "</select>";
    $output .= "<tr><td>" . $lang['CRON Run'] . " " . $lang['Delay'] . ":<td><select name=cronDelay>";
    for ( $i = 1; $i <= 30; ++$i ) {
        if ( $i == $adminOptions[ 'cronDelay' ] )
            $output .= "<option value=$i selected>$i " . $lang['min'] . "</option>";
        else
            $output .= "<option value=$i>$i " . $lang['min'] . "</option>";
    }
    $output .= "</select>";
    $output .= "<tr><td>" . $lang['Maximum Cron Posts'] . ":<td><select name=maxCronPosts>";
    for ( $i = 1; $i <= 10; ++$i ) {
        if ( $i == $adminOptions[ 'maxCronPosts' ] )
            $output .= "<option value=$i selected>$i " . $lang['posts'] . "</option>";
        else
            $output .= "<option value=$i>$i " . $lang['posts'] . "</option>";
    }
    $output .= "</select>
        <tr><td>imgur.com " . $lang['Client'] . " " . $lang['ID'] . ":<br><small><a href='https://api.imgur.com/oauth2/addclient' target='_new'>(" . $lang['Click to Create'] . ")</a></small><td><input type=" . ( $hardDemo ? "password" : "text" ) . " name='imgurCID' class='textbox' placeholder='For Image Uploading' value='" . $adminOptions[ 'imgurCID' ] . "'>
        </table>";
    //Customization Options
    $output .= "<br clear=all><table class=user><colgroup>
	       <col span='1' style='width: 40%;'>
	       <col span='1' style='width: 60%;'>
	    </colgroup>
        <tr><th colspan=2>" . $lang['Customization'] . " " . $lang['Options'] . "<br /><small>" . $lang['Leave blank default'] . "</small>
        <tr><td>" . $lang['Theme'] . ":<td><select name='theme' class='textbox'>";
    $themes = glob( "themes/*.html" );
    foreach ( $themes as $file ) {
        $filename = substr( $file, 7, -5 );
        $output .= "<option value='$filename'" . ( $filename == $adminOptions[ 'theme' ] ? " selected" : "" ) . ">" . ucwords( $filename );
    }
    $output .= "</select>
        <tr><td>" . $lang['Logo'] . " " . $lang['URL'] . ":<td><input type=text name='scriptLogo' class='textbox' placeholder='' value='" . $adminOptions[ 'scriptLogo' ] . "'>
        <tr><td>" . $lang['Webpage'] . " " . $lang['Title'] . ":<td><input type=text name='scriptTitle' class='textbox' placeholder='Facebook Multi Page Group Poster' value='" . $adminOptions[ 'scriptTitle' ] . "'>
        <tr><td>" . $lang['Main Heading'] . ":<td><input type=text name='scriptHeading' class='textbox' placeholder='Facebook Poster' value='" . $adminOptions[ 'scriptHeading' ] . "'>
        <tr><td>" . $lang['Extra Footer'] . ":<td><textarea name='scriptFooter' class='textbox' placeholder='HTML allowed. You may also place scripts (like Google Analytics etc.) here'>" . $adminOptions[ 'scriptFooter' ] . "</textarea>";        
    $output .= "</table>";
    if ( $adminOptions[ 'theme' ] != 'fbmpgp' ) {
        $output .= "<br clear=all><table class=user><colgroup>
	       <col span='1' style='width: 40%;'>
	       <col span='1' style='width: 60%;'>
	    </colgroup>
                    <tr><th colspan=2>" . $lang['Theme'] . " " . $lang['Options'] . "
                    <tr><td>" . $lang['Main BG Color'] . ":<td><input type=text name='modernMBGC' class='color {hash:true, required:false} textbox' value='" . $adminOptions[ 'modernMBGC' ] . "'>
                    <tr><td>" . $lang['Content BG Color'] . ":<td><input type=text name='modernCBGC' class='color {hash:true, required:false} textbox' value='" . $adminOptions[ 'modernCBGC' ] . "'>
                    <tr><td>" . $lang['Header BG Color'] . ":<td><input type=text name='modernHBGC' class='color {hash:true, required:false} textbox' value='" . $adminOptions[ 'modernHBGC' ] . "'>
                    </table>";
    }
    if ( count( $availablePlugins ) ) {
        $output .= "<br clear=all><table class=user>
            <tr><th colspan=2>" . $lang['Plugins'];
        foreach( $availablePlugins as $pluginName ) {
            $output .= "<tr><td>" . ucwords( $pluginName ) . "<td><input type=checkbox name='plug_" . $pluginName . "' " . (  $adminOptions[ 'plug_' . $pluginName ] ? "checked" : "" ) . ">";
        }
        $output .= "</table>";
    }    
    $output = doPlug( 'adminPanel', $output );
    $output .= "<br clear=all><p><center><input type=submit value='" . $lang['Save Settings'] . "'></center></p>
        </form></div>";
    
    $output .= "</div>
            <script>
            $( \"#admindiv\" ).tooltip();            
            $('#adminForm').submit(function(event){
                $('#adminForm').block({ 
                    message: '<img src=\"img/loading.gif\">', 
                    timeout: 10000,
                    css: { border: '0px', backgroundColor: 'rgba(255, 255, 255, 0)' },
                    overlayCSS:  { backgroundColor: '#fff', opacity: 0.8 } ,
                    fadeIn:  0
                }); 
                $('input[type=checkbox]').each(function() {
                    if (this.checked) {
                        this.value=1;
                    } else {
                        this.checked=true;
                        this.value=0;
                    }
                });
                document.forms[\"adminForm\"].adminTimeZoneId.value = document.forms[\"adminForm\"].adminTimeZone.selectedIndex;
            });
            $('#adminToken').easyconfirm({
                eventType: 'submit',
                locale: { title: \"" . $lang['Important Note'] . "\", text: \"" . $lang['Admin Token Note'] . "\", button: [\"" . $lang['Cancel'] . "\",\"" . $lang['Proceed'] . "\"]}
            });
            $('#changeAppText').easyconfirm({
                eventType: 'click',
                locale: { title: \"" . $lang['Important Note'] . "\", text: \"" . $lang['App Change Note'] . "\", button: [\"" . $lang['Cancel'] . "\",\"" . $lang['Proceed'] . "\"]}
            });
            $('#purchaseCodeForm').submit(function(event){
                event.preventDefault();
                $('#purchaseCode').trigger('close');
                $('#checkUpdate').block({ 
                    message: '<img src=\"img/loading.gif\">', 
                    //timeout: 30000,
                    css: { border: '0px', backgroundColor: 'rgba(255, 255, 255, 0)' },
                    overlayCSS:  { backgroundColor: '#fff', opacity: 0.8 } ,
                    fadeIn:  0
                }); 
                var options = {
                    target:        '#checkUpdateResult',   // target element(s) to be updated with server response                    
                    //timeout:   25000
                };
                $('#purchaseCodeForm').ajaxSubmit(options);
            });            
            $(document).ready(function() {        
                tz = parseFloat(" . $adminOptions[ 'adminTimeZoneId' ] . ");
                document.getElementById(\"adminTimeZone\").selectedIndex = tz; 
                $('#changeAppText').click(function(e) {
                    $('#changeApp').lightbox_me({
                        centered: true, 
                        onLoad: function() { 
                            $('#changeAppForm').find('input:first').focus()
                            }
                        });
                    //e.preventDefault();
                });                
                " . ( $adminOptions['purchaseCode'] != '' ? "$('#purchaseCodeForm').submit();" : "" ) . "
            });
            function checkUpdateClick() {
                $('#purchaseCode').lightbox_me({
                    centered: true, 
                    onLoad: function() { 
                        $('#purchaseCodeForm').find('input:first').focus()
                        }
                    });
                //e.preventDefault();
            }
           </script>";    
    return $output;
}
?>