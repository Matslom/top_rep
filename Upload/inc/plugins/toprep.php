<?php
if(!defined('IN_MYBB'))
	die('This file cannot be accessed directly.');

$plugins->add_hook("index_end", "toprep_show");
#$plugins->add_hook("portal_end", "toprep_show"); 

function toprep_info()
{

	return array(
		"name"		=> "Top Reputation and Post",
		"description"		=> "Plugin pokazujący top x użytkowników z największą reputacją lub postami.",
		"website"		=> "http://www.mybboad.pl",
		"author"		=> "Matslom",
		"authorsite"		=> "http://www.mybboad.pl",
		"version"		=> "1.0.0",
		"guid" 			=> "*",
		"compatibility"	=> "16*"
		);
}


function toprep_install()
{
	global $mybb, $db;
	$db->write_query("INSERT INTO `".TABLE_PREFIX."settinggroups` VALUES (NULL, 'toprep', 'Top reputation and posts', 'Ustawienia pluginu Top reputation.', 1, 0)");
   $query = $db->simple_select("settinggroups", "gid", "name = 'toprep'", array("limit" => 1));
	$gid = $db->fetch_field($query, "gid");

	$styl = 'max-height: 30px; 
		 max-width: 30px; 
		 float: left; 
		 margin: 5px;
		 padding: 0 2px 0 0;';

	$toprep_limit = array(
		'name'			=> 'topreplimit',
		'title'			=> 'Ilość wyświetlanych użytkowników',
		'description'	=> 'Liczba użytkowników, którzy mają być wyświetlani',
		'optionscode'	=> 'text', 
		'value'			=> '5', 
		'disporder'		=> '1', 
		'gid'			=> intval($gid)
	);

		$toprep_order = array(
		'name'			=> 'topreporder',
		'title'			=> 'Sortuj według:',
		'description'	=> 'Wybierz według jakieś wartości plugin ma pokazywać ranking?',
		'optionscode'	=> 'select
		posts=Postów
		reputation=Reputacji', 
		'value'			=> 'reputation', 
		'disporder'		=> '3', 
		'gid'			=> intval($gid)
	);
	
		$toprep_install = array(
		'name'			=> 'toprepinstall',
		'title'			=> 'Zainstalowany',
		'description'	=> 'Zainstalowany?',
		'optionscode'	=> 'text', 
		'value'			=> '1', 
		'disporder'		=> '4', 
		'gid'			=> ''
	);

	$toprep_groups = array(
		'name'			=> 'toprepgroups',
		'title'			=> 'Wybrane grupy do wyświetlania',
		'description'	=> 'Wpisz numery grup z których mają być wybrani użytkownicy do rankingu. Liczby oddzialaj przecinkami. Przykład: 5,8,10 - plugin pobierze tylko użytkowników z grup o numerach 5 8 oraz 10, pozostałe zostaną ominięte <br/>Pozostaw puste pole, aby wybrać wszystkie grupy.',
		'optionscode'	=> 'text', 
		'value'			=> null, 
		'disporder'		=> '5', 
		'gid'			=> intval($gid)
	);

	$toprep_awatar = array(
		'name'			=> 'toprep_awatar',
		'title'			=> 'Wyświetlanie awatarów na liście',
		'description'	=> 'Włączenie lub wyłączenie pokazywania awatarów w pluginie',
		'optionscode'	=> 'yesno', 
		'value'			=> '1', 
		'disporder'		=> '6', 
		'gid'			=> intval($gid)
	);

	$toprep_dafault_a = array(
		'name'			=> 'toprepdef_avatar',
		'title'			=> 'Ścieżka do domyślnego awatara',
		'description'	=> 'Podaj ścieżkę do domyślnego awatara w przypadku gdy użytkownik go nie posiada',
		'optionscode'	=> 'text', 
		'value'			=> './images/default_avatar.gif', 
		'disporder'		=> '7', 
		'gid'			=> intval($gid)
	);

	$toprep_style = array(
		'name'			=> 'toprepstyle',
		'title'			=> 'Styl wyświetlania awatarów',
		'description'	=> 'Za pomocą języka CSS, określ jaki mają mieć wygląd awatary w pluginie',
		'optionscode'	=> 'textarea', 
		'value'			=> $styl, 
		'disporder'		=> '8', 
		'gid'			=> intval($gid)
	);

	$db->insert_query('settings', $toprep_limit);
	$db->insert_query('settings', $toprep_install);
	$db->insert_query('settings', $toprep_order);
	$db->insert_query('settings', $toprep_groups);
	$db->insert_query('settings', $toprep_awatar);
	$db->insert_query('settings', $toprep_dafault_a);
	$db->insert_query('settings', $toprep_style);
	rebuild_settings(); 

$template_table = '<table border="0" cellspacing="{$theme[borderwidth]}" cellpadding="{$theme[tablespace]}" class="tborder" style="clear: both; border-bottom-width: 0;">
<tr>
<td class="thead" colspan="2">
Ranking użytkowników
 </td>
</tr>
{$top_rep_trow}
</table>';
$template_tr = '
<tr><td class="trow2" style="vertical-align:middle;">
{$show_awatar} {$nick} 
<div style="float:right; padding-right: 5px;">
<span style="background:  #D8D8D8; border: 1px solid #E2E2E2;padding: 1px 6px !important; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; color: black;">
<a title="Posty">{$top_rep[\'postnum\']}</a></span>
<span style=" background: #7BA60D; border: 1px solid #8DBE0D; padding: 1px 6px !important; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; color: white;"> <a title="Reputacja">{$top_rep[\'reputation\']}</a> </span>

</div>
</td></tr>';


	$db->write_query("INSERT INTO `".TABLE_PREFIX."templates` VALUES (NULL, 'top_rep', '".$db->escape_string($template_table)."', '-1', '1', '', '".time()."')");
	$db->write_query("INSERT INTO `".TABLE_PREFIX."templates` VALUES (NULL, 'top_rep_trow', '".$db->escape_string($template_tr)."', '-1', '1', '', '".time()."')");
}

function toprep_is_installed()
{
	global $db;
	$query = $db->write_query("SELECT * FROM " . TABLE_PREFIX . "settings WHERE `name` = 'toprepinstall'");

	if($db->num_rows($query) > 0)
		return true;
	else
		return false; 
}

function toprep_uninstall()
{
	global $mybb, $db;

	$db->write_query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN ('topreplimit', 'toprepinstall', 'toprepshow', 'toprepgroups', 'toprepawatar', 'toprepdef_avatar', 'toprepstyle' )");
	$db->write_query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name = 'toprep'");
	rebuild_settings();
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title IN('top_rep', 'top_rep_trow')");

} 


function toprep_activate()
{

}

function toprep_deactivate()
{

}

function toprep_show()
{
	global $db, $mybb, $page, $toprep, $theme, $templates;

if($mybb->settings['topreporder'] == 'reputation'){ $order = 'u.reputation'; }
else { $order = 'u.postnum'; }

if($mybb->settings['toprepgroups'] == NULL) {

$query = $db->query("
	SELECT
	 u.username, u.usergroup, u.displaygroup, u.avatar, u.uid, u.reputation, u.postnum
	FROM ".TABLE_PREFIX."users as u
	ORDER BY ". $order ." DESC LIMIT " . $mybb->settings['topreplimit']);
}
else {

	$query = $db->query("
	SELECT
	 u.username, u.usergroup, u.displaygroup, u.avatar, u.uid, u.reputation, u.postnum, u.usergroup
	FROM ".TABLE_PREFIX."users as u WHERE u.usergroup  IN(". $mybb->settings['toprepgroups'] .")
	ORDER BY ". $order ." DESC LIMIT " . $mybb->settings['topreplimit']);
}


	
while($top_rep = $db->fetch_array($query))
	{

	if($top_rep['avatar'] == null) { 
	$top_rep['avatar'] = $mybb->settings['toprepdef_avatar']; }
	$awatar = "<a href=\"".$mybb->settings['bburl']."/".get_profile_link($top_rep['uid'])."\"><img src=\"".$top_rep['avatar']."\" alt=\"avatar\" style=\"".$mybb->settings['toprepstyle']."\"  class=\"favimg toprep_avatar\" /></a>";
	$usernameFormatted = format_name($top_rep['username'], $top_rep['usergroup'], $top_rep['displaygroup']);
    $top_rep['showNick'] = '<a href="member.php?action=profile&uid='.intval($top_rep['uid']).'"> '.$usernameFormatted.'</a>';
    if($mybb->settings['toprep_awatar'] == '1' ) { 
    	$top_rep['showAwatar'] = $awatar;
    	$top_rep['displayStyle'] = 'line-height: 40px;';
    	 }
	
	eval('$top_rep_trow .= "'.$templates->get("top_rep_trow").'";');
	}

eval('$toprep = "'.$templates->get('top_rep').'";');

}


?>
