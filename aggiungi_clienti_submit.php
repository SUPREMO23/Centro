<?php
session_start();
if (!isset($_SESSION['utente'])) {
    header('Location: login.php');
    exit();
}

include 'connessione.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if ($nome && $cognome && $email) {
        $stmt = $conn->prepare("INSERT INTO clienti (nome, cognome, email, telefono) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nome, $cognome, $email, $telefono);
        if ($stmt->execute()) {
            header("Location: dashboard.php?success=Cliente aggiunto con successo");
            exit();
        } else {
            header("Location: dashboard.php?error=Errore durante l'inserimento cliente");
            exit();
        }
    } else {
        header("Location: dashboard.php?error=Compila tutti i campi obbligatori");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>
