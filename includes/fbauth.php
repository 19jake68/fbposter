<?php
// Facebook Multi Page/Group Poster v3
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();

if ( isset( $bypass ) )
    goto tokenBypass;
// Get user access token
$d         = '';
$d         = readURL( "https://graph.facebook.com/" . $GLOBALS[ '__FBAPI__' ] . "/oauth/access_token?client_id=" . $config[ 'appId' ] . "&client_secret=" . $config[ 'secret' ] . "&code=" . $_GET[ 'code' ] . "&redirect_uri=" . urlencode( 'http://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'SCRIPT_NAME' ] ) );
if ( !$d )
    showHTML( "Failed to read URL from Facebook. cURL missing or server is blocking online resources" );
$d         = json_decode( $d );
$userToken = $d->access_token;
// Now to get Long-live user token
if ( $userToken ) {
    unset( $d );
    $d             = readURL( "https://graph.facebook.com/" . $GLOBALS[ '__FBAPI__' ] . "/oauth/access_token?grant_type=fb_exchange_token&client_id=" . $config[ 'appId' ] . "&client_secret=" . $config[ 'secret' ] . "&fb_exchange_token=" . $userToken );
    if ( !$d )
        showHTML( "Failed to read Long Token URL from Facebook. cURL missing or server is blocking online resources" );
    $d             = json_decode( $d );
    $longUserToken = $d->access_token;
    tokenBypass:
    if ( $longUserToken ) { // Long User token obtained. Now to get Pages and Groups data    
        unset( $d );
        $d = readURL( "https://graph.facebook.com/" . $GLOBALS[ '__FBAPI__' ] . "/me/accounts?limit=10000&access_token=" . $longUserToken );
        if ( $d ) {
            $d        = json_decode( $d, true );
            $pageData = '';
            
            // get pages            
            foreach ( $d[ 'data' ] as $s ) {
                $pageData .= $s[ 'id' ] . ":" . $s[ 'category' ] . ":" . urlencode( $s[ 'name' ] ) . ":" . $s[ 'access_token' ] . "\n";                
            }
            try {
                $response = $fb->api( "/" . $GLOBALS[ '__FBAPI__' ] . "/me", "GET", array(
                     "access_token" => $longUserToken,
                     "fields" => "id,name,first_name,last_name" 
                ) );
                $groups   = $fb->api( "/" . $GLOBALS[ '__FBAPI__' ] . "/me/groups", "GET", array(
                     "access_token" => $longUserToken,
                     "fields" => "id,name",
                     "limit" => "10000"
                ) );
                execComponent( 'preAuth' );
                $lpcount = 0;
                do {
                    if ( isset( $liked_pages[ 'paging' ][ 'next' ] ) ) {
                        $liked_pages   = $fb->api( "/" . $GLOBALS[ '__FBAPI__' ] . "/me/likes", "GET", array(
                             "access_token" => $longUserToken,
                             "fields" => "id,name",
                             "limit" => "100",
                             "after" => $liked_pages[ 'paging' ][ 'cursors' ][ 'after' ]
                        ) );
                    } else {
                        $liked_pages   = $fb->api( "/" . $GLOBALS[ '__FBAPI__' ] . "/me/likes", "GET", array(
                             "access_token" => $longUserToken,
                             "fields" => "id,name,can_post",
                             "limit" => "100"
                        ) );
                    }
                    //print_r($liked_pages);
                    //die();
                    foreach ( $liked_pages[ 'data' ] as $s ) {
                    	if ( $s[ 'can_post' ] )
                        	$pageData .= $s[ 'id' ] . "L:L:" . urlencode( $s[ 'name' ] ) . ":0\n";                
                    }
                    ++$lpcount;
                } while ( isset( $liked_pages[ 'paging' ][ 'next' ] ) && ( $lpcount < 30 ) );
            }
            catch ( Exception $e ) {
                showHTML( $e->getMessage() );
            }
            $pageData = urlencode( $pageData );
            // get User ID
            $userId    = $response[ 'id' ];
            $tokenDate = date( 'd-M-Y G:i' );
            $groupData = '';
                        
            // get groups
            foreach ( $groups[ 'data' ] as $s ) {
                $groupData .= $s[ 'id' ] . ":" . urlencode( $s[ 'name' ] ) . "\n";
            }
            $groupData = urlencode( $groupData );
            
            execComponent( 'midAuth' );
            
            // Now we must save the obtained data to the database
            $state = urldecode( $_GET[ 'state' ] );
            if ( $state === "adminToken" ) {
                $roles = json_decode( readURL( 'https://graph.facebook.com/' . $GLOBALS[ '__FBAPI__' ] . '/' . $config[ 'appId' ] . '/roles?limit=10000&access_token=' . $config[ 'appId' ] . '|' . $config[ 'secret' ] ) );
                $role  = "";
                foreach ( $roles->data as $r ) {
                    if ( $r->user == $userId ) {
                        $role = $r->role;
                        break;
                    }
                }                            
                if ( $role == "administrators" ) {
                    $adminOptions[ "admintoken" ] = $longUserToken;
                    saveAdminOptions();
                    header( "Location: ./" );
                    exit;
                } else {
                    showHTML( "You must be an admin of the FB App to install the Application Administrator Token." );
                }
            }              
            if ( $db = new PDO( 'sqlite:' . $dbName . '-users.db' ) ) {
                $statement = $db->prepare( "SELECT COUNT(*) FROM FB WHERE userid = \"$userId\"" );
                if ( $statement ) {
                    $statement->execute();
                } else {
                    showHTML( "Error while checking user-id from database." );
                }
                if ( $statement->fetchColumn() > 0 ) {
                    // User exists, this must be a re-auth request.
                    $statement = $db->prepare( "SELECT * FROM FB WHERE userid = \"$userId\"" );
                    if ( $statement ) {
                        $statement->execute();
                    } else {
                        showHTML( 'Error while selecting user-id from database.' );
                    }
                    $tempData    = $statement->fetchAll();
                    $userName    = $tempData[ 0 ][ 'username' ];
                    $fullname    = ucwords( $response[ "first_name" ] . " " . $response[ "last_name" ] );
                    $pass        = decrypt( $tempData[ 0 ][ 'password' ] );
                    $userOptions = readOptions( $tempData[ 0 ][ 'useroptions' ] );
                    $statement   = null;
                    $statement   = $db->prepare( "UPDATE FB SET tokendate=\"$tokenDate\", usertoken=\"$longUserToken\", pagedata=\"$pageData\", groupdata=\"$groupData\", fullname=\"$fullname\" WHERE userid = \"$userId\"" );
                    if ( $statement ) {
                        $statement->execute();
                    } else {
                        showHTML( "Error while updating FaceBook table." );
                    }
                } else {
                    // Create the new user                    
                    $fullname = ucwords( $response[ "first_name" ] . " " . $response[ "last_name" ] );
                    if ( isset( $bypass ) )
                        $suun = $userName;
                    else
                        $suun     = strstr( $state, "|safInit", true );
                    if ( $suun ) {
                        $statement = $db->prepare( "UPDATE FB SET userid=\"$userId\", tokendate=\"$tokenDate\", usertoken=\"$longUserToken\", pagedata=\"$pageData\", groupdata=\"$groupData\", fullname=\"$fullname\" WHERE username = \"$suun\"" );
                        execComponent( 'newAuth' );
                        if ( $statement ) {
                            $statement->execute();
                        } else {
                            showHTML( "User Data Update failed during safinit session." );
                        }
                        if ( isset( $bypass ) ) {
                        	$userOptions[ 'role' ] = 'user';
                        	saveUserOptions();
                            header( "Location: ./" );
                            exit;
                        }
                        
                        $roles = json_decode( readURL( 'https://graph.facebook.com/' . $GLOBALS[ '__FBAPI__' ] . '/' . $config[ 'appId' ] . '/roles?limit=10000&access_token=' . $config[ 'appId' ] . '|' . $config[ 'secret' ] ) );
                        $role  = "";
                        foreach ( $roles->data as $r ) {
                            if ( $r->user == $userId ) {
                                $role = $r->role;
                                break;
                            }
                        }
                        $statement = $db->prepare( "SELECT * FROM FB WHERE username = \"$suun\"" );
                        if ( $statement )
                            $statement->execute();
                        else
                            showHTML( "Statement Error during ARA" );
                        $tempData    = $statement->fetchAll();
                        $userOptions = readOptions( $tempData[ 0 ][ 'useroptions' ] );
                        $userOptions[ "role" ] = "";
                        $tempUserIdHolder = $userId;
                        $userId = $suun;
                        saveUserOptions();
                        $userId = $tempUserIdHolder;
                        if ( !isset( $adminOptions[ "admintoken" ] ) || $adminOptions[ "admintoken" ] == "" ) {
                            if ( $role == "administrators" ) {
                                $adminOptions[ "admintoken" ] = $longUserToken;
                                saveAdminOptions();
                            } else {
                                //No admin token yet and a new user who is not admin :(
                            }
                        } elseif ( $role == "" && $adminOptions[ 'enableARA' ] ) {                            
                            try {
                                $response = $fb->api( "/" . $GLOBALS[ '__FBAPI__' ] . "/" . $config[ 'appId' ] . "/roles", "POST", array(
                                     "access_token" => $adminOptions[ "admintoken" ],
                                    "user" => $userOptions[ "guid" ],
                                    "role" => "testers" 
                                ) );
                            }
                            catch ( Exception $e ) {
                                showHTML( "FB API Error during ARA" );
                            }
                        }
                    } else {
                        if ( $state === "safX" ) {
                            showHTML( "No record of such a user. <br />Please signup to use this application.", "Password Retrieval Failed" );                        
                        } else {
                            list( $userName, $p ) = explode( "|", $state );
                            $statement = $db->prepare( "SELECT * FROM FB WHERE username = \"$userName\"" );
                            if ( $statement ) {
                                $statement->execute();
                            } else {
                                showHTML( "Statement Error during Auth. Path impossible" );
                            }
                            $tempData = $statement->fetchAll();
                            if ( $tempData )
                                $pass     = $tempData[ 0 ][ 'password' ];
                            if ( $p !== md5( decrypt( $pass ) ) )
                                showHTML( "Illegal Attempt! Check your PHP Version. Minimum required PHP version is 5.3" );
                        }
                        $statement = $db->prepare( "INSERT INTO FB VALUES (\"$userId\",\"$pass\",\"$userName\",\"$tokenDate\",\"$longUserToken\",\"$pageData\",\"$groupData\",\"$fullname\",\"\")" );
                        if ( $statement ) {
                            $statement->execute();
                        } else {
                            showHTML( "Saving failed during user creation!" );
                        }
                    }
                }
            } else {
                showHTML( "Users Database Open Error." );
            }
            if ( $state === "safX" ) {
                $message = "Here are your login details for this page<br />
                        Your Username: <b>$userName</b><br />
                        Your Password: <b>$pass</b><br /><br />";
                if ( isset( $userOptions[ 'role' ] ) && $userOptions[ 'role' ] == "administrators" ) {
                    if ( $db = new PDO( 'sqlite:' . $dbName . '-settings.db' ) ) {
                        $statement = $db->prepare( "SELECT * FROM Settings" );
                        if ( $statement ) {
                            $statement->execute();
                            $tempData = $statement->fetchAll();
                            $message .= "As you are the administrator of the Facebook application, here are Admin Credentials for logging into script as admin, just in case you forogt them.<br />
                            Admin: <b>" . $tempData[ 0 ][ 'admin' ] . "</b><br />                                    
                            Admin Password: <b>" . decrypt( $tempData[ 0 ][ 'adminpass' ] ) . "</b><br />";
                        }
                    }
                }
                $message .= "<p>Please note and keep your passwords safe. You will need it for future login to this page.</p>
                        <form method=post action='.'>Click the button to login and continue: <input type=submit value=Login></form>";
                showHTML( $message, "Welcome to FB Multi Page/Group Poster" );
            } else {
                header( "Location: ./" );
                exit;
            }
        } else {
            showHTML( "Unable to retrieve accounts using access token" );
        }
    } else {
        showHTML( "No long user token failure 009" );
    }
} else {
    showHTML( "No user token failure 011" );
}
?>