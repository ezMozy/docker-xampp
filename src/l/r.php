<?php
require 'db.php';

if (!isset($_GET['code'])) {
    die("Codice non valido.");
}

$short_code = $_GET['code'];

// Recupera il link originale
$stmt = $connection->prepare("SELECT link_originale, n_visite FROM Link WHERE link_short = ?");
$stmt->bind_param("s", $short_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Link non trovato.");
}

$link = $result->fetch_assoc();
$stmt->close();

// Aggiorna il contatore delle visite
$stmt = $connection->prepare("UPDATE Link SET n_visite = n_visite + 1 WHERE link_short = ?");
$stmt->bind_param("s", $short_code);
$stmt->execute();
$stmt->close();

// Reindirizza l'utente al link originale
header("Location: " . $link['link_originale']);
exit();