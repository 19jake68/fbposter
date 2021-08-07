<?php
// Facebook Multi Page/Group Poster v3
// Created by Novartis (Safwan)

if ( count( get_included_files() ) == 1 )
    die();
$posts = readSavedPosts();
$message = '
        <script type=text/javascript>
            function showFB(f) {
                s = document.getElementById("fbpost");
                if (f==1) {
                    s.innerHTML = "<div class=\"Row\"><label class=\"Label\">' . $lang['Message'] . ': </label><br/><textarea id=\"Text\" name=\"Message\" cols=\"58\" rows=\"8\"></textarea></div><div class=\"Row\"></div>";
                } else if (f==2) {
                    s.innerHTML = "<div class=\"Row\"><div class=\"RowSm\"><label class=\"Label\">' . $lang['Link'] . ' ' . $lang['URL'] . ':</label><br/><input name=\"URL\" id=\"Link\" type=\"text\" size=\"80\" /></div></div><div class=\"Row\"><label class=\"Label\">' . $lang['Message'] . ': </label><br/><textarea id=\"Text\" name=\"Message\" cols=\"58\" rows=\"8\"></textarea></div><div class=\"Row\"><label class=\"Label\">' . $lang['Link'] . ' ' . $lang['Title'] . ':</label><br/><input name=\"Title\" id=\"Title\" type=\"text\" size=\"80\"></div><div class=\"Row\"><label class=\"Label\">' . $lang['Link'] . ' ' . $lang['Description'] . ': </label><br/><textarea id=\"Desc\" name=\"Description\" cols=\"58\" rows=\"8\"></textarea></div><div class=\"Row\"><label class=\"Label\">' . $lang['Link'] . ' ' . $lang['Caption'] . ':</label><br/><input name=\"Caption\" id=\"Caption\" type=\"text\" size=\"80\"></div><div class=\"Row\"><label class=\"Label\">' . $lang['Picture'] . ' ' . $lang['URL'] . ': ' . ( $adminOptions[ 'imgurCID' ] ? '<small>( <a class=imgUp onclick=\"uploadFile();\">' . $lang['Upload'] . '</a> <span id=imgUpResult></span> )</small>' : '' ) . '</label><br/><input name=\"Picture\" id=\"Picture\" type=\"text\" size=\"80\"> </div>";
                } else if (f==3  || f==4) {
                    s.innerHTML = "<div class=\"Row\"><label class=\"Label\">' . $lang['Image'] . ' ' . $lang['Description'] . ': </label><br/><textarea id=\"Text\" name=\"Message\" cols=\"58\" rows=\"8\"></textarea></div><div class=\"Row\"><div class=\"RowSm\"><label class=\"Label\">' . $lang['Image'] . ' ' . $lang['URL'] . ': ' . ( $adminOptions[ 'imgurCID' ] ? '<small>( <a class=imgUp onclick=\"uploadFile();\">' . $lang['Upload'] . '</a> <span id=imgUpResult></span> )</small>' : '' ) . '</label><br/><input name=\"URL\" id=\"Picture\" type=\"text\" size=\"80\" /><br><input type=checkbox name=proxy>' . $lang['Use'] . ' ' . $lang['Image'] . ' ' . $lang['Proxy'] . '?</div></div>";
                } else if (f==5) {
                    s.innerHTML = "<div class=\"Row\"><label class=\"Label\">' . $lang['Video'] . ' ' . $lang['Title'] . ':</label><br/><input name=\"Title\" id=\"Title\" type=\"text\" size=\"80\"></div><div class=\"Row\"><label class=\"Label\">' . $lang['Video'] . ' ' . $lang['Description'] . ':</label><br/><textarea id=\"Text\" name=\"Message\" cols=\"58\" rows=\"8\"></textarea></div><div class=\"Row\"><div class=\"RowSm\"><label class=\"Label\" title=\"(Local Server Path, Youtube Video URL or Video file URL)\">' . $lang['Video'] . ' ' . $lang['URL'] . ':</label><br/><input name=\"URL\" id=\"Link\" type=\"text\" size=\"80\" /></div></div>";
                } else if (f==6) {
                    s.innerHTML = "<div class=\"Row\"><label class=\"Label\">' . $lang['Slideshow'] . ' ' . $lang['Title'] . ':</label><br/><input name=\"Title\" id=\"Title\" type=\"text\" size=\"80\"></div><div class=\"Row\"><label class=\"Label\">' . $lang['Slideshow'] . ' ' . $lang['Description'] . ':</label><br/><textarea id=\"Text\" name=\"Message\" cols=\"58\" rows=\"8\"></textarea></div>";
                    for (i=1;i<=7;++i) {
						s.innerHTML += "<div class=\"Row\"><div class=\"RowSm\"><label class=\"Label\">' . $lang['Image'] . ' ' . $lang['URL'] . ': ' . ( $adminOptions[ 'imgurCID' ] ? '<small>( <a class=imgUp onclick=\"uploadFile("+i+")\">' . $lang['Upload'] . '</a> <span id=imgUpResult"+i+"></span> )</small>' : '' ) . '</label><br/><input name=\"URL"+i+"\" id=\"Picture"+i+"\" type=\"text\" size=\"80\" /></div></div>";
					}
                }     
            }
            function uploadFile(i) {
				if (i)
					document.getElementById(\'letarget\').value = i;
				$(\'input[id=lefile]\').click();
			}
        </script>
        <input id="lefile" type="file" accept="image/jpeg,image/gif, image/png" style="display:none">
        <input id="letarget" type="hidden">        
        <div class="clear align-center text-center"><form name=presetForm method=get>
            <label class="Label">' . $lang['Wish Message'] . '</label><br /><br />
            ' . $lang['Load Post'] . ' : &nbsp;&nbsp;<select name=preset>';
if ($posts)
    foreach ($posts as $s)
        $message .= "<option value='$s'>$s</option>";
$message .= ' </select>
            <img onclick="document.forms[\'presetForm\'].submit();" src="img\load.png" id=loadpreset title="Load Post Preset" alt="Load Post" width="16px">&nbsp;&nbsp;<img src="img\save.gif" id="savepreset" title="Save Post Preset" alt="Save Post" width="16px">&nbsp;&nbsp;<img src="img\delete.png" id="deletepreset" title="Delete Post Preset" alt="Delete Post" width="16px">
            <input type=hidden name=delete>
            </form>            
        </div>
        <div id=savepost class="lightbox ui-widget-content"><center>
          <h3 class="lightbox ui-widget-header">' . $lang['Preset Name'] . ' :</h3>
          <form name=savepost id="formsavepost" class="lightbox" method=post>
          <input type=text size=10 name=savepostname style="width: 200px !important"><br />          
          <input type=submit id="savesubmit" value="' . $lang['Save'] . '"><br><span id="saveresult"></span></form>
        </div>
            
        <form id="FBform" method=post name="FBform">            		
        <input type=hidden name=pageid>  
        <input type=hidden name=savename>
        <input type=hidden name=targetselections>
        <input type=hidden name=sdelay>
        <input type=hidden name=totalposts>
        <input type=hidden name=successposts>
        <input type=hidden name=failedposts>
        <div>
        <div class="clear align-center text-center">
            <div class="Row">            
            <div id="radioset" style="font-size: 0.7em;margin-top: 5px">
                &nbsp;<input type="radio" name="Type" onclick="showFB(1)" id="TypeT" value="T" checked="checked" /><label for="TypeT" class="RowSm">' . $lang['Text'] . ' ' . $lang['Post'] . '</label>
                &nbsp;<input type="radio" name="Type" onclick="showFB(2)" id="TypeL" value="L" /><label for="TypeL" class="RowSm">' . $lang['Link'] . '</label>
                &nbsp;<input type="radio" name="Type" onclick="showFB(3)" id="TypeI" value="I"/><label for="TypeI" class="RowSm">' . $lang['Image'] . '</label>
                &nbsp;<input type="radio" name="Type" onclick="showFB(4)" id="TypeA" value="A" ' . ( $userOptions[ "delayHandling" ] == 1 ? 'disabled' : '' ) . '/><label for="TypeA"class="RowSm"' . ( $userOptions[ "delayHandling" ] == 1 ? ' title="Not available with server delay option"' : '' ) . '>' . $lang['Album'] . ' ' . $lang['Post'] . '</label>
                &nbsp;<input type="radio" name="Type" onclick="showFB(5)" id="TypeV" value="V" /><label for="TypeV" class="RowSm">' . $lang['Video'] . '</label>
                &nbsp;<input type="radio" name="Type" onclick="showFB(6)" id="TypeS" value="S" /><label for="TypeS" class="RowSm">' . $lang['Slideshow'] . '</label>';
$message = doPlug( 'postTypes', $message );
$message .= '
            </div></div><script>$( "#radioset" ).buttonset();</script>           
            <br />
            <div id=fbpost>
                <div class="Row"><label class="Label">' . $lang['Message'] . ': </label><br/><textarea id="Text" name="Message" cols="58" rows="8"></textarea></div><div class="Row"></div>
            </div>
        </div>
        <br /><hr>
        <div class="Row">
          <div class="Left">
          <br />
          <div>' . $lang['Select'] . ' ' . $lang['Your'] . ' ' . $lang['Timezone'] . ':
          <select name="timezone" id="timezone">
                ' . file_get_contents( 'includes/timezones.html' ) . '
          </select></div><br /><br />
          <div>' . $lang['When to Post'] . ':<b>
          <label>' . $lang['Date'] . '</label> <input type=text id=date name=date size=15> <label> ' . $lang['Time'] . '</label> <input type=text id=time name=time size=15></b></div>
          <br />
          <span id="Delay" title="--Advisable Delays For Posting--
               3-25 Groups/Pages: 3-10 sec, 25-50 Groups/Pages: 10-25 sec, 
               50+ Groups/Pages: at least 25 sec or more,  The Larger the delay, the less probability of getting blocked by Facebook.">' . $lang['Select'] . ' ' . $lang['Delay'] . ':</span> <select name=delay>';
for ( $z = $adminOptions[ 'minimumDelay' ]; $z <= 1800; $z += 5 ) {
    $z = $z - ( $z % 5 );
    if ( $z == 0 )
        $z = 1;
    if ( $z == $adminOptions[ 'defaultDelay' ] )
        $message .= "<option value=$z selected>$z " . $lang['sec'] . "</option>";
    else
        $message .= "<option value=$z>$z " . $lang['sec'] . "</option>";
}
$message .= '</select>
          <div class="submit"><input style="font-weight: bold;" type="button" value="' . $lang['Post'] . '" id=SubmitPosts>&nbsp;&nbsp;&nbsp;&nbsp;<input id="CloseBt" class="bClose" type="button" value="' . $lang['Clear'] . '"><br /><br /><div id="submitting" style="display:block;visibility:hidden" >' . $lang['Please'] . ' ' . $lang['Wait'] . ' - ' . $lang['Posting'] . '<img style="vertical-align: middle;" src="img/sending.gif" /></div><div id="Result">&nbsp;</div></div>
          <div id="LoaderPost" style="display: none";> <img src="img/loading.gif" /> ' . $lang['Posting'] . '...., ' . $lang['take time'] . '...  </div>
          <div class="Right">            
      </div>
      </div>
      </div>
      </div></form>
      <div><h2>' . $lang['Select'] . ' ' . $lang['Pages'] . '/' . $lang['Groups'] . ':</h2>
      <p><center><small>
      <a href=# onclick="$(\'.chk\').prop(\'checked\', true);return false;">' . $lang['Select'] . ' ' . $lang['All'] . '</a>&nbsp;
      <a href=# onclick="$(\'.chkpage\').prop(\'checked\', true);return false;">' . $lang['Select'] . ' ' . $lang['All'] . ' ' . $lang['Pages'] . '</a>&nbsp;
      <a href=# onclick="$(\'.chkgroup\').prop(\'checked\', true);return false;">' . $lang['Select'] . ' ' . $lang['All'] . ' ' . $lang['Groups'] . '</a>&nbsp;
      <a href=# onclick="$(\'.chk\').prop(\'checked\', false);return false;">' . $lang['De Select'] . ' ' . $lang['All'] . '</a></small></center></p>';
if ( $adminOptions[ 'enableDemo' ] )
    $message .= '<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
                            <center><p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
                            <strong>Online Demo Restriction:</strong> Only at most 2 Pages and 5 Groups will be shown.
                            <br /><center>Buy this script for full functionality.</p></center>
                        </div>';
$message .= "<br><div onclick='chkToggle(event,\"#chk$userId\")' class='page odd'><input onclick='chkToggle(event,null)' id=chk$userId class='chk chkpage' type=checkbox value=$userId>" . $lang['Your'] . " " . $lang['Profile'] . "<a class='visit' href='http://www.facebook.com/$userId' target='_blank'>" . $lang['Visit'] . "</a><div id=$userId class=results onclick='chkToggle(event,null)'></div></div>\n";
$message = doPlug( 'mainform', $message );
$i = 0;
$k = 0;
$message .= "<br><img style='vertical-align: middle;' src='img/facebookpage.png' width='16px' title='Facebook Pages' alt='Facebook Pages' />&nbsp;<strong>" . $lang['Pages'] . "</strong> (%%p%%):<a><img src='img/deleteall.png' width='16px' onclick='delAccounts(event,\"page\",\"pages\");' class=alignright></a><br><br><div style='overflow-y: auto; max-height:300px;'>";
foreach ( $pages as $page ) {
    if ( $page != "" ) {
        $p = explode( ":", $page );
        if ( ( $p[ 1 ] == 'L' ) && !isset( $liked_pages_started ) ) {
            $liked_pages_started = true;
            $k = $i;
            $message .= "</div><br><img style='vertical-align: middle;' src='img/facebookpage.png' width='16px' title='Facebook Pages' alt='Facebook Pages' />&nbsp;<strong>Liked " . $lang['Pages'] . "</strong> (%%l%%):<br><br><div style='overflow-y: auto; max-height:300px;'>";
        }
        ++$i;
        $message .= "<div onclick='chkToggle(event,\"#chk$p[0]\")' class='page " . ( ( $i % 2 ) == 0 ? 'even' : 'odd' ) . "'><input onclick='chkToggle(event,null)' id=chk$p[0] class='chk chkpage' type=checkbox value=$p[0]>" . htmlentities( urldecode( $p[ 2 ] ), ENT_COMPAT, 'UTF-8' ) . "<a class='visit' href='http://www.facebook.com/" . ( $p[ 1 ] == 'L' ? substr( $p[0],0,-1 ): $p[0] ) . "' target='_blank'><img src='img/visit.png' title='" . $lang['Visit'] . "' width='12px'>&nbsp;<img src='img/delete.png' width='12px' onclick='delAccounts(event,\"page\",\"$p[0]\");'></a><div id=$p[0] class=results onclick='chkToggle(event,null)'></div></div>\n";
        if ( $adminOptions[ 'enableDemo' ] && $i == 2 )
            break;
    }
}
$j = 0;
$message .= "</div><br><br><img style='vertical-align: middle;' src='img/facebookgroup.png' width='16px' title='Facebook Groups' alt='Facebook Groups' />&nbsp;<strong>" . $lang['Groups'] . "</strong> (%%g%%): <small>(<a class=groupUp onclick='uploadGroups();'>" . $lang['Add Groups'] . "</a>)<a><img src='img/deleteall.png' width='16px' onclick='delAccounts(event,\"group\",\"groups\");' class=alignright></a><br><br><div style='overflow-y: auto; max-height:300px;'>";
foreach ( $groups as $group ) {
    if ( $group != "" ) {
        ++$j;
        $g = explode( ":", $group );
        @$message .= "<div onclick='chkToggle(event,\"#chk$g[0]\")' class='group " . ( ( $j % 2 ) == 0 ? 'even' : 'odd' ) . "'><input onclick='chkToggle(event,null)' id=chk$g[0] class='chk chkgroup' type=checkbox value=$g[0]>" . htmlentities( urldecode( $g[ 1 ] ), ENT_COMPAT, 'UTF-8' ) . "<a class='visit' href='http://www.facebook.com/$g[0]' target='_blank'><img src='img/visit.png' title='" . $lang['Visit'] . "' width='12px'>&nbsp;<img src='img/delete.png' width='12px' onclick='delAccounts(event,\"group\",\"$g[0]\");'></a><div id=$g[0] class=results onclick='chkToggle(event,null)'></div></div>\n";
        if ( $adminOptions[ 'enableDemo' ] && $j == 5 )
            break;
    }
}
$message = str_replace( array(
    "%%g%%",
    "%%p%%",
    "%%l%%"
), array(
     $j,
    $k,
    $i-$k
), $message );
$message .= '</div></div>    
    <form name=upGroup id=upGroup method=POST enctype="multipart/form-data">
        <input type=hidden name="MAX_FILE_SIZE" value="6291456" />
        <input id=legroupsfile name=upGroupsFile type=file accept="text/html" style="display:none" />
        <input type=hidden name=upGroups value=1 />
        </form>
    <form name=delAccount class="confirm" id=delAccount method=post>
        <input type=hidden name=accType value="">
        <input type=hidden name=pageid value="">
        <input type=hidden name=delAcc value="1">
     </form>
    <script>';
$script = '
    function readCookie(cookieName) {
        var theCookie=" "+document.cookie;
        var ind=theCookie.indexOf(" "+cookieName+"=");
        if (ind==-1) ind=theCookie.indexOf(";"+cookieName+"=");
        if (ind==-1 || cookieName=="") return "";
        var ind1=theCookie.indexOf(";",ind+1);
        if (ind1==-1) ind1=theCookie.length; 
        return unescape(theCookie.substring(ind+cookieName.length+2,ind1));
    }
    function delAccounts(e, t, pid) {
        e.stopPropagation();
        e.preventDefault();
        document.forms["delAccount"].accType.value=t;
        document.forms["delAccount"].pageid.value=pid;        
        if ((pid=="pages") || (pid=="groups")) {
            $(".confirm").easyconfirm({
                eventType: "submit",
                locale: { title: "Removing Account", text: "' . $lang['Are you sure'] . " " . $lang['Remove'] . " " . $lang['All'] . '", button: ["' . $lang['Cancel'] . '","' . $lang['Remove'] . '"]}
            });
        } else {
            $(".confirm").easyconfirm({
                eventType: "submit",
                locale: { title: "Removing Account", text: "' . $lang['Are you sure'] . " " . $lang['Remove'] . '", button: ["' . $lang['Cancel'] . '","' . $lang['Remove'] . '"]}
            });
        }
        $("#delAccount").trigger("submit");
    }
    function chkToggle(e,f) {
        e.stopPropagation();
        if (f !== null) $(f).click();
    }
    function setTimeZone() {
        tz = parseFloat(readCookie("FBMPGPTimezone"));
        document.getElementById("timezone").selectedIndex = tz;
    }
    function uploadGroups() {
        $("input[id=legroupsfile]").click();        
    }
    $("#savesubmit").click(function (event) {
        document.forms["FBform"].pageid.value=0;
        document.forms["FBform"].savename.value=document.forms["savepost"].savepostname.value;
        $("input:checkbox:checked").each(function() {
                if (this.name == "proxy") return;
                t = this.value;                
                document.forms["FBform"].targetselections.value += t+"|";
            }
        );
        var options = {
            target:        "#saveresult",   // target element(s) to be updated with server response
            timeout:   5000 ,
            beforeSubmit:  function(formData, jqForm, options) {
                document.getElementById("saveresult").innerHTML=" <img src=\"img/loading.gif\" class=bottom />";                    
            },
            success: null,               
            //clearForm: true        // clear all form fields after successful submit
            //resetForm: true        // reset the form after successful submit
        };
        $(\'#FBform\').ajaxSubmit(options);            
        event.preventDefault();
    });
    $("#CloseBt").click(function (event) {
        $(\'#FBform\').resetForm();
        $(".results").html("");
        $("#submitting").css("display","none");
        showFB(1);
        setTimeZone();
    });
    $("#SubmitPosts").click(function (event) {
        //event.preventDefault;
        i = 0;
        j = 0;
        $("input:checkbox:checked").each(function() {
                if (this.name != "proxy") ++j;
            }
        );
        if (j>0) {
            $("#FBform").block({ 
                message: "<img src=\"img/loading.gif\">", 
                //timeout: 30000,
                css: { border: "0px", backgroundColor: "rgba(255, 255, 255, 0)" },
                overlayCSS:  { backgroundColor: "#fff", opacity: 0.7 } 
            }); 
            tz = document.forms["FBform"].timezone.selectedIndex;
            tv = document.forms["FBform"].timezone.value;
            document.cookie="FBMPGPTimezone="+tz+"; expires=Tue, 31 Dec 2052 12:00:00 UTC";
            document.cookie="FBMPGPTimezoneValue="+tv+"; expires=Tue, 31 Dec 2052 12:00:00 UTC";
            document.getElementById("SubmitPosts").disabled = true;
            document.getElementById("CloseBt").disabled = true;
            $("#submitting").css("visibility","visible");
        } else {
            return;
        }
        pDelay = parseInt(document.forms["FBform"].delay.value)*1000;
        document.forms["FBform"].sdelay.value=pDelay;
        document.forms["FBform"].pageid.value="";
        $("input:checkbox:checked").each(function() {
            t = this.value;
            //alert(this.name);
            if (this.name == "proxy") return;';            
if ( $userOptions[ 'delayHandling' ] == 0 )
    $script .= '
            setTimeout((function(x,k){
                return function() {
                    document.forms["FBform"].pageid.value=x;
                    //alert(document.forms["FBform"].pageid.value);
                    if (j-k == 1) fn = showResp; else fn = null;
                    var options = {
                        target:        "#"+x,   // target element(s) to be updated with server response
                        //async: false,
                        //timeout:   5000 ,
                        beforeSubmit:  function(formData, jqForm, options) {
                            var queryString = $.param(formData);
                            //alert(formData[0].value);
                            document.getElementById(formData[0].value).innerHTML=" <img src=\"img/loading.gif\" class=bottom /> ' . $lang['Posting'] . '...., ' . $lang['take time'] . '... ";
                        }, // pre-submit callback
                        success: fn
                        // other available options:
                        //url:       url         // override for form\'s \'action\' attribute
                        //type:      type        // \'get\' or \'post\', override for form\'s \'method\' attribute
                        //dataType:  null        // \'xml\', \'script\', or \'json\' (expected server response type)
                        //clearForm: true        // clear all form fields after successful submit
                        //resetForm: true        // reset the form after successful submit
                
                        // $.ajax options can be used here too, for example:
                    };
                    $(\'#FBform\').ajaxSubmit(options);
                }
            })(t,i),i*pDelay);';
else
    $script .= '
            document.forms["FBform"].pageid.value=document.forms["FBform"].pageid.value+";"+t;';            
$script .= '++i;
        });';
if ( $userOptions[ 'delayHandling' ] == 1 )
    $script .= '
        if (i>0) {
            nextAJAX();
        }';
$script .= '
    });
    function nextAJAX(responseText, statusText, xhr, $form) {
        var options = {
            target:        "#Result",   // target element(s) to be updated with server response
            //async: false,
            timeout:   30000,            
            success: nextAJAX
        };
        if ( typeof nextAJAX.counter == "undefined" ) {
            nextAJAX.counter = 0;
            nextAJAX.idpoolbuff = document.forms["FBform"].pageid.value.split(";");
            nextAJAX.idpool=[];
            for (j=0;j<nextAJAX.idpoolbuff.length;++j) {
                if (nextAJAX.idpoolbuff[j]!="" ) {
                    nextAJAX.idpool.push(nextAJAX.idpoolbuff[j]);
                }
            }
            document.forms["FBform"].totalposts.value=nextAJAX.idpool.length;            
        }
        document.forms["FBform"].successposts.value=nextAJAX.counter;
        if (nextAJAX.idpool.length==0) {
            $.notify("' . $lang["Successfully"] . ' ' . $lang["posted"] . ' ' . $lang["All"] . ' "+nextAJAX.counter+" ' . $lang["posts"] . ' ' . $lang["using CRON"] . '", {globalPosition: "bottom right", className: "success", autoHideDelay: 10000});
            delete nextAJAX.counter;
            delete nextAJAX.idpool;
            delete nextAJAX.idpoolbuff;
            showResp();
            return;
        }
        if (nextAJAX.idpool.length>0) {
            curpool = nextAJAX.idpool.splice(-' . MAX_BATCH_IDS . ',' . MAX_BATCH_IDS . ');
        }
        document.forms["FBform"].pageid.value="";
        for (j=0;j<curpool.length;++j) {
            document.forms["FBform"].pageid.value=document.forms["FBform"].pageid.value+";"+curpool[j];
            ++nextAJAX.counter;
        }
        $(\'#FBform\').ajaxSubmit(options);
    }
    $(document).ready(function() {
        $("#Delay").tooltip();         
        $(\'#date\').click(function() { $(\'#date\').pickadate({
                                                        today: \'\',
                                                        max: 150,
                                                        min: true,
                                                        format: \'d mmmm yyyy\'
                                                    }); } );
        $(\'#time\').click(function() { $(\'#time\').pickatime({
                                                        editable: true
                                                    }); } );
        $(\'#savepreset\').click(function(e) {
            $(\'#savepost\').lightbox_me({
                centered: true, 
                onLoad: function() { 
                    $(\'#savepost\').find(\'input:first\').focus()
                    }
                });
            e.preventDefault();
        });
        $(\'#deletepreset\').click(function(e) {
            document.forms["presetForm"].delete.value = "1";
            document.forms["presetForm"].submit();
        });                                        
        setTimeZone();
        $("#legroupsfile").change(function() {
            $("#upGroup").trigger("submit");
        });
        $("#legroupsfile").easyconfirm({
            eventType: "click",
            locale: { title: "' . $lang["Adding Groups"] . ' (' . $lang["Maximum Size"] . ' ' . ini_get("upload_max_filesize") . ')", text: "<ol><li> ' . $lang["Visit"] . ' <a target=\"_new\" href=\'https://www.facebook.com/bookmarks/groups/\'> ' . $lang['Facebook'] . ' ' . $lang['Groups List'] . '</a><li>' . $lang['Save Groups List'] . '<li>' . $lang['Choose Groups List'] . '</ol>", button: ["' . $lang["Cancel"] . '","' . $lang['Proceed'] . '"]}
        });';
