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

require_once("classes/table_column.php");
require_once("classes/table_row.php");
require_once("classes/sql.php");

define("MAX_PAGES", 15);
define("SERIALDIR", CACHEDIR.'/tables/');

class TableExcept extends Exception {}

class Table {
	private $db;
	private $s_;
	
	private $sql;
	private $cols;
	private $badlines;
	private $name;
	private $width;
	
	private $theme_pref = '';
	
	private $show_pagesw = true;
	private $show_sql_cols = false;
	private $show_all_cols = false;
	private $show_bottom = false;
	private $show_autobottom = true;
	private $show_search = false;
	
	private $search_col = '';
	private $select_cols_set = false;
	private $total_records;
	
	function __construct(&$db, $name, $sql, $cols = array(), $badlines = array(), $width = 0)
	{
		$this->db =& $db;
		
		$this->name = $name;
		$this->sql = new SelectSQL($sql);
		$this->cols = $cols;
		$this->badlines = $badlines;
		$this->width = $width;

		$this->s_ =& $_SESSION[S_ID][$this->name];
	}
	
	function SetWidth($w) { $this->width = $w; }
	function SetTheme($theme_pref) { $this->theme_pref = $theme_pref . "_"; }
	function Show_PageSwitch($s) { $this->show_pagesw = $s; }
	function Show_SQLCols($s) { $this->show_sql_cols = $s; }
	function Show_AllCols($s) { $this->show_all_cols = $s; }
	function Show_Bottom($s) { $this->show_bottom = $s; }
	function Show_AutoBottom($s) { $this->show_autobottom = $s; }

	function Show_Search($col_id)
	{
		if($col_id === false) {
			$this->show_search = false;
			return;
		}
		
		$this->show_search = true;
		$this->search_col = $col_id;
	}

	function GetName() { return $this->name; }
	function GetThemePref() { return $this->theme_pref; }
	
