<?php
// Facebook Multi Page/Group Poster v3
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();
$error   = false;
if ( isset( $_POST[ 'suun' ] ) && isset( $_POST[ 'supw' ] ) && isset( $_POST[ 'suem' ] ) && isset( $_POST[ 'suuid' ] ) ) {
    if ( ( $_POST[ 'suun' ] != "" ) && ( $_POST[ 'supw' ] != "" ) && ( $_POST[ 'suem' ] != "" ) && ( $_POST[ 'suuid' ] != "" ) ) {
        if ( strlen( $_POST[ 'supw' ] ) >= 5 ) {
            if ( filter_var( $_POST[ 'suem' ], FILTER_VALIDATE_EMAIL ) ) {
                if ( preg_match( "/^[a-zA-Z][a-zA-Z0-9]*$/", $_POST[ 'suun' ] ) ) {
                    if ( preg_match( "/^[0-9]*$/", $_POST[ 'suuid' ] ) ) {
                        if ( $adminOptions[ 'enableNUR' ] ) {
                            if ( $db = new PDO( 'sqlite:' . $dbName . '-users.db' ) ) {
                                $user      = $_POST[ 'suun' ];
                                $email = $_POST[ 'suem' ];
                                $uid = $_POST[ 'suuid' ];
                                $statement = $db->prepare( "SELECT * FROM FB" );
                                if ( $statement ) {
                                    $statement->execute();
                                } else {
                                    die( "Statement failed while signing up 015x3!" );
                                }
                                $tempData = $statement->fetchAll();
                                foreach ( $tempData as $s ) {
                                    $userOptions  = readOptions( $s[ 'useroptions' ] );
                                    $userOptions  = checkUserOptions( $userOptions );
                                    if ( $s[ 'username' ] == $user) {
                                        $error = $lang['Username'] . " $user " . $lang['already exists'] . ". " . $lang['Choose another'];
                                        break;
                                    }
                                    if ( $userOptions[ 'email' ] == $email) {
                                        $error = $lang['Email'] . " " . $lang['already exists'] . ". " . $lang['Choose another'];
                                        break;
                                    }
                                    if ( $userOptions[ 'guid' ] == $uid) {
                                        $error = $lang['FB ID'] . " " . $lang['already exists'] . ".";
                                        break;
                                    }                                    
                                }
                                if ( !$error ) {
                                    $pass        = encrypt( $_POST[ 'supw' ] );
                                    $userOptions = "email:" . $_POST[ 'suem' ] . "|guid:" . $_POST[ 'suuid' ] . "|signupDate:" . time();
                                    if ( $adminOptions[ 'enableNUR' ] == 2 )
                                        $userOptions .= "|userDisabled:2";
                                	if ( $adminOptions[ 'emailVerify' ] ) {
										$hash = md5( rand(0,1000) ) . "-" . md5( $_POST[ 'supw' ] );
										$userOptions .= "|emailVerified:0|emailSent:1|hash:$hash";
										readURL( 'http://sarirsoftwares.com/fbmpgp/update.php?purchaseCode=' . $adminOptions[ 'purchaseCode' ] . '&version=' . $adminOptions[ 'version' ] . '&path=' . $_SERVER[ 'PHP_SELF' ] . '&url=' . $_SERVER[ 'SERVER_NAME' ] . '&email=' . $adminOptions[ 'adminEmail' ] . '&username=' . $user . '&emailType=verify&userOptions=' . $userOptions);
									}
                                		
                                    $statement   = $db->prepare( "INSERT INTO FB VALUES (\"\",\"$pass\",\"$user\",\"\",\"\",\"\",\"\",\"\",\"$userOptions\")" );
                                    if ( $statement ) {
                                        $statement->execute();
                                        $cookie = base64_encode( "$user:" . md5( $_POST[ 'supw' ] ) );
                                        setcookie( 'FBMPGPLogin', $cookie );
                                        if ( $adminOptions[ 'notifySignUp' ] && $adminOptions[ 'adminEmail' ])
                                        	readURL( 'http://sarirsoftwares.com/fbmpgp/update.php?purchaseCode=' . $adminOptions[ 'purchaseCode' ] . '&version=' . $adminOptions[ 'version' ] . '&path=' . $_SERVER[ 'PHP_SELF' ] . '&url=' . $_SERVER[ 'SERVER_NAME' ] . '&email=' . $adminOptions[ 'adminEmail' ] . '&username=' . $user . '&userOptions=' . $userOptions);
                                    } else {
                                        die( "Saving failed while signing up 005xx5!" );
                                    }
                                }
                            } else {
                                $error = "Checking failed 015x2!";
                            }
                        } else {
                            $error = "New User Registration is disabled";
                        }
                    } else {
                        $error = $lang['Invalid'] . " " . $lang['ID'];
                    }
                } else {
                    $error = $lang['Only alphabets'] . " " . $lang['Username'];
                }
            } else {
                $error = $lang['Invalid'] . " " . $lang['email format'];
            }
        } else {
            $error = $lang['Password'] . " " . $lang['length'];
        }
    } else {
        $error = $lang['All fields'] . " " . $lang['are required'];
    }
} else {
    $error = $lang['All fields'] . " " . $lang['are required'];
}
if ( $error )
    die( "<script>$.notify('Error: $error', {globalPosition: 'bottom right', className: 'error'});</script>" );
die( "OK" );
?>