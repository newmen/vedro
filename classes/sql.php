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

// классы для работы с SQL запросами

class ErrorSQL extends Exception { }

abstract class SQL {
	protected $sql;
	protected $original_sql;
	
	function __construct($sql)
	{
		$this->original_sql = $sql;
		$this->sql = strtolower($sql);
	}
	
	function SQL() { return $this->original_sql; }
	abstract function Columns();
	abstract function Table();
}

class SelectSQL extends SQL {
	private $select_string;
	private $select_array;
	private $from_string;
	private $where_string;
	private $order_string;
	private $order_array;
	private $limit_string;
	private $limit_array;
	
	private $changed = false;
	
	function SQL()
	{
		if(!$this->changed) return $this->original_sql;
		
		$sql = "SELECT ".$this->SelectString()." FROM ".$this->FromString();
		try {
			$sql .= " WHERE ".$this->WhereString();
		} catch(ErrorSQL $e) { }
		
		try {
			$sql .= " ORDER by ".$this->OrderString();
		} catch(ErrorSQL $e) { }
		
		try {
			$sql .= " LIMIT ".$this->LimitString();
		} catch(ErrorSQL $e) { }
		
		$this->sql = strtolower($sql);
		return $this->original_sql = $sql;
	}
	
	function Columns() { return $this->SelectArray(); }
	function SelectString()
	{
		if(isset($this->select_string)) return $this->select_string;
		
		if(($select_pos = strpos($this->sql, "select ")) === false)
			throw new ErrorSQL("В запросе на выборку '".$this->original_sql."' нет SELECT!");
		
		if(!$from_pos = strpos($this->sql, " from "))
			throw new ErrorSQL("В запросе на выборку '".$this->original_sql."' нет FROM!");
		
		return $this->select_string = trim(substr($this->original_sql, $select_pos+7, $from_pos-($select_pos+7)));
	}
	
	function SelectArray()
	{
		if(isset($this->select_array)) return $this->select_array;
		
		$this->SelectString();
		if($this->select_string == '*')
			throw new ErrorSQL("В запросе на выборку '".$this->original_sql.
				" необходимо явно указать столбцы, по которым идёт выборка");
		
		$this->select_array = explode(",", $this->select_string);
		foreach($this->select_array as $k=>$v)
			$this->select_array[$k] = trim((($pos = strpos(strtolower($v), " as ")) !== false) ? substr($v, $pos+4) : $v);
		
		return $this->select_array;
	}

	function Table() { return FromString(); }	
	function FromString()
	{
		if(isset($this->from_string)) return $this->from_string;
		
		if(!$from_pos = strpos($this->sql, " from "))
			throw new ErrorSQL("В запросе на выборку '".$this->original_sql."' нет FROM!");
		
		$sql = substr($this->original_sql, $from_pos+6);
		if($pos = strpos(strtolower($sql), " where ")) $sql = substr($sql, 0, $pos);
		elseif($pos = strpos(strtolower($sql), " order by ")) $sql = substr($sql, 0, $pos);
		elseif($pos = strpos(strtolower($sql), " limit ")) $sql = substr($sql, 0, $pos);
		
		return $this->from_string = trim($sql);
	}
	
	function FirstTable()
	{
		$this->FromString();
		
		if($pos = strpos(strtolower($this->from_string), 'join'))
		{
			$arr = explode(" ", substr($this->from_string, 0, $pos));
			return $arr[0];
		}
		
		return $this->from_string;
	}
	
	function FromOneTable()
	{
		$this->FromString();
		
		if(ereg(",", $this->from_string) || ereg(" join ", strtolower($this->from_string))) return false;
		else return true;
	}
	
	function WhereString()
	{
		if(isset($this->where_string)) return $this->where_string;

		if(!$where_pos = strpos($this->sql, " where "))
			throw new ErrorSQL("В запросе на выборку '".$this->original_sql."' нет WHERE!");
		
		$sql = substr($this->original_sql, $where_pos+7);
		if($pos = strpos(strtolower($sql), " order by ")) $sql = substr($sql, 0, $pos);
		elseif($pos = strpos(strtolower($sql), " limit ")) $sql = substr($sql, 0, $pos);
		
		return $this->where_string = trim($sql);
	}
	
	function AddWhere($compare, $addstring)
	{
		try {
			$this->WhereString();
			$this->where_string .= ' '.(($compare != 'or') ? 'and' : 'or').' ('.$addstring.')';
		} catch(ErrorSQL $e) {
			$this->where_string = $addstring;
		}
		
		$this->changed = true;
	}
	
