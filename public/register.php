<?php
session_start();
require "../app/config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } 
    else {

        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $checkStmt->execute([":email" => $email]);

        if ($checkStmt->fetch()) {
            $error = "Email already registered.";
        } 
        else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password)
                VALUES (:name, :email, :password)
            ");

            $stmt->execute([
                ":name" => $name,
                ":email" => $email,
                ":password" => $hashedPassword
            ]);

            // ✅ Fetch newly created user
            $getUser = $pdo->prepare("SELECT id, name, email FROM users WHERE email = :email");
            $getUser->execute([":email" => $email]);
            $user = $getUser->fetch(PDO::FETCH_ASSOC);

            // ✅ Auto login
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];

            header("Location: dashboard.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="d-flex align-items-center" style="min-height:100vh;">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">

            <div class="card shadow">

                <!-- Header -->
                <div class="card-header custom-header text-center">
                    <h4 class="mb-0">Create Account</h4>
                </div>

                <div class="card-body p-4">

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   placeholder="Enter your full name"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   placeholder="Enter your email"
                                   required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   placeholder="Enter your password"
                                   required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn custom-btn">
                                Register
                            </button>
                        </div>

                    </form>

                    <hr>

                    <div class="text-center">
                        <small>
                            Already have an account?
                            <a href="login.php" class="fw-semibold">Login here</a>
                        </small>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
