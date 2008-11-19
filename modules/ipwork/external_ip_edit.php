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
		$db->Query("DELETE FROM external_ip WHERE id='".$_GET["id"]."'");
		MessageBox("Внешний IP больше ничему не соответсвует");
		return false;
	}
	
	return true;
}

function ShowForm()
{
	global $db, $exip_id;
	
	if(!CheckAction() || $exip_id == 0) return;

	$sql = "SELECT ip, location, description "
		. "FROM external_ip WHERE id='".$exip_id."' LIMIT 0,1";
	$elemsedit = array(
		"ip" => new FIP("ip", "IP:")
		,"location" => new FSelect($db, "SELECT id, name FROM locations ORDER by name", "location", "Локация:")
		,"description" => new FInput("description", "Описание:")
		);
	$formedit = new FormUpdateSQL($db, $sql, $elemsedit);
	$formedit->SetUserFunc("CheckExternalIPForm");

	$elemsform = array(
		"id" => new FHidden("id", $exip_id)
		,"editform" => new FBlock($formedit->AsString())
		);
	$fr = new Form($elemsform, "Изменить");
	$fr->AddButton("<input type=\"button\" value=\"Уничтожить\" "
		. "onClick=\"if(window.confirm('Уверены, что хотите удалить соответствие?')) GoURL('?action=full_delete&id=".$exip_id."')\">");

	$fr->Show();
}

global $exip_id;
$exip_id = isset($_GET["id"]) ? $_GET["id"] : 0;

echo "<script type=\"text/javascript\" src=\"/js/functions.js\"></script>\n";
echo "<form action=\"\" method=\"GET\">\n" . "<select name=\"id\">\n";
if($exip_id == 0) echo "<option> - выберите - </option>";

$exip_list = $db->Query_Fetch("SELECT id, ip FROM external_ip ORDER by ip");
foreach($exip_list as $ex) {
	echo "<option value=\"" . $ex["id"] . "\" onClick=\"ReloadWithID(" . $ex["id"] . ")\"";
	if($exip_id == $ex["id"]) echo " selected";
	echo ">" . long2ip($ex["ip"]) . "</option>\n";
}
echo "</select></form>";

ShowForm();

?>