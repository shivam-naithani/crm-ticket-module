<?php
session_start();
require "../app/config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

if (!isset($_POST["id"])) {
    die("Invalid request.");
}

$ticketId = (int) $_POST["id"];
$userId = $_SESSION["user_id"];

/* Verify ownership */
$stmt = $pdo->prepare("
    SELECT id FROM tickets
    WHERE id = :id
    AND created_by = :user_id
    AND deleted_at IS NULL
");

$stmt->execute([
    ":id" => $ticketId,
    ":user_id" => $userId
]);

$ticket = $stmt->fetch();

if (!$ticket) {
    die("Unauthorized action.");
}

/* Soft delete */
$stmt = $pdo->prepare("
    UPDATE tickets
    SET deleted_at = NOW()
    WHERE id = :id
");

$stmt->execute([
    ":id" => $ticketId
]);

header("Location: dashboard.php");
exit;
