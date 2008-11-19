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

class UserError extends Exception {
	private $user;

	function __construct($e, $user)
	{
		parent::__construct($e);
		
		$this->user = $user;
	}

	function Error()
	{
		echo "У пользователя " . $this->user->SystemName() . " произошла ошибка: " . parent::getMessage();
		if(DEBUG) echo parent::getTraceAsString();
	}
}

class User {
	protected $db;
	
	protected $id;
	protected $login;

	function __construct(&$db)
	{
		$this->db = &$db;
	}

	final function SystemName()
	{
		return "[" . $this->id . "] " . $this->login;
	}
}

?>
