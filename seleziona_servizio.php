<?php
session_start();
if (!isset($_SESSION['utente'])) {
    header('Location: login.php');
    exit();
}

include 'connessione.php';

// Recupero tutti i servizi
$result = $conn->query("SELECT id, nome_servizio, prezzo FROM servizi ORDER BY nome_servizio ASC");
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Seleziona Servizio</title>
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
  max-width: 500px;
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
</style>
</head>
<body>

<div class="box">
  <h1>Seleziona Servizio da Modificare</h1>
  <table>
    <tr>
      <th>Nome Servizio</th>
      <th>Prezzo</th>
      <th>Azioni</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['nome_servizio']) ?></td>
        <td><?= number_format($row['prezzo'], 2, ',', '.') ?> €</td>
        <td><a class="button" href="modifica_servizio.php?id=<?= $row['id'] ?>">Modifica</a></td>
      </tr>
    <?php endwhile; ?>
  </table>

  <div class="back">
    <a href="dashboard.php">⬅ Torna alla Dashboard</a>
  </div>
</div>

</body>
</html>
