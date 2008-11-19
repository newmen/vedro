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

$tr_search = array("/(А|а|A)/", "/(Б|б|B)/", "/(В|в|V)/", "/(Г|г|Ж|ж|G)/", "/(Д|д|D)/", "/(е|ё|э|E)/", "/(З|з|Z)/", "/(И|и|Ы|ы|I)/", "/(Й|й|J)/", "/(К|к|K)/", "/(Л|л|L)/", "/(М|м|M)/", "/(Н|н|N)/", "/(О|о|O)/", "/(П|п|P)/", "/(Р|р|R)/", "/(С|с|S)/", "/(Т|т|T)/", "/(У|у|U)/", "/(Ф|ф|F)/", "/(Х|х|H)/", "/(Ц|ц|C)/", "/(Ч|ч)/", "/(Ш|ш|Щ|щ)/", "/(Ъ|ъ|Ь|ь|,|\.)/", "/(Ю|ю)/", "/(Я|я)/", "/\ /");
$tr_replace = array("a", "b", "v", "g", "d", "e", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "f", "h", "c", "ch", "sh", "", "yu", "ya", "_");

function Translit($str)
{
	global $tr_search, $tr_replace;
	$str = preg_replace($tr_search, $tr_replace, $str);
	
	return $str;
}

function CTime($time = false)
{
	if($time === false) $time = time();
	return date("d.m.Y H:i:s", $time);
}

function MessageBox($msg)
{
	echo "<table border=1 cellspacing=0 cellpadding=2>\n"
		. "<tr class=\"c_message\"><td>".$msg."</td></tr>\n"
		. "</table>\n"
		. "<br />";
}

function ShowErrPage($title, $msg)
{
	echo "<html>"
		. "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />"
		. "<title>" . $title . "</title>"
		. "<link rel=\"stylesheet\" type=\"text/css\" href=\"/templates/design.css\" />"
		. "<body>"
		. "<table border=0 width=100% height=100%>" 
		. "<tr height=100% valign=\"center\">"
		. "<td width=100% align=\"center\">"
		. "<h3>" . $msg . "</h3>"
		. "</td></tr>"
		. "</table>"
		. "</body></html>";
}

function LogWrite($file, $msg, $path = false)
{
	$file_name = (($path !== false) ? $patch : '') . LOGDIR . "/" . $file;
	if(!$f = @fopen($file_name, "a")) throw new Exception("can't open " . $file_name);
	
	fwrite($f, CTime() . " - " . $msg . "\n");
	
	fclose($f);
}

function DeleteCacheFile(&$directory, &$file)
{
	$filestats = stat($directory.$file);
	if(date('H', $filestats["atime"]) > SESSION_HOURS)
		if(!unlink($directory.$file))
			throw new Exception("Невозможно удалить файл кеша ".$directory.$file);
}

function ClearCache()
{
	$directory = CACHEDIR.'/';
	if($handle = @opendir($directory))
	{
	    while(($file = readdir($handle)) !== false)
	        if($file != '.' && $file != '..' && $file != 'tables'  && is_readable($directory.$file))
		        DeleteCacheFile($directory, $file);
	    
	    closedir($handle); 
	}
	
	$directory = CACHEDIR.'/tables/';
	if($handle = @opendir($directory))
	{
	    while(($file = readdir($handle)) !== false)
	        if($file != '.' && $file != '..' && is_readable($directory.$file))
			    DeleteCacheFile($directory, $file);

	    closedir($handle); 
	}
}

function GetDBConnect()
{
	return new DB_MySQL(DB_LOCAL_HOST, DB_LOCAL_USER, DB_LOCAL_PASSWD, DB_LOCAL_DB);
}

?>
