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
require_once("classes/formupdatesql.php");
require_once($inc_path."include/functions.php");

function CheckAction()
{
	global $db;
	
	if(isset($_GET["id"]) && $_GET["id"] > 0
		&& isset($_GET["action"]) && $_GET["action"] == "full_delete")
	{
		$db->Query("DELETE FROM locations WHERE id='".$_GET["id"]."'");
		MessageBox("Локация удалёна");
		return false;
	}
	
	return true;
}

function ShowForm()
{
	global $db, $locate_id;
	
	if(!CheckAction() || $locate_id == 0) return;

	$sql = "SELECT name FROM locations WHERE id='".$locate_id."' LIMIT 0,1";
	$elemsedit = array("name" => new FInput("name", "Название:"));
	$formedit = new FormUpdateSQL($db, $sql, $elemsedit);
	$formedit->SetUserFunc("CheckLocateForm");

	$elemsform = array(
		"id" => new FHidden("id", $locate_id)
		,"editform" => new FBlock($formedit->AsString())
		);
	$fr = new Form($elemsform, "Изменить");
	$fr->AddButton("<input type=\"button\" value=\"Уничтожить\" "
		. "onClick=\"if(window.confirm('Уверены, что хотите безвозвратно удалить локацию? Помните! Это - очень плохо и может привести к проблемам!')) GoURL('?action=full_delete&id=".$locate_id."')\">");

	$fr->Show();
}

global $locate_id;
$locate_id = isset($_GET["id"]) ? $_GET["id"] : 0;

echo "<script type=\"text/javascript\" src=\"/js/functions.js\"></script>\n";
echo "<form action=\"\" method=\"GET\">\n" . "<select name=\"id\">\n";
if($locate_id == 0) echo "<option> - выберите - </option>";

$locate_list = $db->Query_Fetch("SELECT id, name FROM locations");
foreach($locate_list as $l) {
	echo "<option value=\"" . $l["id"] . "\" onClick=\"ReloadWithID(" . $l["id"] . ")\"";
	if($locate_id == $l["id"]) echo " selected";
	echo ">" . $l["name"] . "</option>\n";
}
echo "</select></form>";

ShowForm();

?>