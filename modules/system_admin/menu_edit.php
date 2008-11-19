<?php

require_once('classes/table.php');

function GetParentID($id) {
	global $db;
	
	if($row = $db->Query_Fetch_Array('SELECT parent_id FROM menus WHERE id='.$id))
		return $row[0];
	else return 0;
}

function GetChildIDButton($parentid) {
	global $db;
	if(!$db->Query_Fetch_Array('SELECT id FROM menus WHERE parent_id='.$parentid))
		return '';
	 
	return '<input type="button" value=">>>" onClick="GoURL(\'?id='.$parentid.'\')">';
}

function ShowMenuItems($id) {
	global $db;
	
	$sql = "SELECT id, name, position, parent_id FROM menus WHERE parent_id='".$id."' ORDER by position";
	
	//$columns['id'] = new Column_Number('ID', 0);
	$columns['name'] = new Column('Название');
	$columns['position'] = new Column_Number('Позиция', 0);
	if($id != 0)
		$columns['back'] = new Column_Custom('Назад', '<input type="button" value="<" onClick="GoURL(\'?id='.GetParentID($id).'\')" />');
	$columns['more'] = new Column_UserFunc('Далее', 'GetChildIDButton', array('id'));
//	$columns['more'] = new Column_Button('Далее', '', 'id', '>>>');
	
	$table = new Table($db, 'menu_edit', $sql, $columns);
	$table->Show_PageSwitch(false);
	$table->Show();
}

$id = 0;
if(isset($_GET['id']) || isset($_GET['parent_id'])) {
	if(isset($_GET['id'])) $id = ($_GET['id'] > 0) ? $_GET['id'] : 0;
	else $id = ($_GET['parent_id'] > 0) ? $_GET['parent_id'] : 0;
}
ShowMenuItems($id);

?>