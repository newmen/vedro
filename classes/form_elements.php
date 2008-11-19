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

global $f_c; // для цвета
$f_c = rand(1,2);

interface iFElement {
	function AsString();
	function Show();
}

class FBlock implements iFElement {
	private $content;
	
	function __construct($content)
	{
		$this->content = $content;
	}
	
	function AddContent($content)
	{
		$this->content .= $content;
	}
	
	function AsString()
	{
		global $f_c;
		$str = "<tr class='f_line".(++$f_c % 2)."'><td colspan=2 align='center'>"
			. $this->content
			. "</td></tr>";
		
		return $str;
	}
	
	function Show()
	{
		echo $this->AsString();
	}
}

abstract class FBaseElement implements iFElement {
	protected $name;
	protected $value;
	protected $id;
	
	protected $changed = false;

	function __construct($name, $value='', $id='')
	{
		$this->name = $name;
		$this->value = $value;
		$this->id = $id;
	}
	
	function Changed() { return $this->changed; }
	function GetName() { return $this->name; }
	function SetName($name) { $this->name = $name; }
	function GetValue() { return $this->value; }
	function SetValue($value) { $this->value = $value; }
	function SetURLValue($value)
	{
		$value = addslashes(urldecode($value));
		if($this->value == $value) return;
		$this->value = $value;
		$this->changed = true;
	}
	
	function AsString()
	{
		return $this->ShowElement();
	}
	
	function Show()
	{
		echo $this->AsString();
	}
	
	abstract protected function ShowElement();
}

class FHidden extends FBaseElement {
	protected function ShowElement()
	{
		return "<input type='hidden' id='".$this->id."' "
			 . "name='".$this->name."' "
			 . "value='".$this->value."'>";
	}	
}

abstract class FElement extends FBaseElement {
	protected $desc;

	function __construct($name, $desc='', $value='', $id='')
	{
		parent::__construct($name, $value, $id);
		$this->desc = $desc;
	}
	
	function AsString()
	{
		global $f_c;
		$str = "<tr class='f_line".(++$f_c % 2)."'><td>"
			. $this->desc."&nbsp;</td>\n"
			. "<td>"
			. $this->ShowElement()
			. "</td></tr>\n";
		
		return $str;
	}
}

class FCheckbox extends FElement {
	function __construct($name, $desc='', $value=false, $id='')
	{
		$value = ($value) ? true : false;
		parent::__construct($name, $desc, $value, $id);
	}

	function Checked() { return $this->value; }
	function GetValue() { return $this->value ? 1 : 0; }
	function SetValue($value) { $this->value = ($value) ? true : false; }
	function SetURLValue($value)
	{
		$value = ($value) ? true : false;
		if($this->value === $value) return;
		$this->value = $value;
		$this->changed = true;
	}

	function AsString()
	{
		global $f_c;
		return "<tr class='f_line".(++$f_c % 2)."'><td colspan=2 align='center'>"
			. "<label for='".$this->id."'>"
			. $this->ShowElement()
			. $this->desc
			. "</label>"
			. "</td></tr>\n";
	}

	protected function ShowElement()
	{
		global $f_c;
		return "<input class='f_line".($f_c % 2)."' type='checkbox' id='".$this->id."' "
			. "name='".$this->name."' "
//			. "value='".$this->value."'"
			. "value='1'"
			. (($this->value) ? " checked" : '')
			. ">\n";
	}
}

class FSelect extends FElement {
	private $selected_array = array();
	
	function __construct(&$db, $sql, $name, $desc='', $value=0, $id='', $user_func='')
	{
		parent::__construct($name, $desc, $value, $id);
	
		if(!$result = $db->Query_Fetch($sql)) return;
		
		foreach($result as $row)
			$this->selected_array[$row[0]] =
				($user_func != '') ? call_user_func($user_func, $row[1]) : $row[1];
	}
	
	protected function ShowElement()
	{
		global $f_c;
		$str = "<select class='f_line".($f_c % 2)."' id='".$this->id."' "
			. "name='".$this->name."'>\n";
		
		if($this->value == 0) $str .= "<option value='0'> - выберите - </option>\n";
		
		foreach($this->selected_array as $k=>$v)
			$str .= "<option value='".$k."'"
				. (($this->value == $k) ? " SELECTED" : '')
				. ">".$v."</option>\n";
		
		$str .= "</select>\n";
		
		return $str;
	}
}

class FInput extends FElement {
	protected function ShowElement()
	{
		global $f_c;
		return "<input class='f_line".($f_c % 2)."' type='text' id='".$this->id."' "
			 . "name='".$this->name."' "
			 . "value='".$this->value."'>\n";
	}
}

class FPassword extends FElement {
	protected function ShowElement()
	{
		global $f_c;
		return "<input class='f_line".($f_c % 2)."' type='password' id='".$this->id."' "
			 . "name='".$this->name."' "
			 . "value=''>\n";
	}
	
	function GetValue() { return md5($this->value); }
	function SetURLValue($value)
	{
		if($value == '') return;
		$this->value = $value;
		$this->changed = true;
	}
}

class FIP extends FInput {
	function GetValue() { return ip2long($this->value); }
	function SetValue($value) { $this->value = long2ip($value); }
}

?>