	/*
	 * сначала реконструируим столбцы, сессию и SQL
	 * потом рисуем
	 */
	function Show()
	{	
		// слегка изменяем столбцы
		$sql_columns = $this->sql->SelectArray();
		foreach($sql_columns as $cname)
		{
			if(isset($this->cols[$cname]))
			{
				$this->cols[$cname]->SetIsSQL();
				continue;
			}
			
			// если включена опция отображения всех SQL столбцов - засовываем...
			if(!$this->show_sql_cols) continue;
			
			$this->cols[$cname] = new Column($cname, 1);
			$this->cols[$cname]->SetIsSQL();
		}
	
		// чекаем параметры сессии из пришедшего GET-a
		if($this->GETtoThisTable())
		{
			if(isset($_GET["order"])) $this->s_["order"] =& $_GET["order"];
			if(isset($_GET["sort"])) $this->s_["order_sort"] =& $_GET["sort"];
		}
		elseif(!isset($this->s_["order"]))
		{
			try {
				$order = $this->sql->OrderArray();
				$this->s_["order"] = $order["column"];
				$this->s_["order_sort"] = $order["type"];
			} catch(ErrorSQL $e) { }
		}

		// присобачиваем ордер
		if(isset($this->s_["order"]) && isset($this->s_["order_sort"]))
			$this->sql->SetOrderString($this->s_["order"]." ".$this->s_["order_sort"]);
		
		if($this->show_all_cols) $this->ColSelect();
		if($this->show_search) $this->Search();
		if($this->show_pagesw) $this->PageSwitcher();
		
		//echo "<h1>\"".$this->sql->SQL()."\"</h1><br />";

		// шлёпаем основную таблицу
		echo "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/js/hltable.js\"></script>\n";
		echo "<table id=\"Table_" . $this->name . "\" border=0 cellspacing=1 cellpadding=2"
			. (($this->width != 0) ? " width=" . $this->width : '')
			. ">\n"
			. "<tr class=\"c_" . $this->theme_pref . "theader\">\n"
			. "<th>#</th>";
		
		// шлёпаем шапки столбцов
		foreach($this->cols as $k=>$col)
			echo $col->Head($this, $k);
		
		echo "</tr>\n";
		
		// засовываем в таблицу инфу
		$n = $i = isset($this->s_["from"]) ? $this->s_["from"] : 0;
		$c = rand(0, 1);
		
		if(!$result = $this->db->Query_Fetch($this->sql->SQL())) {
			echo "<tr class=\"c_" . $this->theme_pref . "nodata\">"
				. "<td align=\"center\">" . (++$i) . "</td>"
				. "<td align=\"center\" colspan=" . count($this->cols) . ">нет данных</td>"
				. "</tr>\n";
			if(!$this->show_bottom) echo "</table>\n";
			return;
		}
		
		// шлёпаем строчки
		foreach($result as $row_key=>$row)
		{
			try {
				$badline = false;
				
				foreach($this->badlines as $col_id=>$r_line)
					if(($this->cols[$col_id]->Visible()
						|| !(is_a($this->cols[$col_id], 'Column_UserFunc') || is_subclass_of($this->cols[$col_id], 'Column_UserFunc')))
							&& $r_line->Compare($this->cols[$col_id]->Value($row, $col_id)))
					{
						$badline = true;
						break;
					}
				
				echo "<tr class=\"c_" . $this->theme_pref
					. (($badline) ? "badline" : "goodline")
					. ($c++ % 2 + 1) . "\" valign=\"center\">"
					. "<td align=\"center\">" . (++$i) . "</td>";
				
				foreach($this->cols as $c_id=>$col)
					echo $col->Body($row, $c_id);
				
				echo "</tr>\n";
			} catch(Exception $e) {
				echo "<tr class=\"c_" . $this->theme_pref . "nodata\">"
					. "<td align=\"center\">" . (++$i) . "</td>"
					. "<td align=\"left\" colspan=" . count($this->cols) . ">" . $e->getMessage()
					. "<br />" . $e->getTraceAsString() . "</td>" . "</tr>\n";
			
			}
		}
		
		if($this->show_bottom || ($this->show_autobottom && ($i - $n) > 50)) {
			echo "<tr class=\"c_" . $this->theme_pref . "theader\">\n" . "<th>#</th>";
			
			// шлёпаем столбцы
			foreach($this->cols as $k=>$col)
				echo $col->Head($this, $k);
			
			echo "</tr>\n";
			echo "</table>\n";
			
			if($this->show_pagesw) $this->PageSwitcher();
			if($this->show_all_cols) $this->ColSelect();
		}
		else
			echo "</table>\n";
			
		// симпатишная стыреная подсвечивалка (=
		echo "<script type=\"text/javascript\">\n"
		. "	highlightTableRows(\"Table_" . $this->name . "\", \"c_" . $this->theme_pref . "hover\");\n"
		. "</script>\n";
	}

	// если в GET-е пришла информация именно для этой таблицы
	private function GETtoThisTable()
	{
		// на этот раз без кеширования)
		return (isset($_GET["t_name"]) && $_GET["t_name"] == $this->name);
	}
	
	// статические функции для аякса
	private static function AddAlikeCompare(&$arr)
	{
		$arr['eq'] = 'равно';
		$arr['ne'] = 'не равно';
	}
	
	private static function AddMinMaxCompare(&$arr)
	{
		$arr['gt'] = 'больше';
		$arr['lt'] = 'меньше';
	}

	private static function AddTextLikeCompare(&$arr)
	{
		$arr['like_center'] = 'содержит';
		$arr['not_like_center'] = 'не содержит';
		$arr['eq'] = 'совпадает с';
		$arr['ne'] = 'не совпадает с';
		$arr['like_begin'] = 'начинается с';
		$arr['not_like_begin'] = 'не начинается с';
		$arr['like_end'] = 'заканчивается на';
		$arr['not_like_end'] = 'не заканчивается на';
	}

