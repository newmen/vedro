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

require_once ("classes/module.php");

// жирненький интерфейс
class Module_Tool extends Module {
	private $xml;
	
	private $find_menus; // искали ли в меню
	private $find_modules; // искали ли в зависимых модулях
	private $inited; // инициализирован ли
	

	private $depend_modules_list = array(); // массив зависимых модулей. в качестве ключей используются id модулей,

	// для быстрого поиска уже существующих
	

	function __construct(&$db, $id = false)
	{
		$this->inited = false;
		
		$this->find_menus = false;
		$this->find_modules = false;
		
		parent::__construct(&$db, $id);
	}

	private static function ValidFile(&$path, &$file)
	{
		if(ereg("^http:\/\/", $file)) return true;
		return file_exists($path.$file);
	}
	
	function InitWithPath($path) // тут модуль проверяется на ошибки конфигурации
	{
		$this->path = $path;
		
		if(!$cache = @fopen($this->path . "config.xml", "r")) throw new ModuleExcept("не найден конфигурационный XML (" . $this->path . "config.xml)");
		
		if(!$content = fread($cache, filesize($this->path . "config.xml"))) throw new ModuleExcept("невозможно прочесть конфигурационный XML (" . $this->path . "config.xml)");
		//var_dump($content);
		fclose($cache);
		
		$this->xml = new SimpleXMLElement($content);
		
		if(!isset($this->xml->name)) throw new ModuleExcept("не указано имя модуля");
		
		// сначала проверяется, описано ли меню...
		// если нет - проверяется имеется ли ядро? если нет - выскакивает исключение
		if(isset($this->xml->menu)
			&& (isset($this->xml->menu->item) || (isset($this->xml->menu["script_name"])
				&& self::ValidFile($this->path, $this->xml->menu["script_name"]))))
		{
			// меню описано - проверяем каждый элемент меню
			if(isset($this->xml->menu->item)) $this->CheckMenuItems(&$this->xml->menu->item);
		}
		elseif(!isset($this->xml->kernel) || !file_exists($this->xml->kernel)) throw new ModuleExcept("не найдено ни ядра, ни ниодного элемента меню");
		
		// если описано меню или задано ядро, то проверяется нет ли зависимости от других модулей
		if(isset($this->depend) && isset($this->depend->module)) {
			foreach($this->depend->module as $module) {
				if(!isset($module["name"])) throw new ModuleExcept("не заданно имя модуля, от которого требуется зависимость");
				
				if(!$row = $this->db->Query_Fetch_Assoc("SELECT id FROM modules " . "WHERE name='" . $module["name"] . "' AND kernel<>NULL")) {
					throw new ModuleExcept("не найден родительский модуль (" . $module["name"] . ") имеющий ядро, от которого зависит текущий");
				}
				$module["id"] = $row["id"];
			}
		}
		
		$this->inited = true;
		// проверка пройдена успешно!
	}

