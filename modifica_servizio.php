<?php
session_start();
if (!isset($_SESSION['utente'])) {
    header('Location: login.php');
    exit();
}

include 'connessione.php';

$servizio_id = intval($_GET['id'] ?? 0);
$error = "";
$success = "";
$servizio = null;

// Recupero dati servizio solo se ID valido
if ($servizio_id > 0) {
    $stmt = $conn->prepare("SELECT nome_servizio, descrizione, prezzo FROM servizi WHERE id=?");
    $stmt->bind_param("i", $servizio_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $servizio = $result->fetch_assoc();

    if (!$servizio) {
        $error = "Servizio non trovato.";
    }
}

// Gestione invio form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $servizio) {
    $updates = [];
    $params = [];
    $types = "";

    if (!isset($_POST['skip_nome'])) {
        $updates[] = "nome_servizio=?";
        $params[] = $_POST['nome_servizio'];
        $types .= "s";
    }
    if (!isset($_POST['skip_descrizione'])) {
        $updates[] = "descrizione=?";
        $params[] = $_POST['descrizione'];
        $types .= "s";
    }
    if (!isset($_POST['skip_prezzo'])) {
        $updates[] = "prezzo=?";
        $params[] = $_POST['prezzo'];
        $types .= "d";
    }

    if ($updates) {
        $sql = "UPDATE servizi SET " . implode(", ", $updates) . " WHERE id=?";
        $params[] = $servizio_id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $success = "Servizio aggiornato con successo!";
        } else {
            $error = "Errore durante l'aggiornamento del servizio.";
        }
    } else {
        $error = "Tutti i campi sono stati saltati, niente da aggiornare.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Modifica Servizio</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
<style>
body {
  font-family: 'Space Grotesk', sans-serif;
  background: linear-gradient(135deg, #f9c5d1, #fddde6);
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  margin: 0;
}
.form-box {
  background: rgba(255, 255, 255, 0.9);
  padding: 30px;
  border-radius: 15px;
  box-shadow: 0 0 20px rgba(255, 105, 180, 0.3);
  max-width: 500px;
  width: 100%;
}
h1 { text-align: center; color: #880e4f; margin-bottom: 20px; }
form { display: flex; flex-direction: column; gap: 15px; }
.row { display: flex; justify-content: space-between; align-items: center; gap: 10px; }
input[type="text"], input[type="number"], textarea { padding: 10px; border: 1px solid #ccc; border-radius: 8px; flex: 1; }
textarea { resize: vertical; }
label { font-weight: 500; margin-right: 5px; }
button { margin-top: 20px; background-color: #e91e63; color: white; padding: 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
button:hover { background-color: #c2185b; }
.back { text-align: center; margin-top: 20px; }
.back a { text-decoration: none; color: #ad1457; font-weight: bold; }
.error { color: red; text-align: center; margin-bottom: 15px; font-weight: bold; }
.success { color: green; text-align: center; margin-bottom: 15px; font-weight: bold; }
</style>
</head>
<body>

<div class="form-box">
  <h1>Modifica Servizio</h1>

  <?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
  <?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
  <?php endif; ?>

  <?php if ($servizio): ?>
  <form method="POST">
    <div class="row">
      <input type="text" name="nome_servizio" value="<?= htmlspecialchars($servizio['nome_servizio']) ?>">
      <label><input type="checkbox" name="skip_nome"> Non modificare</label>
    </div>
    <div class="row">
      <textarea name="descrizione"><?= htmlspecialchars($servizio['descrizione']) ?></textarea>
      <label><input type="checkbox" name="skip_descrizione"> Non modificare</label>
    </div>
    <div class="row">
      <input type="number" step="0.01" name="prezzo" value="<?= htmlspecialchars($servizio['prezzo']) ?>">
      <label><input type="checkbox" name="skip_prezzo"> Non modificare</label>
    </div>
    <button type="submit">Salva Modifiche</button>
  </form>
  <?php endif; ?>

  <div class="back">
    <a href="seleziona_servizio.php">&larr; Torna alla selezione servizi</a>
  </div>
</div>

</body>
</html>
