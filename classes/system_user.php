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

define("USERS_LOG", "users.log");

require_once("classes/user.php");
require_once("include/functions.php");

class SysUserError extends UserError {}

class System_User extends User {
	protected $name;
	protected $family;
	protected $daddy;
	
	protected $mobile_telephone;
	
	protected $utm_lacisa_login;
	protected $utm_lacisa_password;
	protected $utm_amin_login;
	protected $utm_amin_password;
	
	protected $is_deleted;
	
	protected $groups;
	protected $groups_flip;
	
	protected $groups_SQL = '';
	private $set_groups_sql = false;

	function __construct(&$db, $id = false)
	{
		parent::__construct($db);
		
		if(!$id) {
			$this->id = 0;
			$this->login = '';
			$this->name = '';
			$this->family = '';
			$this->daddy = '';
			$this->mobile_telephone = '';
			$this->is_deleted = false;
			
			$this->groups = array();
			$this->groups_flip = array();
			
			$this->groups_SQL = '';
			$this->set_groups_sql = false;
			return;
		}
		
		$this->id = $id;
		
		if(!$row = $this->db->Query_Fetch_Assoc("SELECT * FROM users WHERE id=" . $this->id . " LIMIT 0,1")) throw new SysUserError("невозможно создать объект - не найдена запись в БД", $this->id);
		
		$this->login = $row["login"];
		$this->name = $row["name"];
		$this->family = $row["family"];
		$this->daddy = $row["daddy"];
		$this->mobile_telephone = $row["mobile_telephone"];
		$this->is_deleted = ($row["is_deleted"] == 0) ? false : true;
		
		if(!$result = $this->db->Query_Fetch("SELECT group_id FROM groups_users WHERE user_id=" . $this->id)) throw new SysUserError("невозможно создать объект - не найдено записей груп", $this->id, $this->login);
		
		foreach($result as $row)
			$this->groups[] = $row["group_id"];
	}

	function __get($name)
	{
		switch($name) {
		case "id":
		case "login":
		case "name":
		case "family":
		case "daddy":
		case "mobile_telephone":
		case "groups":
		case "is_deleted":
			return $this->$name;
		
		case "full_name":
			return $this->family . " " . $this->name;
		case "fio":
			return $this->family . " " . $this->name . " " . $this->daddy;
		
		case "groups_SQL":
			if($this->set_groups_sql) return $this->groups_SQL;
			
			$this->groups_SQL = '';
			foreach($this->groups as $group_id)
				$this->groups_SQL .= 'groups_menus.group_id=' . $group_id . ' OR ';
			$this->groups_SQL .= 'groups_menus.group_id=0';
			
			$this->set_groups_sql = true;
			return $this->groups_SQL;
		
		default:
			throw new SysUserError("нет свойства " . $name, $this);
		}
	}

	function Current() // жирно?
	{
		$this->__construct($this->db, $_SESSION[S_ID]["user_id"]);
	}

	function Reset($id) // жирно?
	{
		$this->__construct($this->db, $id);
	}

	function IsGroup($gid)
	{
		if(!isset($this->groups_flip)) $this->groups_flip = array_flip($this->groups);
		return isset($this->groups_flip[$gid]);
	}

	function UpdateLocation()
	{
		$url = $_SERVER["REQUEST_URI"];
		if(strlen($url) > 70) $url = substr($url, 0, 70) . "...";
		
		$this->db->Query("UPDATE users_online " . "SET last_view='" . $url . "' " . ",time_last_active=NOW() " . "WHERE id=" . $_SESSION[S_ID]["online_table_id"]);
	}

	function Login()
	{
		$this->UpdateHistory(1);
		$this->RestoreSession();
		
		if(!$result = $this->db->Query_Fetch("SELECT id, inet_ntoa(ip) as ip, sessid " . "FROM users_online " . "WHERE uid=" . $this->id . " ")//		 . "LIMIT 0,1"
		) {
			// если нету такого залогиненого пользователя
			$this->db->Query("INSERT INTO users_online (uid, ip, sessid, time_login) "
				. "VALUES ('" . $this->id . "', " . "inet_aton('" . $_SESSION[S_ID]["ip_login"] . "'), " . "'" . $_REQUEST["PHPSESSID"] . "', " . "NOW()" .
//			 . "'".CTime($_SESSION["ip_login"]["time"])."'"
			")");
			
			$_SESSION[S_ID]["online_table_id"] = $this->db->Insert_ID();
			return;
		}
		
		// бред)
		// долго думал и пришёл к выводу, что бывает 4 варианта различных записей заходов в базе, которые может найти
		$variantes = array(0 => 0, // обрыв сессии, заход с того же ип, но с другой сессией
			1 => 0, // заход с другого ип, под тем же логином, и, соответственно, с другой сессией
			2 => 0, // несанкционированный доступ! заход с такой же сессией с того же ип
			3 => 0)// несанкционированный доступ! заход с такой же сессией с другого ип
			;
		
		foreach($result as $row)
		{
			if($row["sessid"] == $_REQUEST["PHPSESSID"]) {
				if($row["ip"] == $_SESSION[S_ID]["ip_login"]) $variantes[2] = $row["id"];
				else $variantes[3] = $row["id"];
			}
			else {
				if($row["ip"] == $_SESSION[S_ID]["ip_login"])
				    $variantes[0] = $row["id"];
				else $variantes[1] = $row["id"];
			}
		}
		//var_dump($variantes);	

		// сортируем каки-бяки...
		if($variantes[2] != 0 || $variantes[3] != 0) LogWrite(USERS_LOG, "повторение сессии! " . "(Login: " . $this->login . ", IP: " . $_SESSION[S_ID]["ip_login"] . ", SESSID: " . $_REQUEST["PHPSESSID"] . ")");
		
		if($variantes[0] != 0 || $variantes[2] != 0) {
			$this->db->Query("UPDATE users_online " . "SET time_last_active=NOW(), " . "time_login=NOW() " . //			 . "time_login='".CTime($_SESSION["ip_login"]["time"])."' "
			"WHERE id=" . $row["id"]);
			
			$_SESSION[S_ID]["online_table_id"] = $row["id"];
		}
		elseif($variantes[1] != 0 || $variantes[3] != 0) {
			// дублируем запрос на добавление записи (см. выше)
			$this->db->Query("INSERT INTO users_online (uid, ip, sessid, time_login) " . "VALUES ('" . $this->id . "', " . "inet_aton('" . $_SESSION[S_ID]["ip_login"] . "'), "
			    . "'" . $_REQUEST["PHPSESSID"] . "', " . "NOW() "
 //			    . "'".CTime($_SESSION["ip_login"]["time"])."'"
			    . ")");
			
			$_SESSION[S_ID]["online_table_id"] = $this->db->Insert_ID();
		}
	}

