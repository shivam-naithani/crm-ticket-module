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

/* Fetch users only if author */
if ($isAuthor) {
    $stmtUsers = $pdo->prepare("
        SELECT id, name 
        FROM users 
        WHERE id != :current_user
    ");

    $stmtUsers->execute([
        ":current_user" => $userId
    ]);

    $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
}

/* Handle POST */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $status = trim($_POST["status"]);

    if ($isAuthor) {

        $name = trim($_POST["name"]);
        $description = trim($_POST["description"]);
        $assignedTo = !empty($_POST["assigned_to"]) ? (int)$_POST["assigned_to"] : null;

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
    }

    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Ticket</title>
</head>
<body>

<h2>Edit Ticket</h2>

<form method="POST">

<?php if ($isAuthor): ?>

    <label>Assign To:</label><br>
    <select name="assigned_to">
        <option value="">-- Select User --</option>

        <?php foreach ($users as $user): ?>
            <option value="<?= $user['id'] ?>"
                <?= $ticket['assigned_to'] == $user['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($user['name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Title:</label><br>
    <input type="text" name="name"
        value="<?= htmlspecialchars($ticket['name']) ?>" required><br><br>

    <label>Description:</label><br>
    <textarea name="description" required><?= htmlspecialchars($ticket['description']) ?></textarea><br><br>

<?php endif; ?>

    <label>Status:</label><br>
    <select name="status">
        <option value="pending" <?= $ticket['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="inprogress" <?= $ticket['status'] == 'inprogress' ? 'selected' : '' ?>>In Progress</option>
        <option value="completed" <?= $ticket['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
        <option value="onhold" <?= $ticket['status'] == 'onhold' ? 'selected' : '' ?>>On Hold</option>
    </select><br><br>

    <button type="submit">Update Ticket</button>

</form>

<p><a href="dashboard.php">Back to Dashboard</a></p>

</body>
</html>
