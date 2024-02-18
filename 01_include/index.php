<!DOCTYPE html>
<html>
<head>
	<title>01: Include example</title>
</head>
<body>
	<h1>Můj webík s podstránkami</h1>

	<p>Menu:
		<a href="?stranka=domu">Index</a>,
		<a href="?stranka=odkazy">Zajímavé odkazy</a>,
		<a href="?stranka=kontakty">Kontakty</a>
	</p>

<?php
	if (isset($_GET['stranka'])) {
		include("stranky/".$_GET['stranka']);
	} else {
		include("stranky/domu");
	}
?>

</body>
</html>