	function Install($parent_menu_id = false, $position = false) // засовывание всей инфы о модуле в базу
	{
		if($this->db->Query_Fetch_Array("SELECT id FROM modules WHERE name='" . $this->xml->name . "'")) throw new ModuleExcept("модуль с именем \"" . $this->xml->name . "\" уже установлен в системе");
		
		if(!$this->inited) throw new ModuleExcept("невозможно установить модуль. необходима инициализация");
		
		// тут (ниже) идёт кривой код имхо... слишком много проверок одного и того же... хз мож надо переделать
		//		if($menu_id === false && !isset($this->xml->kernel) && (!isset($this->xml->menu) || !isset($this->xml->menu->item)))
		//			throw new ModuleExcept("невозможно установить модуль как \"неотображаемый\", т.к. не заданы ни файл ядра модуля, ни меню модуля");
		

		/*
		if(isset($this->xml->sql_file))
		{
			
		}
*/
		
		$query = "INSERT INTO modules (name, path";
		if(isset($this->xml->kernel)) $query .= ", kernel";
		$query .= ") " . "VALUES ('" . $this->xml->name . "', '" . $this->path . "'";
		if(isset($this->xml->kernel)) $query .= ", '" . $this->xml->kernel . "'";
		$query .= ")";
		
		$this->db->Query($query); // добавляем запись в таблицу modules
		

		$this->id = $this->db->Insert_ID(); // и запоминаем id свежедобавленного модуля, в БД
		$this->name = (string)$this->xml->name;
		$this->kernel = (string)$this->xml->kernel;
		$this->path = (string)$this->xml->path;
		
		if(isset($this->xml->menu)) {
			if($parent_menu_id === false) $parent_menu_id = 0;
			
			// определяем максимальную позицию... и увеличиваем её - это всё для нового элемента меню - названия модуля (=
			if($position === false) {
				$row = $this->db->Query_Fetch_Array("SELECT MAX(position) FROM menus WHERE parent_id=" . $parent_menu_id);
				
				$pos = ($row[0] != '') ? $row[0] : 0;
			}
			else
				$pos = &$position;
				
			// добавляем запись модуля в меню
			$menu_name = (isset($this->xml->menu["name"])) ? $this->xml->menu["name"] : $this->xml->name;
			
			$query = "INSERT INTO menus (name, translit_name, ";
			if(isset($this->xml->menu["full_name"])) $query .= "full_name, ";
			if(isset($this->xml->menu["script_name"])) $query .= "script_name, ";
			$query .= "parent_id, module_id, position, is_module_root) " . "VALUES ('" . $menu_name . "', '" . Translit($menu_name) . "', ";
			if(isset($this->xml->menu["full_name"])) $query .= "'" . $this->xml->menu["full_name"] . "', ";
			if(isset($this->xml->menu["script_name"])) $query .= "'" . $this->xml->menu["script_name"] . "', ";
			$query .= $parent_menu_id . ", " . $this->id . ", " . $pos . ", 1)";
			
			$this->db->Query($query);
			$last_id = $this->db->Insert_ID();
			
			// добавляем права на только что добавленую запись - можно для всех
			$this->db->Query("INSERT INTO groups_menus (group_id, menu_id) VALUES (0, " . $last_id . ")");
		}
		
		if(isset($this->xml->menu->item)) {
			// добавляем записи пунктов меню
			$this->AddMenuItem(&$this->xml->menu->item, &$last_id);
		}
		
		if(isset($this->xml->depend) && isset($this->xml->depend->module)) {
			foreach($this->xml->depend->module as $module) // добавляем записи зависимости модулей
{
				$this->db->Query("INSERT INTO modules_depend (module_id, extend_module_id) " . "VALUES (" . $this->id . ", " . $module["id"] . ")");
			}
		}
		
	/*	
		// если модуль системный
		if(isset($this->xml->kernel) && isset($this->xml->kernel["system"]) && $this->xml->kernel["system"] == "yes")
		{
			$this->db->Query("INSERT INTO modules_depend (module_id, extend_module_id) VALUES (0, ".$this->id.")");
		}
*/
	}

	function Remove()
	{
		if(!isset($this->id)) throw new ModuleExcept("модуль не идентифицирован!\n" . $this->PrintAllModules(0, 0, 0)); // не пешите так!!!!!
		

		if(!$this->find_menus) $this->FindDependMenus();
		if(!$this->find_modules) $this->FindDependModules();
		
		while($module = array_pop($this->depend_modules_list)) {
			$module->Remove(); // этакая рекурсия? (=
		}
		
		// чистим меню
		if(($result = $this->db->Query_Fetch("SELECT id FROM menus WHERE module_id=" . $this->id)) !== false) {
			$query = "DELETE FROM groups_menus WHERE ";
			
			foreach($result as $row) {
				$query .= "menu_id=" . $row["id"] . " OR ";
			}
			$query = substr($query, 0, -4);
			
			$this->db->Query($query);
			$this->db->Query("DELETE FROM menus WHERE module_id=" . $this->id);
		}
		
		// чистим зависимости модулей
		$this->db->Query("DELETE FROM modules_depend WHERE extend_module_id=" . $this->id);
		
		// чистим таблицу установленых модулей
		$this->db->Query("DELETE FROM modules WHERE id=" . $this->id);
	}

	function Reinstall()
	{
		$parent_menu_id = false;
		$position = false;
		$this->GetMenuInfo($parent_menu_id, $position);
		
		$this->InitWithPath($this->path);
		$this->Remove();
		$this->Install($parent_menu_id, $position);
	}

	private function GetMenuInfo(&$parent_menu_id, &$position)
	{
		if(!$row = $this->db->Query_Fetch_Assoc("SELECT parent_id, position FROM menus WHERE is_module_root=1 and module_id=" . $this->id)) return;
		
		$parent_menu_id = $row["parent_id"];
		$position = $row["position"];
	}

	private function FindDependMenus() // ищет модули, чьё меню вложено в меню данного модуля
	{
		if($this->find_modules) return;
		
		if(($result = $this->db->Query_Fetch("SELECT id FROM menus WHERE module_id=" . $this->id)) !== false) foreach($result as $row) {
			$this->FindDependModulesMenu($row["id"]);
		}
		
		$this->find_menu = true;
	}

	private function FindDependModules() // рекурсивно ищет модули, чьё меню вложено в меню данного модуля
	{
		if($this->find_menus) return;
		
		$this->FindDependModulesModule(&$this->id);
		
		$this->find_menus = true;
	}

