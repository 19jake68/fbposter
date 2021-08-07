<?php
// Facebook Multi Page/Group Poster v3
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();

// Page/Groups Refresh Data
if ( $hardDemo && $userName == "Multi" )
    return;
if ( isset( $_POST[ 'upGroups' ] ) ) {
    $groupsRawData = file_get_contents( $_FILES[ 'upGroupsFile' ][ 'tmp_name' ] );    
    $groupsRawData = getStringBetween( $groupsRawData, '"BookmarkSeeAllEntsSectionController","init",', '</script>');
    $groupData = '';
    while ( ( ( $groupLine = getStringBetween( $groupsRawData, '{id','}', true ) ) != "" ) ||
    	( ( $groupLine = getStringBetween( $groupsRawData, '{"id"','}', true ) ) != "" ) ) {    		
        $groupID = getStringBetween( $groupLine, '"', '",' );
        $groupName = preg_replace_callback( "/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", getStringBetween( $groupLine, 'name:"', '",' ) ? getStringBetween( $groupLine, 'name:"', '",' ) : getStringBetween( $groupLine, 'name":"', '",' ) );
        $groupName = str_replace( array( "\u003C", '\n', "\/" ), array( "<", "", "/" ), $groupName);
        if ( ( $groupID != "" ) && ( $groupName != "" ) ) {
            $groupIDs[] = $groupID;
            if ( !$userOptions[ 'autoRemoveGroups' ] )
                $groupData .= $groupID . ":" . urlencode( $groupName ) . "\n"; 
        }
    }
    if ( !isset( $groupIDs ) )
        showHTML( "No groups found in the file" );
    if ( $userOptions[ 'autoRemoveGroups' ] ) {
        try {
            $batchIDs = '';
            for ( $i = 1; $i < count( $groupIDs ); ++$i ) {
                if ( $batchIDs != '')
                    $batchIDs .= ',';
                $batchIDs .= $groupIDs[$i];
                if ( !( $i % 50 ) || ( $i == ( count( $groupIDs ) - 1 ) ) ) {
                    $groups = $fb->api( "/" . $GLOBALS[ '__FBAPI__' ] . "/", "GET", array(
                         "ids" => $batchIDs,
                         "method" => "GET"
                    ) );
                    foreach ( $groups as $s ) {
                        $groupData .= $s[ 'id' ] . ":" . urlencode( $s[ 'name' ] ) . "\n";
                    }
                    $batchIDs = '';
                }
            }              
        }
        catch ( Exception $e ) {
            showHTML( $e->getMessage() );
        }
    }        
    $groupData = urlencode( $groupData );
    if ( $db = new PDO( 'sqlite:' . $dbName . '-users.db' ) ) {        
        $statement   = $db->prepare( "UPDATE FB SET groupdata=\"$groupData\" WHERE userid = \"$userId\"" );
        if ( $statement ) {
            $statement->execute();
        } else {
            showHTML( "Error while updating FaceBook table." );
        }
    } else
        showHTML( "Error while writing groups to DB" );
    header( "Location: ./" );
    die();
}
authRedirect();
?>