<?php
// Start PHP sessions (pro přihlášení)
session_start();

if (isset($_POST['logout'])) {
	session_destroy();
	header('Location: .');
	exit();
}

// Obstarání přihlášení = zkontrolujeme heslo a kdyžtak nastavíme SESSION
if (isset($_POST['login']) and isset($_POST['passwd'])) {
	if ($_POST['login'] == "admin" && $_POST['passwd'] == "heslo123") {
		$_SESSION["login"] = true;
		$_SESSION["admin"] = true;
		$_SESSION["name"] = "Jára Cimrman";

		header('Location: .');
		exit();
	}
}

////////////////////////////////////////////////////////////////////////////////

// Vytvoření databáze a připojení k ní
$db = new SQLite3('../db.sqlite');
if (!$db) {
	echo $db->lastErrorMsg();
}
$db->query("CREATE TABLE IF NOT EXISTS prispevky (autor string, text string)");

// Vložení nového příspěvku
if (isset($_POST["text"])) {
	$stmt = $db->prepare("INSERT INTO prispevky (autor, text) VALUES (:autor, :text)");
	$stmt->bindValue(':autor', $_POST['autor']);
	$stmt->bindValue(':text', $_POST['text']);
	$stmt->execute();

	$db->close();
	header('Location: .');
	exit();
}

if (isset($_POST["delete"]) && $_SESSION["admin"]) {
	$stmt = $db->prepare("DELETE FROM prispevky WHERE rowid=:id");
	$stmt->bindValue(':id', $_POST['delete']);
	$stmt->execute();

	$db->close();
	header('Location: .');
	exit();
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>05: XSS</title>
</head>
<body>

<h1>Skvělý chat</h1>

<div style="float: right">
<?php
if (isset($_SESSION['login']) && $_SESSION['login']) {
	echo "Přihlášený uživatel: <b>".$_SESSION['name']."</b>";
	if ($_SESSION['admin']) {
		echo " <b>ADMIN</b>";
	}
	echo "<br><form method='POST'><input type='submit' name='logout' value='Odhlásit'></form>\n";
} else {
	echo "<form method='POST'>
	Login:<input name='login'>
	Passwd:<input type='password' name='passwd'>
	<input type='submit' value='Login'>
	</form>";
}
?>
</div>

<?php
// Vypsání všech příspěvků
$results = $db->query("SELECT rowid, * FROM prispevky");
if ($results) {
	while ($row = $results->fetchArray()) {
		// Špatný způsob
		echo "<b>Autor:</b> ".$row['autor']."<br>
			<b>Text:</b><br>\n".$row['text']."\n";
		// Správný způsob:
		// echo "<b>Autor:</b> ".htmlspecialchars($row['autor'])."<br>
		// 	<b>Text:</b><br>\n".htmlspecialchars($row['text'])."\n";

		if (isset($_SESSION["admin"]) && $_SESSION["admin"] == true) {
			echo "<br><form method='POST'>
			<input type='hidden' name='delete' value='".$row['rowid']."'>
			<input type='submit' value='Smazat'></form>";
		}
		echo "<hr>\n";
	}
} else {
	echo $db->lastErrorMsg();
}

$db->close();
?>

<h3>Vložit příspěvek:</h3>

<form method="POST">
	Autor: <input type="text" name="autor"><br>
	Text: <textarea name="text" cols=40 rows=5></textarea><br>
	<input type="submit" value="Vložit">
</form>

</body>
</html>