	static function GetSearchArray($table_name, $column_id, $row_id, $color, $theme_pref)
	{
		$filename = SERIALDIR.$table_name.'_'.$column_id;
		if(!$f = @fopen($filename, 'r'))
			return 'Невозможно прочитать файл '.$filename.' с сериализованным объектом';
		
		$unserial_column = unserialize(fread($f, filesize($filename)));
		fclose($f);

		$s_ =& $_SESSION[S_ID][$table_name]['search'];
		$return_arr = array('type_text' => '', 'value_text' => true);
		$value_arr = array();
		
		if(is_a($unserial_column, 'Column_Bool') || is_subclass_of($unserial_column, 'Column_Bool'))
		{
			self::AddAlikeCompare($value_arr);
			$return_arr['value_text'] = false;
		}
		elseif(is_a($unserial_column, 'Column_Number') || is_subclass_of($unserial_column, 'Column_Number')
			|| is_a($unserial_column, 'Column_Time') || is_subclass_of($unserial_column, 'Column_Time'))
		{
			self::AddAlikeCompare($value_arr);
			self::AddMinMaxCompare($value_arr);
		}
		elseif(is_a($unserial_column, 'Column_IP') || is_subclass_of($unserial_column, 'Column_IP'))
		{
			self::AddTextLikeCompare($value_arr);
			self::AddMinMaxCompare($value_arr);
		}
		else
		{
			self::AddTextLikeCompare($value_arr);
		}
	
		$return_arr['type_text'] = "<select style='width: 170px;' name='search_".$row_id."_type' "
			. "class=\"c_" . $theme_pref . "search".($color++ % 2 + 1)."\">\n";
		foreach($value_arr as $k=>$v)
			$return_arr['type_text'] .= "<option value='".$k."'"
				. ((isset($s_[$row_id]['type']) && $s_[$row_id]['type'] == $k) ? ' selected' : '')
				. ">".$v."</option>\n";
		$return_arr['type_text'] .= "</select>\n";
		
		if($return_arr['value_text'])
			$return_arr['value_text'] = "<input style='width: 170px;' type='text' name='search_".$row_id."_text' "
				. "class=\"c_" . $theme_pref . "search".($color % 2 + 1)."\" value='"
				. (isset($s_[$row_id]['text']) ? $s_[$row_id]['text'] : '')
				. "'>\n";
		else
		{
			$return_arr['value_text'] = "<select style='width: 170px;' name='search_".$row_id."_text' "
				. "class=\"c_" . $theme_pref . "search".($color % 2 + 1)."\">\n";
			$object_arr = $unserial_column->GetArray();
			foreach($object_arr as $k=>$v)
				$return_arr['value_text'] .= "<option value='".$k."'"
					. ((isset($s_[$row_id]['text']) && $s_[$row_id]['text'] == $k) ? ' selected' : '')
					. ">".$v."</option>\n";
			$return_arr['value_text'] .= "</select>\n";
		}
		
		return $return_arr;
	}
	
	private function SerializeColumn(&$k, &$col)
	{
		$filename = SERIALDIR.$this->name.'_'.$k;
		
		$alike = false;
		if(file_exists($filename))
		{
			if(!$f = @fopen($filename, 'r'))
				throw new TableExcept('Невозможно прочитать файл '.$filename.' с сериализованным объектом');
			
			$unserial_col = unserialize(fread($f, filesize($filename)));
			if($col == $unserial_col) $alike = true;
			
			fclose($f);
		}
		
		if($alike) return; 
		
		if(!$f = @fopen($filename, 'w'))
			throw new TableExcept('Невозможно записать файл '.$filename.' с сериализованным объектом');
		
		fwrite($f, serialize($col));
		fclose($f);
	}
	
