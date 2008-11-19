function createRequestObject()
{
	var ro;
	var browser = navigator.appName;
	if(browser == 'Microsoft Internet Explorer') ro = new ActiveXObject('Microsoft.XMLHTTP');
	else ro = new XMLHttpRequest();
	
	return ro;
}

var http = createRequestObject();

function sndReq(request, script)
{
	if(typeof script == 'undefined') script = 'ajax.php';
	
	http.open('get', '/' + script + '?' + request, false);
	//http.onreadystatechange = handleResponse;
	http.send(null);
	
	if(http.status == 200)
	{
		var response = http.responseText;
		var update = new Array();
	
		if(response.indexOf('|') != -1)
		{
			update = response.split('|');
			for(i=0; i<update.length; i+=2)
				document.getElementById(update[i]).innerHTML = update[i+1];
		}
	}
}

/*
function handleResponse()
{
	if(http.readyState != 4) return;

	var response = http.responseText;
	var update = new Array();

	if(response.indexOf('|') != -1)
	{
		update = response.split('|');
		for(i=0; i<update.length; i+=2)
			document.getElementById(update[i]).innerHTML = update[i+1];
	}
}
*/
