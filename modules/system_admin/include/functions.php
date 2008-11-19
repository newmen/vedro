<?php

require_once("include/functions.php");

function CheckUserForm()
{
	global $db, $user_edit_id;

	if(!isset($_GET["users_login"]))
		return false;
	
	if($_GET["users_login"] == '')
	{
		MessageBox("Неуказан логин!");
		return false;
	}
	
	if($row = $db->Query_Fetch_Array("SELECT id FROM users WHERE login='".$_GET["users_login"]."'"
		. (isset($user_edit_id) ? " AND id!=".$user_edit_id : '')))
	{
		MessageBox("Пользователь с логином ".$_GET["users_login"]." уже существует!");
		return false;
	}
	
	if($_GET["users_password"] != $_GET["users_password2"])
	{
		MessageBox("Пароли не совпадают!");
		return false;
	}
	
	if(!isset($user_edit_id))
	{
		if($_GET["users_password"] == '')
		{
			MessageBox("Неуказан пароль!");
			return false;
		}
		MessageBox("Пользователь добавлен");
	}
	else MessageBox("Пользователь успешно изменён");
	
	return true;
}

function CheckGroupForm()
{
	global $db, $group_edit_id;
	
	if(!isset($_GET["groups_name"]))
		return false;
	
	if($_GET["groups_name"] == '')
	{
		MessageBox("Неуказано название!");
		return false;
	}
	
	if($row = $db->Query_Fetch_Array("SELECT id FROM groups WHERE name='".urldecode($_GET["groups_name"])."'"
		. (isset($group_edit_id) ? " AND id!=".$group_edit_id : '')))
	{
		MessageBox("Группа ".urldecode($_GET["groups_name"])." уже существует!");
		return false;
	}
	
	if(!isset($group_edit_id)) MessageBox("Группа добавлена");
	else MessageBox("Группа успешно изменёна");
	
	return true;
}

function CheckLocateForm()
{
	global $db, $locate_id;
	
	if(!isset($_GET["locations_name"]))
		return false;
	
	if($_GET["locations_name"] == '')
	{
		MessageBox("Неуказано название!");
		return false;
	}
	
	if($row = $db->Query_Fetch_Array("SELECT id FROM locations WHERE name='".urldecode($_GET["locations_name"])."'"
		. (isset($locate_id) ? " AND id!=".$locate_id : '')))
	{
		MessageBox("Локация ".urldecode($_GET["locations_name"])." уже существует!");
		return false;
	}
	
	if(!isset($locate_id)) MessageBox("Локация добавлена");
	else MessageBox("Локация успешно изменёна");
	
	return true;
}

?>