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

// Types:
// 0 - php, который лежит на локальной машине
// 1 - php, который лежит на удалённой машине, или локальный html
// 2 - xml определённого формата

require_once ("config.php");
require_once ("include/session.php");
require_once ("include/functions.php");
require_once ("classes/class404.php");
require_once ("classes/db_mysql.php");
require_once ("classes/menu.php");
require_once ("classes/system_user.php");
require_once ("classes/template.php");
require_once ("classes/goback.php");

class PageExcept extends Exception {}

class Page {
	private $db;
	private $sys_user;
	private $menu;
	private $module;
	private $template;

	private $goback;
	
	private $type;
	private $content;
	private $cache_full_url = array();
	
	private $timestart;

	function __construct($module_name, $menu_t_name)
	{
		$this->timestart = microtime(true);	
	
		// формируем глобальный _GET
		if(isset($_SERVER["REDIRECT_QUERY_STRING"]))
		{
			$query_arr = explode("&", $_SERVER["REDIRECT_QUERY_STRING"]);
			
			foreach($query_arr as $nv) {
				$nv_arr = explode("=", $nv);
				$_GET[$nv_arr[0]] = $nv_arr[1];
			}
		}
		
		$this->db = GetDBConnect();
		
		$this->sys_user = new System_User($this->db);
		$this->sys_user->Current();
		$this->sys_user->UpdateLocation();
		
		if(!$row = $this->db->Query_Fetch_Assoc("SELECT menus.id as menu_id "
			. "FROM modules LEFT JOIN menus ON modules.id=menus.module_id "
			. "LEFT JOIN groups_menus ON menus.id=groups_menus.menu_id "
			. "WHERE modules.name='" . $module_name
			. "' AND menus.translit_name='" . $menu_t_name . "' "
			. "AND (" . $this->sys_user->groups_SQL . ") "
			. "LIMIT 0,1"))
				throw new class404("нет элемента меню " . $menu_t_name . " для модуля - " . $module_name);
		
		$menu_id = &$row["menu_id"];
		
		$this->menu = new Menu($this->db, $this->sys_user, $menu_id);
		$this->module = &$this->menu->module;
		
		$this->template = new Template($this->sys_user, $this->menu);
		
		$this->goback = new goback_refrr();
		
		// переменные окружения!
		include ("global.php");
		$db = $this->db;
		$sys_user = $this->sys_user;
		$path = "/" . $this->module->path;
		$inc_path = $this->module->path;
		$url_path = $module_name."/".$menu_t_name;
		
		// определение типа отображаемого скрипта
		if(ereg("xml$", $this->menu->script))
		{
			$this->type = 2;
			return;
		}
		
		if(ereg("^http:\/\/", $this->module->path) || ereg("htm[l]?$", $this->menu->script)
			|| ereg("^http:\/\/", $this->menu->script))
		{
			$this->type = 1;
			return;
		}
		
		$this->type = 0;
	}

	function __destruct()
	{
		while(ob_get_level() != 0) ob_end_flush();
	}

