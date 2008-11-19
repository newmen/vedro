#!/usr/bin/php
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
 
// скрипт установки новых модулей, через командную строку

function Help()
{
	$script_name = @$_SERVER["argv"][0];

	die("Неправильное использование!\n"
		. "правильно так: ".$script_name." [действие] [параметр]\n"
		. " [действие] бывает и используется с параметрами:\n"
		. "  -i, --install [путь/к/модулю] - установить модуль по пути\n"
		. "  -r, --remove [имя_модуля] - удаление модуля по имени\n\n"
		. "  -e, --reinstall [имя_модуля] - перечитать конфигурационный xml модуля\n\n"
		. "  -l, --list - показать все модули\n"
		. " удачи! (=\n");
}

$options = @$_SERVER["argv"][1];
$param = @$_SERVER["argv"][2];

if($options == "")
{
	Help();
}

require_once("config.php");
require_once("classes/db_mysql.php");
require_once("classes/module_tool.php");

try
{
	$db = new DB_MySQL(DB_LOCAL_HOST, DB_LOCAL_USER, DB_LOCAL_PASSWD, DB_LOCAL_DB);

	switch($options)
	{
	case "-i" :
	case "--install" :
		$m = new Module_Tool($db);
		$m->InitWithPath($param);
		$m->Install();

		echo "Модуль \"".$m->name."\" был успешно установлен\n";
		break;

	case "-r" :
	case "--remove" :
		$row = $db->Query_Fetch_Array("SELECT id FROM modules WHERE name='".$param."'");
		$m = new Module_Tool($db, $row[0]);
		$m->Remove();

		echo "Модуль \"".$m->name."\" был успешно удалён\n";
		break;
	
	case "-e" :
	case "--reinstall" :
		if(!$row = $db->Query_Fetch_Array("SELECT id FROM modules WHERE name='".$param."'"))
			throw new ModuleExtend("no installed module ".$param);

		$m = new Module_Tool($db, $row[0]);
		$path = $m->path;
		$m->Reinstall();

		echo "Модуль \"".$m->name."\" был успешно переустановлен\n";
		break;

	case "-l" :
	case "--list" :
		$m = new Module_Tool($db);
		echo $m->PrintAllModules();
		break;

	default : Help();
    }
}
catch(Exception $e)
{
	echo $e->getMessage()."\n";
}

?>
