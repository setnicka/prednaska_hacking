<!DOCTYPE html>
<html>
<head>
	<title>03: Upload example</title>
</head>
<body>
	<h1>Nahrajte si soubor, abyste ho mohli přidat do příspěvku:</h1>

<?php
	$fileExtensionsAllowed = ['jpeg','jpg','png'];

	if (isset($_POST['submit'])) {
		$filename = $_FILES['upload']['name'];
		$fileTmpName  = $_FILES['upload']['tmp_name'];

		$uploadPath = "images/" . basename($filename);

		$errors = [];
		$parts = explode('.', $filename);
		$fileExtension = strtolower(end($parts));
		if (!in_array($fileExtension, $fileExtensionsAllowed)) {
			$errors[] = "Nepovolená přípona, povolené jsou jenom jpeg, jpg nebo png";
		}
		if ($_FILES['upload']['size'] > 4000000) {
			$errors[] = "Příliš velký soubor (limit 4MB)";
		}

		if (empty($errors)) {
			move_uploaded_file($fileTmpName, $uploadPath);
			echo "Soubor nahrán: <a href='$uploadPath'>$uploadPath</a>";

		} else {
			foreach ($errors as $error) {
				echo "Chyba: " . $error . "\n<br>";
			}
		}

		echo "<hr>";
	}
?>

	<form action="index2.php" method="post" enctype="multipart/form-data">
		Vyberte obrázek pro nahrání:
		<input type="file" name="upload" id="upload">
		<input type="submit" value="Upload" name="submit">
	</form>
</body>
</html>
