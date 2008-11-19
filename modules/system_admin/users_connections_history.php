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

function users_connection_history()
{
	include ("global.php");
	
	$sql = "SELECT users_connection_history.id as id, uid, login, ip, time_active, state "
		. "FROM users_connection_history LEFT JOIN users ON users_connection_history.uid=users.id "
		. "ORDER by id DESC";
	
	$columns = array("id" => new Column_Number("ID", 0)
//		, "uid" => new Column_HLink("ID пользователя", 1, "center")
		, "login" => new Column("Логин")
		, "ip" => new Column_IP("IP")
		, "time_active" => new Column("Время")
		, "state" => new Column_Bool("Статус", "пришёл", "ушёл")
		);
	$columns["id"]->SetDBTableName("users_connection_history");
	$columns["login"]->SetLink("user_edit.php", "uid");
	
	$badlines = array("state" => new Row("=", 0));
	
	$table = new Table($db, "system_users_history", $sql, $columns, $badlines, "95%");
	$table->SetTheme("system");
	$table->Show_PageSwitch(true);
	$table->Show_Search("login");
	
	$table->Show();
}

users_connection_history();