	function Logout()
	{
		$this->UpdateHistory(0);
		$this->SaveSession();
		
		if(!$row = $this->db->Query_Fetch_Assoc("SELECT uid, INET_NTOA(ip) as ip, sessid "
			. "FROM users_online " . "WHERE id=" . $_SESSION[S_ID]["online_table_id"] . " "
//			. "AND sessid='".$_REQUEST["PHPSESSID"]."' "
//			. "AND ip=INET_ATON('".$_SESSION[S_ID]["ip_login"]."') "
			. "LIMIT 0,1"))
		{
			LogWrite(USERS_LOG, "ненайдена сессия для " . $this->login . ", в подключенных пользователях");
			return;
		}
		
		if($row["uid"] != $this->id) LogWrite(USERS_LOG, "для пользователя " . $this->login . " в сессии " . $_REQUEST["PHPSESSID"] . " не совпадает id пользователя (" . $row["uid"] . ")");
		
		if($row["ip"] != $_SESSION[S_ID]["ip_login"])
		LogWrite(USERS_LOG, "для пользователя " . $this->login . " в сессии " . $_REQUEST["PHPSESSID"] . " не совпадает ip (" . $_SESSION[S_ID]["ip_login"] . " - " . $row["ip_login"] . ")");
		
		if($row["sessid"] != $_REQUEST["PHPSESSID"]) LogWrite(USERS_LOG, "для пользователя " . $this->login . " не совпадают сессии " . $_REQUEST["PHPSESSID"] . " и " . $row["sessid"]);
		
		$this->db->Query("DELETE FROM users_online WHERE id=" . $_SESSION[S_ID]["online_table_id"]);
		
		$user = new System_User($this->db);
		// проверяем есть ли в списке залогиненых этот же пользователь? если есть, и последняя активность более 24 часов, то удаляем запись
		if(!$result = $this->db->Query_Fetch("SELECT id, uid, TIMEDIFF(NOW(), time_last_active)+0 as time, inet_ntoa(ip) as ip, time_login, time_last_active "
			. "FROM users_online ")
//			. "WHERE uid=".$this->id." "
//			. "LIMIT 0,1"
		) return;
		
		// чистим таблицу с давно законнекчиными
		foreach($result as $row) {
			if($row["time"] < SESSION_HOURS) continue;
			
			$this->db->Query("INSERT INTO users_connection_history (uid, ip, time_active, state) "
				. "VALUES ('".$row["uid"]."', inet_aton('".$row["ip"]."'), '".$row["time_last_active"]."', 0)");
			$this->db->Query("DELETE FROM users_online WHERE id=" . $row["id"]);
			
			$user->Reset($row["uid"]);
			LogWrite(USERS_LOG, "из залогиненых пользователей была удалена старая запись пользователя " . $user->login . ", " . "от " . $row["time_login"] . " (IP: " . $row["ip"] . ")");
		}
		
		// чистим старые кешы
		ClearCache();
		
	}

	function SaveSession()
	{
		$filename = USERSDIR . "/" . $this->login . ".dat";
		if(!$f = @fopen($filename, "w")) throw new SysUserError("немогу открыть файл " . $filename . " для записи информации о сессии", $this);
		
		$session_strings = explode("\n", var_export($_SESSION[S_ID], true));
		$saved_string = '';
		foreach($session_strings as $str)
		{
			if(ereg("'user_id' =>", $str) || ereg("'ip_login' =>", $str)
				|| ereg("'time_login' =>", $str) || ereg("'online_table_id' =>", $str))
					continue;
			
			$saved_string .= $str . "\n";
		}
		
		fwrite($f, $saved_string);
		fclose($f);
	}

	function RestoreSession()
	{
		$filename = USERSDIR . "/" . $this->login . ".dat";
		
		if(!$f = @fopen($filename, "r")) return;
			//throw new SysUserError("немогу открыть файл ".$filename." для чтения информации о сессии", $this);

		//eval('$_SESSION[S_ID] = '.fread($f, filesize($filename)).';');
		eval('$temp_arr = ' . fread($f, filesize($filename)) . ';');
		
		foreach($temp_arr as $k=>$v) {
			$_SESSION[S_ID][$k] = $v;
		}
		
		fclose($f);
	}

	private function UpdateHistory($state)
	{
		$this->db->Query("INSERT INTO users_connection_history (uid, ip, time_active, state) " . "VALUES ('" . $this->id . "', inet_aton('" . $_SESSION[S_ID]["ip_login"] . "'), NOW(), " . $state . ")");
	}
}
?>
