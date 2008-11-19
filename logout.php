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

require_once ("config.php");
require_once ("classes/db_mysql.php");
require_once ("classes/system_user.php");
require_once ("include/session.php");
require_once ("include/functions.php");

if(session_is_registered(S_ID)) {
	$good = true;
	try {
		$db = GetDBConnect();
		
		$sys_user = new System_User($db);
		$sys_user->Current();
		
		$sys_user->Logout();
	} catch(Exception $e) {
		ShowErrPage("Ошибочка", $e->GetMessage());
		$good = false;
	}
	
	if($good) {
		session_destroy();
		header("Location: login.php");
		exit();
	}
}

?>
