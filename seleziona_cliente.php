<?php
session_start();
if (!isset($_SESSION['utente'])) {
    header('Location: login.php');
    exit();
}

include 'connessione.php';

// Recupero tutti i clienti
$result = $conn->query("SELECT id, nome, cognome, email FROM clienti ORDER BY nome ASC");

// Controllo se arriva messaggio di successo via GET
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Seleziona Cliente</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
<style>
body {
  font-family: 'Space Grotesk', sans-serif;
  background: linear-gradient(135deg, #f9c5d1, #fddde6);
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
}
.box {
  background: rgba(255, 255, 255, 0.9);
  padding: 30px;
  border-radius: 15px;
  box-shadow: 0 0 20px rgba(255, 105, 180, 0.3);
  max-width: 600px;
  width: 100%;
}
h1 { text-align: center; color: #880e4f; margin-bottom: 20px; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 10px; border-bottom: 1px solid #ccc; text-align: left; }
th { color: #e91e63; }
a.button {
  display: inline-block;
  padding: 8px 12px;
  background-color: #e91e63;
  color: white;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
}
a.button:hover { background-color: #c2185b; }
.back { text-align: center; margin-top: 20px; }
.back a { text-decoration: none; color: #ad1457; font-weight: bold; }
.success { color: green; text-align: center; margin-bottom: 15px; font-weight: bold; }
.error { color: red; text-align: center; margin-bottom: 15px; font-weight: bold; }
</style>
</head>
<body>

<div class="box">
  <h1>Seleziona Cliente da Modificare</h1>

  <?php if($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
  <?php if($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

  <table>
    <tr>
      <th>Nome</th>
      <th>Cognome</th>
      <th>Email</th>
      <th>Azioni</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['nome']) ?></td>
        <td><?= htmlspecialchars($row['cognome']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><a class="button" href="modifica_cliente.php?id=<?= $row['id'] ?>">Modifica</a></td>
      </tr>
    <?php endwhile; ?>
  </table>

  <div class="back">
    <a href="dashboard.php">⬅ Torna alla Dashboard</a>
  </div>
</div>

</body>
</html>
