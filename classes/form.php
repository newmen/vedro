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

define("SUBMITTEXT", "хоккей");

class Form extends FormElementList {
	private $submit_text;
	private $id;
	
	private $custom_buttons = '';
	private $goback = true;
	
	function __construct($elements=array(), $submit_text='', $id='')
	{
		$this->elements = is_array($elements) ? $elements : array($elements);
		$this->submit_text = $submit_text;
		$this->id = $id;
	}
	
	function AsString()
	{
		$this->elements[] = new FBlock($this->custom_buttons
			. "<input ".(!$this->goback ? "id=\"t_serv\" " : '')
			. "type='submit' value='"
			. (($this->submit_text == '') ? SUBMITTEXT : $this->submit_text)
			. "'>\n");
	
		$str = "<form id='".$this->id."' action='' method='GET'>\n"
			. parent::AsString()
			. "</form>";
		
		return $str;
	}
	
	function AddButton($bstr) { $this->custom_buttons .= $bstr; }
	function SetGoBack($v) { $this->goback = ($v === true); }
}

?>
