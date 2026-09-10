<?php
session_start();
if (!isset($_SESSION['utente'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Aggiungi Cliente</title>
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
      background: rgba(255, 255, 255, 0.8);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 0 20px rgba(255, 105, 180, 0.3);
      max-width: 400px;
      width: 100%;
    }
    h1 {
      text-align: center;
      color: #880e4f;
    }
    form {
      display: flex;
      flex-direction: column;
    }
    label {
      margin-top: 10px;
      font-weight: 500;
    }
    input {
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-family: inherit;
    }
    button {
      margin-top: 20px;
      background-color: #e91e63;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
    }
    button:hover {
      background-color: #c2185b;
    }
    .back {
      text-align: center;
      margin-top: 20px;
    }
    .back a {
      text-decoration: none;
      color: #ad1457;
      font-weight: bold;
    }
  </style>
</head>
<body>
<div class="form-box">
  <h1>Aggiungi Cliente</h1>
  <form action="aggiungi_clienti_submit.php" method="POST">
    <label for="nome">Nome</label>
    <input type="text" name="nome" required>
    <label for="cognome">Cognome</label>
    <input type="text" name="cognome" required>
    <label for="email">Email</label>
    <input type="email" name="email" required>
    <label for="telefono">Telefono</label>
    <input type="text" name="telefono" pattern="^\d{3}[\s\-]?\d{6,7}$">
    <button type="submit">Salva Cliente</button>
  </form>
  <form action="modifica_cliente.php" method="GET" style="margin-top:10px;">
    <button type="submit">Modifica Cliente</button>
  <div class="back">
    <a href="dashboard.php">&larr; Torna alla Dashboard</a>
  </div>
</div>
</body>
</html>