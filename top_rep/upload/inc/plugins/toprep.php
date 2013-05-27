<?php
if(!defined('IN_MYBB'))
	die('This file cannot be accessed directly.');

$plugins->add_hook("index_end", "toprep_show");

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
		'disporder'		=> '1', 
		'gid'			=> ''
	);

	$db->insert_query('settings', $toprep_limit);
	$db->insert_query('settings', $toprep_install);
	$db->insert_query('settings', $toprep_order);
	rebuild_settings(); 

$template_table = '<!--Plugin top_rep by Matslom--> 
<table border="0" cellspacing="{$theme[borderwidth]}" cellpadding="{$theme[tablespace]}" class="tborder" style="clear: both; border-bottom-width: 0;">
<tr>
<td class="thead" colspan="2">
<strong>Ranking użytkowników</strong>
 </td>
</tr>
{$top_rep_trow}
</table>';
$template_tr = '
<tr><td class="trow2">
{$nick} 
<div style="float:right; padding-right: 5px;">
<span style="background:  #D8D8D8; border: 1px solid #E2E2E2;padding: 1px 6px !important; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; color: black;">
{$top_rep[\'postnum\']}</span>
<span style=" background: #7BA60D; border: 1px solid #8DBE0D; padding: 1px 6px !important; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; color: white;"> {$top_rep[\'reputation\']} </span>

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

	$db->write_query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN ('topreplimit')");
	$db->write_query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN ('toprepinstall')");
	$db->write_query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN ('topreporder')");
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

$query = $db->query("
	SELECT
	 u.username, u.usergroup, u.displaygroup, u.avatar, u.uid, u.reputation, u.postnum
	FROM ".TABLE_PREFIX."users as u
	ORDER BY ". $order ." DESC LIMIT " . $mybb->settings['topreplimit']);
	
while($top_rep = $db->fetch_array($query))
	{
	$rep = $top_rep['reputation'];
	$usernameFormatted = format_name($top_rep['username'], $top_rep['usergroup'], $top_rep['displaygroup']);
   $nick= '<a href="member.php?action=profile&uid='.intval($top_rep['uid']).'"> '.$usernameFormatted.'</a>';
	eval('$top_rep_trow .= "'.$templates->get("top_rep_trow").'";');
		}
eval('$toprep = "'.$templates->get('top_rep').'";');

}


?>