	function OrderString()
	{
		if(isset($this->order_string)) return $this->order_string;
	
		if(!$order_pos = strpos($this->sql, " order by "))
			throw new ErrorSQL("В запросе на выборку '".$this->original_sql."' нет ORDER BY!");
		
		$sql = substr($this->original_sql, $order_pos+10);
		if($pos = strpos(strtolower($sql), " limit "))
			$sql = substr($sql, 0, $pos);
		
		return $this->order_string = trim($sql);
	}
	
	function SetOrderString($str)
	{
		$this->order_string = $str;
		$this->changed = true;
	}
	
	function OrderArray()
	{
		if(isset($this->order_array)) return $this->order_array;
		
		$this->OrderString();
		if(ereg(",", $this->order_string))
			throw new ErrorSQL("Множественная сортировка SQL не поддерживается (".$this->original_sql.")!");
		
		$order_array = explode(" ", $this->order_string);
		if(count($order_array) > 2)
			throw new ErrorSQL("Ошибка при определении сортировки в '".$this->order_sql."'");
		
		$this->order_array["column"] = $order_array[0];
		$this->order_array["type"] = (count($order_array) == 2) ? $order_array[1] : "asc";
		
		return $this->order_array;
	}

	function LimitString()
	{
		if(isset($this->limit_string)) return $this->limit_string;
		
		if(!$limit_pos = strpos($this->sql, " limit "))
			throw new ErrorSQL("В запросе на выборку '".$this->original_sql."' нет LIMIT!");
		
		$this->limit_string = substr($this->sql, $limit_pos+7);
		if($pos = strrpos($this->limit_string, ';'))
			$this->limit_string = substr($this->limit_string, 0, $pos);
		
		return $this->limit_string;
	}
	
	function SetLimitString($str)
	{
		$this->limit_string = $str;
		$this->changed = true;
	}
	
	function LimitArray()
	{
		if(isset($this->limit_array)) return $this->limit_array;
		
		$this->LimitString();
		$limit_array = explode(",", $this->limit_string);
		if(count($limit_array) == 1 || count($limit_array) > 2)
			throw new ErrorSQL("Ошибка определения лимита (".$this->sql.")");
		
		$this->limit_array["from"] = $limit_array[0];
		$this->limit_array["num"] = $limit_array[1];
		
		return $this->limit_array;
	}
	
	function LimitValid01()
	{
		$this->LimitArray();
		
		return ($this->limit_array["from"] == 0 && $this->limit_array["num"] == 1);
	}
}

class InsertSQL extends SQL {
	private $insertinto_sql;
	private $insertinto_array;
	private $tablename;
	
	function Columns() { return $this->InsertArray(); }
	
	function InsertArray()
	{
		if(isset($this->insertinto_array)) return $this->insertinto_array;

		$this->InsertIntoString();
		
		if(!($skop_pos = strpos($this->insertinto_sql, "(")) || !($skcl_pos = strpos($this->insertinto_sql, ")")))
			throw new ErrorSQL("Ошибка выборки добавляемых столбцов в запросе ".$this->original_sql);
		
		$columns_string = substr($this->insertinto_sql, $skop_pos+1, $skcl_pos-($skop_pos+1));
		$this->insertinto_array = explode(",", $columns_string);
		foreach($this->insertinto_array as $k=>$v) $this->insertinto_array[$k] = trim($v);
		
		return $this->insertinto_array;
	}
	
	function Table()
	{
		if(isset($this->table)) return $this->table;
		
		$this->InsertIntoString();
		
		$this->tablename = trim(($skop_pos = strpos($this->insertinto_sql, "("))
			? substr($this->insertinto_sql, 0, $skop_pos) : $this->insertinto_sql);
		
		if(ereg(" ", $this->tablename))
			throw new ErrorSQL("В запросе на выборку ".$this->sql
				. " неверное название таблицы: '".$this->tablename."'!");

		return $this->tablename;
	}
	
	function InsertIntoString()
	{
		if(isset($this->insertinto_sql)) return $this->insertinto_sql;
	
		if(($insert_pos = strpos($this->sql, "insert into ")) === false)
			throw new ErrorSQL("В запросе на добавление ".$this->original_sql." отсутствует INSERT INTO!");
		
		if(!$values_pos = strpos($this->sql, " values "))
			throw new ErrorSQL("В запросе на добавление ".$this->original_sql." отсутсвует VALUES!");
		
		return $this->insertinto_sql = substr($this->original_sql, $insert_pos+12, $values_pos-($insert_pos+12));
	}
}

?>
