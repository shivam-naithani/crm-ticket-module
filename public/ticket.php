<?php
session_start();
require "../app/config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);

    if (empty($name) || empty($description)) {
        die("All fields are required.");
    }

    $filePath = null;

    if (!empty($_FILES["file"]["name"])) {

        $uploadDir = "uploads/";
        $fileName = time() . "_" . basename($_FILES["file"]["name"]);
        $targetFile = $uploadDir . $fileName;

        $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedTypes)) {
            die("Invalid file type. Only JPG, PNG, PDF allowed.");
        }

        if ($_FILES["file"]["size"] > 2 * 1024 * 1024) {
            die("File too large. Max 2MB allowed.");
        }

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
            $filePath = $targetFile;
        } else {
            die("File upload failed.");
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO tickets (name, description, file, created_by)
        VALUES (:name, :description, :file, :created_by)
    ");

    $stmt->execute([
        ":name" => $name,
        ":description" => $description,
        ":file" => $filePath,
        ":created_by" => $_SESSION["user_id"]
    ]);

    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Ticket</title>
</head>
<body>

<h2>Create Ticket</h2>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Ticket Title" required>
    <br><br>
    <textarea name="description" placeholder="Ticket Description" required></textarea>
    <br><br>
    <label>Upload File:</label><br>
    <input type="file" name="file"><br><br> 
    <br><br>
    <button type="submit">Create Ticket</button>
    

</form>

</body>
</html>

