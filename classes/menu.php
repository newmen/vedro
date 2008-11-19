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

// передаваемый в конструктор объект системного пользователя нужен лишь для того, чтобы смотреть на права доступа этого пользователя
// по идее, можно было бы сделать чтобы права доступа передавались просто тупо в виде массива.
// этот массив реализован в $sys_user->groups

require_once ("classes/module.php");

class MenuExcept extends Exception {}

class Menu {
	private $db;
	private $sys_user;
	private $module;

	private $s_;
	private $menuhtml = ''; // строка в которую сохраняется откешированное меню
	
	private $id;
	private $script;
	private $topic;

	function __construct(&$db, &$sys_user, $menu_id)
	{
//		echo $db->QueryNums();
		$this->db = &$db;
		$this->sys_user = &$sys_user;
		$this->s_ = &$_SESSION[S_ID]["menu"];
		
		// тут не делаем проверку на группы пользователя, т.к. проверка пройдена в вызывающем классе - page.php
		if(!$row = $this->db->Query_Fetch_Assoc("SELECT menus.id as menu_id, menus.script_name as menu_script, menus.module_id, "
		 . "menus.name as menu_name, menus.full_name as menu_full_name, menus.parent_id as menu_parent_id "
		 . "FROM menus "
		 . "WHERE menus.id='" . $menu_id . "'"))
		 	throw new MenuExcept("нет элемента меню с id - " . $menu_id);
		
		if($row["menu_script"] == '') {
			$this->s_[$row["menu_parent_id"]] = $row["menu_id"];
			$params = $this->FindMenuWithScript($menu_id);
		}
		else $params = $row;
			
		$this->id = $params["menu_id"];
		$this->script = $params["menu_script"];
		$this->topic = ($params["menu_full_name"] != "") ? $params["menu_full_name"] : $params["menu_name"];
		
		$this->module = new Module($this->db, $params["module_id"]);
		
		$this->s_[$params["menu_parent_id"]] = $params["menu_id"];
//		echo $db->QueryNums();
	}

	function __get($name)
	{
		switch($name) {
		case "id":
		case "script":
		case "topic":
		case "module":
			return $this->$name;
		
		default:
			return false;
		}
	}

	function Show()
	{
		if($this->id == 0) return;
		
		// если путь до загруженного модуля не обновился, то берём меню из кеша
		if($this->CheckCache() !== false) return;
		
		// всё что выводится засовываем в $this->menuhtml;
		ob_start(array(&$this, "BuffCache"));
		
		$this->ShowLevelsUp($this->id);
		
		// показать 1 подУровень
		if($result = $this->db->Query_Fetch("SELECT menus.name as menu_name, menus.translit_name, "
		 . "menus.script_name, modules.name as module_name "
		 . "FROM menus LEFT JOIN groups_menus ON menus.id=groups_menus.menu_id "
		 . "LEFT JOIN modules ON menus.module_id=modules.id "
		 . "WHERE menus.parent_id='" . $this->id . "' "
		 . "AND (" . $this->sys_user->groups_SQL . ") "
		 . "ORDER by menus.position"))
			foreach($result as $row)
				echo "&nbsp;<a href=\"/" . $row["module_name"] . "/" . $row["translit_name"] . "/\">"
				 . $row["menu_name"] . "</a>&nbsp;\n";
		
		ob_end_flush();
		
		$this->SaveCache();
		echo $this->menuhtml;
	}
	
	private function BuffCache($buff)
	{
		$this->menuhtml .= $buff;
	}
	
	private function CheckCache()
	{
		include("global.php");
	
		$filename = CACHEDIR."/".$_REQUEST["PHPSESSID"];
		if(!$f = @fopen($filename, "r"))
			return false;
		
		$content = fread($f, filesize($filename));
		fclose($f);
		
		$xml = new SimpleXMLElement($content);
		if(!isset($xml->path) || $xml->path != $url_path) return false;
		
		echo rawurldecode($xml->menu); // рисуем закешированное меню		
		return true;
	}

	private function SaveCache()
	{
		include("global.php");
	
		$filename = CACHEDIR."/".$_REQUEST["PHPSESSID"];
		if(!$f = @fopen($filename, "w"))
			throw new Exception("немогу открыть файл кеша ".$filename);
		
		fwrite($f, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
			. "<data>\n"
			. "\t<path>".$url_path."</path>\n"
			. "\t<menu>".rawurlencode($this->menuhtml)."</menu>\n"
			. "</data>");
		fclose($f);
	}

	// рекурсивная
	// показать верхние уровни меню, включая текущий
	private function ShowLevelsUp($id)
	{
		if(!$row = $this->db->Query_Fetch_Array("SELECT parent_id FROM menus WHERE id=" . $id . " LIMIT 0,1")) throw new MenuExcept("невозможно найти родителя для меню id - " . $id);
		
		if($row[0] != 0) $this->ShowLevelsUp($row[0]);
		
		// выбирает все элементы меню, у которых парентом стоит такой же как у текущего
		if(!$result = $this->db->Query_Fetch("SELECT menus.id, menus.name as menu_name, menus.translit_name, "
		 . "menus.full_name, menus.script_name, modules.name as module_name "
		 . "FROM menus LEFT JOIN groups_menus ON menus.id=groups_menus.menu_id "
		 . "LEFT JOIN modules ON menus.module_id=modules.id "
		 . "WHERE menus.parent_id=" . $row[0]
		 . " AND (" . $this->sys_user->groups_SQL . ") "
		 . "ORDER by menus.position")) return;
		
		foreach($result as $row) {
			echo "&nbsp;";
			if($row["id"] == $id) {
				if($row["id"] != $this->id && $row["script_name"] != "") echo "<a href=\"/" . $row["module_name"] . "/" . $row["translit_name"] . "/\">";
				
				echo "<b>" . $row["menu_name"] . "</b>";
				
				if($row["id"] != $this->id && $row["script_name"] != "") echo "</a>";
			}
			else
				echo "<a href=\"/" . $row["module_name"] . "/" . $row["translit_name"] . "/\">" . $row["menu_name"] . "</a>";
			echo "&nbsp;\n";
		}
		echo "<br />\n";
	}

	private function FindMenuWithScript(&$id) // рекурсивная
	{
		$in_session = false;
		
		if(isset($this->s_[$id]) && $row = $this->db->Query_Fetch_Assoc("SELECT menus.id as menu_id, menus.script_name as menu_script, menus.module_id, " . "menus.name as menu_name, menus.full_name as menu_full_name, menus.parent_id as menu_parent_id " . "FROM menus LEFT JOIN groups_menus ON menus.id=groups_menus.menu_id " . "WHERE menus.id='" . $this->s_[$id] . "' AND (" . $this->sys_user->groups_SQL . ") " . "LIMIT 0,1")) $in_session = true;
		
		if(!$in_session && !$row = $this->db->Query_Fetch_Assoc("SELECT menus.id as menu_id, menus.script_name as menu_script, menus.module_id, " . "menus.name as menu_name, menus.full_name as menu_full_name, menus.parent_id as menu_parent_id " . "FROM menus LEFT JOIN groups_menus ON menus.id=groups_menus.menu_id " . "WHERE menus.parent_id='" . $id . "' AND (" . $this->sys_user->groups_SQL . ") " . "ORDER by menus.position " . "LIMIT 0,1")) throw new class404("при поиске скрипта не найден модуль с id - " . $id . " (видимо не достаточно прав доступа)");
		
		$params = ($row["menu_script"] == "") ? $this->FindMenuWithScript($row["menu_id"]) : $row;
		
		return $params;
	}
}

?>