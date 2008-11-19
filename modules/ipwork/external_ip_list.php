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

include("global.php");
require_once("classes/table.php");
require_once($inc_path."include/functions.php");

function ExternalIPList()
{
	include ("global.php");
	
	$sql = "SELECT id, ip, location, description, date_create "
		. "FROM external_ip ORDER by ip";
	$colums = array(
		"ip" => new Column_IP("IP адрес")
		,"location" => new Column_List($db, "SELECT id, name FROM locations ORDER by name", "Локация")
		,"description" => new Column("Описание")
		,"date_create" => new Column("Время присвоения")
		,"ping_ajax" => new Column_Custom("Пинг (быстрый)", "<div id='ping_[[id]]'> </div><script language='javascript'>sndReq('id=[[id]]&action=ping&ip=[[ip]]', '".$inc_path."ajax.php');</script>", 0)
		,"ping" => new Column_UserFunc("Пинг (долгий)", "Ping", array("ip"), 0)
		);
	$colums["ip"]->SetLink("external_ip_edit.php", "id");
	$colums["description"]->SetLink("external_ip_edit.php", "id");
	
	$badlines = array(
		"ping" => new Row('=', 'Нет')
		);
	
	$table = new Table($db, "external_ip_list", $sql, $colums, $badlines);
	$table->Show_PageSwitch(true);
	$table->Show_AllCols(true);
	$table->Show_Search("description");
	$table->SetWidth("95%");
	
	$table->Show();
}

ExternalIPList();


?>
