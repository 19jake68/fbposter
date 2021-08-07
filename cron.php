#!/usr/local/bin/php
<?php
// Facebook Multi Page/Group Poster v3
// Created by Novartis (Safwan)

chdir( dirname( __FILE__ ) );

$curTime = time();
$curTimeString = date( 'd-M-Y G:i', $curTime );

if ( !file_exists( 'params.php' ) )
    echoDie( "$curTimeString: No Prms\n", 1 );
else
    require_once( 'params.php' );
require_once( 'functions.php' );

//DB existence check, die otherwise
if ( !file_exists( $dbName . '-settings.db' ) || !file_exists( $dbName . '-logs.db' ) || !file_exists( $dbName . '-crons.db' ) || !file_exists( $dbName . '-users.db' ) )
    echoDie( "$curTimeString: No DBs\n", 1);

readSettings();
if ( !$adminOptions[ 'useCron' ] )
    echoDie( "$curTimeString: CRON disabled\n", 1 );

if ( ( $curTime - $adminOptions[ 'lastCronRun' ] ) < ( $adminOptions[ 'cronDelay' ] * 60 ) )
    echoDie( "$curTimeString: Too Early Run\n", 1 );

$postsToGet = $adminOptions[ 'maxCronPosts' ];
$totalPosts = 0;
$adminOptions[ 'lastCronExecution' ] = $curTime;
saveAdminOptions();

if ( ($db = new PDO( 'sqlite:' . $dbName . '-crons.db' )) && ($db2 = new PDO( 'sqlite:' . $dbName . '-users.db' )) ) {
	selectPosts:
	$failedPosts = 0;
    $statement = $db->prepare( "SELECT * FROM Crons WHERE date <= " . $curTime . " ORDER BY date DESC LIMIT 0," . $postsToGet );
    if ( $statement ) {
        $statement->execute();
    } else {
        echoDie( "$curTimeString: DB Fail\n", 1 );
    }
    $tempData = $statement->fetchAll();
    if ( !count( $tempData ) )
        die();
    $totalPosts += count( $tempData );
    $adminOptions[ 'lastCronRun' ] = $curTime;
    saveAdminOptions();
    $db->beginTransaction();
    foreach ( $tempData as $v ) {
        $p      = explode( '|', $v[ 'params' ] );
        $params = array();
        foreach ( $p as $param ) {
            list( $paramName, $paramValue ) = explode( ',', $param );
            $params[ $paramName ] = urldecode( $paramValue );
        }
        $username = substr( $v[ 'user' ], strpos( $v[ 'user' ], "(" ) + 1, -1 );            
        $statement2 = $db2->prepare( "SELECT * FROM FB WHERE username = \"" . $username . "\"" );
        if ( $statement2 ) {
            $statement2->execute();
            $tempData2 = $statement2->fetchAll();
            if ( count( $tempData2 ) ) {
            	$userOptions  = readOptions( $tempData2[ 0 ][ 'useroptions' ] );
        		$userOptions  = checkUserOptions( $userOptions );
        		$userId = $username;
        		if ( $userOptions[ 'userDisabled' ] ) {
					echoDie( "$curTimeString: User Disabled/NotApproved ($username)\n" );
					++$failedPosts;
					goto PostDel; //Is delete acceptable in this case? Or defer would be better?
				}
				if ( $userOptions[ 'autoPause' ] ) {
					if (($userOptions['totalCronPosts'] >= $userOptions['autoPauseAfter']) && (($curTime - $userOptions['lastCronPostTime'])< ( $userOptions[ 'autoPauseDelay' ] * 60 ))) {
						$newSchedule = $curTime + (( $userOptions[ 'autoPauseDelay' ] * 60 )- ($curTime - $userOptions['lastCronPostTime']));
						$statement = $db->prepare( "UPDATE Crons SET date=\"$newSchedule\" WHERE status = \"" . $v[ 'status' ] . "\"" );
				        if ( $statement ) {
				            $statement->execute();
				            echoDie( "$curTimeString: Post Defer (AutoPause) till " . date( 'd-M-Y G:i', $newSchedule ) . " for " . $v[ 'status' ] . " ($username)\n" );
				        } else {
				            echoDie( "$curTimeString: Defer Fail for " . $v[ 'status' ] . " ($username)\n" );
				        }
				        ++$failedPosts;
				        continue;
					} else {
						if (($curTime - $userOptions['lastCronPostTime']) > ( $userOptions[ 'autoPauseDelay' ] * 60 ) )
							$userOptions['totalCronPosts'] = 0;
						$userOptions['lastCronPostTime'] = time();
						++$userOptions['totalCronPosts'];
						saveUserOptions();							
					}
				}						
            	if ( !array_key_exists( "access_token", $params ) || !$params[ "access_token" ] ) {
                	$params[ "access_token" ] = $tempData2[ 0 ][ 'usertoken' ];
                }
            } else {
                echoDie( "$curTimeString: User Gone $username\n" );
                ++$failedPosts;
                goto PostDel;
            }
        } else {
            echoDie( "$curTimeString: User DB Fail\n" );
            ++$failedPosts;
            goto PostDel;
        }
        try {        	
            require_once( "src/facebook.php" );
            $fb  = new Facebook( $config );
            $ret = $fb->api( $v[ 'feed' ], 'POST', $params );
            echoDie( "$curTimeString: Posted " . $v[ 'status' ] . " ($username)\n" );
        }
        catch ( Exception $e ) {
            echoDie( "$curTimeString: Post Fail for " . $v[ 'status' ] . ": " . $e->getMessage() . " ($username)\n" );
        }
        PostDel:
        $statement = $db->prepare( "DELETE FROM Crons WHERE status = \"" . $v[ 'status' ] . "\"" );
        if ( $statement ) {
            $statement->execute();
        } else {
            echoDie( "$curTimeString: Del Fail for " . $v[ 'status' ] . " ($username)\n" );
        }
    }
    $db->commit();
    if ($failedPosts && ($totalPosts<50)) {
		$postsToGet = $failedPosts;
		goto selectPosts;
	}
    $db = null;    
}

function echoDie($string = '', $shouldDie = false) {
	if ($string){
		if ( !file_exists( 'cronlog.php' ) || ( filesize( 'cronlog.php' ) > 1048576 ) ) {
			$fp = fopen("cronlog.php", "w");
			fwrite($fp, "<?php\n\r/*");
			fclose($fp);
		}
		$fp = fopen("cronlog.php", "a");
		fwrite($fp, "$string");
    	fclose($fp);
	}
    if ($shouldDie)
   		die();  
}
?>