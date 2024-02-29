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
$db->query("CREATE TABLE IF NOT EXISTS lajky (prispevekID int, name string)");

if (isset($_SESSION['login']) && $_SESSION['login']) {

	// Vložení nového příspěvku
	if (isset($_POST["text"])) {
		$stmt = $db->prepare("INSERT INTO prispevky (autor, text) VALUES (:autor, :text)");
		$stmt->bindValue(':autor', $_SESSION['name']);
		$stmt->bindValue(':text', $_POST['text']);
		$stmt->execute();

		$db->close();
		header('Location: .');
		exit();
	}

	// Přidání lajku
	if (isset($_GET['like'])) {
		$stmt = $db->prepare("INSERT INTO lajky (prispevekID, name) VALUES (:prispevekID, :name)");
		$stmt->bindValue(':prispevekID', $_GET['like']);
		$stmt->bindValue(':name', $_SESSION['name']);
		$stmt->execute();

		$db->close();
		header('Location: .');
		exit();
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>06: CSRF</title>
</head>
<body>

<h1>Skvělý chat jen pro přihlášené</h1>

<div style="float: right">
<?php
if (isset($_SESSION['login']) && $_SESSION['login']) {
	echo "Přihlášený uživatel: <b>".$_SESSION['name']."</b>";
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
		echo "<b>Autor:</b> ".htmlspecialchars($row['autor'])."<br>
			<b>Text:</b><br>\n".htmlspecialchars($row['text'])."<br>
			<b>Lajky:</b> ";

		$likes = $db->query("SELECT * FROM lajky WHERE prispevekID=".$row['rowid']);
		while ($like = $likes->fetchArray()) {
			echo $like['name'].", ";
		}

		if (isset($_SESSION['login']) && $_SESSION['login']) {
			echo "<br><a href='?like=".$row['rowid']."'>Dát lajk</a>";
		}
		echo "<hr>\n";
	}
} else {
	echo $db->lastErrorMsg();
}

$db->close();

if (isset($_SESSION['login']) && $_SESSION['login']) {
	echo '<h3>Vložit příspěvek:</h3>

	<form method="POST">
		Text: <textarea name="text" cols=40 rows=5></textarea><br>
		<input type="submit" value="Vložit">
	</form>';
}
?>

</body>
</html>
