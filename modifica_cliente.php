<?php
session_start();
if (!isset($_SESSION['utente'])) {
    header('Location: login.php');
    exit();
}

include 'connessione.php';

$cliente_id = intval($_GET['id'] ?? 0);
$error = "";
$success = "";
$cliente = null;

// Recupero dati cliente solo se ID valido
if ($cliente_id > 0) {
    $stmt = $conn->prepare("SELECT nome, cognome, email, telefono FROM clienti WHERE id=?");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cliente = $result->fetch_assoc();

    if (!$cliente) {
        $error = "Cliente non trovato.";
    }
}

// Gestione invio form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cliente) {
    $updates = [];
    $params = [];
    $types = "";

    if (!isset($_POST['skip_nome'])) {
        $updates[] = "nome=?";
        $params[] = $_POST['nome'];
        $types .= "s";
    }
    if (!isset($_POST['skip_cognome'])) {
        $updates[] = "cognome=?";
        $params[] = $_POST['cognome'];
        $types .= "s";
    }
    if (!isset($_POST['skip_email'])) {
        $updates[] = "email=?";
        $params[] = $_POST['email'];
        $types .= "s";
    }
    if (!isset($_POST['skip_telefono'])) {
        $updates[] = "telefono=?";
        $params[] = $_POST['telefono'];
        $types .= "s";
    }

    if ($updates) {
        $sql = "UPDATE clienti SET " . implode(", ", $updates) . " WHERE id=?";
        $params[] = $cliente_id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            // reindirizzo alla lista clienti con messaggio successo
            header("Location: seleziona_cliente.php?success=Cliente aggiornato con successo");
            exit();
        } else {
            $error = "Errore durante l'aggiornamento del cliente.";
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
<title>Modifica Cliente</title>
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
input[type="text"], input[type="email"] { padding: 10px; border: 1px solid #ccc; border-radius: 8px; flex: 1; }
label { font-weight: 500; margin-right: 5px; }
button { margin-top: 20px; background-color: #e91e63; color: white; padding: 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
button:hover { background-color: #c2185b; }
.back { text-align: center; margin-top: 20px; }
.back a { text-decoration: none; color: #ad1457; font-weight: bold; }
.error { color: red; text-align: center; margin-bottom: 15px; font-weight: bold; }
</style>
</head>
<body>

<div class="form-box">
  <h1>Modifica Cliente</h1>

  <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

  <?php if ($cliente): ?>
  <form method="POST">
    <div class="row">
      <input type="text" name="nome" value="<?= htmlspecialchars($cliente['nome']) ?>">
      <label><input type="checkbox" name="skip_nome"> Non modificare</label>
    </div>
    <div class="row">
      <input type="text" name="cognome" value="<?= htmlspecialchars($cliente['cognome']) ?>">
      <label><input type="checkbox" name="skip_cognome"> Non modificare</label>
    </div>
    <div class="row">
      <input type="email" name="email" value="<?= htmlspecialchars($cliente['email']) ?>">
      <label><input type="checkbox" name="skip_email"> Non modificare</label>
    </div>
    <div class="row">
      <input type="text" name="telefono" value="<?= htmlspecialchars($cliente['telefono']) ?>">
      <label><input type="checkbox" name="skip_telefono"> Non modificare</label>
    </div>
    <button type="submit">Salva Modifiche</button>
  </form>
  <?php endif; ?>

  <div class="back">
    <a href="seleziona_cliente.php">&larr; Torna alla selezione clienti</a>
  </div>
</div>

</body>
</html>
