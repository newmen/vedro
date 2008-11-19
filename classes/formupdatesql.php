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

require_once("classes/formelementlist.php");
require_once("classes/sql.php");

// класс актуален для работы с одной таблицей
class FormUpdateSQL extends FormElementList {
	private $db;
	private $sql;
	
	private $checkboxs = array();

	function __construct(&$db, $sql, $elements=array())
	{
		$this->sql = new SelectSQL($sql);
		
		if(!$this->sql->LimitValid01())
			throw new Exception("Запрос '".$this->sql->SQL()."' должен быть на одно первое поле!");

		if(!$this->sql->FromOneTable())
			throw new Exception("Запрос '".$this->sql->SQL()."' не может быть из нескольких таблиц!");
			
		$this->db = &$db;
		$this->elements = $elements;
		$table = $this->sql->FromString();
		
		foreach($this->elements as $name=>$element)
		{
			$element->SetName($table."_".$element->GetName());
			if(is_a($element, "FCheckbox"))
				$this->checkboxs[$name] = $element;
		}

		$row = $this->db->Query_Fetch_Assoc($this->sql->SQL());
		//var_dump($this->sql->SQL(), $row);
		foreach($row as $k=>$v)
		{
			if(isset($this->elements[$k])) $this->elements[$k]->SetValue($v);
			else $this->elements[$k] = new FInput($k, $k, $v, $k);
		}
		//var_dump($this->elements);
	}
	
	// засовывает всё что изменилось в БД
	protected function Processing()
	{
		if(count($_GET) == 0 || !parent::Processing()) return false;
		
		$sql_set = '';
		$cols = array_flip($this->sql->SelectArray());

		foreach($this->elements as $name=>$element)
		{
			if(!isset($_GET[$element->GetName()])) continue;
			$element->SetURLValue($_GET[$element->GetName()]);
			if(!isset($cols[$name]) || !$element->Changed()) continue;
			$sql_set .= $name."='".$element->GetValue()."', "; 
		}
		
		foreach($this->checkboxs as $name=>$cbx)
		{
			if($cbx->Changed() || isset($_GET[$cbx->GetName()])) continue;
			$cbx->SetURLValue(false);
			if(!$cbx->Changed()) continue;
			$sql_set .= $name."='".$cbx->GetValue()."', ";
		}
		
		if(strlen($sql_set) == 0) return;
		$sql_set = substr($sql_set, 0, -2); // убираем лишнюю запятую
		$sql = "UPDATE ".$this->sql->FromString()." SET ".$sql_set." "
			. "WHERE ".$this->sql->WhereString();
		
		//echo $sql;
		$this->db->Query($sql);
		
		return true;
	}
}

?>