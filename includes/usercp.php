<?php
// Facebook Multi Page/Group Poster v3
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();
if ( $loggedIn ) {
    if ( isset( $_POST[ 'userOptions' ] ) ) {
        foreach ( $_POST as $key => $data ) {            
            if ( $key != "userOptions" )
                $userOptions[ $key ] = $data;
        }
        saveUserOptions();
        header( "Location: ./?ucp" );
        exit;
    } elseif ( isset( $_POST[ 'token' ] ) && ( $_POST[ 'token' ] != '') && ( $_POST[ 'token' ] != $userToken ) ) {
        if ( $hardDemo && $userName == "Multi" )
            return;        
        if ( ( $longUserToken = getStringBetween( $_POST[ 'token' ], 'access_token=','&', true ) ) == "" )
        	$longUserToken = $_POST[ 'token' ];
        $bypass = true;
        require_once( 'includes/fbauth.php' );
    }
    $output = "<h4>" . $lang['Access Token'] . " " . $lang['Information'] . ": ";
    try {    
        $permissions = $fb->api( "/" . $GLOBALS[ '__FBAPI__' ] . "/me/permissions", array( "access_token" => $userToken ) );
        $appData = $fb->api( "//app", array( "access_token" => $userToken ) );
        $output .= "<img src='" . $appData[ "icon_url" ] . "'> " . $appData[ "name" ] . "</h4>";
        foreach ( $permissions[ 'data' ] as $perm ) {
            if ( $perm[ 'status' ] == 'granted' ) {
                if ( $perm[ 'permission' ] == 'public_profile' ) $public_profile = true;
                if ( $perm[ 'permission' ] == 'user_photos' ) $user_photos = true;
                if ( $perm[ 'permission' ] == 'user_managed_groups' ) $user_groups = true;
                if ( $perm[ 'permission' ] == 'manage_pages' ) $manage_pages = true;
                if ( $perm[ 'permission' ] == 'publish_pages' ) $publish_pages = true;
                if ( $perm[ 'permission' ] == 'publish_actions' ) $publish_actions = true;
                if ( $perm[ 'permission' ] == 'user_likes' ) $user_likes = true;
            }
        }
        if ( isset( $public_profile ) )
            $output .= "$successImg " . $lang['Your Profile'] . " " . $lang['Permission Granted'] . "<br />";
        else
            $output .= "$failImg <strong>" . $lang['Your Profile'] . " " . $lang['Not Found'] . "</strong><br />";
        if ( isset( $user_photos ) )
            $output .= "$successImg " . $lang['Your Photos'] . " " . $lang['Permission Granted'] . "<br />";
        else
            $output .= "$failImg <strong>" . $lang['Your Photos'] . " " . $lang['Not Found'] . "</strong><br />";
        if ( isset( $user_groups ) )
            $output .= "$successImg " . $lang['Groups List'] . " " . $lang['Permission Granted'] . "<br />";
        else
            $output .= "$failImg <strong>" . $lang['Groups List'] . " " . $lang['Not Found'] . "</strong><br />";
        if ( isset( $manage_pages ) && isset( $publish_pages ) && isset( $user_likes ) )
            $output .= "$successImg " . $lang['Your Pages'] . " " . $lang['Permission Granted'] . "<br />";
        else
            $output .= "$failImg <strong>" . $lang['Your Pages'] . " " . $lang['Not Found'] . "</strong><br />";
        if ( isset( $publish_actions ) )
            $output .= "$successImg " . $lang['Publish Actions'] . " " . $lang['Permission Granted'] . "<br />";
        else
            $output .= "$failImg <strong>" . $lang['Publish Actions'] . " " . $lang['Not Found'] . "</strong><br />"; 
    }
    catch ( Exception $e ) {
        $output .= "$failImg " . $e->getMessage();
    }    
    $output .= "<br />";
    if ( isset( $public_profile ) && isset( $user_photos ) && isset( $user_groups ) && isset( $manage_pages ) && isset( $publish_pages ) && isset( $user_likes ) && isset( $publish_actions ) )
    	$output .= "<form name=refresh id=userToken method=post><input type=hidden name=token value='access_token=" . $userToken . "&'><input type=submit title='" . $lang['Refresh Data message'] . "' value='" . $lang['Refresh Groups'] . "'>";
    else
    	$output .= "<form name=refresh id=userToken method=get><input type=hidden name=rg value=1><input type=submit value='" . $lang['Refresh Token'] . "'>";
    $output .= "&nbsp;<input type=button onclick='showToken()' value='" . $lang['View Token'] . "'>&nbsp;<sup><a href='' onclick='showTokenHelp();return false;'>[?]</a></sup></form>";
    $output .= '<div id=tokenhelp class="lightbox ui-widget-content">
					<div id="fb-root"></div><script>(function(d, s, id) {  var js, fjs = d.getElementsByTagName(s)[0];  if (d.getElementById(id)) return;  js = d.createElement(s); js.id = id;  js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.3";  fjs.parentNode.insertBefore(js, fjs);}(document, "script", "facebook-jssdk"));</script><div class="fb-video" data-allowfullscreen="1" data-href="/SarirSoftwares/videos/vb.658561290933922/767674873355896/?type=3"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/SarirSoftwares/videos/767674873355896/"><a href="https://www.facebook.com/SarirSoftwares/videos/767674873355896/">Two Methods of Getting Access Tokens</a><p>Tutorial on getting application access tokens.Method One : Graph API Explorer TokenMethod Two: HTC Sense App TokenGraph API token is short lived, and expires after a few hours, or a day at most. HTC token has long expiry times.Graph API Explorer Tool URL:https://developers.facebook.com/tools/explorer/The URL to get HTC Sense Token, as indicated in the video is;https://www.facebook.com/dialog/oauth/?app_id=41158896424&amp;next=http%3A%2F%2Fwww.facebook.com%2Fconnect%2Flogin_success.html&amp;response_type=token&amp;client_id=41158896424&amp;state=y&amp;scope=public_profile,user_photos,user_likes,user_managed_groups,user_groups,manage_pages,publish_pages,publish_actions</p>Posted by <a href="https://www.facebook.com/SarirSoftwares/">Sarir Softwares</a> on Wednesday, 18 November 2015</blockquote></div></div>
				</div>'; 
    $output .= "<script>            
            $('#userToken').easyconfirm({
                eventType: 'submit',
                locale: { title: '" . $lang['Important Note'] . "', text: '" . $lang['User Token Note'] . "', button: ['" . $lang['Cancel'] . "','" . $lang['Proceed'] . "']}
            });
            </script>";
    $message = $output . "<hr><h4>" . $lang['Change'] . " " . $lang['password'] . ": </h4>
                <form name=userCP method=post action='?ucp'>
                <table><tr><td>" . $lang['Enter'] . " " . $lang['current'] . " " . $lang['password'] . ":<td> <input type=password name=oldP>
                <tr><td>" . $lang['Enter'] . " " . $lang['new'] . " " . $lang['password'] . ":<td> <input type=password name=newP>
                <tr><td>" . $lang['Repeat'] . " " . $lang['new'] . " " . $lang['password'] . ":<td> <input type=password name=renewP>
                <tr><td colspan=2 class='text-center'><input type=submit value='" .$lang['Submit'] . "'></table></form>";
    $message .= "<hr><h4>" . $lang['User'] . " " . $lang['Options'] . "</h4>
    			<form action='?ucp' name=userOptionsForm id=userOptionsForm method=post><input type=hidden name=userOptions value=1>
                  <table class='user alignleft'>
                  <tr><td>" . $lang['Auto Clear'] . "<td><input type=checkbox name=autoClearForm " . ( $userOptions[ 'autoClearForm' ] == 0 ? "" : "checked" ) . ">
                  <tr><td>" . $lang['Auto Remove'] . "<td><input type=checkbox name=autoRemoveGroups " . ( $userOptions[ 'autoRemoveGroups' ] == 0 ? "" : "checked" ) . ">
                  <tr><td>" . $lang['Delay Handling'] . "<td><input type=radio name=delayHandling value=0 " . ( $userOptions[ 'delayHandling' ] == 0 ? "checked" : "" ) . ">" . $lang['Browser'] . "<input type=radio name=delayHandling value=1 " . ( $adminOptions[ 'useCron' ] ? "" : "disabled title='This setting cannot be used unless CRON is enabled by administrator'" ) . " " . ( $userOptions[ 'delayHandling' ] == 1 ? "checked" : "" ) . ">" . $lang['Server'] . "
                  <tr><td>" . $lang['Auto Pause'] . "<td><input id=autoPauseYes type=radio name=autoPause value=1 " . ( $adminOptions[ 'useCron' ] ? "" : "disabled title='This setting cannot be used unless CRON is enabled by administrator'" ) . " " . ( $userOptions[ 'autoPause' ] == 1 ? "checked" : "" ) . ">" . $lang['Yes'] . "
            &nbsp;<input id=autoPauseNo type=radio name=autoPause value=0 " . ( $userOptions[ 'autoPause' ] == 0 ? "checked" : "" ) . ">" . $lang['No'];
	$message .= "<tr><td>" . $lang['Auto Pause After'] . "<td><select id=autoPauseAfter name=autoPauseAfter " . ( ($adminOptions[ 'useCron' ] && $userOptions[ 'autoPause' ]) ? "" : "disabled" ) . ">";
    for ( $i = 1; $i <= 400; $i += 5 ) {
    	$i = $i - ( $i % 5 );
        if ( $i == 0 )
            $i = 1;        
        if ( $i == $userOptions[ 'autoPauseAfter' ] )
            $message .= "<option value=$i selected>$i " . $lang['posts'] . "</option>";
        else
            $message .= "<option value=$i>$i " . $lang['posts'] . "</option>";
    }
    $message .= "</select>
    			  <tr><td>" . $lang['Auto Pause Duration'] . "<td><select id=autoPauseDelay name=autoPauseDelay " . ( ($adminOptions[ 'useCron' ] && $userOptions[ 'autoPause' ]) ? "" : "disabled" ) . ">";
    for ( $i = 1; $i <= 360; $i += 5 ) {
        $i = $i - ( $i % 5 );
        if ( $i == 0 )
            $i = 1;
        if ( $i == $userOptions[ 'autoPauseDelay' ] )
            $message .= "<option value=$i selected>$i " . $lang['min'] . "</option>";
        else
            $message .= "<option value=$i>$i " . $lang['min'] . "</option>";
    }
    $message .= "</select>
    			<tr><td>" . $lang['API Version'] . "<td><select id=fbapi name=fbapi>";
    for ( $i = -1; $i <= 7; ++$i ) {
        if ( $i === -1 )
            $j = 'none';
        else
        	$j = "v2.$i";
        if ( $j == $userOptions[ 'fbapi' ] )
            $message .= "<option value='$j' selected>" . ( $i === -1 ? $lang['No Override'] : $j ) . "</option>";
        else
            $message .= "<option value='$j'>" . ( $i === -1 ? $lang['No Override'] : $j ) . "</option>";
    }
    $message .= "</select>
    			<tr><td colspan=2 class='text-center'><input type=submit value='" .$lang['Save'] . "'>                               </table></form>
                  <br />
                  <div id=token class='lightbox ui-widget-content'><center>
                      <form name=Account class='confirm' id=Account method=post>
                      <h3 class='lightbox ui-widget-header'>" . $lang['Access Token'] . "</h3>
                      <br />
                      <textarea name=token id=userTokenValue class='textbox' rows=5>" . ( $hardDemo && ( $userName == "Multi" ) ? "*****" : $userToken ) . "</textarea><input type=hidden name='users'>
                      </table>
                      <input id=updateToken type=submit default value='" . $lang['Update'] . "' disabled> <input type=button value='" . $lang['OKay'] . "'  onclick=\"$('#token').trigger('close');\">
                      </form><br />
                      <strong>" . $lang['Get Token'] . "&nbsp;<a href='https://www.facebook.com/dialog/oauth/?app_id=41158896424&next=https%3A%2F%2Fwww.htcsense.com%2Fauth%2Ffacebook%2Fcallbacks&response_type=token&client_id=41158896424&state=YOUR_STATE_VALUE&scope=public_profile,user_photos,user_likes,user_managed_groups,user_groups,manage_pages,publish_pages,publish_actions' target='_new'><img src='https://fbcdn-photos-d-a.akamaihd.net/hphotos-ak-xfa1/t39.2080-0/851580_10151624509931425_2016153603_n.gif' title='HTC Sense'></a>
                      &nbsp;<a href='https://www.facebook.com/dialog/oauth/?app_id=193278124048833&next=https%3A%2F%2Fwww.htcsense.com%2Fauth%2Ffacebook%2Fcallbacks&response_type=token&client_id=41158896424&state=YOUR_STATE_VALUE&scope=public_profile,user_photos,user_likes,user_managed_groups,user_groups,manage_pages,publish_pages,publish_actions' target='_new'><img src='https://fbcdn-photos-a-a.akamaihd.net/hphotos-ak-xaf1/t39.2081-0/10173491_716190728424234_1087184338_n.png' title='HTC Sense 2'></a>
                      &nbsp;<a href='https://www.facebook.com/dialog/oauth/?app_id=24553799497&next=http%3A%2F%2Fwww.facebook.com%2Fconnect%2Flogin_success.html&response_type=token&state=YOUR_STATE_VALUE&scope=public_profile,user_photos,user_likes,user_managed_groups,user_groups,manage_pages,publish_pages,publish_actions' target='_new'><img src='https://fbstatic-a.akamaihd.net/rsrc.php/v2/yE/r/7Sq7wKJHi_5.png' title='mobileblog'></a>
                      &nbsp;<a href='https://www.facebook.com/v2.0/dialog/oauth?redirect_uri=https%3A%2F%2Fwww.facebook.com%2Fconnect%2Flogin_success.html&scope=public_profile,user_photos,user_likes,user_managed_groups,user_groups,manage_pages,publish_pages,publish_actions&response_type=token&sso_key=com&client_id=10754253724&_rdr' target='_new'><img src='https://fbcdn-photos-c-a.akamaihd.net/hphotos-ak-xaf1/t39.2080-0/851556_10151474535983725_1160551704_n.gif' title='iPhoto'></a>
                      &nbsp;<a href='https://developers.facebook.com/tools/explorer/145634995501895/?method=GET&version=v2.0' target='_new'><img src='https://fbcdn-photos-a-a.akamaihd.net/hphotos-ak-xaf1/t39.2081-0/851576_646264348772288_612357246_n.png' title='Graph API Explorer'></a>
                      </strong><br /><br /></center>
					  
                  </div>
                  <script>					
                    $('#userOptionsForm').submit(function(event){
                        $('#userOptionsForm').block({ 
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
                    });
                    function showToken() {
                        $('#token').lightbox_me({
                            centered: true, 
                            onLoad: function() { 
                                $('#reasonCell').find('input:first').focus()
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
                        $('#autoPauseNo').click(function(e) {
		                    $('#autoPauseAfter').attr('disabled','disabled');
		                    $('#autoPauseDelay').attr('disabled','disabled');
		                    //e.preventDefault();
		                });
		                $('#autoPauseYes').click(function(e) {
		                    $('#autoPauseAfter').enable();
		                    $('#autoPauseDelay').enable();
		                    //e.preventDefault();
		                });
                    });
                  </script>";            
    if ( isset( $_POST[ 'oldP' ] ) && isset( $_POST[ 'newP' ] ) && isset( $_POST[ 'renewP' ] ) ) {
        if ( $_POST[ 'oldP' ] != $password ) {
            $message .= "<span class='notice'>" . $lang['Incorrect'] . " " . $lang['password'] . "</span>";
        } elseif ( $_POST[ 'newP' ] != $_POST[ 'renewP' ] ) {
            $message .= "<span class='notice'>" . $lang['Passwords'] . " " . $lang['do not match'] . "</span>";
        } elseif ( strlen( $_POST[ 'newP' ] ) < 5 ) {
            $message .= "<span class='notice'>" . $lang['Password'] . " " . $lang['length'] . "</span>";
        } elseif ( $hardDemo && ( $userName == "Multi" ) ) {
            $message .= "<span class='notice'>Password cannot be changed for this user!</span>";
        } else {
            $newP = encrypt( $_POST[ 'newP' ] );
            if ( $db = new PDO( 'sqlite:' . $dbName . '-users.db' ) ) {
                $statement = $db->prepare( "UPDATE FB SET password = \"$newP\" WHERE username = \"$userName\"" );
                if ( $statement ) {
                    $statement->execute();
                    $message .= "<span class='notice'>" . $lang['Password'] . " " . $lang['Changed'] . " " . $lang['Successfully'] . "</span>";
                } else {
                    $message .= "<span class='notice'>" . $lang['Critical Error'] . " " . $lang['while changeing'] . " " . $lang['Password'] . "</span>";
                }
            } else {
                $message .= "<span class='notice'>Error opening database!</span>";
            }
        }
    }    
    showHTML( $message, $lang['User Control Panel'] );
}
?>