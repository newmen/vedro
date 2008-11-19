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

require_once ("config.php");
require_once ("include/functions.php");
require_once ("classes/system_user.php");
require_once ("classes/menu.php");

class TemplateExcept extends Exception {}

class Template {
	private $path;
	
	private $sys_user;
	private $menu;
	
	private $custom_header = '';
	private $system_info = '';

	function __construct(&$sys_user, &$menu, $path_to_template = false)
	{
		$this->sys_user = &$sys_user;
		$this->menu = &$menu;
		
		$this->path = ($path_to_template !== false) ? $path_to_template : "templates";
	}

	function __set($name, $value)
	{
		switch($name) {
		case "app_custom_header":
			$this->custom_header .= $value;
			break;
			
		case "app_system_info":
			$this->system_info .= "<br />".$value."\n";
			break;
			
		default:
			throw new TemplateExcept("неизвестное свойство " . $name);
		}
	}

	function Header()
	{
		$this->SystemTemplate("header.html");
		//		echo "<h2>".$this->menu->topic."</h2>\n";
	}

	function Footer()
	{
		$this->SystemTemplate("footer.html");
	}

	private function SystemTemplate($file)
	{
		if(!ereg("/", $file)) $file = $this->path . "/" . $file;
		
		if(!$f = @fopen($file, "r")) throw new TemplateExcept("не могу открыть шаблон " . $file);
		$content = fread($f, filesize($file));
		fclose($f);
		
		$template_arr = preg_split("/\[\[([^\]]*)\]\]/", $content, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		foreach($template_arr as $k=>$v) {
			if($k % 2 == 0) {
				echo $v;
				continue;
			}
			
			switch($v) {
			case "system_info":
				echo $this->system_info;
				break;
				
			case "custom_header":
				echo $this->custom_header;
				break;
			
			case "topic":
				echo $this->menu->topic;
				break;
			
			case "title":
				echo TITLE;
				if(strlen($this->menu->topic) > 0) echo " :: " . $this->menu->topic;
				break;
			
			case "ip":
				echo $_SESSION[S_ID]["ip_login"];
				break;
			
			case "current_time":
				echo CTime(time());
				break;
			
			case "login_time":
				echo CTime($_SESSION[S_ID]["time_login"]);
				break;
			
			case "menu":
				echo $this->menu->Show();
				break;
			
			case "full_name":
				echo ($this->sys_user->full_name == ' ') ? $this->sys_user->login : $this->sys_user->full_name . " (" . $this->sys_user->login . ")";
				break;
			
			case "login":
				echo $this->sys_user->login;
				break;
			
			default:
				echo $v;
			}
		}
	}
}

?>
