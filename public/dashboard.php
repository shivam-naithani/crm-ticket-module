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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Dashboard</h1>
        <div>
            <a href="ticket.php" class="btn btn-primary me-2">Create New Ticket</a>
            <a href="logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>

    <!-- Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Your Tickets</h5>
        </div>

        <div class="card-body p-0">

            <?php if (empty($tickets)): ?>
                <div class="p-4">
                    <div class="alert alert-info mb-0">
                        No tickets found.
                    </div>
                </div>
            <?php else: ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Assigned To</th>
                            <th>File</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($tickets as $ticket): ?>
                        <tr>

                            <!-- Title -->
                            <td><?= htmlspecialchars($ticket["name"]) ?></td>

                            <!-- Status Badge -->
                            <td>
                                <?php
                                    $status = strtolower($ticket["status"]);
                                    $badgeClass = match($status) {
                                        "pending" => "bg-warning text-dark",
                                        "inprogress" => "bg-primary",
                                        "completed" => "bg-success",
                                        "onhold" => "bg-danger",
                                        default => "bg-secondary"
                                    };
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= ucfirst(htmlspecialchars($status)) ?>
                                </span>
                            </td>

                            <!-- Created At -->
                            <td><?= htmlspecialchars($ticket["created_at"]) ?></td>

                            <!-- Assigned To -->
                            <td>
                                <?= $ticket["assigned_user_name"] 
                                    ? htmlspecialchars($ticket["assigned_user_name"]) 
                                    : '<span class="text-muted">Not Assigned</span>' ?>
                            </td>

                            <!-- File -->
                            <td>
                                <?php if (!empty($ticket["file"])): ?>
                                    <a href="<?= htmlspecialchars($ticket["file"]) ?>" 
                                       target="_blank"
                                       class="btn btn-sm btn-info">
                                       View
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No File</span>
                                <?php endif; ?>
                            </td>

                            <!-- Actions -->
                            <td class="text-center">

                                <a href="edit-ticket.php?id=<?= $ticket['id'] ?>" 
                                   class="btn btn-sm btn-success me-1">
                                   Edit
                                </a>

                                <?php if ($ticket["created_by"] == $userId): ?>
                                    <form method="POST" action="delete-ticket.php" class="d-inline">
                                        <input type="hidden" name="id" value="<?= $ticket['id'] ?>">
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete this ticket?');">
                                            Delete
                                        </button>
                                    </form>
                                <?php endif; ?>

                            </td>

                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>
            </div>

            <?php endif; ?>

        </div>
    </div>

</div>

</body>
</html>
