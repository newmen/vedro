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
require_once("classes/formchecklist.php");
require_once("classes/forminsertsql.php");
require_once($inc_path."include/functions.php");

function ShowForm()
{
	global $db;
	
	$sql = "INSERT INTO users (login, password, name, family, daddy, mobile_telephone) "
		. "VALUES ()";
	$elemsedit = array(
		"login" => new FInput("login", "Логин:")
		,"password" => new FPassword("password", "Пароль:")
		,"password2" => new FPassword("password2", "Пароль ещё раз:")
		,"name" => new FInput("name", "Имя:")
		,"family" => new FInput("family", "Фамилия:")
		,"daddy" => new FInput("daddy", "Отчество:")
		,"mobile_telephone" => new FInput("mobile_telephone", "Мобильный:")
		);
	$formedit = new FormInsertSQL($db, $sql, $elemsedit);
	$formedit->SetUserFunc("CheckUserForm");

	$list_sql = "SELECT id, name FROM groups";
	$join_sql = "SELECT group_id FROM groups_users WHERE user_id=";
	$formcheckboxs = new FormCheckList($db, $list_sql, $join_sql, true);
	$formcheckboxs->SetWidth("100%");

	$elemsform = array(
		"editform" => new FBlock($formedit->AsString())
		,"checkboxs" => new FBlock($formcheckboxs->AsString())
		);
	$fr = new Form($elemsform, "Добавить");
	$fr->SetGoBack(false);
	$fr->Show();
}

ShowForm();

?>
