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

require_once ("classes/table_base_types.php");

class Column {
	protected $name;
	protected $visible;
	protected $align;
	protected $width;
	
	private $is_sql = false;
	private $db_tablename = '';
	protected $link;
	
	private $s_;
	private $head_str;

	function __construct($name, $visible = true, $align = 'center', $width = false)
	{
		$this->name = $name;
		$this->visible = $visible;
		$this->align = $align;
		$this->width = $width;
	}

	function SetLink($link, $param) { $this->link = new T_Link($link, $param); }
	
	function IsSQL() { return $this->is_sql; }
	function SetIsSQL() { $this->is_sql = true; }
	
	function Visible() { return $this->visible; }
	function SetVisible($visible) { $this->visible = ($visible == true); }
	
	function GetName() { return $this->name; }
	
	function GetDBTableName($col) { return ($this->db_tablename != '') ? $this->db_tablename.'.'.$col : $col; }
	function SetDBTableName($db_tname) { $this->db_tablename = $db_tname; }
	
	function Value(&$row, &$id) { return $row[$id]; }
	function RebuildValue(&$value) { return $value; }

	function Head(&$table, &$col_id)
	{
		if(!$this->visible) return; 
		if(isset($this->head_str)) return $this->head_str;

		if(!isset($this->s_)) $this->s_ =& $_SESSION[S_ID][$table->GetName()];
		
		$this->head_str = "<th"
			. ((isset($this->s_["order"]) && $this->s_["order"] == $col_id)
					? " class=\"c_" . $table->GetThemePref() . "sort\"" : "")
			. (($this->width) ? " width=\"" . $this->width . "\"" : "") . ">";
		
		if($this->is_sql)
		{
			$sort_arrow_up = 'arrowu';
			$sort_arrow_down = 'arrowd';
			if(isset($this->s_["order"]) && isset($this->s_["order_sort"]) && $this->s_["order"] == $col_id)
				if($this->s_["order_sort"] == "asc") $sort_arrow_down .= '_selected';
				else $sort_arrow_up .= '_selected';
		
			$script = "?t_name=" . $table->GetName() . "&order=" . $col_id;
			$this->head_str .= "<nobr>&nbsp;<a href=\"".$script."&sort=asc\">"
				. "<img src=\"/templates/img/".$sort_arrow_down.".png\" height=13 border=0></a>&nbsp;"
				. "<a href=\"" . $script . "&sort="
				. ((isset($this->s_["order"]) && isset($this->s_["order_sort"])
					&& $this->s_["order"] == $col_id && $this->s_["order_sort"] == "asc")
						? "desc" : "asc")
				. "\">"
				. $this->name . "</a>"
				. "&nbsp;<a href=\"".$script."&sort=desc\">"
				. "<img src=\"/templates/img/".$sort_arrow_up.".png\" height=13 border=0></a></nobr>&nbsp;\n";
		}
		else $this->head_str .= $this->name;
		$this->head_str .= "</th>";
		
		return $this->head_str;
	}

	function Body(&$row, &$col_id)
	{
		if(!$this->visible) return; 
		$str = "<td align=\"" . $this->align . "\">"
			. (isset($this->link)
				? $this->link->Link_html($row, $this->BodyString($row, $col_id))
				: $this->BodyString($row, $col_id))
			. "</td>";
		
		return $str;
	}

	protected function BodyString(&$row, &$col_id) { return $this->Value($row, $col_id); }
}

class Column_Button extends Column {
	private $value;

	function __construct($name, $link, $link_param, $value, $visible=true, $align='center', $width=false)
	{
		parent::__construct($name, $visible, $align, $width);
		parent::SetLink($link, $link_param);
		$this->value = $value;
	}
	
	function Value(&$row, &$id)
	{
		throw new Exception('Столбец с кнопками не может возвращать значение');
	}
	
	function Body(&$row, &$col_id)
	{
		if(!$this->visible) return; 
		return "<td align=\"" . $this->align . "\">"
			. $this->BodyString($row, $col_id)
			. "</td>";
	}
	
	protected function BodyString(&$row, &$col_id)
	{
		return "<input type='button' value='".$this->value."' "
			. "onClick=\"GoURL('".$this->link->URL($row)."')\" />";
	}
}

