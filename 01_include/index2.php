<!DOCTYPE html>
<html>
<head>
	<title>02: Include example</title>
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
		$path = realpath("stranky/".$_GET['stranka']);
		$dir = dirname(__FILE__)."/stranky";
		if (str_starts_with($path, $dir)) {
			include($path);
		} else {
			echo "TY HACKUJEŠ, ŽE JO? NO TFUJ!";
		}
	} else {
		include("stranky/domu");
	}
?>

</body>
</html>
