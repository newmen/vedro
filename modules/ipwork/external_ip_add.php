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
require_once("classes/forminsertsql.php");
require_once($inc_path."include/functions.php");

function ShowForm()
{
	global $db;
	
	$sql = "INSERT INTO external_ip (ip, location, description) VALUES ()";
	$elemsedit = array(
		"ip" => new FIP("ip", "IP:")
		,"location" => new FSelect($db, "SELECT id, name FROM locations ORDER by name", "location", "Локация:")
		,"description" => new FInput("description", "Описание:")
		);
	$formedit = new FormInsertSQL($db, $sql, $elemsedit);
	$formedit->SetUserFunc("CheckExternalIPForm");

	$elemsform = array("editform" => new FBlock($formedit->AsString()));
	$fr = new Form($elemsform, "Присвоить");
	$fr->SetGoBack(false);
	$fr->Show();
}

ShowForm();

?>