	// панелька поиска
	private function Search()
	{
		// палим пришедший запрос, на предмет поиска
		if($this->GETtoThisTable())
		{
			// заполняем сессию на предмет поиска
			$search_unset = false;
			foreach($_GET as $k=>$v) {
				if(!ereg("^search_", $k)) continue;
				
				if(!$search_unset) {
					unset($this->s_["search"]);
					$search_unset = true;
					$this->s_["search_max_row_id"] = 0;
				}
				
				$els = explode("_", $k);
				if($els[1] == "separator") {
					$this->s_["search_separator"] = ($v == "or") ? "OR" : "AND";
					continue;
				}
				
				if($els[2] == 'text') $v = urldecode($v);
				
				$this->s_["search"][$els[1]][$els[2]] = $v;
				if($this->s_["search_max_row_id"] < $els[1]) $this->s_["search_max_row_id"] = $els[1];
			}
		}
		
		if(!isset($this->s_["search_separator"])) $this->s_["search_separator"] = "AND";
		
		// смотрим сессию на предмет поиска - если что - изменяем SQL
		$search_columns = array(); // массив, для хранения всех столбцов, по которым идёт поиск
		if(isset($this->s_["search"]))
		{
			$compare_sql = '';
			
			foreach($this->s_["search"] as $s)
			{
				if(count($s) < 3 || $s["text"] == '') continue;
				
				$search_columns[$s["column"]] = 1; // запоминаем, что по такому столбцу был поиск
				$compare = $this->cols[$s["column"]]->GetDbTableName($s["column"]);
				switch($s["type"])
				{
				case 'eq': // равно
					$compare .= "='";
					break;
				case 'ne': // неравно
					$compare .= "!='";
					break;
				case 'gt': // больше
					$compare .= ">'";
					break;
				case 'lt': // меньше$this->cols[$s["column"]]->
					$compare .= "<'";
					break;
				case 'like_center': // содержит
				case 'like_end': // заканчивается на
					$compare .= " LIKE '%";
					break;
				case 'not_like_center': // несодержит
				case 'not_like_end': // не заканчивается на
					$compare .= " NOT LIKE '%";
					break;
				case 'like_begin': // начинается с
					$compare .= " LIKE '";
					break;
				case 'not_like_begin': // не начинается с
					$compare .= " NOT LIKE '";
					break;
				}

				$compare .= $this->cols[$s["column"]]->RebuildValue($s["text"]);

				switch($s["type"])
				{
				case 'eq': // равно
				case 'ne': // неравно
				case 'gt': // больше
				case 'lt': // меньше
				case 'like_end': // заканчивается на
				case 'not_like_end': // не заканчивается на
					$compare .= "'";
					break;
				case 'like_center': // содержит
				case 'not_like_center': // несодержит
				case 'like_begin': // начинается с
				case 'not_like_begin': // не начинается с
					$compare .= "%'";
					break;
				}
				
				$compare_sql .= $compare . " " . $this->s_["search_separator"] . " ";
			}
			
			if($compare_sql != '')
			{
				$compare_sql = substr($compare_sql, 0, -(strlen($this->s_["search_separator"]) + 2));
				$this->sql->AddWhere("and", $compare_sql);
			}
		}

		if(!isset($this->s_["search"]))
		{
			$this->s_["search"][0] = array();
			$this->s_["search_max_row_id"] = 0;
		}
		
		// проверяем искали ли уже? если искали, то потом рисуем кнопку "отмена"
		$searched = false;
		foreach($this->s_["search"] as $s)
			if(isset($s["text"]) && $s["text"] != '')
			{
				$searched = true;
				break;
			}
		
		// ИЗО
		/*
		 * создаём форму и таблицу в ней
		 * в эту таблицу, потом яваскриптом будем засовывать строчки
		 */
		$c = rand(0, 1); // для цвета
		echo "<form action=\"\" method=\"GET\" style=\"margin: 0; padding: 0;\">"
			. "<input type=\"hidden\" name=\"t_name\" value=\"" . $this->name . "\">\n"
			. "<table id=\"t_search\" border=0 cellspacing=1 cellpadding=2" . (($this->width != 0) ? " width=" . $this->width : '') . ">\n"
			. "<tbody>"
			. "</tbody>"
			. "</table></form>";

		// яваскрипт для засовывания строчек в вышесозданную табличку
		echo "<script language=\"JavaScript\" type=\"text/javascript\">\n"
			. "var c = " . $c . ";\n"
			. "var d = document;\n"
			. "table = d.getElementById('t_search').getElementsByTagName('tbody')[0];\n"
			. "var max_row_id = " . $this->s_["search_max_row_id"] . ";\n"
			. "function DelRow(row_id)\n"
			. "{\n"
			. "	table.removeChild(d.getElementById('row' + row_id))\n"
			. "	sndReq('t_name=" . $this->name . "&search_line=del&row_id=' + row_id);\n"
			. "}\n"
//			. "function AddRow(id, column, type, text)\n"
			. "function AddRow(id, column)\n"
			. "{\n"
			. "	if(id != 0) c++;\n"
			. "	var row = d.createElement('tr');\n"
			. "	table.appendChild(row);\n"
			. "	row.id = 'row' + id;\n"
			. "	if(id == 0)\n"
			. "	{\n"
			. "		var td_search_text = d.createElement('td');\n"
			. "		row.appendChild(td_search_text);\n"
			. "		td_search_text.id = 't_search_text';\n"
			. "		td_search_text.className = 'c_" . $this->theme_pref . "search' + (++c % 2 + 1);\n"
			. "		td_search_text.innerHTML = 'Поиск по:&nbsp;"
			. "<label id=\"r_and\"><input type=\"radio\" class=\"c_" . $this->theme_pref . "search' + (c % 2 + 1) + '\" "
			. "name=\"search_separator\" value=\"and\" onClick=\"sndReq(\'t_name=" . $this->name . "&search_separator=and\');\""
			. ($this->s_["search_separator"] == "AND" ? " CHECKED" : "")
			. ">и&nbsp;&nbsp;</label>&nbsp;&nbsp;"
			. "<label id=\"r_or\"><input type=\"radio\" class=\"c_" . $this->theme_pref . "search' + (c % 2 + 1) + '\" "
			. "name=\"search_separator\" value=\"or\" onClick=\"sndReq(\'t_name=" . $this->name . "&search_separator=or\');\""
			. ($this->s_["search_separator"] == "OR" ? " CHECKED" : "")
			. ">или</label>';\n"
			. "	}\n"
			. "	var td_col = d.createElement('td');\n"
			. "	var td_type = d.createElement('td');\n"
			. "	var td_text = d.createElement('td');\n"
			. "	row.appendChild(td_col);\n"
			. "	row.appendChild(td_type);\n"
			. "	row.appendChild(td_text);\n"
			. "	td_col.align = 'center';\n"
			. "	td_col.className = 'c_" . $this->theme_pref . "search' + (++c % 2 + 1);\n"
			. "	td_col.innerHTML = '<table border=0 cellspacing=0 cellpadding=0>'\n"
			. "		 + '<tr class=\"c_" . $this->theme_pref . "search' + (c % 2 + 1) + '\"><td>Поле:&nbsp;</td>'\n"
			. "		 + '<td>'\n"
			. "		 + '<select id=\"search_' + id + '_column\" name=\"search_' + id + '_column\" "
			. "class=\"c_" . $this->theme_pref . "search' + (c % 2 + 1) + '\">'\n";
		
		// составляем выпадающий список возможных для поиска столбцов
		// затем, сериализуем, для дальнейшего обращения Аяксом
		foreach($this->cols as $k=>$col)
			if(($col->Visible() || isset($search_columns[$k])) && $col->IsSQL()
				&& !is_a($col, "Column_Custom") && !is_a($col, "Column_Button"))
			{
				echo "		 + '<option value=\"" . $k . "\""
					. (($k == $this->search_col) ? " selected" : "")
					. " onClick=\"sndReq(\'t_name=".$this->name."&search_column=".$k."&row_id=' + id + '&color=' + (c+1) + '&theme_pref=".$this->theme_pref."\')\">"
					. $col->GetName() . "</option>'\n";
				
				$this->SerializeColumn($k, $col);
			}
		
		echo "		 + '</select>'\n"
			. "		 + '</td></tr>'\n"
			. "		 + '</table>';\n"
//			. "	td_type.id = 'td_type_row' + id;\n"
			. "	td_type.align = 'center';\n"
			. "	td_type.className = 'c_" . $this->theme_pref . "search' + (++c % 2 + 1);\n"
			. "	td_type.innerHTML = '<table border=0 cellspacing=0 cellpadding=0>'\n"
			. "		 + '<tr class=\"c_" . $this->theme_pref . "search' + (c % 2 + 1) + '\"><td>Тип:&nbsp;</td>'\n"
			. "		 + '<td>'\n"
			. "		 + '<div id=\"".$this->name."_search_type_list_row' + id + '\"> </div>'\n"
			. "		 + '</td></tr>'\n"
			. "		 + '</table>';\n"
			. "	td_text.align = 'center';\n"
			. "	td_text.className = 'c_" . $this->theme_pref . "search' + (++c % 2 + 1);\n"
			. "	td_text.innerHTML = '<div id=\"".$this->name."_search_text_row' + id + '\"> </div>';\n"
			. "	var s_column = d.getElementById('search_' + id + '_column');\n"
			. "	s_column.value = column;\n"
			. "	var td_add;\n"
			. "	var td_del = d.createElement('td');\n"
			. "	td_del.align = 'center';\n"
			. "	sndReq('t_name=".$this->name."&search_column=' + column + '&row_id=' + id + '&color=' + (c+1) + '&theme_pref=".$this->theme_pref."');\n"
			. "	if(id == 0)\n"
			. "	{\n"
			. "		var td_search = d.createElement('td');\n"
			. "		td_add = d.createElement('td');\n"
			. "		row.appendChild(td_search);\n"
			. "		row.appendChild(td_add);\n"
			. "		row.appendChild(td_del);\n"
			. "		td_search.id = 't_search_bcol';\n"
			. "		td_search.align = 'center';\n"
			. "		td_search.className = 'c_" . $this->theme_pref . "search' + (++c % 2 + 1);\n"
			. "		td_search.innerHTML = '<input type=\"submit\" id=\"t_serv\" class=\"c_" . $this->theme_pref . "search' + (c % 2 + 1) + '\" '\n"
			. "			 + 'value=\"Найти\">'";
		
		if($searched)
			echo "		 + '<input type=\"button\" class=\"c_" . $this->theme_pref . "search' + ((c+1) % 2 + 1) + '\" value=\"Отменить\" '"
				. "			 + 'onClick=\"window.location=\'?t_name=" . $this->name . "&search_0_text=\';\">';";
		
		echo ";\n"
			. "		td_add.id = 't_search_badd';\n"
			. "		td_add.align = 'center';\n"
			. "		td_add.className = 'c_" . $this->theme_pref . "search' + (++c % 2 + 1);\n"
			. "		td_del.className = 'c_" . $this->theme_pref . "search' + (++c % 2 + 1);\n"
			. "		td_del.innerHTML = '<input type=\"button\" class=\"c_" . $this->theme_pref . "search' + (c % 2 + 1) + '\" "
			. "value=\"Убрать\" disabled>';\n"
			. "	}\n"
			. "	else {\n"
			. "		search_text = d.getElementById('t_search_text');\n"
			. "		search_text.rowSpan++;\n"
			. "		search_button = d.getElementById('t_search_bcol');\n"
			. "		search_button.rowSpan++;\n"
			. "		td_add = d.getElementById('t_search_badd');\n"
			. "		td_add.rowSpan++;\n"
			. "		row.appendChild(td_del);\n"
			. "		td_del.className = 'c_" . $this->theme_pref . "search' + (++c % 2 + 1);\n"
			. "		td_del.innerHTML = '<input type=\"button\" class=\"c_" . $this->theme_pref . "search' + (c % 2 + 1) + '\" "
			. "value=\"Убрать\" onClick=\"DelRow(' + id + ');\">';\n"
			. "	}\n"
			. "	td_add.innerHTML = '<input type=\"button\" value=\"Ещё\" class=\"c_" . $this->theme_pref . "search' + (c % 2 + 1) + '\" "
			. "onClick=\"++max_row_id; "
			. "sndReq(\'t_name=" . $this->name . "&search_line=add&row_id=' + (max_row_id+1) + '\'); "
			. "AddRow(' + (max_row_id+1) + ', \'" . $this->search_col . "\');\">';\n"
			. "}\n";

		foreach($this->s_["search"] as $i=>$s)
		{
			if(!isset($s["column"])) $s["column"] =& $this->search_col;
			
			echo "AddRow(" . $i . ", '" . $s["column"] . "');\n";
		}
		
		echo "</script>\n";
	}

