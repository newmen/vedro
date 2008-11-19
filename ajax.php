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


require_once("config.php");
require_once("classes/table.php");
require_once("include/session.php");

//var_export($_GET);

if(isset($_GET["t_name"]))
{
	$s_ =& $_SESSION[S_ID][$_GET["t_name"]];
	
	if(isset($_GET["search_line"]) && isset($_GET["row_id"]))
		switch($_GET["search_line"])
		{
		case "add":
			
			//$rows = count($s_["search"]);
			$s_["search"][$_GET["row_id"]] = array();
			$s_["search_max_row_id"] = $_GET["row_id"];
			break;
		
		case "del":
			unset($s_["search"][$_GET["row_id"]]);
			break;
		}
	
	if(isset($_GET["search_separator"]))
		$s_["search_separator"] = ($_GET["search_separator"] == "or") ? "OR" : "AND";

	if(isset($_GET["search_column"]) && isset($_GET["row_id"]) && isset($_GET["color"]) && isset($_GET["theme_pref"]))
	{
		$sr_arr = Table::GetSearchArray($_GET["t_name"], $_GET["search_column"], $_GET["row_id"], $_GET["color"], $_GET["theme_pref"]);
		
		echo $_GET["t_name"]."_search_type_list_row".$_GET["row_id"]
			. "|".$sr_arr['type_text']."|"
			. $_GET["t_name"]."_search_text_row".$_GET["row_id"]
			. "|".$sr_arr['value_text'];
	}
}

?>
