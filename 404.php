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


//phpinfo();
require_once ("classes/class404.php");
require_once ("classes/page.php");
require_once ("include/functions.php");

try {
	$url = explode("/", $_SERVER["REDIRECT_URL"]);
	if(count($url) < 3) throw new class404("таки мне кажется, вы хотите невозможного");
	
	$page = new Page($url[1], $url[2]);
	$page->Show();
} catch(class404 $e) {
	ShowErrPage("404", "404!<br />извините такого документа нет.\n" . $e->getMessage());
} catch(Exception $e) {
	$message = str_replace("\n", "<br />", $e->getMessage());
	ShowErrPage("Error", $message . "<br /><br />\n" . $e->getTraceAsString());
}

?>