	// панелька выбора отображаемых столбцов
	private function ColSelect()
	{
		if(!$this->select_cols_set)
		{
			// проверяем, не приехало ли выбранных столбцов со страницы
			if($this->GETtoThisTable())
			{
				$colunset = false;
				foreach($_GET as $k=>$v) {
					if(!ereg("^col_", $k)) continue;
					
					// приехал хотябы один - обнуляем старые выбранные столбцы	
					if(!$colunset) {
						unset($this->s_["cols"]);
						$colunset = true;
					}
					
					// суём выбранные столбцы в сессию
					$this->s_["cols"][substr($k, 4)] = $v;
				}
			}
			
			// изменяем столбцы на отображаемые/неотображаемые
			if(isset($this->s_["cols"]))
				foreach($this->cols as $k=>$col)
					$col->SetVisible(isset($this->s_["cols"][$k]));
			
			$this->select_cols_set = true;
		}
	
		// рисуем панельку
		$c = rand(0, 1);
		echo "<table border=0 cellspacing=1 cellpadding=2";
		if($this->width != 0) echo " width=" . $this->width;
		echo ">\n<tr class=\"c_" . $this->theme_pref . "colselect" . ($c % 2 + 1) . "\">"
			. "<td>Отображать:&nbsp;</td>"
			. "<form action=\"\" method=\"GET\">\n"
			. "<td align='center'>"
			. "<input type=\"hidden\" name=\"t_name\" value=\"" . $this->name . "\">\n";
		
		$sl = 0; // длинна выводимой строки
		foreach($this->cols as $k=>$col) {
			echo "<label id=\"column_" . $k . "\">"
				. "<input type=\"checkbox\" class=\"c_" . $this->theme_pref . "colselect" . ($c % 2 + 1)
				. "\" name=\"col_" . $k . "\" for=\"column_" . $k . "\" value=\"1\""
				. ($col->Visible() ? " CHECKED" : '')
				. ">"
				. $col->GetName()
				. "</label>\n";

			$sl += strlen($col->GetName())+3; // 3 символа на флажок
			if($sl > 100)
			{
				echo "<br />\n";
				$sl = 0;
			}
		}
		
		echo "</td>"
			. "<td align=\"center\">"
			. "<input type=\"submit\" class=\"c_" . $this->theme_pref . "colselect" . ($c % 2 + 1) . "\" "
			. "id=\"t_serv\" value=\"Применить\"></td>"
			. "</form>"
			. "</tr></table>\n";
	}
	
