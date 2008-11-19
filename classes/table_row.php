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

function eq(&$a, &$b) { return $a == $b; }
function ne(&$a, &$b) { return $a != $b; }
function gt(&$a, &$b) { return $a > $b; }
function ge(&$a, &$b) { return $a >= $b; }
function lt(&$a, &$b) { return $a < $b; }
function le(&$a, &$b) { return $a <= $b; }

class Row {
	protected $comp_func;
	protected $value;

	function __construct($compare, $value)
	{
		switch($compare) {
		case "=":
		case "==":
		case "eq":
			$func = "eq";
			break;
		case "!=":
		case "<>":
		case "ne":
			$func = "ne";
			break;
		case ">":
		case "gt":
			$func = "gt";
			break;
		case ">=":
		case "ge":
			$func = "ge";
			break;
		case "<":
		case "lt":
			$func = "lt";
			break;
		case "<=":
		case "le":
			$func = "le";
			break;
		default:
			throw new Exception("неизвестный тип сравнения \"$compare\"");
		}
		
		$this->comp_func = $func;
		$this->value = $value;
	}

	function Compare($value) { return call_user_func($this->comp_func, $value, $this->value); }
}

?>