	function Show()
	{
		ob_start(array(&$this, "GetContent"));

		try {
			switch($this->type)
			{
			case 0:
				$this->ShowPHP();
				break;
			
			case 1:
				$this->ShowHTML();
				break;
			
			case 2:
				$this->ShowXML();
				break;
			
			default:
				throw new PageExcept("неизвестный тип элемента меню - " . $this->type);
			}
		} catch(UserError $ue) {
			$ue->Error();
		} catch(Exception $e) {
//			$message = str_replace("\n", "<br />", $e->getMessage());
			echo nl2br("<h3>" . $e->getMessage() . (DEBUG ? "<br /><br />".$e->getTraceAsString() : ''). "</h3>");
		}
		
		ob_end_flush();
		
		$this->goback->go();
		
		$content_arr = preg_split("/(<body[^>]*>)/i", $this->content, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		if(count($content_arr) == 3) {
			$content_arr[0] = preg_replace("/(<script\s.*src=['\"])(\S*)(['\"][^>]*><\/script>)/ei", '\$this->UpdateHeader("$1", "$2", "$3")', $content_arr[0]);
			$content_arr[0] = preg_replace("/(<link\s.*href=['\"])(\S*)(['\"][^>]*><\/link>)/ei", '\$this->UpdateHeader("$1", "$2", "$3")', $content_arr[0]);
			$content_arr[0] = preg_replace("/(<style>[\S\s\n\t]*<\/style>)/ei", '\$this->UpdateHeader("$1")', $content_arr[0]);
			$content_arr[0] = preg_replace("/(<script[^>]*>[\S\s\n\t]*<\/script>)/ei", '\$this->UpdateHeader("$1")', $content_arr[0]);
		}
		
		$this->template->Header();
//		var_dump($this->cache_full_url);
		echo $this->UpdateBody((count($content_arr) == 3)
			? preg_replace("/(<\/body>[\S\s\n\t]*<\/html>)/i", '', $content_arr[2]) : $this->content);

		if(DEBUG)
		{
			$timecount = round(1000 * (microtime(true) - $this->timestart)) / 1000;
			$this->template->app_system_info = "Общее число запросов к базе данных на странице: ".$this->db->QueryNums();
			$this->template->app_system_info = "Время генерации страницы: ".$timecount." сек";
		}
		
		$this->template->Footer();
	}

	private function GetContent($buff)
	{
		$this->content = preg_replace("/(" . str_replace('/', '\/', $this->menu->script) . "\?)/i", "?", $buff);
	}

	private function UpdateHeader($begin, $url = false, $end = false)
	{
		$this->template->app_custom_header = (($url !== false && $end !== false) ? $this->ChangeSrc(&$begin, &$url, &$end) : $begin) . "\n";
	}

	private function UpdateBody($buff)
	{
		$buff = preg_replace("/(<a\s)([^>]*href=[\"'])(\S*)([\"'][^>]*>)/ei", '"$1".\$this->ChangeLink("$2", "$3")."$4"', $buff);
//		$buff = stripslashes(preg_replace("/(<a\s)([^>]*href=[\"'])(\S*)([\"'][^>]*>)/ei", '"$1".\$this->ChangeLink("$2", "$3")."$4"', $buff));
		$buff = preg_replace("/(<img\s[^>]*src=['\"])(\S*)(['\"][^>]*>)/ei", '\$this->ChangeSrc("$1", "$2", "$3")', $buff);
		$buff = preg_replace("/(<script\s[^>]*src=['\"])(\S*)(['\"][^>]*><\/script>)/ei", '\$this->ChangeSrc("$1", "$2", "$3")', $buff);
		$buff = preg_replace("/(<input\s[^>]*type=['\"]submit['\"][^>]*>)/ei", '\$this->WriteGoBack("$1")', $buff);
		return $buff;
	}

	private function WriteGoBack($input)
	{
		$input = stripslashes($input);
		// проверяем не является ли форма формой изменения вида таблицы
		if(!ereg("id=\"t_serv\"", $input)) return $input . $this->goback->hinput();
		return $input;
	}

	private function ChangeSrc($begin, $url, $end)
	{
		if(preg_match("/^\//", $url)) return $begin . $url . $end;
		if(!ereg($this->module->path, $url)) return $begin . "/" . $this->module->path . $url . $end;
		return $begin . "/" . $url . $end;
	}

	// метод для замены всех ссылок на странице, на валидные
	// очень мозгоклюйная фукнция
	private function ChangeLink($href, $url)
	{
		if(ereg("target=", $href) || preg_match("/^#/", $url)) return $href . $url;
		
		if(isset($this->cache_full_url[$url])) return $this->cache_full_url[$url];
		
		$arr = array();
		if(preg_match("/^(http:\/\/)([^\/]+)(.*)/i", $url, $arr)) {
			if($arr[2] != $_SERVER["SERVER_NAME"] && $arr[2] != $_SERVER["SERVER_ADDR"])
				return $this->cache_full_url[$url] = "target=_blank " . $href . $url;
			
			if(strlen($arr[3]) < 2) return $this->cache_full_url[$url] = $href . $url;
			
			$url = $arr[3];
		}
		
		// если начинается со слеша - отрубаем этот слеш
		if(($pos = strpos($url, "/") !== false) && $pos == 0 && (strpos($url, "/", 1) === false))
			$url = substr($url, 1);
		
		// тут надо дописывать
		if(($rpos = strrpos($url, "/")) !== false)
		{
			// кусок кода для замены ссылок в открываемых УРЛов 
			
//			// совершенно непонятный кусок кода... какой-то бред написал...) но удалять боюсь)
//			$script = (($pos = strpos($url, "?")) !== false)
//				? substr($url, $rpos + 1, $pos - $rpos) : substr($url, $rpos + 1);
//			$module = substr($url, 0, $pos);
//			if(!$this->db->Query_Fetch_Assoc("SELECT id FROM modules WHERE module_name='" . $module . "'"))
//				$this->cache_full_url[$url] = "target=_blank " . $href . $url;
//			else $this->cache_full_url[$url] = $href . $url;
		}
		else {
			if($pos = strpos($url, "?")) {
				$script = substr($url, 0, $pos);
				$params = substr($url, $pos);
			}
			else {
				if($pos === 0) return $this->cache_full_url[$url] = $href . $url;
				$script = $url;
				$params = '';
			}
			
			// очень вкусная строчка) не в коем случае её не удалять)
			if(isset($this->cache_full_url[$script])) return $this->cache_full_url[$script].$params;
			
			if($script == "404.php" || ereg($script, $this->menu->script)) {
				return $this->cache_full_url[$url] = $href . $params;
			}
			
			if(!$row = $this->db->Query_Fetch_Assoc("SELECT translit_name FROM menus "
				. "WHERE script_name='" . $script
				. "' AND module_id='" . $this->module->id . "' LIMIT 0,1"))
					return $this->cache_full_url[$url] = "target=_blank " . $href . "/" . $this->module->path . $url;
			
			$this->cache_full_url[$script] = $href . "/" . $this->module->name . "/" . $row["translit_name"] . "/";
			return $this->cache_full_url[$script].$params;
			//return $this->cache_full_url[$url] = $href . "/" . $this->module->name . "/" . $row["translit_name"] . "/" . $params;
		}
	}

	private function ShowPHP()
	{
		$this->Require_All_Modules($this->module->id);
		
		$script_name = $this->menu->script;
		if($pos = strpos($script_name, "?")) {
			$get = substr($script_name, $pos + 1);
			$get = explode("&", $get);
			
			foreach($get as $kv_str) {
				$kv = explode("=", $kv_str);
				
				if(isset($_GET[$kv[0]])) throw new PageExcept("Ошибка задания переменной \$_GET!\n" . "(поле " . $kv[0] . " уже есть)\nПроверьте конфигурацию модуля " . $this->module->name);
				
				$_GET[$kv[0]] = $kv[1];
			}
			
			$script_name = substr($script_name, 0, $pos);
		}
		
		require_once ($this->module->path . $script_name);
	}

	private function ShowHTML()
	{
		echo '<script>alert("Функция открытия сайтов поддерживается не полностью.\nПриносим извинения за доставленные неудобства.");</script>';

		$url = ereg("^http:\/\/", $this->module->path) ? $this->module->path : $this->menu->script;
		if(!$f = @fopen($url, 'rb')) throw new Exception('Невозможно открыть ресурс '.$url);
		
		while(!feof($f)) echo fread($f, 8192);
		fclose($f);
	}

	private function ShowXML()
	{
		$this->Require_All_Modules($this->module->id);
		
		if(!$f = @fopen($this->module->path . $this->menu->script, "r")) throw new PageExcept("неудаётся открыть " . $this->module->path . $this->menu->script);
		
		$content = fread($f, filesize($this->module->path . $this->menu->script));
		fclose($f);
		$xml = new SimpleXMLElement($content);
		
		if(isset($xml->template)) {
			if(isset($xml->template["src"])) {
				$template_file = $this->module->path . $xml->template["src"];
				
				if(!is_file($template_file)) throw new PageExcept("не найден шаблон для отображения " . $template_file);
			}
			else {
				$showall_zone1 = false;
				switch($xml->template) {
				case "single":
					$showall_zone1 = true;
				case "double":
				case "triple":
				case "triple-left":
				case "triple-right":
				case "triple-top":
				case "triple-bottom":
				case "triple-ass":
				case "quad":
					$template_file = $xml->template . ".html";
					break;
				default:
					$template_file = "single.html";
					$showall_zone1 = true;
				}
				$template_file = "templates/" . $template_file;
			}
		}
		
		if(!$f = @fopen($template_file, "r")) throw new PageExcept("неудаётся открыть файл " . $template_file);
		$template = fread($f, filesize($template_file));
		fclose($f);
		
		$template_arr = preg_split("/\[\[zone_([^\]]*)\]\]/i", $template, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		if(!$showall_zone1) {
			foreach($template_arr as $k=>$v) {
				if($k % 2 == 0) {
					echo $v;
					continue;
				}
				
				if(!isset($xml->zone)) {
					if($k == 1) $this->ShowXMLZone($xml);
					continue;
				}
				
				foreach($xml->zone as $zone_xml)
					if($zone_xml['id'] == $v) $this->ShowXMLZone($zone_xml);
			}
		}
		else {
			if(!isset($xml->zone)) $this->ShowXMLZone($xml);
			else foreach($xml->zone as $zone_xml)
				$this->ShowXMLZone($zone_xml);
		}
	}

	private function Require_All_Modules($id) // рекурсивная
	{
		if($row = $this->db->Query_Fetch_Assoc("SELECT extend_module_id FROM modules_depend WHERE module_id=" . $id)) $this->Require_All_Modules(&$row["id"]);
		
		if($id == $this->module->id) return;
		
		if(!$row = $this->db->Query_Fetch_Assoc("SELECT name, path, kernel FROM modules WHERE id=" . $id)) throw new PageExcept("нет модуля с id - " . $id);
		
		if($row["kernel"] == "") throw new PageExcept("у модуля " . $row["name"] . " (" . $id . ") нет ядра!");
		
		require_once($row["path"] . $row["kenrel"]);
	}

	private function ShowXMLZone($zone_xml)
	{
		if(!isset($zone_xml)) return;

		foreach($zone_xml->children() as $tag=>$value) {
			switch($tag) {
			case "script":
				if(!isset($value["src"])) throw new PageExcept("в xml не указан файл скрипта");
				if(!is_file($this->module->path . $value["src"])) throw new PageExcept("не могу найти файл " . $this->module_path . $value["src"]);
				require_once ($this->module->path . $value["src"]);
				break;
			
			case "html":
				echo preg_replace("/<\/?html>/i", "", $value->asXML());
				break;
			
			case "table":
				var_dump($value);
				break;
			}
		}
	}
}

?>
