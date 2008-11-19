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
class FormInsertSQL extends FormElementList {
	private $db;
	private $sql;
	
	function __construct(&$db, $sql, $elements=array())
	{
		$this->sql = new InsertSQL($sql);
		
		$this->db = &$db;
		$this->elements = $elements;

		$columns = $this->sql->InsertArray();
		if(count($columns) == 0)
			throw new Exception("Запрос на добавление ".$this->sql->SQL()." не может не содержать столбцов в которые следует добавлять информацию!");
		
		$table = $this->sql->Table();
		foreach($this->elements as $el)
			$el->SetName($table."_".$el->GetName());
		
		foreach($columns as $v)
		{
			if(isset($this->elements[$v])) continue;
			
			$this->elements[$v] = new FInput($table."_".$v, $v);
		}

	}
	
	// засовывает всё что изменилось в БД
	protected function Processing()
	{
		if(count($_GET) == 0 || !parent::Processing()) return false;
		
		$insert_sql = '';
		$cols = $this->sql->InsertArray();

		foreach($cols as $cname)
		{
			if(is_a($this->elements[$cname], "FCheckbox"))
				$this->elements[$cname]->SetValue((isset($_GET[$this->elements[$cname]->GetName()])) ? true : false);
			else
				$this->elements[$cname]->SetURLValue((isset($_GET[$this->elements[$cname]->GetName()])) ? $_GET[$this->elements[$cname]->GetName()] : '');

			$insert_sql .= "'".$this->elements[$cname]->GetValue()."', "; 
		}
		
		$insert_sql = substr($insert_sql, 0, -2); // убираем лишнюю запятую
		$sql = "INSERT INTO ".$this->sql->InsertIntoString()." VALUES (".$insert_sql.")";
		
//		echo $sql."<br />";
		$this->db->Query($sql);
		
		return true;
	}
}

?>