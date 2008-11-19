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

require_once("classes/form_elements.php");

abstract class FormElementList implements iFElement {
	protected $elements = array();
	protected $width;
	
	private $user_func;
	static private $user_func_result = true;

	function AsString()
	{
		$this->Processing();
	
		$tfr = rand();
		$str = "<script language=\"JavaScript\" type=\"text/javascript\" src=\"/js/hltable.js\"></script>\n"
			. "<table id=\"FormCheckList_".$tfr."\" border=0 cellspacing=1 cellpadding=2"
			. (isset($this->width) ? ' width="'.$this->width.'"' : '')
			. ">\n";

		foreach($this->elements as $elm) $str .= $elm->AsString();
		
		$str .= "</table>"
		
		// симпатишная стыреная подсвечивалка (=
			. "<script type=\"text/javascript\">\n"
			. "	highlightTableRows(\"FormCheckList_".$tfr."\", \"f_hover\");\n"
			. "</script>\n";
			
		return $str;
	}
	
	function Show()
	{
		echo $this->AsString();
	}

	function SetWidth($w) { $this->width = $w; }
	
	/*
	 * Задаваемая пользовательская функция должна возвращать булево значение
	 * true - если функция отработала без ошибок
	 * false - если во время выполнения возникли ошибки
	 */
	function SetUserFunc($fname) { $this->user_func = $fname; }
	
	/*
	 * Если эта функция возвращает false то все функции, вызывающие её в условии - не выполняются
	 */
	protected function Processing()
	{
		if(!self::$user_func_result) return false;
		if(isset($this->user_func) && $this->user_func != '')
		{
			if(call_user_func($this->user_func)) return true;
			else
			{
				self::$user_func_result = false;
				return false;
			}
		}
		return true;
	}
}

?>
