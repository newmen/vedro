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

require_once ("classes/table.php");

function users_connection_list()
{
	include ("global.php");
//	echo $db->QueryNums();
	
	$sql = "SELECT uid as id, users.login as login, ip, last_view, time_last_active, time_login "
	. "FROM users_online LEFT JOIN users ON users_online.uid=users.id "
	. "ORDER by time_last_active DESC";
	
	$colums = array("login" => new Column("Логин")
		, "ip" => new Column_IP("IP")
		, "last_view" => new Column("Последний просмотр", 1, "left")
		, "time_last_active" => new Column("Активность")
		, "time_login" => new Column("Время входа")
		);
	$colums["login"]->SetLink("user_edit.php", "id");
	
	$table = new Table($db, "system_users_online", $sql, $colums);
	$table->SetTheme("system");
	$table->Show_PageSwitch(false);
	
	$table->Show();
//	echo $db->QueryNums();
}

users_connection_list();
