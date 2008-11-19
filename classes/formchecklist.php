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
require_once("include/functions.php");

class FormCheckList extends FormElementList {
	private $db;
	private $join_sql;
	
	private $where_array;
	private $join_array = array();
	
	private $inserted;

	function __construct(&$db, $list_sql, $join_sql, $inserted=false)
	{
		$this->db =& $db;
		$this->inserted = $inserted;
		
		$list_sql = new SelectSQL($list_sql);
		$this->join_sql = new SelectSQL($join_sql);
		
		if(count($list_sql->SelectArray()) != 2)
			throw new Exception("Запрос '".$list_sql->SQL()."' должен выбирать 2 столбца!");
		
		if(count($this->join_sql->SelectArray()) != 1)
			throw new Exception("Запрос '".$this->join_sql->SQL()."' должен выбирать 1 столбец!");
			
		if(!$this->join_sql->FromOneTable())
			throw new Exception("Запрос '".$this->join_sql->SQL()."' должен быть из одной таблицы!");
		
		$this->where_array = explode("=", $this->join_sql->WhereString());
		if(!$this->inserted)
		{
			if(count($this->where_array) != 2)
				throw new Exception("Запрос '".$this->join_sql->SQL()."' должен быть по одному условию!");
			
			if($result = $this->db->Query_Fetch($this->join_sql->SQL()))
			{
				foreach($result as $row)
					$this->join_array[] = $row[0];
				$this->join_array = array_flip($this->join_array);
			}		
		}
		
		foreach($this->where_array as $k=>$v) $this->where_array[$k] = trim($v);
		
		$table = Translit($this->join_sql->FromString());
				
		$result = $this->db->Query_Fetch($list_sql->SQL());
		foreach($result as $row)
		{
			$name = $table."_id_".$row[0];
			$this->elements[$name] = new FCheckbox($name, $row[1], (isset($this->join_array[$row[0]]) ? true : false));
		} 
	}
	
	/*
	 * Функция написана на скорую руку. Если возникнет ситуация, когда в таблице из которой производится
	 * join_sql много записей, могут происходить дополнительные тормоза.
	 * Во избежании этих тормозов, рекомендуется:
	 *  - Отказаться от массива new_join_sql в пользу уже существующего this->elements.
	 *  - Сделать проверку на неизменение массива галочек. Если изменения нет, то не делать запрос
	 * на удаление.
	 */
	protected function Processing()
	{
		if(count($_GET) == 0 || !parent::Processing()) return false;
		
		$join_id = $this->join_sql->SelectArray();
		$join_id = $join_id[0];
		
		$new_join_array = array();
		$delete_sql = '';
		foreach($this->elements as $name => $element)
		{
			if(!isset($_GET[$name]))
			{
				$element->SetValue(false);
				continue;
			}
			
			$pos = strrpos($name, "_");
			$id = substr($name, $pos+1);
			$new_join_array[] = $id;
			
			$element->SetURLValue(true);
			$delete_sql .= $join_id."!='".$id."' AND ";
		}
		
		if(!$this->inserted)
		{
			if(strlen($delete_sql) > 0) $delete_sql = substr($delete_sql, 0, -5);

			$delete_sql = "DELETE FROM ".$this->join_sql->FromString()
				. " WHERE ".$this->join_sql->WhereString()
//				. " AND ".$delete_sql;
				. ((strlen($delete_sql) > 0) ? " AND ".$delete_sql : '');
		
			//echo $delete_sql."<BR />";
			$this->db->Query($delete_sql);
		}
		
		if(count($new_join_array) == 0) return;
		
		if($this->inserted)
		{
			if(!$insert_id = $this->db->Insert_ID())
				throw new Exception("Ошибка определения последней добавленной записи в БД");
		}
		else $insert_id = $this->where_array[1];
		
		foreach($new_join_array as $jid)
		{
			if(isset($this->join_array[$jid])) continue;
			
			$insert_sql = "INSERT INTO ".$this->join_sql->FromString()." (".$this->where_array[0].", ".$join_id.") "
				. "VALUES ('".$insert_id."', '".$jid."')";
			
			//echo $insert_sql."<BR />";
			$this->db->Query($insert_sql);
		}
		
		return true;
	}
}
?>