<!DOCTYPE html>
<html>
<head>
	<title>03: Upload example</title>
</head>
<body>
	<h1>Nahrajte si soubor, abyste ho mohli přidat do příspěvku:</h1>

<?php
	if (isset($_POST['submit'])) {
		$filename = $_FILES['upload']['name'];
		$fileTmpName = $_FILES['upload']['tmp_name'];

		$uploadPath = "images/" . basename($filename);

		move_uploaded_file($fileTmpName, $uploadPath);
		echo "Soubor nahrán: <a href='$uploadPath'>$uploadPath</a>";

		echo "<hr>";
	}
?>

	<form action="index.php" method="post" enctype="multipart/form-data">
		Vyberte obrázek pro nahrání:
		<input type="file" name="upload" id="upload">
		<input type="submit" value="Upload" name="submit">
	</form>
</body>
</html>
