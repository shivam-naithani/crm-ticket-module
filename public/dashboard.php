<?php
session_start();
require "../app/config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];

$stmt = $pdo->prepare("
    SELECT 
        tickets.*,
        users.name AS assigned_user_name
    FROM tickets
    LEFT JOIN users ON tickets.assigned_to = users.id
    WHERE (tickets.created_by = :user_id 
           OR tickets.assigned_to = :user_id)
    AND tickets.deleted_at IS NULL
    ORDER BY tickets.created_at DESC
");

$stmt->execute([":user_id" => $userId]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>

<h2>Welcome to Dashboard</h2>

<p>
    <a href="ticket.php">Create New Ticket</a> |
    <a href="logout.php">Logout</a>
</p>

<h3>Your Tickets</h3>

<?php if (empty($tickets)): ?>
    <p>No tickets found.</p>
<?php else: ?>

<table border="1" cellpadding="8">
    <tr>
        <th>Title</th>
        <th>Description</th>
        <th>Status</th>
        <th>Created At</th>
        <th>Assigned To</th>
        <th>File</th>
        <th>Actions</th>
    </tr>

    <?php foreach ($tickets as $ticket): ?>
    <tr>
        <td><?= htmlspecialchars($ticket["name"]) ?></td>
        <td><?= htmlspecialchars($ticket["description"]) ?></td>
        <td><?= htmlspecialchars($ticket["status"]) ?></td>
        <td><?= htmlspecialchars($ticket["created_at"]) ?></td>

        <td>
            <?= $ticket["assigned_user_name"] 
                ? htmlspecialchars($ticket["assigned_user_name"]) 
                : "Not Assigned" ?>
        </td>

        <td>
            <?php if (!empty($ticket["file"])): ?>
                <a href="<?= htmlspecialchars($ticket["file"]) ?>" target="_blank">
                    View
                </a>
            <?php else: ?>
                No File
            <?php endif; ?>
        </td>

        <td>
            <a href="edit-ticket.php?id=<?= $ticket['id'] ?>">Edit</a>

            <?php if ($ticket["created_by"] == $userId): ?>
                |
                <form method="POST" action="delete-ticket.php" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $ticket['id'] ?>">
                    <button type="submit"
                        onclick="return confirm('Are you sure you want to delete this ticket?');">
                        Delete
                    </button>
                </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>

</table>

<?php endif; ?>

</body>
</html>
