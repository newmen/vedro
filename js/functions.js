function GoURL(url)
{
	window.location = url;
}

function ReloadWithID(id)
{
	GoURL("?id=" + id);
}