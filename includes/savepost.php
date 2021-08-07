<?php
// Facebook Multi Page/Group Poster v3
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();
$presetname = $_POST[ 'savename' ];
if ( $db = new PDO( 'sqlite:' . $dbName . '-presets.db' ) ) {
    $statement = $db->prepare("SELECT COUNT(*) FROM Presets WHERE username = \"$userName\" AND presetname = \"$presetname\"");
    if ( $statement ) {
        $statement->execute();
    } else {
        die( $failImg." Checking failed!" );
    }
    $s = $statement->fetchColumn();
    if ( $s == 0 ) {            
        $params = array();
        $ptype = $_POST[ 'Type' ];
        $targetselections = $_POST['targetselections'];
        if ( $ptype == "T" ) {
            if ( $_POST[ 'Message' ] != '' ) $params[ "message" ] = $_POST['Message'];
        } elseif ($ptype == "L") {
            if ( $_POST[ 'URL' ] != '' ) $params["link"] = $_POST['URL'];
            if ($_POST['Title'] != '') $params["name"] = $_POST['Title'];
            if ($_POST['Description'] != '') $params["description"] = $_POST['Description'];
            if ($_POST['Message'] != '') $params["message"] = $_POST['Message'];
            if ($_POST['Caption'] != '') $params["caption"] = $_POST['Caption'];
            if ($_POST['Picture'] != '') $params["picture"] = $_POST['Picture'];
        } elseif ($ptype == "I" || $ptype == "A") {
            if (isset($_POST['proxy'])) $params['Proxy'] = '1';
            if ( $_POST[ 'Message' ] != '' ) $params["message"] = $_POST['Message'];
            if ( $_POST[ 'URL' ] != '' ) $params["url"] = $_POST['URL'];
        } elseif ($ptype == "V") {
            if ($_POST['Title'] != '') $params["title"] = $_POST['Title'];
            if ($_POST['Message'] != '') $params["description"] = $_POST['Message'];
            if ( $_POST[ 'URL' ] != '' ) $params["url"] = $_POST['URL'];
        } elseif ($ptype == "S") {
            if ($_POST['Title'] != '') $params["title"] = $_POST['Title'];
            if ($_POST['Message'] != '') $params["description"] = $_POST['Message'];
            for ($i=1;$i<=7;++$i) {
            	if ( $_POST[ 'URL'.$i ] != '' ) $params["url$i"] = $_POST['URL'.$i];
            }
        }
        $postParams = '';
        foreach ($params as $k=>$v) {
            if ($postParams != '') $postParams .= '|';
            $postParams .= "$k:".urlencode($v);
        }
        $statement = $db->prepare("INSERT INTO Presets VALUES (\"$userId\",\"$userName\",\"$presetname\",\"$ptype\",\"$postParams\",\"$targetselections\")");
        if ($statement) {
            $statement->execute();
            $script = "<script type='text/javascript'>
                var x = document.forms['presetForm'].preset;
                var option = document.createElement('option');
                option.text = '$presetname';
                option.value = '$presetname';
                x.add(option);
                setTimeout((function(){
                    $('#savepost').trigger('close');
                }), 500);
                </script>";
            die($successImg. " ".$lang['Settings Saved'] . $script);
        } else {
            die($failImg." Preset Saving failed!");
        }
    } else {
        die($failImg." ".$lang['Name exits']);
    }
} else {
    die($failImg. " Unknown error!");
}
?>