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

session_start();

require_once ("config.php");
require_once ("classes/db_mysql.php");
require_once ("classes/system_user.php");
require_once ("include/functions.php");

global $db, $msg;

/*
 * chance - шанс того как часто будет появляться.
 * 1 - часто, 100 - никогда
 */
function GetImage($directory, $chance, $sizex=0)
{
	if(rand(1, 99) < $chance) return;
	
	$images = array();
	if($handle = @opendir($directory)) {
	    while(($file = readdir($handle)) !== false)
	        if($file != "." && $file != ".." && is_readable($directory.$file)) $images[] = $file; 
	    
	    closedir($handle); 
	}

	return "<img "
		. (($sizex != 0) ? "width='".$sizex."' " : '')
		. "src='".$directory.$images[rand(0, count($images)-1)]."' border=0>\n";
}

function Login($msg='')
{
	$challenge = rand();
	session_register(S_ID . "_challenge");
	$_SESSION[S_ID . "_challenge"]["random"] = $challenge;
	
	echo "<html>\n"
		. "<head>\n"
		. "<meta HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\" />\n"
		. "<title>" . TITLE . " :: Кто здесь??</title>\n"
		. "<link rel=\"stylesheet\" type=\"text/css\" href=\"/templates/design.css\" />\n"
		. "<script src=\"js/md5.js\" type=\"text/javascript\"></script>\n"
		. "<script type=\"text/javascript\">\n"
		. "function Change()\n"
		. "{\n"
		. "    document.login.challenge.value = hex_md5(document.login.challenge.value + hex_md5(document.login.pswd.value));\n"
		. "    document.login.pswd.value = '';\n"
		. "    return true;\n" 
		. "}\n"
		. "function SetFocus()\n"
		. "{\n";
	
	if(isset($_GET["name"])) echo "	document.login.name.select();\n";
	
	echo "	document.login.name.focus();\n" . "}\n"
		. "</script>\n"
		. "</head>\n"
		. "<body onLoad=\"SetFocus()\">\n"
		. "<table style='background-color: #ffffff;' cellspacing=0 cellpadding=0 border=0 width=100% height=100%>\n"
		. "    <tr height=30% valign=\"center\">\n"
		. "	<td width=23% align=\"center\">"
		. GetImage("./templates/pngs/", 97, 150)
		. "</td>\n"
		. "	<td width=54% rowspan=3 align=\"center\">\n";
		
	if ($msg != '' ) MessageBox($msg);
	
	echo "	    <form name=\"login\" action=\"\" method=\"GET\" onSubmit=\"Change()\">\n"
		. "	    <input type=\"hidden\" name=\"challenge\" value=\"" . $challenge . "\">"
		. "	    <table class=\"clogin\" cellspacing=0 cellpadding=0 border=0 width=500 height=200>\n"
		. "	    	<tr valign=\"center\">"
		. "    		    <td colspan=4 width=100% align=\"left\"><h4>&nbsp;&nbsp;Кто здесь?!</h4></td>\n"
		. "    		</tr>\n"
		. "	    	<tr class=\"clogin\" valign=\"center\">\n"
		. "	    	    <td> </td>\n"
		. "    		    <td width=40% class=\"cloginint\" align=\"right\">Логин:&nbsp;</td>\n"
		. "		    <td width=50% align=\"left\"><input class=\"clogininttable\" type=\"text\" name=\"name\" size=15 maxlength=20";
	
	if(isset($_GET["name"])) echo " value=\"" . $_GET["name"] . "\"";
	
	echo "></td>\n"
		. "		    <td width=10%> </td>\n"
		. "	    	</tr>\n"
		. "		<tr class=\"clogin\"><td colspan=2> </td></tr>\n"
		. "	    	<tr class=\"clogin\" valign=\"center\">\n"
		. "	    	    <td> </td>\n"
		. "	    	    <td class=\"cloginint\" align=\"right\">Пароль:&nbsp;</td>\n"
		. " 	   	    <td align=\"left\"><input class=\"clogininttable\" type=\"password\" name=\"pswd\" size=15 maxlength=20></td>\n"
		. "		    <td> </td>\n"
		. "	    	</tr>\n"
		. "	    	<tr valign=\"center\">\n"
		. "	    	    <td colspan=4 align=\"center\"><input class=\"clogininttable\" style=\"font-size: 20px; width: 240;\" type=\"submit\" value=\"Войти!\"></td>\n"
		. "	    	</tr>\n"
		. "	    </table>\n"
		. "	    </form>\n"
		. "	<td width=23% align=\"center\" align='center'> </td>\n"
		. "    </tr>\n"
		. "    <tr height=50% valign=\"bottom\">\n"
		. "	<td width=23% rowspan=2 align=\"center\"> </td>\n"
		. "		<td align='center'>"
		. 			"<a href='http://vedro.skynet/'>".GetImage("./templates/logos/", 1)."</a>"
		. "		</td>"
		. "    </tr>\n"
		. "<tr height=10% valign='center'>"
		. "	<td align=\"center\" valign='bottom'><div style='font-size: 18px;'><a href='http://vedro.skynet/'>vedro.skynet</a></div><br /></td>\n"
		. "     </tr>"
		. "</table>\n"
		. "</body>\n"
		. "</html>";
}

function Check(&$login, &$c_passwd)
{
	global $db, $msg;
	try {
		$db = GetDBConnect();
	} catch(Exception $e) {
		$msg = $e->getMessage();
		return false;
	}
	
	if($row = $db->Query_Fetch_Assoc("SELECT id, password FROM users "
		. "WHERE login='" . strtolower($login) . "' AND is_deleted=0 LIMIT 0,1"))
		if(isset($_SESSION[S_ID . "_challenge"]["random"])
			&& md5($_SESSION[S_ID . "_challenge"]["random"] . $row["password"]) == $c_passwd)
		{
			session_destroy();
			return $row["id"];
		}
	
	return false;
}

//$msg = '';

if(session_is_registered(S_ID)) {
	header("Location: index.php");
	exit();
}

if(isset($_GET["name"]) && isset($_GET["challenge"]))
{
	if($user_id = Check($_GET["name"], $_GET["challenge"]))
	{
		session_register(S_ID);
		
		$_SESSION[S_ID]["user_id"] = $user_id;
		$_SESSION[S_ID]["ip_login"] = &$_SERVER["REMOTE_ADDR"];
		$_SESSION[S_ID]["time_login"] = time();
		//$_SESSION[S_ID]["menu_id"] = 0;

		$good = true;
		try {
			$sys_user = new System_User($db, $user_id);
			$sys_user->Login();
		} catch(Exception $e) {
			$msg = $e->getMessage();
			$good = false;
			
			session_destroy();
		}
		
		if($good) {
			header("Location: index.php");
			exit();
		}
	}
}

Login($msg);

?>
