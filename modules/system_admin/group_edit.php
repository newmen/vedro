<?php
/*
 *   Данная программа представляет собой свободно распространяемый
 *   программный продукт; вы можете распространять ее далее и\или изменять
 *   на условиях Стандартной публичной лицензии GNU, опубликованной
 *   "Free Software Foundation" - либо ее версии номер 2, либо (по вашему
 *   выбору) любой более поздней ее версии.
 *
 *   Распространяя данный программный продукт, мы надеемся что он окажется
 *   полезным, но НЕ ДАЕМ НИКАКИХ ГАРАНТИЙ, даже подразумеваемой гарантии
 *   ПРИГОДНОСТИ К КУПЛЕ-ПРОДАЖЕ или ИСПОЛЬЗОВАНИЮ В КОНКРЕТНЫХ ЦЕЛЯХ
 *   (см. "Стандартную публичную лицензию GNU").
 *
 *   Вместе с данной программой вы должны были получить копию "Стандартной
 *   публичной лицензии GNU"; если это не так, напишите в Free Software
 *   Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *  
 *   Copyright (C) 2008 by Gleb Y. Averchuk <altermn@gmail.com> 
 *   Vedro System - Web CMS с удобными компонентами для работы с БД.
 */

include ("global.php");
require_once("classes/form.php");
require_once("classes/formchecklist.php");
require_once("classes/formupdatesql.php");
require_once($inc_path."include/functions.php");

function CheckAction()
{
	global $db;
	
	if(isset($_GET["id"]) && $_GET["id"] > 0
		&& isset($_GET["action"]) && $_GET["action"] == "full_delete")
	{
		$db->Query("DELETE FROM groups WHERE id='".$_GET["id"]."'");
		MessageBox("Группа удалёна");
		return false;
	}
	
	return true;
}

function ShowForm()
{
	global $db, $group_edit_id;
	
	if(!CheckAction() || $group_edit_id == 0) return;

	$sql = "SELECT name, is_deleted "
		. "FROM groups "
		. "WHERE id='".$group_edit_id."' LIMIT 0,1";
	$elemsedit = array(
		"is_deleted" => new FCheckbox("is_deleted", "Удалён")
		,"name" => new FInput("name", "Название:")
		);
	$formedit = new FormUpdateSQL($db, $sql, $elemsedit);
	$formedit->SetUserFunc("CheckGroupForm");

	$list_sql = "SELECT id, login FROM users";
	$join_sql = "SELECT user_id FROM groups_users WHERE group_id=".$group_edit_id;
	$formcheckboxs = new FormCheckList($db, $list_sql, $join_sql);
	$formcheckboxs->SetWidth("100%");

	$elemsform = array(
		"id" => new FHidden("id", $group_edit_id)
		,"editform" => new FBlock($formedit->AsString())
		,"checkboxs" => new FBlock($formcheckboxs->AsString())
		);
	$fr = new Form($elemsform, "Изменить");
	$fr->AddButton("<input type=\"button\" value=\"Уничтожить\" "
		. "onClick=\"if(window.confirm('Уверены, что хотите безвозвратно уничтожить группу?')) GoURL('?action=full_delete&id=".$group_edit_id."')\">");

	$fr->Show();
}

global $group_edit_id;
$group_edit_id = isset($_GET["id"]) ? $_GET["id"] : 0;

echo "<script type=\"text/javascript\" src=\"/js/functions.js\"></script>\n";
echo "<form action=\"\" method=\"GET\">\n" . "<select name=\"id\">\n";
if($group_edit_id == 0) echo "<option> - выберите - </option>";

$group_list = $db->Query_Fetch("SELECT id, name FROM groups");
foreach($group_list as $g) {
	echo "<option value=\"" . $g["id"] . "\" onClick=\"ReloadWithID(" . $g["id"] . ")\"";
	if($group_edit_id == $g["id"]) echo " selected";
	echo ">" . $g["name"] . "</option>\n";
}
echo "</select></form>";

ShowForm();

?>