/*
 * столбец с любым пользовательским HTML
 * заменяет вхождения [[column_name]] на значение соответсвующих выдраных из базы столбцов
 */
class Column_Custom extends Column {
	private $html;

	function __construct($name, $html, $visible = true, $align = 'center', $width = false)
	{
		parent::__construct($name, $visible, $align, $width);
		$this->html = $html;
	}
	
	function Value(&$row, &$id) { return $this->BodyString($row, $col_id); }
		
	protected function BodyString(&$row, &$col_id)
	{
		$arr_html = preg_split("/\[\[([^\]]*)\]\]/", $this->html, -1, PREG_SPLIT_DELIM_CAPTURE);
		$result = '';
		
		foreach($arr_html as $k=>$v)
		{
			if($k % 2 == 0) {
				$result .= $v;
				continue;
			}
			
			$result .= isset($row[$v]) ? $row[$v] : '';
		}
		
		return $result;
	}
}

class Column_UserFunc extends Column {
	private $func;
	private $args;
	
	function __construct($name, $func, $args, $visible = true, $align = 'center', $width = false)
	{
		parent::__construct($name, $visible, $align, $width);
		$this->func = $func;
		$this->args = $args;
	}
	
	function Value(&$row, &$id) { return $this->BodyString($row, $id); }
		
	protected function BodyString(&$row, &$col_id)
	{
		$param_str = '';
		foreach($this->args as $a)
			$param_str .= ', "'.(isset($row[$a]) ? $row[$a] : $a).'"';
		
		eval('$result = call_user_func($this->func'.$param_str.');');
		return $result;
	}
}

class Column_Number extends Column {
	private $precision;
	
	function __construct($name, $precision=2, $visible = true, $align = 'center', $width = false)
	{
		parent::__construct($name, $visible, $align, $width);
		$this->precision = $precision;
	}
	
	protected function BodyString(&$row, &$col_id) { return round($row[$col_id], $this->precision); }
}

class Column_Time extends Column {
	private $format;
	
	function __construct($name, $visible = true, $align = 'center', $width = false, $format='d.m.Y H:i:s')
	{
		parent::__construct($name, $visible, $align, $width);
		$this->format = $format;
	}
	
	function SetFormat($format) { $this->format = $format; }
	
	function Value(&$row, &$id) { return $this->BodyString($row, $id); }
	function RebuildValue(&$value) { return strtotime($value); }
	
	protected function BodyString(&$row, &$col_id) { return date($this->format, $row[$col_id]); }
}

class Column_IP extends Column {
	function GetDBTableName($col) { return 'inet_ntoa('.parent::GetDBTableName(&$col).')'; }

	protected function BodyString(&$row, &$col_id) { return long2ip($row[$col_id]); }
}

class Column_Bool extends Column {
	protected $value_arr = array();
	
	function __construct($name, $yes_str='Да', $no_str='Нет', $visible = true, $align = 'center', $width = false)
	{
		parent::__construct($name, $visible, $align, $width);
		$this->value_arr[0] = $no_str;
		$this->value_arr[1] = $yes_str;
	}
	
	function GetArray() { return $this->value_arr; }
	
	protected function BodyString(&$row, &$col_id)
	{
		return (isset($this->value_arr[$row[$col_id]]))
			? $this->value_arr[$row[$col_id]] : $row[$col_id];
	}
}

class Column_Array extends Column_Bool {
	private $as_value = false;
	function __construct($name, $values, $visible = true, $align = 'center', $width = false)
	{
		$this->name = $name;
		$this->visible = $visible;
		$this->align = $align;
		$this->width = $width;
		
		$this->value_arr = $values;
	}
	
	function AsValue() { $this->as_value = true; }
	
	function Value(&$row, $id) {
		return($this->as_value) ? $this->value_arr[$row[$id]] : parent::Value($row, &$id);
	}
}

class Column_List extends Column_Array {
	function __construct(&$db, $sql, $name, $visible = true, $align = 'center', $width = false)
	{
		$this->name = $name;
		$this->visible = $visible;
		$this->align = $align;
		$this->width = $width;

		if(!$result = $db->Query_Fetch($sql)) return;
		foreach($result as $row) $this->value_arr[$row[0]] = $row[1];
	}
}

?>