	// рекурсивная
	// проверяет есть ли скрипты, на которые ссылается меню; да и ваще валидность элемента
	private function CheckMenuItems($item_xml)
	{
		foreach($item_xml as $item) {
			if(!isset($item["name"]) || $item["name"] == "") throw new ModuleExcept("не указано имя для одного из элементов меню");
			
			/* ЖЕСТЬ (=
			if($item["script_name"] == "" && $item["full_name"] != "")
				throw new ModuleExcept("для элемента меню, не указывающего на файл, имеется полное название пункта меню");
*/
			$script_name = '';
			if(isset($item["script_name"])) $script_name = $item["script_name"];
			
			if(isset($item->item)) $this->CheckMenuItems(&$item->item);
			else {
				if(!isset($script_name) || $script_name == "") throw new ModuleExcept("не указан файл для конечного элемента меню " . $item["name"]);
				elseif($pos = strpos($script_name, "?")) $script_name = substr($script_name, 0, $pos);
			}
			
			if(!self::ValidFile($this->path, $script_name)) throw new ModuleExcept("не найден файл " . $this->path . $script_name);
			
		//			if(!filesize($this->path.$script_name))
		//				throw new ModuleExcept("файл меню ".$this->path.$item["script_name"]." нулевой длинны");
		}
	}

	// рекурсивная
	private function AddMenuItem($item_xml, $parent_id) // засовывает элементы меню в базу
	{
		$pos = 0;
		foreach($item_xml as $item) {
			if(isset($item->pos) && $item->pos > 0) $pos = $item->pos;
			else $pos += 10;
			
			$name = (isset($item["full_name"])) ? $item["full_name"] : $item["name"];
			
			$query = "INSERT INTO menus (name, translit_name" . ((isset($item["full_name"]) && $item["full_name"] != "") ? ", full_name" : '') . ((isset($item["script_name"]) && $item["script_name"] != "") ? ", script_name" : '') . ", parent_id, module_id, position) " . "VALUES ('" . $item["name"] . "', '" . Translit($name) . "'" . ((isset($item["full_name"]) && $item["full_name"] != "") ? ", '" . $item["full_name"] . "'" : '') . ((isset($item["script_name"]) && $item["script_name"] != "") ? ", '" . $item["script_name"] . "'" : '') . ", " . $parent_id . ", " . $this->id . ", " . $pos . ")";
			
			$this->db->Query($query);
			$last_id = $this->db->Insert_ID();
			
			// добавление прав - разрешить всем всё... на данный пункт меню
			$this->db->Query("INSERT INTO groups_menus (group_id, menu_id) VALUES (0, " . $last_id . ")");
			
			if(isset($item->item)) $this->AddMenuItem(&$item->item, &$last_id);
		}
	}

	// рекурсивная
	// код можно оптимизировать! (не проходить по тем элементам, по которым прошло уже) | 24.03.2008
	private function FindDependModulesMenu($parent_id) // ищет зависимые по меню модули, и засовывает их в массив depend_modules_list
	{
		if(!$result = $this->db->Query_Fetch("SELECT id, module_id FROM menus WHERE parent_id=" . $parent_id)) return;
		
		foreach($result as $row) {
			if($row["module_id"] != $this->id && !isset($this->depend_modules_list[$row["module_id"]])) $this->depend_modules_list[$row["module_id"]] = new Module($this->db, $row["module_id"]);
			
			$this->FindDependModulesMenu(&$row["id"]);
		}
	}

	// рекурсивная
	// ищет модули зависимые от данного модуля, и засовывает их в массив depend_modules_list
	private function FindDependModulesModule($module_id)
	{
		if(!$result = $this->db->Query_Fetch("SELECT module_id FROM modules_depend WHERE extend_module_id=" . $module_id)) return;
		
		foreach($result as $row) {
			if($row["module_id"] != $this->id && !isset($this->depend_modules_list[$row["module_id"]])) $this->depend_modules_list[$row["module_id"]] = new Module($this->db, $row["module_id"]);
			
			$this->FindDependModulesModule(&$row["module_id"]);
		}
	}

	function PrintAllModules($install_time = true, $kernel = true, $path = true, $id = true, $name = true)
	{ // такой вот извратный (=
		$query = "SELECT";
		if($id) $query .= " id";
		if($name) $query .= ", name";
		if($path) $query .= ", path";
		if($kernel) $query .= ", kernel";
		if($install_time) $query .= ", install_time";
		$query .= " FROM modules";
		if(!$result = $this->db->Query_Fetch($query)) {
			return "Модули не установленны\n";
		}
		
		$string = "Список доступных модулей:\n";
		if($id) $string .= "ID";
		if($name) $string .= "\tИмя";
		if($path) $string .= "\tГде лежит";
		if($kernel) $string .= "\tЯдро";
		if($install_time) $string .= "\tКогда установлен";
		$string .= "\n";
		
		foreach($result as $row) {
			foreach($row as $r) // !!!
{
				if($r == '') {
					$string .= "отсутсвует\t";
					continue;
				}
				
				$string .= "[" . $r . "]\t";
			}
			$string .= "\n";
		}
		
		return $string;
	}
}

?>
