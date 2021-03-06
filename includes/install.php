<?php
// Facebook Multi Page/Group Poster v2.8
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();
$installError = "";
$output = "";
if ( !function_exists( "mcrypt_encrypt" ) )
    $installError .= "$failImg mCrypt PHP extension is missing or disabled. Please ask your hosting to enable/install mCrypt for PHP.<br>";
if ( !extension_loaded('pdo_sqlite') )
    $installError .= "$failImg PDO SQLite support is missing or disabled. Please ask your hosting to enable/install PDO SQLite support for PHP.";
if ( $installError ) {
    $output .= "<h3>Server Compatibility Issues.</h3>
    Your server is missing some default PHP components.<br>The script cannot run properly until these issues are resolved.<br>Please rectify them as instructed below.<br><br>
    <p><strong>$installError</strong></p>";
    return $output;
}
$output .= '<h3>Configuring the Facebook Multi-Poster</h3>
<p>It looks like you are using this software for the first time, or first time after an update.
<p>Please take a moment to configure the software. Future updates will NOT require re-configuring.
<p>
<form id="form_901892" class="appnitro" name="installform" method=post action="./">
    <hr>
    <div id="form_container">
        <div class="form_description">
            <h1>Fill the following fields with appropriate values. Consult user-guide for information.</h1>
        </div>
        <center>
            <table style="text-align: center;" >
                <tr>
                    <td><label class="description" for="element_6">DataBase Prefix</label>
                        <input id="element_6" name="dbprefix" class="element text medium" type="text" style="width:150px" maxlength="10" value="FBMPGPAuth" title="The DB Prefix is used to prefix all SQLite databases created/used by this script. SQLite databases cannot be downloaded if .htaccess file is uploaded and properly working, but as an added security measure, choosing a unique DB Prefix is recommended here." /> 
                    <td><label class="description" for="element_7">Encryption Key (*)</label>
                        <input id="element_7" name="enckey" class="element text medium" type="text" maxlength="8" value="safcomcl" title="The Encryption key is used for encrypting various sensitive data in the database. Must be 8 characters long" /> 
            </table>
        </center>
        <ul >
            <li class="section_break"></li>
            <li id="li_1" >
                <label class="description" for="element_1">Facebook Application ID </label>
                <div>
                    <input id="element_1" name="appid" class="element text medium" type="text" maxlength="255" value=""/> 
                </div>
                <p class="guidelines" id="guide_1"><small>The Facebook Application ID and SECRET you created in the Facebook  Developer Dashboard.</small></p>
            </li>
            <li id="li_2" >
                <label class="description" for="element_2">Facebook Application Secret </label>
                <div>
                    <input id="element_2" name="appsecret" class="element text medium" type="text" maxlength="255" value=""/> 
                </div>
            </li>
            <li class="section_break">
            </li>
            <li id="li_4" >
                <label class="description" for="element_4">Create Admin UserName </label>
                <div>
                    <input id="element_4" name="admin" class="element text medium" type="text" maxlength="255" value=""/> 
                </div>
                <p class="guidelines" id="guide_4"><small>Administrator login will be used only for accessing admin panel, not for posting.</small></p>
            </li>
            <li id="li_5" >
                <label class="description" for="element_5">Create Admin Password </label>
                <div>
                    <input id="element_5" name="adminpass" class="element text medium" type="password" maxlength="255" value=""/> 
                </div>
            </li>
            <li class="buttons">
                <input type="hidden" name="form_id" value="1" />			    
            </li>
        </ul>
    </div>
    <center><input id="saveForm" class="button_text" type="submit" name="submit" value="Submit" /></center>
    <hr>
</form>
<p>
<center><b>For detailed help and usage information, visit: <a href="http://sarirsoftwares.com/facebook-multi-pagegroup-poster-documentation/">Quick Start Guide </a></b><br></center>';
return $output;