if ( $adminOptions[ 'imgurCID' ] )
    $script .= '
        $("#lefile").change(function() {
        	letarget=document.getElementById("letarget").value;
            $("#imgUpResult"+letarget).html("");
            $("#FBform").block({ 
                message: "<img src=\"img/loading.gif\">", 
                timeout: 30000,
                css: { border: "0px", backgroundColor: "rgba(255, 255, 255, 0)" },
                overlayCSS:  { backgroundColor: "#fff", opacity: 0.7 } 
            }); 
            var reader = new FileReader();
            reader.onload = function(e) {
                var iurl = e.target.result.substr(e.target.result.indexOf(",") + 1, e.target.result.length);
                var clientId = "' . $adminOptions[ 'imgurCID' ] . '";               
                $.ajax({
                    url: "https://api.imgur.com/3/upload",
                    type: "POST",
                    datatype: "json",
                    data: {
                        "image": iurl,
                        "type": "base64"
                    },
                    success: fdone,
                    error: function(){
                            $("#FBform").unblock();
                            $("#imgUpResult"+letarget).html("<img width=\"11px\" src=\"img/error.png\">");
                           },
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader("Authorization", "Client-ID " + clientId);
                    }
                });
            };
            reader.readAsDataURL(this.files[0]);
        });
        function fdone(data) {
            $("#FBform").unblock();
            if (data.success) {
                $("#Picture"+letarget).val(data.data.link);
                $("#imgUpResult"+letarget).html("<img width=\"11px\" src=\"img/check.png\">");
            }
        }
        ';
