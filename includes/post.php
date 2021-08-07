<?php
// Facebook Multi Page/Group Poster v3
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();
$access_token = '';
if ( ( $userOptions[ "delayHandling" ] == 0 ) || isset( $_POST[ 'delAcc' ] ) ) {
    $pageID = $_POST[ "pageid" ];    
    if ( $userId == $pageID ) {
        $access_token      = $userToken;
    }
    execComponent( 'accessToken' );
    if ( !$access_token ) {
        foreach ( $pages as $key => $page ) {
            if ( $page != "" ) {
                $p = explode( ":", $page );
                if ( $p[ 0 ] == $pageID ) {
                    if ( isset( $_POST[ 'delAcc' ] ) )
                        unset($pages[$key]);
                    if ( $p[ 1 ] == 'L' ) {
                        $access_token = $userToken;                        
                        $pageID = substr( $_POST[ 'pageid' ], 0, -1 );
                        $isLikedPagePost = true;
                    } else
                    	$access_token = $p[ 3 ];
                    break;
                }
            }
        }
    }
    if ( !$access_token ) {
        // No pages found with matching id, lets check Groups
        foreach ( $groups as $key => $group ) {
            if ( $group != "" ) {
                $g = explode( ":", $group );
                if ( $g[ 0 ] == $pageID ) {
                    // so this IS a group post, we need the usertoken for this
                    if ( isset( $_POST[ 'delAcc' ] ) )
                        unset($groups[$key]);
                    $access_token = $userToken;
                    $isGroupPost  = true;
                    break;
                }
            }
        }
    }
    if ( isset( $_POST[ 'delAcc' ] ) ) {
        if ( !$hardDemo || ( $userName != "Multi" ) ) {    
            $pageData = urlencode(implode("\n",$pages));
            $groupData = urlencode(implode("\n",$groups));
            if ($db = new PDO('sqlite:'.$dbName.'-users.db')) {
                if ($_POST['pageid'] == "pages") $pageData='';
                if ($_POST['pageid'] == "groups") $groupData='';
                $statement = $db->prepare("UPDATE FB SET pagedata=\"$pageData\", groupdata=\"$groupData\" WHERE userid = \"$userId\"");
                if ($statement) {
                    $statement->execute();
                } else {
                    showHTML("Update failed while removing account");
                }            
            } else {
                showHTML("Database Error while removing account");
            }
        }  
        header( "Location: ./" );
        exit;
    }    
    if ( !$access_token ) {
        die( $lang['No Token'] );
    }
} else {
    $isCronJob = true;
    $pageID = "###";
}
$resp   = $lang['posted'];
$params = array(
     "access_token" => $access_token // see: https://developers.facebook.com/docs/facebook-login/access-tokens/
);
if ( isset( $_POST[ 'Type' ] ) ) {
    $ptype = $_POST[ 'Type' ];
} else {
    $ptype = "T";
}
$postlink = "http://www.facebook.com/";

