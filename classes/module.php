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

require_once ("include/functions.php");

class ModuleExcept extends Exception {}

class Module {
	protected $db;
	
	protected $id;
	protected $name;
	protected $kernel;
	protected $path;
	private $install_time;

	function __construct(&$db, $id = false)
	{
		$this->db = &$db;
		
		if(!$id) return;
		
		if(!$row = $this->db->Query_Fetch_Assoc("SELECT name, kernel, path, install_time FROM modules WHERE id=" . $id))
			throw new ModuleExcept("не найдено модуля с id - " . $id);
		
		$this->id = $id;
		$this->name = $row["name"];
		$this->kernel = $row["kernel"];
		$this->path = $row["path"];
		$this->install_time = $row["install_time"];
	}

	function __get($name)
	{
		switch($name) {
		case "id":
		case "name":
		case "kernel":
		case "path":
		case "depend_modules_list":
			return $this->$name;
		default:
			return false;
		}
	}

	function Reset($id)
	{
		$this->__construct($this->db, $id);
	}
}

?>