if (isset($_GET['preset'])) {
    $presetname = $_GET['preset'];
    if ($db = new PDO('sqlite:' . $dbName . '-presets.db')) {
        $statement = $db->prepare("SELECT * FROM Presets WHERE username = \"$userName\" AND presetname = \"$presetname\"");
        if ($statement) {
            $statement->execute();
            $tempData = $statement->fetchAll();
            $postParams = explode('|',$tempData[0]['params']);
            $targetselections = explode('|',$tempData[0]['groups']);
            $params = array();
            foreach ($postParams as $s) {
                list($k,$v) = explode(':',$s);
                $params[$k] = str_replace(array("\n","'"),array("\\n","\'"),urldecode($v));
            }
            foreach ($targetselections as $s) {
                if ($s != '')
                    $script .= "$('#chk$s').click();";
            }
            switch ($tempData[0]['posttype']) {
                case 'T':
                    $script .= "$('#TypeT').click();
                                document.forms['FBform'].Message.value = '".$params['message']."';";
                    break;
                case 'L':
                    $script .= "$('#TypeL').click();
                                document.forms['FBform'].Message.value = '".$params['message']."';
                                document.forms['FBform'].URL.value = '".$params['link']."';
                                document.forms['FBform'].Title.value = '".$params['name']."';
                                document.forms['FBform'].Description.value = '".$params['description']."';
                                document.forms['FBform'].Caption.value = '".$params['caption']."';
                                document.forms['FBform'].Picture.value = '".$params['picture']."';
                                ";
                    break;                         
                case 'I':
                    $script .= "$('#TypeI').click();
                                document.forms['FBform'].proxy.checked = '".$params['Proxy']."';
                                document.forms['FBform'].Message.value = '".$params['message']."';
                                document.forms['FBform'].URL.value = '".$params['url']."';";
                    break;
                case 'A':
                    $script .= "$('#TypeA').click();
                                document.forms['FBform'].proxy.checked = '".$params['Proxy']."';
                                document.forms['FBform'].Message.value = '".$params['message']."';
                                document.forms['FBform'].URL.value = '".$params['url']."';";
                    break;
                case 'V':
                    $script .= "$('#TypeV').click();
                                document.forms['FBform'].Message.value = '".$params['description']."';
                                document.forms['FBform'].URL.value = '".$params['url']."';
                                document.forms['FBform'].Title.value = '".$params['title']."';";
                	break;
                case 'S':
                    $script .= "$('#TypeS').click();
                                document.forms['FBform'].Message.value = '".$params['description']."';
                                document.forms['FBform'].Title.value = '".$params['title']."';";  
                    for ($i=1;$i<=7;++$i) {
						if ( isset( $params['url'.$i] ) )
							$script .= "document.forms['FBform'].URL$i.value = '".$params['url'.$i]."';";
					}              
            }                    
        }
    }
}
$script .= '
    });
    function showResp(responseText, statusText, xhr, $form)  {
        $("#FBform").unblock();
        $("#submitting").css("visibility","hidden");
        document.getElementById("SubmitPosts").disabled = false;
        document.getElementById("CloseBt").disabled = false;';
if ( $userOptions[ 'autoClearForm' ] ) {
    $script .= '
        curPType = $("input:radio:checked").attr("id");
        $("#FBform").resetForm();
        $("#"+curPType).trigger("click");';
}
$script .= '
    }';
$script = doPlug( 'mainformScript', $script );
?>