$spintax = new Spintax();
if ( $userOptions[ "delayHandling" ] == 0 ) {
    foreach ( $_POST as $postKey => $postValue ) {
        $postValue = $spintax -> process( $postValue );
        $_POST[ $postKey ] = str_replace( array( "--TARGETNAME--", "--MYNAME--", "--FULLDATETIME--", "--DATE--", "--TIME--", "--SCHEDULEDATE--", "--SCHEDULETIME--", "--UNIQUEID--" ),
                                          array( ( $isGroupPost ? htmlentities( urldecode( $g[ 1 ] ), ENT_COMPAT, 'UTF-8' ) : ( $userId == $pageID ? $fullname : htmlentities( urldecode( $p[ 2 ] ), ENT_COMPAT, 'UTF-8' ) ) ), $fullname, date( 'd-M-Y G:i', time() ), date( 'd/m/y', time() ), date( 'G:i', time() ), $_POST[ 'date' ], $_POST[ 'time' ], uniqid() ), $postValue );
    }
}
execComponent( 'prePost' );
if ( ( ( $ptype == "A" ) || ( $ptype == "I" ) ) && isset( $_POST[ 'proxy' ] ) ) {
    // If proxy option is selected by user
    $_POST[ 'URL' ] = 'http://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'SCRIPT_NAME' ] . '?proxyurl=' . encrypt( $_POST[ 'URL' ] );
}
if ( $ptype == "I" ) {
    // It is an Image Post
    if ( $_POST[ 'URL' ] == '' ) {
        die( $failImg . " " . $lang['No image'] );
    }
    $params[ "message" ] = $_POST[ 'Message' ];
    $params[ "url" ]     = $_POST[ 'URL' ];
    $feed                = '/' . $GLOBALS[ '__FBAPI__' ] . '/' . $pageID . '/' . "photos";
    $postlink .= "photo.php?fbid=";
} elseif ( $ptype == "A" ) {
    // Album Post
    if ( !isset( $_POST[ 'AlbumID' ] ) ) {
        if ( $_POST[ 'URL' ] == '' ) {
            die( $failImg . " " . $lang['No album'] );
        }
        try {           
            $albums = $fb->api( '/' . $GLOBALS[ '__FBAPI__' ] . '/' . $pageID . '/albums', array(
                     "access_token" => $access_token,
                     "limit" => "10000"
                ) );
            echo "<form id=frm" . $pageID . " method=post>
                <input type=hidden name=pageid value='" . $pageID . "'>
                <input type=hidden name=Type value='" . $ptype . "'>
                <input type=hidden name=Message value='" . $_POST[ 'Message' ] . "'>
                <input type=hidden name=URL value='" . $_POST[ 'URL' ] . "'>
                <input type=hidden name=timezone value='" . $_POST[ 'timezone' ] . "'>
                <input type=hidden name=date value='" . $_POST[ 'date' ] . "'>
                <input type=hidden name=time value='" . $_POST[ 'time' ] . "'>";
            echo $lang['Almost done'] . " - " . $lang['Select Album'] . ": <select name='AlbumID'>";
            foreach ( $albums[ 'data' ] as $album ) {
                if ( $album[ 'name' ] == 'Cover Photos' || $album[ 'name' ] == 'Profile Pictures' ) continue; // we cannot post to these node from this API edge
                echo "<option value='" . $album[ 'id' ] . "'>" . $album[ 'name' ] . "</option>\n"; // should return whole form here to support multiple postings
            }
            echo "</select>
                    <input type=submit value=Continue>
                    </form>
                    <script>
                        var options = {
                                    target:        '#" . $pageID . "',
                                    //timeout:   5000 ,
                                    beforeSubmit:  function(formData, jqForm, options) {
                                        var queryString = $.param(formData);
                                        //alert(formData[0].value);
                                        document.getElementById(formData[0].value).innerHTML=' <img src=\"img/loading.gif\" class=bottom /> " . $lang['Posting'] . "...., " . $lang['take time'] . "... ';
                                    } // pre-submit callback
                                    //success:       showResponse  // post-submit callback
                                };
                        $('#frm" . $pageID . "').ajaxForm(options);
                    </script>";
            die( 0 );
        }
        catch ( Exception $e ) {
            die( $failImg . " " . $e->getMessage() );
        }
    } else {
        $params[ "message" ] = $_POST[ 'Message' ];
        $params[ "url" ]     = $_POST[ 'URL' ];
        $feed                = '/' . $GLOBALS[ '__FBAPI__' ] . '/' . $_POST[ 'AlbumID' ] . '/photos';
        $postlink .= "photo.php?fbid=";
    }
} elseif ( $ptype == "L" ) {
    // Link Post
    if ( $_POST[ 'URL' ] == '' ) {
        die( $failImg . " " . $lang['No link'] );
    }
    $params[ "link" ] = $_POST[ 'URL' ];
    if ( isset( $_POST[ 'Title' ] ) && ( $_POST[ 'Title' ] != '' ) )
        $params[ "name" ] = $_POST[ 'Title' ];
    if ( isset( $_POST[ 'Description' ] ) && ( $_POST[ 'Description' ] != '' ) )
        $params[ "description" ] = $_POST[ 'Description' ];
    if ( isset( $_POST[ 'Message' ] ) && ( $_POST[ 'Message' ] != '' ) )
        $params[ "message" ] = $_POST[ 'Message' ];
    if ( isset( $_POST[ 'Caption' ] ) && ( $_POST[ 'Caption' ] != '' ) )
        $params[ "caption" ] = $_POST[ 'Caption' ];
    if ( isset( $_POST[ 'Picture' ] ) && ( $_POST[ 'Picture' ] != '' ) )
        $params[ "picture" ] = $_POST[ 'Picture' ];
    $feed = '/' . $GLOBALS[ '__FBAPI__' ] . '/' . $pageID . '/' . "feed";
    $postlink .= $pageID . "/posts/";
} elseif ( $ptype == "S" ) {
	// Slideshow Post
	$urlcount = 0;
	for ($i=1;$i<=7;++$i) {
    	if ( $_POST[ 'URL'.$i ] != '' ) {
    		++$urlcount;
    		$params["url$i"] = $_POST['URL'.$i];
    		$imgURLs[] = $_POST['URL'.$i];
    	}
    }
    if ( $urlcount < 3 ) {
        die( $failImg . " " . $lang['Slideshow Images Required'] );
    }
    $params[ "title" ]       = $_POST[ 'Title' ];
    $params[ "description" ] = $_POST[ 'Message' ];
    $params[ "slideshow_spec" ] = "{\"images_urls\":[\"".implode("\",\"",$imgURLs)."\"],\"duration_ms\": 2500,\"transition_ms\": 300}";
    $feed                    = '/' . $GLOBALS[ '__FBAPI__' ] . '/' . $pageID . '/' . "videos";
    $postlink .= "photo.php?v=";
} elseif ( $ptype == "V" ) {
    // Video Post        
    if ( $_POST[ 'URL' ] == '' ) {
        die( $failImg . " " . $lang['No video'] );
    }
    $params[ "title" ]       = $_POST[ 'Title' ];
    $params[ "description" ] = $_POST[ 'Message' ];
    $feed                    = '/' . $GLOBALS[ '__FBAPI__' ] . '/' . $pageID . '/' . "videos";
    $postlink .= "photo.php?v=";
    
    //video checking for dailymotion
    if ( stripos( $_POST[ 'URL' ], 'dailymotion.com/video' ) !== false ) {
        if ( $adminOptions[ 'enableDemo' ] )
            die( "$failImg Dailymotion " . $lang['Video uploading'] . " " . $lang['disabled in demo'] . ". " . $lang['Buy script'] );
        $url = str_replace( '/video/', '/embed/video/', $_POST[ 'URL' ] );
        $embed = readURL( $url );
        $url = getStringBetween( $embed, "\"stream_h264_hd_url\":", "," );
        if ( $url == "null" )
            $url = getStringBetween( $embed, "\"stream_h264_hq_url\":", "," );
        if ( $url == "null" )
            $url = getStringBetween( $embed, "\"stream_h264_ld_url\":", "," );
        if ( $url == "null" )
            $url = getStringBetween( $embed, "\"stream_h264_url\":", "," );
        $url = str_replace( '\\', '', $url );
        $params[ "file_url" ] = 'http://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'SCRIPT_NAME' ] . '?proxyurl=' . encrypt( urlencode( substr( $url, 1, -1 )  ) );
    } else {  
        //video checker for youtube
        $vid        = parseYtUrl( $_POST[ 'URL' ] );
        if ( $vid ) {
            $format = "video/mp4"; // the MIME type of the video. e.g. video/mp4, video/webm, etc.
            parse_str( readURL( "http://www.youtube.com/get_video_info?video_id=" . $vid ), $info ); //decode the data
            if ( isset( $info[ 'errorcode' ] ) )
                die( "$failImg " . $info[ 'reason' ] );
            $streams = $info[ 'url_encoded_fmt_stream_map' ]; //the video's location info            
            $streams = explode( ',', $streams );
            foreach ( $streams as $stream ) {
                parse_str( urldecode( $stream ), $data ); //decode the stream
                if ( stripos( $data[ 'type' ], $format ) !== false ) {
                    // We've found the right stream with the correct format
                    $url   = $data[ 'url' ];
                    $sig   = $data[ 'signature' ];
                    $params[ "file_url" ] = str_replace( '%2C', ',', $url . '&' . http_build_query( $data ) . '&signature=' . $sig );
                    unset( $data );                
                    break;
                }
            }
        } elseif ( !file_exists( $_SERVER[ 'DOCUMENT_ROOT' ] . $_POST[ 'URL' ] ) ) {
            $params[ "file_url" ] = $_POST[ 'URL' ];
        } else {
            $params[ "file_url" ] = $_SERVER['HTTP_HOST'] . $_POST[ 'URL' ];
        }
    }
} else {
    // simple status update
    if ( $_POST[ 'Message' ] == '' ) {
        die( $failImg . " " . $lang['empty message'] );
    }
    $params[ "message" ] = $_POST[ 'Message' ];
    $feed                = '/' . $GLOBALS[ '__FBAPI__' ] . '/' . $pageID . '/' . "feed";
    $postlink .= $pageID . "/posts/";
}
if ( ( !$isGroupPost && ( $userId != $pageID ) ) || $adminOptions[ 'useCron' ] ) {
    // Group/Profile posts cannot be scheduled unless cron enabled by Admin (%5Dkp%22kx%2AWylsx)
    if ( isset( $_POST[ 'timezone' ] ) ) {
        if ( is_numeric( $_POST[ 'timezone' ] ) ) {
            $timezone = 'Etc/GMT' . ( $_POST[ 'timezone' ] > 0 ? '-' : '+' );
            $timezone .= abs( $_POST[ 'timezone' ] );
        } else {
            $timezone = $_POST[ 'timezone' ];
        }
        date_default_timezone_set( $timezone );
    }
    $dt = $_POST[ 'date' ];
    $tm = $_POST[ 'time' ];
    if ( $dt || $tm || ( $userOptions[ "delayHandling" ] == 1 ) ) {
        if ( !$dt ) {
            $dt = date( 'd-M-Y' );
        }
        if ( !$tm ) {
            $tm = date( 'G:i' );
        }
        $schedule = strtotime( "$dt $tm" );
        if ( !$isGroupPost && !$isLikedPagePost && ( ( $schedule - time() ) > 900 ) && ( $userId != $pageID ) && ( $userOptions[ "delayHandling" ] == 0 ) ) {
            $params[ "scheduled_publish_time" ] = $schedule;
            $params[ "published" ]              = false;
            $resp                               = $lang['scheduled for'] . " $dt $tm";
        }
        if ( $isGroupPost | $isLikedPagePost || ( $userId == $pageID ) ) {
            $isCronJob = true;
            $resp      = $lang['scheduled for'] . " $dt $tm";
        }
    }
    if ( isset( $_POST[ 'timezone' ] ) )
        date_default_timezone_set( $adminOptions[ 'adminTimeZone' ] );
}
if ( $isGroupPost && $ptype != "V" && $ptype != "I" && $ptype != "A" ) {
    $postlink = "https://www.facebook.com/groups/" . $pageID . "/permalink/";
}
execComponent( 'post' );
try {
    if ( isset( $isCronJob ) && $adminOptions[ 'useCron' ] ) {
        if ( $db = new PDO( 'sqlite:' . $dbName . '-crons.db' ) ) {
            if ( $userOptions[ "delayHandling" ] == 1 ) 
                $pageIDs = explode( ";", $_POST[ "pageid" ] );
            else
                $pageIDs = array( $pageID );                           
            if ( $isGroupPost || ( $userId == $pageID ) )
                $params[ "access_token" ] = "";  
            execComponent( 'preCron' );
            $delay = 0;
            $feedHolder = $feed;
            $postCount = $_POST[ "successposts" ];
            $failCount = $_POST[ "failedposts" ];
            $db->beginTransaction();
            foreach ( $pageIDs as $tempPageID ) {
                if ( !$tempPageID )
                    continue;
                $access_token = '';
                $feed = str_replace( "###", $tempPageID, $feedHolder );
                $pv = "";                
                if ( $userOptions[ "delayHandling" ] == 1 ) {
                    foreach ( $pages as $page ) {
                        if ( $page != "" ) {
                            $p = explode( ":", $page );
                            if ( $p[ 0 ] == $tempPageID ) {
                            	if ( $p[ 1 ] == 'L' ) {
                                    $access_token = $userToken;                        
                                    $feed = str_replace( $tempPageID, substr( $tempPageID, 0, -1 ), $feed );
                                    $isLikedPagePost = true;
                                } else
                                	$access_token = $p[ 3 ];
                                break;
                            }
                        }
                    }
                    foreach ( $params as $pk => $ps ) {
                        if ( $pv != "" )
                            $pv .= "|";
                        $pt = $spintax -> process( $ps );
                        $pt = str_replace( array( "--TARGETNAME--", "--MYNAME--", "--FULLDATETIME--", "--DATE--", "--TIME--", "--SCHEDULEDATE--", "--SCHEDULETIME--", "--UNIQUEID--" ),
                                           array( ( $access_token ? htmlentities( urldecode( $p[ 2 ] ), ENT_COMPAT, 'UTF-8' ) : ( $userId == $tempPageID ? $fullname : $tempPageID ) ), $fullname, date( 'd-M-Y G:i', time() ), date( 'd/m/y', time() ), date( 'G:i', time() ), $_POST[ 'date' ], $_POST[ 'time' ], uniqid() ), $pt );
                        $pv .= $pk . "," . urlencode( $pt );
                    }                   
                    if ( $access_token )
                        $pv .= "|access_token," . urlencode( $access_token );                    
                    $schedule += $delay;
                    $postCount += 1;
                } else {
                    foreach ( $params as $pk => $ps ) {
                        if ( $pv != "" )
                            $pv .= "|";
                        $pv .= $pk . "," . urlencode( $ps );
                    }
                }
                $statement = $db->prepare( "INSERT INTO Crons VALUES (\"$schedule\",\"$fullname ($user)\",\"$feed\",\"$pv\",\"" . microtime() . "\")" );
                if ( $statement ) {
                    $statement->execute();
                } else {
                    if ( $userOptions[ "delayHandling" ] == 1 )
                        $failCount += 1;
                    else
                        die( $failImg . " " . $lang['Cron failed'] );
                }
                $delay = $_POST[ 'delay' ];
            }
            $db->commit();
            if ( $userOptions[ "delayHandling" ] == 1 )
                echo "<script>$.notify('" . $lang['Successfully'] . ' ' . $lang['posted'] . " " . $postCount . "/" . $_POST[ "totalposts" ] . " " . $lang['posts'] . " " . $lang['using CRON'] . ( $failCount ? " ($failCount failed) ..." : " ..." ) . "', {globalPosition: 'bottom right', className: 'information'});document.forms['FBform'].failedposts.value='" . $failCount . "';</script>";                
            else
                echo $successImg . ' ' . $lang['Successfully'] . ' ' . $resp . " " . $lang['using CRON'];
        }
    } else {
        execComponent( 'directPost' );
        $ret = $fb->api( $feed, 'POST', $params );
        if ( strpos( $ret[ 'id' ], "_" ) !== false ) {
            $postlink .= substr( strstr( $ret[ 'id' ], "_" ), 1 );
        } else {
            $postlink .= $ret[ 'id' ];
        }
        echo $successImg . $lang['Successfully'] . ' ' . $resp . " " . $lang['to Facebook'] . " - <a href='$postlink' target=sf>" . $lang['Post Link'] . "</a>";
    }
}
catch ( Exception $e ) {
    die( $failImg . " " . $e->getMessage() );
}
?>