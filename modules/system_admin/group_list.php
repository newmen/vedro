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
require_once ("classes/table.php");

function GroupList()
{
	include ("global.php");
	
	$sql = "SELECT id, name "
		. "FROM groups WHERE is_deleted=0 "
		. "ORDER by name";
	
	$colums = array(
		"name" => new Column("Название")
		,"delete" => new Column_Button("Действие", "?action=group_delete", "id", "удалить")
		);
	$colums["name"]->SetLink("group_edit.php", "id");
	
	$table = new Table($db, "system_group_list", $sql, $colums);
	$table->SetTheme("system");
	$table->Show_PageSwitch(false);
	
	$table->Show();
}

function GroupDelete()
{
	include ("global.php");
	if(!isset($_GET["id"])) return;
	
	$db->Query("UPDATE groups SET is_deleted=1 WHERE id='".$_GET["id"]."'");
}

if(isset($_GET["action"]) && $_GET["action"] == "group_delete") GroupDelete();
GroupList();


?>
