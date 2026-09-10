<?php
session_start();

// Verifica che l'utente sia loggato e sia un capo
if (!isset($_SESSION['utente'])) {
    header("Location: login.php");
    exit();
}

include 'connessione.php';

$messaggio = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $admin_id = $_SESSION['utente']['id'];  // id dell'admin che crea

    if ($username && $password) {
        // Controllo se username esiste già
        $check = $conn->prepare("SELECT id FROM collaboratori WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $messaggio = "⚠️ Username già in uso.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $insert = $conn->prepare("INSERT INTO collaboratori (username, password, ruolo, creato_da) VALUES (?, ?, 'collaboratore', 1)");
            $insert->bind_param("ssi", $username, $hash, $admin_id);

            if ($insert->execute()) {
                $messaggio = "✅ Collaboratore creato con successo!";
            } else {
                $messaggio = "❌ Errore durante la creazione.";
            }
        }
    } else {
        $messaggio = "⚠️ Inserisci tutti i campi.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <title>Crea Collaboratore</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #fce4ec, #f8bbd0);
      padding: 50px;
      display: flex;
      justify-content: center;
    }
    .form-box {
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 0 15px rgba(255, 105, 180, 0.3);
      width: 100%;
      max-width: 450px;
    }
    h2 {
      text-align: center;
      color: #880e4f;
    }
    input {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border-radius: 10px;
      border: 1px solid #ccc;
      font-size: 16px;
    }
    button {
      background-color: #e91e63;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 10px;
      width: 100%;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    button:hover {
      background-color: #c2185b;
    }
    .msg {
      margin-top: 20px;
      color: #ad1457;
      font-weight: bold;
      text-align: center;
    }
    .back {
      text-align: center;
      margin-top: 25px;
    }
    .back a {
      text-decoration: none;
      color: #e91e63;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="form-box">
  <h2>Nuovo Collaboratore</h2>
  <form method="POST">
    <input type="text" name="username" placeholder="Username" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Crea</button>
  </form>
  <?php if ($messaggio): ?>
    <div class="msg"><?= htmlspecialchars($messaggio) ?></div>
  <?php endif; ?>
  <div class="back">
    <a href="dashboard.php">&larr; Torna alla Dashboard</a>
  </div>
</div>

</body>
</html>
