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

class SysGroupError extends Exception {}

class System_Group {
	protected $db;
	
	protected $id;
	protected $name;
	protected $is_deleted;

	function __construct(&$db, $id = false)
	{
		$this->db = &$db;
		
		if(!$id) {
			$this->id = 0;
			$this->name = '';
			$this->is_deleted = false;
			return;
		}
		
		if(!$row = $this->db->Query_Fetch_Assoc("SELECT name, is_deleted FROM groups WHERE id=" . $id)) throw new SysGroupError("нет группы с id=" . $id);
		
		$this->id = $id;
		$this->name = $row["name"];
		$this->is_deleted = ($row["is_deleted"] == 0) ? false : true;
	}

	function __get($name)
	{
		switch($name) {
		case "id":
		case "name":
		case "is_deleted":
			return $this->$name;
		default:
			return false;
		}
	}

	function Reset($id)
	{
		$this->__construct(&$this->db, $id);
	}
}

?>
