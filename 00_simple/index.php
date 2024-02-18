<!DOCTYPE html>
<html>
<head>
	<title>00: Ukázka jednoduchého webu</title>
</head>
<body>
	<h1>Jednoduchý web</h1>

	<p>Znám tajemství a povím ti ho, když mi dáš heslo</p>

	<h2>Form s GET</h2>
	<form method="get" action="index.php">
		Heslo:
		<input type="password" name="heslo1">
		<input type="submit" value="Odeslat">
	</form>

<?php
	if (isset($_GET["heslo1"])) {
		if ($_GET["heslo1"] == "tajneheslo") {
			echo "Moje tajemství: <b>jsem super web</b>";
		} else {
			echo "špatné heslo :(";
		}
	}
?>

	<h2>Form s POST</h2>
	<form method="post" action="index.php">
		Heslo:
		<input type="password" name="heslo2">
		<input type="submit" value="Odeslat">
	</form>

<?php
	if (isset($_POST["heslo2"])) {
		if ($_POST["heslo2"] == "tajneheslo") {
			echo "Moje tajemství: <b>jsem super web</b>";
		} else {
			echo "špatné heslo :(";
		}
	}
?>

</body>
</html>
