<!DOCTYPE html>
<html>
<head>
	<title>03: SQL injection</title>
</head>
<body>

<h3>Vložit klíč:</h3>

<form method="POST">
	Klíč: <input name="new_key">
	Hodnota: <input type="password" name="new_value" autocomplete="off">
	<input type="submit">
</form>

<h3>Dotaz na klíč:</h3>

<form method="POST">
	Klíč: <input name="key" value="<?php  if (isset($_POST['key'])) echo htmlspecialchars($_POST['key']); ?>">
	<input type="submit">
</form>

<hr>

<?php
$db = new SQLite3('../db.sqlite');
if (!$db) {
	echo $db->lastErrorMsg();
}
$db->query("CREATE TABLE IF NOT EXISTS keyval (key string PRIMARY KEY, value string)");

if (isset($_POST["new_key"])) {
	$key = $_POST["new_key"];
	$val = $_POST["new_value"];

	# NE: $sql = "INSERT INTO keyval (key, value) VALUES ('$key', '$val')";

	$stmt = $db->prepare("INSERT INTO keyval (key, value) VALUES (:key, :value)");
	$stmt->bindValue(':key', $key);
	$stmt->bindValue(':value', $val);

	if ($stmt->execute()) {
		echo "Nový klíč <b>".$key."</b> vložen";
	} else {
		echo $db->lastErrorMsg();
	}

} else if (isset($_POST["key"])) {
	$key = $_POST["key"];

	# NE: $sql = "SELECT * FROM keyval WHERE key='$key'";


	$sql = "SELECT * FROM keyval WHERE key='".SQLite3::escapeString($key)."'";



	# Pro ukázku, jak to dělá MySQL (dummy user):
	$mysql = new mysqli('localhost', 'sql_injection_user', 'eiMeijiewaThiile3zei');
	$mysql_sql = "SELECT * FROM keyval WHERE key='".mysqli_real_escape_string($mysql, $key)."'";

	echo "<pre>SQLite:" .$sql."\nMySQL: ".$mysql_sql."</pre><br>";
	$results = $db->query($sql);
	if ($results) {
		$row = $results->fetchArray();
		if ($row) {
			echo "Hodnota pro klíč <b>".$row['key']."</b> je: <b>".$row['value']."</b>";
		} else {
			echo "Takový klíč v databázi neexistuje";
		}
	} else {
		echo $db->lastErrorMsg();
	}
}

$db->close();
?>
</body>
</html>
