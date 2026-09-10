<?php
session_start();
if (!isset($_SESSION['utente'])) {
    header('Location: login.php');
    exit();
}

include 'connessione.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_servizio = trim($_POST['nome_servizio'] ?? '');
    $descrizione = trim($_POST['descrizione'] ?? '');
    $prezzo = floatval($_POST['prezzo'] ?? 0);

    if ($nome_servizio && $descrizione && $prezzo > 0) {
        $stmt = $conn->prepare("INSERT INTO servizi (nome_servizio, descrizione, prezzo) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $nome_servizio, $descrizione, $prezzo);
        if ($stmt->execute()) {
            header("Location: dashboard.php?success=Servizio aggiunto con successo");
            exit();
        } else {
            header("Location: dashboard.php?error=Errore durante l'inserimento servizio");
            exit();
        }
    } else {
        header("Location: dashboard.php?error=Compila tutti i campi obbligatori correttamente");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>
