<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}


function wiki_info()
{
    return array(
        "name"			=> "Foren-Wiki",
        "description"	=> "Hier kannst du ein Foren-Wiki anlegen.",
        "website"		=> "",
        "author"		=> "Ales",
        "authorsite"	=> "",
        "version"		=> "1.0",
        "guid" 			=> "",
        "codename"		=> "",
        "compatibility" => "*"
    );
}

function wiki_install()
{
    global $db, $mybb;

    //Datenbank
    if($db->engine=='mysql'||$db->engine=='mysqli')
    {
        $db->query("CREATE TABLE `".TABLE_PREFIX."wiki_categories` (
          `cid` int(10) NOT NULL auto_increment,
          `category` varchar(500) CHARACTER SET utf8 NOT NULL,
          PRIMARY KEY (`cid`)
        ) ENGINE=MyISAM".$db->build_create_table_collation());

        $db->query("CREATE TABLE `".TABLE_PREFIX."wiki_entries` (
          `wid` int(10) NOT NULL auto_increment,
          `cid` int(11) NOT NULL,
          `linktitle` varchar(255) CHARACTER SET utf8 NOT NULL,
          `link` varchar(255) CHARACTER SET utf8 NOT NULL,
          `title` varchar(255) CHARACTER SET utf8 NOT NULL,
          `subtitle` varchar(255) CHARACTER SET utf8 NOT NULL,
            `wikitext` longtext CHARACTER SET utf8 NOT NULL,
                 `uid` int(10) NOT NULL,
                 `accepted` int(10) DEFAULT '0' NOT NULL,
          PRIMARY KEY (`wid`)
        ) ENGINE=MyISAM".$db->build_create_table_collation());
    }

    //Einstellung
    $setting_group = array(
        'name' => 'wiki',
        'title' => 'Foren-Wiki',
        'description' => 'Einstellung für das Foren-Wiki machen',
        'disporder' => 5, // The order your setting group will display
        'isdefault' => 0
    );

    $gid = $db->insert_query("settinggroups", $setting_group);

    $setting_array = array(
        'wiki_allow_groups' => array(
            'title' => 'Erlaubte Gruppen',
            'description' => 'Welche Gruppen dürfen Einträge machen?',
            'optionscode' => 'groupselect',
            'value' => '2', // Default
            'disporder' => 1
        ),
    );


    foreach($setting_array as $name => $setting)
    {
        $setting['name'] = $name;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
    }

// Don't forget this!
    rebuild_settings();

    //Templates
    $insert_array = array(
        'title'        => 'forenwiki',
        'template'    => $db->escape_string('	<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->foren_wiki}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->foren_wiki}</strong></td>
</tr>
<tr>
<td class="trow1" align="center" valign="top" width="10%">
{$wiki_menu}
		</td>
		<td class="trow1" align="center" valign="top" width="90%">
			<h1>{$lang->foren_wiki}</h1>
		</td>
		</tr>
</table>
</td>
</tr>
</table>
{$footer}
</body>
</html>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'forenwiki_menu',
        'template'    => $db->escape_string('<table width="100%">
	<tr><td class="tcat"><strong>Menü</strong></td></tr>
	<tr><td class="trow1"><a href="misc.php?action=wiki">Hauptseite</td></tr>
	{$add_entry}
	{$forenwiki_menu_cat}

</table>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'forenwiki_menu_cat',
        'template'    => $db->escape_string('<tr><td class="tcat">{$category}</td></tr>
{$entry}'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'forenwiki_modcp_all',
        'template'    => $db->escape_string('<html>
<head>
	<title>{$mybb->settings[\'bbname\']} - {$lang->forenwiki_all}</title>
{$headerinclude}
</head>
<body>
	{$header}
	<table width="100%" border="0" align="center">
		<tr>
			{$modcp_nav}
			<td valign="top">
					<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
					<tr>
						<td class="thead"><strong>{$lang->forenwiki_all_entry}</strong></td>
					</tr>
						<tr>
							<td class="trow1" valign="top">
								<table width="100%">
									<tr>
										<td class="tcat"><strong>{$lang->forenwiki_entry}</strong></td>
										<td class="tcat"><strong>{$lang->forenwiki_link}</strong></td>
										<td class="tcat"><strong>{$lang->forenwiki_options}</strong></td>
									</tr>
									{$modcp_all_bit}
								</table>
							</td>
						</tr>
				</table>
			</td>
		</tr>
	</table>
{$footer}
<script> function askDelete(){
confirm(\'Wirklich löschen?\')
} </script>
</body>
</html>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'forenwiki_modcp_all_bit',
        'template'    => $db->escape_string('<tr><td class="trow1" align="center"><strong>{$title}</strong></td><td class="trow2" align="center"><a href="misc.php?wikientry={$link}" target="_blank">{$linktitle}</a></td><td class="trow1" align="center"><a href="modcp.php?action=forenwiki_edit&edit={$wid}">Editieren</a> | <a href="modcp.php?action=forenwiki_all&delete={$wid}" onClick="askDelete()">Löschen</a></td></tr>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'forenwiki_modcp_control',
        'template'    => $db->escape_string('<html>
<head>
	<title>{$mybb->settings[\'bbname\']} - {$lang->forenwiki_control}</title>
{$headerinclude}
</head>
<body>
	{$header}
	<table width="100%" border="0" align="center">
		<tr>
			{$modcp_nav}
			<td valign="top">
					<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
					<tr>
						<td class="thead"><strong>{$lang->forenwiki_control_entry}</strong></td>
					</tr>
						<tr>
							<td class="trow1">
								
									{$modcp_control_bit}
								
							</td>
						</tr>
				</table>
			</td>
		</tr>
	</table>
{$footer}
</body>
</html>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'forenwiki_modcp_control_bit',
        'template'    => $db->escape_string('<table width="100%"><tr><td class="tcat" align="center"><strong>{$title}</strong>
	<div class="smalltext">{$subtitle}</div>	</td></tr>
<tr><td class="trow1 smalltext"  align="center"><b>Eingereicht von</b> {$user}</td></tr>
<tr><td class="trow1 smalltext"  align="center"><b>Wikitext</b></td></tr>
<tr><td class="trow1 smalltext"><div style="height: 150px; overflow: auto;">{$wikitext}</div></td></tr>
<tr><td class="trow2 smalltext" align="center"><a href="modcp.php?action=forenwiki_control&delete={$wid}">{$lang->wiki_delete}</a> <a href="modcp.php?action=forenwiki_control&accept={$wid}">{$lang->wiki_accept}</a></td></tr>
</table>
<br /><br />'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'forenwiki_modcp_edit',
        'template'    => $db->escape_string('<html>
<head>
	<title>{$mybb->settings[\'bbname\']} - {$lang->forenwiki_edit}</title>
{$headerinclude}
</head>
<body>
	{$header}
	<table width="100%" border="0" align="center">
		<tr>
			{$modcp_nav}
			<td valign="top">
					<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
					<tr>
						<td class="thead"><strong>{$lang->forenwiki_edit}</strong></td>
					</tr>
						<tr>
							<td class="trow1" valign="top">
			
			<form id="edit_wiki" method="post" action="modcp.php?action=forenwiki_edit&edit={$wid}">	
			<input type="hidden" name="wid" id="wid" value="{$wid}" class="textbox" />
		<table width="100%">
			<tr><td class="trow1"><strong>Kategorie</strong></td>
				<td class="trow2"><select name="category" required>
					<option value="%">Kategorie wählen</option>
					{$edit_categories}
					</select> 
				</td></tr>
			<tr><td class="trow1"><strong>Linkname</strong>
			<div class="smalltext">Wie soll der Link im Menü stehen?</div></td><td class="trow2"><input type="text" name="linktitle" id="linktitle" value="{$linktitle}" class="textbox" required /> </td></tr>
			<tr><td class="trow1"><strong>Link</strong>
			<div class="smalltext">Wie soll der Link lauten? misc.php?wikientry=linkname</div></td><input type="text" name="link" id="link" value="{$link}" class="textbox" required /></td></tr>
			<tr><td class="trow1"><strong>Überschrift</strong>
			<div class="smalltext">Wie soll die Überschrift lauten?</div></td><td class="trow2"><input type="text" name="title" id="title" value="{$title}" class="textbox" required /></td></tr>
			<tr><td class="trow1"><strong>Untertitel</strong>
			<div class="smalltext">wenn ein Untertitel vorhanden ist, wie soll diser lauten</div></td><td class="trow2"><input type="text" name="subtitle" id="subtitle" value="{$subtitle}" class="textbox" /></td></tr>
			<tr><td class="trow1" colspan="2"><strong>Wikieintrag</strong></td></tr>
			<tr><td class="trow2" colspan="2"><textarea class="textarea" name="wikitext" id="wikitext" rows="6" cols="30" style="width: 95%">{$wikitext}</textarea></td></tr>
			<tr><td class="tcat" colspan="2" align="center"><input type="submit" name="edit_wiki_entry" value="Eintrag editieren" id="submit" class="button"></td></tr>
		</table>
</form>
							</td>
						</tr>
				</table>
			</td>
		</tr>
	</table>
{$footer}
</body>
</html>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'forenwiki_modcp_nav_wiki',
        'template'    => $db->escape_string('	<tr>
		<td class="tcat tcat_menu tcat_collapse{$collapsedimg[\'modcpusers\']}">
			<div class="expcolimage"><img src="{$theme[\'imgdir\']}/collapse{$collapsedimg[\'modcpusers\']}.png" id="modcpusers_img" class="expander" alt="{$expaltext}" title="{$expaltext}" /></div>
			<div><span class="smalltext"><strong>{$lang->mcp_nav_wiki}</strong></span></div>
		</td>
	</tr>
	<tbody style="{$collapsed[\'modcpwiki_e\']}" id="modcpwiki_e">
		<tr><td class="trow1 smalltext">
			<a href="modcp.php?action=forenwiki_control"  class="modcp_nav_item modcp_nav_modqueue">{$lang->forenwiki_control}</a></td></tr>
					<tr><td class="trow1 smalltext">		<a href="modcp.php?action=forenwiki_all"  class="modcp_nav_item modcp_nav_modqueue">{$lang->forenwiki_all}</a></td></tr>
	</tbody>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'forenwiki_newentry',
        'template'    => $db->escape_string('	<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->foren_wiki}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->add_wiki}</strong></td>
</tr>
<tr>
<td class="trow1" align="center" valign="top" width="10%">
{$wiki_menu}
		</td>
		<td class="trow1" align="center" valign="top">
		{$new_cat}
			
			<form id="add_wiki" method="post" action="misc.php?action=add_wiki">
		<table width="90%"><tr><td class="thead" colspan="2"><strong>{$lang->formular_entry}</strong></td></tr>
			<tr><td class="trow1"><strong>Kategorie</strong></td>
				<td class="trow2"><select name="category" required>
					<option value="%">Kategorie wählen</option>
					{$categories}
					</select> 
				</td></tr>
			<tr><td class="trow1"><strong>Linktitel</strong>
			<div class="smalltext">Wie soll der Linkname im Menü lauten?</div></td><td class="trow2"><input type="text" name="linktitle" id="linktitle" placeholder="Linktitel" class="textbox" required /> </td></tr>
				<tr><td class="trow1"><strong>Link</strong>
				<div class="smalltext">Wie soll der Link lauten? misc.php?wikientry=linkname</div></td><td class="trow2"><input type="text" name="link" id="link" placeholder="todesser, orden etc." class="textbox" required /></td></tr>
			<tr><td class="trow1"><strong>Überschrift</strong></td><td class="trow2"><input type="text" name="title" id="title" placeholder="Überschrift des Artikels" class="textbox" required /></td></tr>
			<tr><td class="trow1"><strong>Untertitel</strong></td><td class="trow2"><input type="text" name="subtitle" id="subtitle" placeholder="Linktitel" class="textbox" /></td></tr>
			<tr><td class="trow1" colspan="2"><strong>Wikieintrag</strong></td></tr>
			<tr><td class="trow2" colspan="2"><textarea class="textarea" name="wikitext" id="wikitext" rows="6" cols="30" style="width: 95%"></textarea></td></tr>
			<tr><td class="tcat" colspan="2" align="center"><input type="submit" name="add_wiki_entry" value="Eintrag einreichen" id="submit" class="button"></td></tr>
		</table>
</form>
		</td>
		</tr>
</table>
</td>
</tr>
</table>
{$footer}
</body>
</html>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'forenwiki_wiki',
        'template'    => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->foren_wiki}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$wiki_title}</strong></td>
</tr>
<tr>
<td class="trow1" align="center" valign="top" width="10%">
{$wiki_menu}
		</td>
	<td class="trow2" valign="top">{$forenwiki_wiki_bit}
	</td>
</tr>
</table>
</td>
</tr>
</table>
{$footer}
</body>
</html>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'forenwiki_wiki_bit',
        'template'    => $db->escape_string('<div class="info_headline">{$title}</div>
<div class="info_subline">{$subtitle}</div>
<div class="smalltext">{$wikitext}</div>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);


}

function wiki_is_installed()
{
    global $db;
    if($db->table_exists("wiki_entries"))
    {
        return true;
    }
    return false;
}

function wiki_uninstall()
{
    global $db;
    if($db->table_exists("wiki_categories"))
    {
        $db->drop_table("wiki_categories");
    }

    if($db->table_exists("wiki_entries"))
    {
        $db->drop_table("wiki_entries");
    }

    $db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='wiki'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='wiki_allow_groups'");

    $db->delete_query("templates", "title LIKE '%forenwiki%'");
    rebuild_settings();
}

function wiki_activate()
{
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", "#".preg_quote('{$menu_calendar}')."#i", '{$menu_calendar} {$forenwiki_header} ');
    find_replace_templatesets("header", "#".preg_quote('<navigation>')."#i", '{$new_wiki_alert} <navigation>');
    find_replace_templatesets("modcp_nav", "#".preg_quote('{$modcp_nav_users}')."#i", '{$modcp_nav_users}{$modcp_nav_wiki}');
}

function wiki_deactivate()
{
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", "#".preg_quote('{$forenwiki_header}')."#i", '', 0);
    find_replace_templatesets("header", "#".preg_quote('{$new_wiki_alert}')."#i", '', 0);
    find_replace_templatesets("modcp_nav", "#".preg_quote('{$modcp_nav_wiki}')."#i", '', 0);
}

//Alerts und Link für die Hauptseite
$plugins->add_hook('global_start', 'wiki_global');

function wiki_global(){
    global $db, $templates, $mybb, $new_wiki_alert, $lang, $forenwiki_header;
    $lang->load('wiki');
    $alert_query = $db->query("SELECT *
    FROM ".TABLE_PREFIX."wiki_entries
    WHERE accepted = 0
    ");

    $count = mysqli_num_rows ($alert_query);

    if($count > 0){
        if($mybb->usergroup['canmodcp'] == 1){
            $new_wiki_alert = "<div class=\"red_alert\"><a href='modcp.php?action=forenwiki_control'>{$lang->forenwiki_alert}</a>
</div>";
        }
    }

    $forenwiki_header = "<li><a href=\"{$mybb->settings['bburl']}/misc.php?action=wiki\" class=\"help\">{$lang->toplinks_forenwiki}</a></li>";

}

$plugins->add_hook('misc_start', 'wiki_misc');

// In the body of your plugin
function wiki_misc()
{
    global $mybb, $templates, $lang, $header, $headerinclude, $footer, $page, $db, $wiki_menu, $categories, $entry, $wiki_title, $options, $add_entry, $new_cat;
    $lang->load('wiki');
    require_once MYBB_ROOT."inc/class_parser.php";;
    $parser = new postParser;
    // Do something, for example I'll create a page using the hello_world_template
    $options = array(
        "allow_html" => 1,
        "allow_mycode" => 1,
        "allow_smilies" => 1,
        "allow_imgcode" => 1,
        "filter_badwords" => 0,
        "nl2br" => 1,
        "allow_videocode" => 0
    );

    //Nur den Gruppen, die es erlaubt ist, neue Einträge zu machen, ist es erlaubt, den Link zu sehen.
    if (is_member($mybb->settings['wiki_allow_groups'])) {
        $add_entry = "<tr><td class=\"trow1\" align=\"center\"><a href=\"misc.php?action=add_wiki\">Eintrag hinzufügen</a></td></tr>";
    }

    //Generieren wir uns mal das Menü, welches sich Automatisch erweitert, wenn neue Einträge in der Datenbank erscheinen.
    $query = $db->query("SELECT *
    FROM ".TABLE_PREFIX."wiki_categories
    ORDER BY category ASC
    ");

    while($cat = $db->fetch_array($query)){
        $category = "";

        $category = $cat['category'];
        $cid = $cat['cid'];
        $entry = "";

        $entry_query = $db->query("SELECT *
      FROM ".TABLE_PREFIX."wiki_entries
      WHERE cid = '".$cid."'
      AND accepted = 1
      ORDER BY linktitle ASC
      ");

        while($row = $db->fetch_array($entry_query)){
            $altbg = alt_trow();
            $link = $row['link'];
            $linktitle = $row['linktitle'];

            $entry .= "<tr><td class='$altbg' align='center'><a href='misc.php?wikientry={$link}'>{$linktitle}</a> </td></tr>";
        }  eval("\$forenwiki_menu_cat .= \"".$templates->get("forenwiki_menu_cat")."\";");
    }

    eval("\$wiki_menu = \"".$templates->get("forenwiki_menu")."\";");


    //Unsere Hauptseite :D
    if($mybb->get_input('action') == 'wiki')
    {
        $lang->load('wiki');
        // Do something, for example I'll create a page using the hello_world_template

        // Add a breadcrumb
        add_breadcrumb('Foren-Wiki', "misc.php?action=wiki");


        eval("\$page = \"".$templates->get("forenwiki")."\";");
        output_page($page);
    }

    /*
     * Hier werden die neuen Einträge ins System eingefügt.
     */



    if($mybb->get_input('action') == 'add_wiki')
    {
        if ($mybb->user['uid'] == 0) {
            error_no_permission();
        }elseif (!is_member($mybb->settings['wiki_allow_groups'])) {
            error_no_permission();
        }        else{
            add_breadcrumb('Wiki-Eintrag hinzufügen', "misc.php?action=add_wiki");

            if($mybb->usergroup['canmodcp'] == 1){
                $new_cat = "<form id=\"add_category\" method=\"post\" action=\"misc.php?action=add_wiki\">
		<table width=\"90%\"><tr><td class=\"thead\" colspan=\"2\"><strong>{$lang->formular_category}</strong></td></tr>
			<tr><td class=\"trow1\"><strong>Kategorie</strong></td>
				<td class=\"trow2\"><input type=\"text\" name=\"category\" id=\"category\" placeholder=\"Kategorie\" class=\"textbox\" required /> 
				</td></tr>
			<tr><td class=\"tcat\" colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"add_wiki_category\" value=\"Eintrag einreichen\" id=\"submit\" class=\"button\"></td></tr>
		</table>
</form><br /><br />";
            }

            if($_POST['add_wiki_category']){
                $new_cat = array(
                    "category" => $db->escape_string($_POST['category'])
                );

                $db->insert_query("wiki_categories", $new_cat);
                redirect("misc.php?action=add_wiki");
            }

            $cat_query = $db->query("SELECT *
            FROM ".TABLE_PREFIX."wiki_categories
            ORDER BY category ASC
            ");

            while($row = $db->fetch_array($cat_query)){
                $categories .= "<option value='{$row['cid']}'>{$row['category']}</option>";
            }
            if($_POST['add_wiki_entry']){

                //Wenn das Team Einträge erstellt, dann wink doch einfach durch. Sonst bitte nochmal zum Prüfung :D
                if($mybb->usergroup['canmodcp'] == '1'){
                    $accepted = 1;
                } else {
                    $accepted = 0;
                }
                $new_entry = array(
                    "cid" => (int)$_POST['category'],
                    "linktitle" => $db->escape_string($_POST['linktitle']),
                    "link" => $db->escape_string($_POST['link']),
                    "title" => $db->escape_string($_POST['title']),
                    "subtitle" => $db->escape_string($_POST['subtitle']),
                    "wikitext" => $db->escape_string($_POST['wikitext']),
                    "uid" => (int)$mybb->user['uid'],
                    "accepted" => (int)$accepted
                );

                $db->insert_query("wiki_entries", $new_entry);
                redirect("misc.php?action=add_wiki");
            }

        }

        eval("\$page = \"".$templates->get("forenwiki_newentry")."\";");
        output_page($page);
    }

    /*
     * Hier passiert die Magie.
     * Für jeden Wikieintrag wird eine neue Seite "erstellt".
     * So reicht es, dass es einen Code gibt, es aber immer mit der Infos der aktuellen gewünschten Seite gefüllt wird.
     */
    $wikientry = $mybb->input['wikientry'];

    if($wikientry){
        $name_query = $db->simple_select("wiki_entries", "*", "link = '".$wikientry."' AND accepted = 1");
        $name = $db->fetch_array($name_query);
        $wid = $name['wid'];
        $wiki_title = $name['title'];

        add_breadcrumb('Foren-Wiki', "misc.php?wikientry={$wikientry}");

        $query = $db->query("SELECT *
        FROM ".TABLE_PREFIX."wiki_entries
        WHERE wid = '".$wid."'
        ");

        while($row = $db->fetch_array($query)){
            $title = $row['title'];
            if(!empty($row['subtitle'])){
                $subtitle = $row['subtitle'];
            }

            $wikitext = $parser->parse_message($row['wikitext'], $options);
            eval("\$forenwiki_wiki_bit = \"".$templates->get("forenwiki_wiki_bit")."\";");

        }

        eval("\$page = \"".$templates->get("forenwiki_wiki")."\";");
        output_page($page);
    }

}

/*
 * Der Spaß darf natürlich auch in der Navigation nicht fehlen :D
 * Auch das ModCP hat eine Navigation und da hängen wir unseren Block einfach mit dran.
 */

$plugins->add_hook("modcp_nav", "wiki_modcp_nav");


function wiki_modcp_nav(){
    global $modcp_nav_wiki, $templates, $lang;
    $lang->load('wiki');


    eval("\$modcp_nav_wiki = \"".$templates->get("forenwiki_modcp_nav_wiki")."\";");
}

/*
 * Und hier kommt unser ModCP.
 * Hier kannst du du das Wiki verwalten, bearbeiten und löschen. Wie cool.
 */
$plugins->add_hook("modcp_start", "wiki_modcp");
function wiki_modcp() {

    global $mybb, $templates, $lang, $header, $headerinclude, $footer, $application, $db, $page, $options, $modcp_nav, $edit_categories, $select;
    require_once MYBB_ROOT."inc/datahandlers/pm.php";
    $pmhandler = new PMDataHandler();
    require_once MYBB_ROOT."inc/class_parser.php";;
    $parser = new postParser;
    $options = array(
        "allow_html" => 1,
        "allow_mycode" => 1,
        "allow_smilies" => 1,
        "allow_imgcode" => 1,
        "filter_badwords" => 0,
        "nl2br" => 1,
        "allow_videocode" => 0
    );

    /*
     * Hier landen alle Einträge, die von Usern eingereicht wurden und zunächst bearbeitet werden müssen. Ist der Eintrag, so wie er eingereicht wurde, in Ordnung? Wenn ja, dann ab dafür. Wenn nicht, wird er gelöscht und abgelehnt.
     */
    if($mybb->get_input('action') == 'forenwiki_control') {
        $lang->load('wiki');
        // Add a breadcrumb
        add_breadcrumb('Alle neuen Wiki-Einträge', "modcp.php?action=forenwiki_control");

        $query = $db->query("SELECT *
        FROM ".TABLE_PREFIX."wiki_entries e
        LEFT JOIN ".TABLE_PREFIX."wiki_categories c
        on (e.cid = c.cid)
        LEFT JOIN ".TABLE_PREFIX."users u
        on (e.uid = u.uid)
        WHERE e.accepted = 0
        ");

        while($row = $db->fetch_array($query)){
            //Erstmal alles leeren
            $title = "";
            $wid = "";
            $subtitle = "";
            $link = "";
            $linktitle ="";
            $wikitext = "";
            $user = "";

            //Füllen wir mal alles mit Informationen
            $username = format_name($row['username'], $row['usergroup'], $row['displaygroup']);
            $user = build_profile_link($username, $row['uid']);
            $title = $row['title'];
            $subtitle = $row['subtitle'];
            $link = $row['link'];
            $linktitle = $row['linktitle'];
            $wid = $row['wid'];
            $wikitext = $parser->parse_message($row['wikitext'], $options);
            eval("\$modcp_control_bit .= \"".$templates->get("forenwiki_modcp_control_bit")."\";");
        }

        $team_uid = $mybb->user['uid'];

        //Der Eintrag wurde vom Team abgelehnt
        if($delete = $mybb->input['delete']){
            $delete_query = $db->query("SELECT uid
            from ".TABLE_PREFIX."wiki_entries
           WHERE wid = '".$delete."'
            ");

            $owner_uid = $db->fetch_array($delete_query);

            $uid = $owner_uid['uid'];


            $pm_change = array(
                "subject" => "Wiki-Eintrag wurde abgelehnt",
                "message" => "{$lang->delete_wiki}",
                //to: wer muss die anfrage bestätigen
                "fromid" => $team_uid,
                //from: wer hat die anfrage gestellt
                "toid" => $uid
            );
            // $pmhandler->admin_override = true;
            $pmhandler->set_data ($pm_change);
            if (!$pmhandler->validate_pm ())
                return false;
            else {
                $pmhandler->insert_pm ();
            }

            $db->delete_query("wiki_entries", "wid = '$delete'");
            redirect("modcp.php?action=forenwiki_control");
        }

        //Der Eintag wurde vom Team angenommen
        if($accept = $mybb->input['accept']){
            $accept_query = $db->query("SELECT uid
            from ".TABLE_PREFIX."wiki_entries
           WHERE wid = '".$accept."'
            ");

            $owner_uid = $db->fetch_array($accept_query);

            $uid = $owner_uid['uid'];


            $pm_change = array(
                "subject" => "Wiki-Eintrag wurde angenommen",
                "message" => "{$lang->accept_wiki}",
                //to: wer muss die anfrage bestätigen
                "fromid" => $team_uid,
                //from: wer hat die anfrage gestellt
                "toid" => $uid
            );
            // $pmhandler->admin_override = true;
            $pmhandler->set_data ($pm_change);
            if (!$pmhandler->validate_pm ())
                return false;
            else {
                $pmhandler->insert_pm ();
            }

            $db->query("UPDATE ".TABLE_PREFIX."wiki_entries SET accepted =1 WHERE wid = '".$accept."'");
            redirect("modcp.php?action=forenwiki_control");
        }


        eval("\$page = \"".$templates->get("forenwiki_modcp_control")."\";");
        output_page($page);

    }


    /*
     * hier werden alle Einträge aufgelistet. Dabei wird aber nur der Titel und der Link zum Eintrag angegeben. Zudem die Möglichkeit, das ganze zu editieren (weiterleitung zu einer neuen Seite) und natürlich das Löschen.
     */
    if($mybb->get_input('action') == 'forenwiki_all') {
        $lang->load('wiki');
        // Add a breadcrumb
        add_breadcrumb('Alle Wiki-Einträge', "modcp.php?action=forenwiki_all");

        $query = $db->query("SELECT *
        FROM ".TABLE_PREFIX."wiki_entries e
        LEFT JOIN ".TABLE_PREFIX."wiki_categories c
        on (e.cid = c.cid)
        LEFT JOIN ".TABLE_PREFIX."users u
        on (e.uid = u.uid)
        WHERE e.accepted =1
        ");

        while($row = $db->fetch_array($query)){
            //Erstmal alles leeren
            $title = "";
            $wid = "";
            $subtitle = "";
            $link = "";
            $linktitle ="";


            //Füllen wir mal alles mit Informationen

            $title = $row['title'];
            $subtitle = $row['subtitle'];
            $link = $row['link'];
            $linktitle = $row['linktitle'];
            $wid = $row['wid'];
            eval("\$modcp_all_bit .= \"".$templates->get("forenwiki_modcp_all_bit")."\";");
        }

        //Der Eintrag wurde vom Team abgelehnt
        if($delete = $mybb->input['delete']){
            $db->delete_query("wiki_entries", "wid = '$delete'");
            redirect("modcp.php?action=forenwiki_all");
        }

        eval("\$page = \"".$templates->get("forenwiki_modcp_all")."\";");
        output_page($page);

    }


    /*
     * Hier können die Einträge im Wiki editiert werden. Sowohl die Links, der Linkname, Überschrift und natürlich den Inhalt.
     * der Autor bleibt aber der gleiche, dieser wird nicht überschrieben und ist auch nur wichtig, wenn die Einträge von Usern kommen. Sonst bleibt es ja beim Team.
     */

    if($mybb->get_input('action') == 'forenwiki_edit') {
        $lang->load('wiki');
        // Add a breadcrumb
        add_breadcrumb('Wiki-Einträge editieren', "modcp.php?action=forenwiki_edit");

        $wid = $mybb->input['edit'];

        $query = $db->query("SELECT *
        FROM ".TABLE_PREFIX."wiki_entries e
        LEFT JOIN ".TABLE_PREFIX."wiki_categories c
        on (e.cid = c.cid)
        LEFT JOIN ".TABLE_PREFIX."users u
        on (e.uid = u.uid)
        WHERE e.wid = '".$wid."'
        ");

        $row = $db->fetch_array($query);

        $wid = "";
        $title = "";
        $wid = "";
        $subtitle = "";
        $link = "";
        $linktitle ="";
        $wikitext = "";
        $user = "";

        //Füllen wir mal alles mit Informationen
        $username = format_name($row['username'], $row['usergroup'], $row['displaygroup']);
        $user = build_profile_link($username, $row['uid']);
        $title = $row['title'];
        $subtitle = $row['subtitle'];
        $link = $row['link'];
        $linktitle = $row['linktitle'];
        $wid = $row['wid'];
        $wikitext = $row['wikitext'];
        $cid = $row['cid'];
        $wid = $row['wid'];


        $cat_query = $db->query("SELECT *
            FROM ".TABLE_PREFIX."wiki_categories
            ORDER BY category ASC
            ");

        while($cat = $db->fetch_array($cat_query)){

            if($cid == $cat['cid']){
                $select = "selected=\"selected\"";
            } else {
                $select = "";
            }


            $edit_categories .= "<option value='{$cat['cid']}' {$select}>{$cat['category']} </option>";
        }



        //Der neue Inhalt wird nun in die Datenbank eingefügt bzw. die alten daten Überschrieben.
        if($_POST['edit_wiki_entry']){
            $wid = $mybb->input['wid'];
            $edit_entry = array(
                "cid" => (int)$mybb->input['category'],
                "linktitle" => $db->escape_string($mybb->input['linktitle']),
                "link" => $db->escape_string($mybb->input['link']),
                "title" => $db->escape_string($mybb->input['title']),
                "subtitle" => $db->escape_string($mybb->input['subtitle']),
                "wikitext" => $db->escape_string($mybb->input['wikitext']),
            );

            $db->update_query("wiki_entries", $edit_entry, "wid = '".$wid."'");
            redirect("modcp.php?action=forenwiki_all");
        }

        eval("\$page = \"".$templates->get("forenwiki_modcp_edit")."\";");
        output_page($page);

    }


}
