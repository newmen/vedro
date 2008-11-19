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
			$db->Query("DELETE FROM users WHERE id='".$_GET["id"]."'");
			MessageBox("Пользователь удалён");
			return false;
		}
	
	return true;
}

function ShowForm()
{
	global $db, $user_edit_id;
	
	if(!CheckAction() || $user_edit_id == 0) return;

	$sql = "SELECT login, password, name, family, daddy, mobile_telephone, "
		. "is_deleted "
		. "FROM users "
		. "WHERE id='".$user_edit_id."' LIMIT 0,1";
	$elemsedit = array(
		"is_deleted" => new FCheckbox("is_deleted", "Удалён")
		,"login" => new FInput("login", "Логин:")
		,"password" => new FPassword("password", "Пароль:")
		,"password2" => new FPassword("password2", "Пароль ещё раз:")
		,"name" => new FInput("name", "Имя:")
		,"family" => new FInput("family", "Фамилия:")
		,"daddy" => new FInput("daddy", "Отчество:")
		,"mobile_telephone" => new FInput("mobile_telephone", "Мобильный:")
		);
	$formedit = new FormUpdateSQL($db, $sql, $elemsedit);
	$formedit->SetUserFunc("CheckUserForm");

	$list_sql = "SELECT id, name FROM groups";
	$join_sql = "SELECT group_id FROM groups_users WHERE user_id=".$user_edit_id;
	$formcheckboxs = new FormCheckList($db, $list_sql, $join_sql);
	$formcheckboxs->SetWidth("100%");

	$elemsform = array(
		"id" => new FHidden("id", $user_edit_id)
		,"editform" => new FBlock($formedit->AsString())
		,"checkboxs" => new FBlock($formcheckboxs->AsString())
		);
	$fr = new Form($elemsform, "Изменить");
	$fr->AddButton("<input type=\"button\" value=\"Уничтожить\" "
		. "onClick=\"if(window.confirm('Уверены, что хотите безвозвратно уничтожить пользователя?')) GoURL('?action=full_delete&id=".$user_edit_id."')\">");

	$fr->Show();
}

global $user_edit_id;
$user_edit_id = isset($_GET["id"]) ? $_GET["id"] : 0;

echo "<script type=\"text/javascript\" src=\"/js/functions.js\"></script>\n";
echo "<form action=\"\" method=\"GET\">\n"
	. "<select name=\"id\">\n";
if($user_edit_id == 0) echo "<option> - выберете - </option>";

$user_list = $db->Query_Fetch("SELECT id, login FROM users ORDER by login");
foreach($user_list as $u) {
	echo "<option value=\"" . $u["id"] . "\" onClick=\"ReloadWithID(" . $u["id"] . ")\"";
	if($user_edit_id == $u["id"]) echo " selected";
	echo ">" . $u["login"] . "</option>\n";
}
echo "</select></form>";

ShowForm();

?>
