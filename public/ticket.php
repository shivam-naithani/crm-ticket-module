<?php
session_start();
require "../app/config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);

    if (empty($name) || empty($description)) {
        $error = "All fields are required.";
    } else {

        $filePath = null;

        if (!empty($_FILES["file"]["name"])) {

            $uploadDir = "uploads/";
            $fileName = time() . "_" . basename($_FILES["file"]["name"]);
            $targetFile = $uploadDir . $fileName;

            $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedTypes)) {
                $error = "Invalid file type. Only JPG, PNG, PDF allowed.";
            } elseif ($_FILES["file"]["size"] > 2 * 1024 * 1024) {
                $error = "File too large. Max 2MB allowed.";
            } else {
                if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
                    $filePath = $targetFile;
                } else {
                    $error = "File upload failed.";
                }
            }
        }

        if (empty($error)) {
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
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">

            <div class="card shadow">

                <div class="card-header custom-header">
                    <h4 class="mb-0">Create New Ticket</h4>
                </div>

                <div class="card-body p-4">

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label">Ticket Title</label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   placeholder="Enter ticket title"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description"
                                      class="form-control"
                                      rows="4"
                                      placeholder="Describe the issue..."
                                      required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Upload File (Optional)</label>
                            <input type="file"
                                   name="file"
                                   class="form-control">
                            <div class="form-text">
                                Allowed: JPG, PNG, PDF (Max 2MB)
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                Cancel
                            </a>

                            <button type="submit" class="btn custom-btn">
                                Create Ticket
                            </button>
                        </div>

                    </form>

                </div>

            </div>

        </div>
    </div>

</div>

</body>
</html>