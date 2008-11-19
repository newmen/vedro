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

class T_Link {
	private $url;
	private $param;

	function __construct(&$url, &$col_id)
	{
		$this->url = ((strpos($url, "?") !== false) ? $url . "&" : $url . "?") . $col_id . "=";
		$this->param = $col_id;
	}

	function Link_html(&$row, &$text)
	{
		if(!isset($row[$this->param])) throw new Exception("в SQL запросе нет столбца " . $this->param);
		return "<a href=\"" . $this->URL($row) . "\">" . $text . "</a>";
	}

	function URL(&$row) { return $this->url.$row[$this->param]; }
}

class T_Object {
	private $obj;
	private $re_param;

	function __construct(&$obj, &$re_col_id)
	{
		$this->obj = $obj;
		$this->re_param = $re_col_id;
	}

/*
	function __get($name)
	{
		switch($name)
		{
			case "obj" : return $this->$name;
		}
	}
*/
	function Method(&$param)
	{
		//if(!isset($this->obj->$param))
		//	throw new Exception("у объекта ".get_class($this->obj)." нет свойства ".$param);
		return $this->obj->$param;
	}

	function Reset(&$row)
	{
		$this->obj->Reset($row[$this->re_param]);
	}
}

?>
