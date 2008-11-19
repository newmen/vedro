var c = 0;
var d = document;
table = d.getElementById('t_search').getElementsByTagName('tbody')[0];
function AddRow(column, type, text)
{
 var id = table.rows.length;
 var row = d.createElement('tr');
 table.appendChild(row);
 var td_col = d.createElement('td');
 var td_type = d.createElement('td');
 var td_text = d.createElement('td');
 row.appendChild(td_col);
 row.appendChild(td_type);
 row.appendChild(td_text);
 td_col.align = 'center';
 td_col.className = 'cenable_activebad' + (++c % 2 + 1);
 td_col.innerHTML = '<table border=0 cellspacing=0 cellpadding=0>'
 + '<tr class="cenable_activebad' + (c % 2 + 1) + '"><td>Поле:&nbsp;</td>'
 + '<td>'
 + '<select id="search_' + id + '_column" name="search_' + id + '_column">'
 + '<option value="id">ID</option>'
 + '<option value="login" selected>Логин</option>'
 + '<option value="full_name">Полное имя</option>'
 + '<option value="actual_address">Адрес</option>'
 + '<option value="mobile_telephone">Мобильный</option>'
 + '</select>'
 + '</td></tr>'
 + '</table>';
 td_type.align = 'center';
 td_type.className = 'cenable_activebad' + (++c % 2 + 1);
 td_type.innerHTML = '<table border=0 cellspacing=0 cellpadding=0>'
 + '<tr class="cenable_activebad' + (c % 2 + 1) + '"><td>Тип поиска:&nbsp;</td>'
 + '<td>'
 + '<select id="search_' + id + '_type" name="search_' + id + '_type">'
 + '<option value="1">совпадает с</option>'
 + '<option value="2">содержит</option>'
 + '<option value="3">начинается с</option>'
 + '<option value="4">заканчивается на</option>'
 + '</select>'
 + '</td></tr>'
 + '</table>';
 td_text.align = 'center';
 td_text.className = 'cenable_activebad' + (++c % 2 + 1);
 td_text.innerHTML = '<table border=0 cellspacing=0 cellpadding=0>'
 + '<tr class="cenable_activebad' + (c % 2 + 1) + '"><td>Текст:&nbsp;</td>'
 + '<td>'
 + '<input type="text" id="search_' + id + '_text" name="search_' + id + '_text">'
 + '</td></tr>'
 + '</table>';
 if(id == 0)
 {
 var td_search = d.createElement('td');
 var td_add = d.createElement('td');
 row.appendChild(td_search);
 row.appendChild(td_add);
 td_search.id = 't_search_bcol';
 td_search.align = 'center';
 td_search.className = 'cenable_activebad' + (++c % 2 + 1);
 td_search.innerHTML = '<input type="submit" id="t_serv" value="Найти">';
 td_add.id = 't_search_badd';
 td_add.align = 'center';
 td_add.className = 'cenable_activebad' + (++c % 2 + 1);
 td_add.innerHTML = '<input type="button" value="Ещё" onClick="AddRow(\'login\', 2, \'\')">';
 }
 else {
 search_button = d.getElementById('t_search_bcol');
 search_button.rowSpan++;
 more_button = d.getElementById('t_search_badd');
 more_button.rowSpan++;
 }
 var s_column = getElementById('search_' + id + '_column);
 var s_type = getElementById('search_' + id + '_type);
 var s_text = getElementById('search_' + id + '_text);
 s_column.value = column;
 s_type.value = type;
 s_text.value = text;
}

/*
function AddRow(c)
{
	c++;
	var d = document;
	
	search_button = d.getElementById('t_search_bcol');
	more_button = d.getElementById('t_search_badd');
	table = d.getElementById('t_search').getElementsByTagName('tbody')[0];
	
	var row = d.createElement('tr');
	table.appendChild(row);
	
	var td_col = d.createElement('td');
	var td_type = d.createElement('td');
	var td_text = d.createElement('td');
	var td_badd = d.createElement('td');
	
	row.appendChild(td_col);
	row.appendChild(td_type);
	row.appendChild(td_text);
	row.appendChild(td_badd);
	
	td_col.align = "center";
	td_col.className = "cenable_activebad" + (++c % 2 + 1);
	td_col.innerHTML = "<table border=0 cellspacing=0 cellpadding=0>"
		 + "<tr class=\"cenable_activebad" + (c % 2 + 1) + "\"><td>Поле:&nbsp;</td>\n"
		 + "<td>"
		 + "<select name=\"search_col\">\n"
		 + "<option value=\"321\""
		 + ">123</option>\n"
		 + "</select>\n"
		 + "</td></tr>"
		 + "</table>\n";

	td_type.align = "center";
	td_type.className = "cenable_activebad" + (++c % 2 + 1);
	td_type.innerHTML = "<table border=0 cellspacing=0 cellpadding=0>"
		 + "<tr class=\"cenable_activebad" + (c % 2 + 1) + "\"><td>Тип поиска:&nbsp;</td>\n"
		 + "<td>"
		 + "<select name=\"search_type\">\n"
		 + "<option value=\"1\">совпадает с</option>"
		 + "<option value=\"2\" selected>содержит</option>"
		 + "<option value=\"3\">начинается с</option>"
		 + "<option value=\"4\">заканчивается на</option>"
		 + "</select>\n"
		 + "</td></tr>"
		 + "</table>\n";
	
	td_text.align = "center";
	td_text.className = "cenable_activebad" + (++c % 2 + 1);
	td_text.innerHTML = "<table border=0 cellspacing=0 cellpadding=0>"
		 + "<tr class=\"cenable_activebad" + (c % 2 + 1) + "\"><td>Текст:&nbsp;</td>\n"
		 + "<td>"
		 + "<input type=\"text\" name=\"search_text\">\n"
		 + "</td></tr>"
		 + "</table>\n";
	
	search_button.rowSpan++;
	more_button.rowSpan++;
	
}
*/
/*
		 . "<tr>"
		 . "<td class=\"cenable_activebad".(++$c % 2 + 1)."\" align=\"center\">"
		 . "<input type=\"hidden\" name=\"t_name\" value=\"".$this->name."\">\n"
		 . "<table border=0 cellspacing=0 cellpadding=0>"
		 . "<tr class=\"cenable_activebad".($c % 2 + 1)."\"><td>Поле:&nbsp;</td>\n"
		 . "<td>"
		 . "<select id=\"search_column\" name=\"search_column\">\n";
		
		
		echo "</select>\n"
		 . "</td></tr>"
		 . "</table>\n"
		 . "</td>"
		 . "<td class=\"cenable_activebad".(++$c % 2 + 1)."\" align=\"center\">"
		 . "<table border=0 cellspacing=0 cellpadding=0>"
		 . "<tr class=\"cenable_activebad".($c % 2 + 1)."\"><td>Тип поиска:&nbsp;</td>\n"
		 . "<td>"
		 . "<select id=\"search_type\" name=\"search_type\">\n"
		 . "<option value=\"1\">совпадает с</option>"
		 . "<option value=\"2\" selected>содержит</option>"
		 . "<option value=\"3\">начинается с</option>"
		 . "<option value=\"4\">заканчивается на</option>"
		 . "</select>\n"
		 . "</td></tr>"
		 . "</table>\n"
		 . "</td>"
		 . "<td class=\"cenable_activebad".(++$c % 2 + 1)."\" align=\"center\">"
		 . "<table border=0 cellspacing=0 cellpadding=0>"
		 . "<tr class=\"cenable_activebad".($c % 2 + 1)."\"><td>Текст:&nbsp;</td>\n"
		 . "<td>"
		 . "<input type=\"text\" name=\"search_text\">\n"
		 . "</td></tr>"
		 . "</table>\n"
		 . "</td>"
		 . "<td rowspan=1 id=\"t_search_bcol\" class=\"cenable_activebad".(++$c % 2 + 1)."\" align=\"center\">"
		 . "<input type=\"submit\" id=\"t_serv\" value=\"Найти\">\n"
		 . "</td>"
		 . "<td rowspan=1 id=\"t_search_badd\" class=\"cenable_activebad".(++$c % 2 + 1)."\" align=\"center\">"
		 . "<input type=\"button\" value=\"Ещё\" onClick=\"AddRow(".++$c.")\">\n"
		 . "</td>"		 
		 . "</tr>"
*/

