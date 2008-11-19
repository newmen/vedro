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

class DBExcept extends Exception {}

class DB_MySQL {
	private $db_socket;
	private $db_name;
	
	static private $querynums = 0;

	function __construct($host, $user, $passwd, $db_name)
	{
		if(!$this->db_socket = @mysql_connect($host, $user, $passwd)) throw new DBExcept("невозможно подключиться к серверу базы данных");
		
		if(!mysql_select_db($db_name)) throw new DBExcept("невозможно найти базу данных");
		$this->db_name = $db_name;
	}

	function __destruct()
	{
		mysql_close($this->db_socket);
	}

	private function CheckConnect()
	{
		if(!isset($this->db_socket)) throw new DBExcept("нет соединения с базой данных");
	}

	function SelectDB($db_name)
	{
		//$this->CheckConnect();
		if(!mysql_select_db($db_name)) throw new DBExcept("невозможно найти базу данных");
		$this->db_name = $db_name;
	}

	function Query($query)
	{
		//$this->CheckConnect();
		
		if(!$result = mysql_query($query, $this->db_socket))
			throw new DBExcept(mysql_error($this->db_socket) . "<br />\nзапрос: " . $query);
		
		self::$querynums++;
		
		return $result;
	}

	function Query_Fetch_Result($result)
	{
		//$this->CheckConnect();
		
		$ress = array();
		while($row = mysql_fetch_array($result)) {
			$ress[] = $row;
		}
		
		return (count($ress) > 0) ? $ress : false;
	}

	function Query_Fetch($query)
	{
		$result = $this->Query($query);
		return $this->Query_Fetch_Result($result);
	}

	function Query_Fetch_Row($query) // криво работает, если строк из базы получает несколько, и используется в цикле, типа while($row = mysql_fetch_...)
	{
		$result = $this->Query($query);
		return mysql_fetch_row($result);
	}

	function Query_Fetch_Array($query) // криво работает, если строк из базы получает несколько, и используется в цикле, типа while($row = mysql_fetch_...)
	{
		$result = $this->Query($query);
		return mysql_fetch_array($result);
	}

	function Query_Fetch_Assoc($query) // криво работает, если строк из базы получает несколько, и используется в цикле, типа while($row = mysql_fetch_...)
	{
		$result = $this->Query($query);
		return mysql_fetch_assoc($result);
	}

	function Insert_ID()
	{
		return mysql_insert_id($this->db_socket);
	}

	function &GetDB()
	{
		return $this->db_name;
	}
	
	function QueryNums() { return self::$querynums; }
}

?>
