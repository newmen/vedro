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

require_once ("classes/db_mysql.php");
require_once ("include/session.php");
require_once ("include/functions.php");

$default_location = "/system_admin/sistem_admin/";

if(!isset($_SESSION[S_ID]["menu"][0])) {
	header("Location: " . $default_location);
	exit();
}

try {
	$db = GetDBConnect();
	
	if(!$row = $db->Query_Fetch_Assoc("SELECT modules.name as module, menus.translit_name as menu " . "FROM menus LEFT JOIN modules ON menus.module_id=modules.id " . "WHERE menus.id='" . $_SESSION[S_ID]["menu"][0] . "' " . "LIMIT 0,1")) {
		header("Location: " . $default_location);
		exit();
	}
	
	header("Location: /" . $row["module"] . "/" . $row["menu"] . "/");
	exit();
} catch(Exception $e) {
	ShowErrPage("Ошибка", $e->getMessage());
}

?>