	// страницепереключатель
	private function PageSwitcher()
	{
		if(!isset($this->total_records))
		{
			// чекаем параметры сессии из пришедшего GET-a
			if($this->GETtoThisTable())
			{
				if(isset($_GET["from"])) $this->s_["from"] =& $_GET["from"];
				if(isset($_GET["limit"]) && is_numeric($_GET["limit"]) && $_GET["limit"] > 0)
					$this->s_["limit"] =& $_GET["limit"];
			}
			
			if(!isset($this->s_["from"]))
			{
				try {
					$limit = $this->sql->LimitArray();
					$this->s_["from"] = $limit["from"];
				}
				catch(ErrorSQL $e) {
					$this->s_["from"] = 0;
				}
			}
			
			if(!isset($this->s_["limit"]))
			{
				try {
					if(!isset($limit)) $limit = $this->sql->LimitArray();
					$this->s_["limit"] = $limit["num"];
				}
				catch(ErrorSQL $e) {
					$this->s_["limit"] = 50;
				}
			}
	
			$select_array = $this->sql->SelectArray();
			$count_param = isset($this->cols[$select_array[0]])
				? $this->cols[$select_array[0]]->GetDBTableName($select_array[0])
				: $select_array[0];
			$count_sql = "SELECT COUNT(" . $count_param . ") FROM ".$this->sql->Table(); //$this->sql->FirstTable();
			try {
				$count_sql .= " WHERE ".$this->sql->WhereString();
			} catch(ErrorSQL $e) { }
			$row = $this->db->Query_Fetch_Array($count_sql);
			$this->total_records = $row[0];
			
			// если посчитанных записей меньше, чем то, на какой странице находимся - обнуляем
			if($this->s_["from"] > $this->total_records) $this->s_["from"] = 0;
			
			$this->sql->SetLimitString($this->s_["from"].",".$this->s_["limit"]);
		}
		
		$pages_num = ceil($this->total_records / $this->s_["limit"]);
		
		// рисуем
		$c = rand(0, 1); // для цвета
		echo "<table border=0 cellspacing=1 cellpadding=2"
			. (($this->width != 0) ? " width=" . $this->width : '')
			. ">\n<tr>";

		$str = ($pages_num == 1) ? 'одна страница [' . $this->total_records . ']' : 'первая';
		echo "<td class=\"c_" . $this->theme_pref . "pagesw" . ($c++ % 2 + 1) . "\">"
			. (($this->s_["from"] != 0) ? "<a href=\"?t_name=" . $this->name . "&from=" . ($this->s_["from"] - $this->s_["limit"]) . "\">&lt;&lt; пред.</td>"
				. "<td class=\"c_" . $this->theme_pref . "pagesw" . ($c++ % 2 + 1) . "\"><a href=\"?t_name=" . $this->name . "&from=0\">" . $str . "</a>"
			: $str) . "</td>\n";
		
		$pmax = MAX_PAGES - 2;
		$n = ceil($this->s_["from"] / $this->s_["limit"]) + 1; // номер страницы
		if($n > $pmax / 2 + 1) {
			echo "<td class=\"c_" . $this->theme_pref . "pagesw" . ($c++ % 2 + 1) . "\">...</td>";
			$n -= round($pmax / 2) - 1;
		}
		else $n = 2;
			
		// постранично пробегаем
		for($i = 0; $i < $pmax; $i++) {
			if($n >= $pages_num) break;
			
			$pnum = ($n - 1) * $this->s_["limit"];
			echo "<td class=\"c_" . $this->theme_pref . "pagesw" . ($c++ % 2 + 1) . "\">"
				. (($pnum != $this->s_["from"]) ? "<a href=\"?t_name=" . $this->name . "&from=" . $pnum . "\">" . $n . "</a>" : $n) . "</td>\n";
			
			$n++;
		}
		
		if($n < $pages_num)
			echo "<td class=\"c_" . $this->theme_pref . "pagesw" . ($c++ % 2 + 1) . "\">...</td>";
		
		if($pages_num > 1) {
			$str = "последняя (" . $pages_num . ") [" . $this->total_records . "]";
			echo "<td class=\"c_" . $this->theme_pref . "pagesw" . ($c++ % 2 + 1) . "\">"
				. (($this->s_["from"] < $this->total_records - $this->s_["limit"])
					? "<a href=\"?t_name=" . $this->name . "&from=" . ($pages_num - 1) * $this->s_["limit"] . "\">"
						. $str . "</a>"
						. "</td>"
						. "<td class=\"c_" . $this->theme_pref . "pagesw" . ($c++ % 2 + 1) . "\">"
						. "<a href=\"?t_name=" . $this->name . "&from=" . ($this->s_["from"] + $this->s_["limit"]) . "\">след. &gt;&gt;\n"
					: $str)
				. "</td>\n";
		}
		
		// шлёпаем переключатель "по сколько показывать"
		echo "<td class=\"c_" . $this->theme_pref . "pagesw" . ($c++ % 2 + 1) . "\"></td>\n"
		    . "<td class=\"c_" . $this->theme_pref . "pagesw" . ($c % 2 + 1) . "\" width=300>\n"
		    . "<center><table class=\"c_" . $this->theme_pref . "pagesw" . ($c % 2 + 1) . "\" border=0 cellspacing=0 cellpadding=0>\n"
		    . "<form action=\"\" method=\"GET\">\n"
		    . "<tr>\n"
		    . "<td>&nbsp;показывать по&nbsp;<input type=\"hidden\" name=\"t_name\" value=\"" . $this->name . "\"></td>\n"
		    . "<td><input class=\"c_" . $this->theme_pref . "pagesw" . ($c % 2 + 1) . "\" type=\"text\" name=\"limit\" size=5 maxlength=5 value=\"" . $this->s_["limit"] . "\"></td>\n"
		    . "<td><input class=\"c_" . $this->theme_pref . "pagesw" . ($c % 2 + 1) . "\" type=\"submit\" id=\"t_serv\" value=\">>\"></td>\n"
		    . "<td><input class=\"c_" . $this->theme_pref . "pagesw" . ($c % 2 + 1) . "\" type=\"button\" value=\"все\" onClick=\"GoURL('?t_name=".$this->name."&limit=".ceil($this->total_records*1.5)."');\"></td>\n"
		    . "</tr>\n"
		    . "</form>\n"
		    . "</table></center>\n"
		    . "</td>\n"
		    . "</tr>\n"
		    . "</table>\n";
	}
}

?>
