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

require_once("include/functions.php");

function CheckExternalIPForm()
{
	global $db, $exip_id;
	
	if(!isset($_GET["external_ip_ip"]))
		return false;
	
	if($_GET["external_ip_ip"] == '')
	{
		MessageBox("Неуказан IP!");
		return false;
	}
	
	if($row = $db->Query_Fetch_Array("SELECT id FROM external_ip WHERE ip=inet_aton('".$_GET["external_ip_ip"]."')"
		. (isset($exip_id) ? " AND id!=".$exip_id : '')))
	{
		MessageBox("IP ".urldecode($_GET["external_ip_ip"])." уже присвоен в другом месте!");
		return false;
	}
	
	if($_GET["external_ip_location"] == 0)
	{
		MessageBox("Не выбрана локация!");
		return false;
	}
	
	if(!isset($exip_id)) MessageBox("Внешний IP присвоен");
	else MessageBox("Информация о внешнем IP успешно изменена");
	
	return true;
}

// функция заточена под freebsd-шный пинг)
function Ping($ip_num)
{
	$ip = long2ip($ip_num);
	if(!$p = @popen('ping -c 1 -t 1 '.$ip, 'r'))
		throw new Exception('Немогу пропинговать '.$ip);
	
	fgets($p);
	$str = fgets($p);
	pclose($p);

	return (ereg($ip, $str) && ereg('icmp_seq=0', $str)) ? "Да" : "Нет";
}

?>
