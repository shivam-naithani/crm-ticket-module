<?php
session_start();
require "../app/config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    die("Invalid request.");
}

$ticketId = (int) $_GET["id"];
$userId = $_SESSION["user_id"];

$stmt = $pdo->prepare("
    SELECT * FROM tickets
    WHERE id = :id
    AND (created_by = :user_id OR assigned_to = :user_id)
    AND deleted_at IS NULL
");

$stmt->execute([
    ":id" => $ticketId,
    ":user_id" => $userId
]);

$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    die("Unauthorized access.");
}

$isAuthor = ($ticket["created_by"] == $userId);
$isAssignee = ($ticket["assigned_to"] == $userId);

$error = "";

/* Load users for assignment (only if author) */
if ($isAuthor) {
    $stmtUsers = $pdo->prepare("
        SELECT id, name FROM users WHERE id != :current_user
    ");
    $stmtUsers->execute([":current_user" => $userId]);
    $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
}

/* Handle Update */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $status = trim($_POST["status"]);

    if ($isAuthor) {

        $name = trim($_POST["name"]);
        $description = trim($_POST["description"]);
        $assignedTo = !empty($_POST["assigned_to"]) ? (int)$_POST["assigned_to"] : null;

        if (empty($name) || empty($description)) {
            $error = "Title and description are required.";
        } else {

            $stmt = $pdo->prepare("
                UPDATE tickets
                SET name = :name,
                    description = :description,
                    status = :status,
                    assigned_to = :assigned_to,
                    assigned_at = CASE 
                        WHEN :assigned_to IS NOT NULL THEN NOW()
                        ELSE NULL
                    END,
                    updated_at = NOW()
                WHERE id = :id
            ");

            $stmt->execute([
                ":name" => $name,
                ":description" => $description,
                ":status" => $status,
                ":assigned_to" => $assignedTo,
                ":id" => $ticketId
            ]);

            header("Location: dashboard.php");
            exit;
        }

    } elseif ($isAssignee) {

        $stmt = $pdo->prepare("
            UPDATE tickets
            SET status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            ":status" => $status,
            ":id" => $ticketId
        ]);

        header("Location: dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">

            <div class="card shadow">

                <div class="card-header custom-header">
                    <h4 class="mb-0">Edit Ticket</h4>
                </div>

                <div class="card-body p-4">

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <?php if ($isAuthor): ?>

                        <div class="mb-3">
                            <label class="form-label">Ticket Title</label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="<?= htmlspecialchars($ticket['name']) ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description"
                                      class="form-control"
                                      rows="4"
                                      required><?= htmlspecialchars($ticket['description']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assign To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">-- Select User --</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>"
                                        <?= $ticket['assigned_to'] == $user['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php endif; ?>

                        <div class="mb-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" <?= $ticket['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="inprogress" <?= $ticket['status'] == 'inprogress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="completed" <?= $ticket['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="onhold" <?= $ticket['status'] == 'onhold' ? 'selected' : '' ?>>On Hold</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                Cancel
                            </a>

                            <button type="submit" class="btn custom-btn">
                                Update Ticket
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