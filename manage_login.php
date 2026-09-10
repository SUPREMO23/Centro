<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cerca_usr = trim($_POST['username']);
    $cerca_psw = trim($_POST['password']);
} else {
    header('Location: login.php');
    exit("Errore: richiesta non valida");
}

include 'connessione.php';
$message = '';

if ($conn) {
    $query = "SELECT * FROM admin WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $cerca_usr);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $riga = $result->fetch_assoc();

        if (password_verify($cerca_psw, $riga['password_hash'])) {
            $_SESSION['utente'] = $cerca_usr;
            header('Location: dashboard.php');
            exit();
        } else {
            $message = "Password errata. Riprova.";
        }
    } else {
        $message = "Utente non trovato.";
    }

    $stmt->close();
    $conn->close();
} else {
    $message = "Errore di connessione al database.";
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Errore Login</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #ffe6f0, #f9d6e2, #ffe6f0);
      background-size: 400% 400%;
      animation: animateBG 15s ease infinite;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    @keyframes animateBG {
      0% {background-position: 0% 50%;}
      50% {background-position: 100% 50%;}
      100% {background-position: 0% 50%;}
    }

    .error-box {
      backdrop-filter: blur(15px);
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.3);
      padding: 40px;
      border-radius: 20px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 10px 40px rgba(255, 105, 180, 0.3);
      text-align: center;
      color: #d6006c;
    }

    .error-box h1 {
      margin-bottom: 15px;
      font-size: 26px;
    }

    .error-box p {
      margin-bottom: 20px;
      font-size: 16px;
    }

    .error-box a {
      color: #fff;
      background: #ff69b4;
      text-decoration: none;
      padding: 10px 20px;
      border-radius: 12px;
      font-weight: bold;
      transition: background 0.3s ease;
    }

    .error-box a:hover {
      background: #ff1493;
    }
  </style>
</head>
<body>
  <div class="error-box">
    <h1>Accesso Negato</h1>
    <p><?= htmlspecialchars($message) ?></p>
    <a href="login.php">Torna al Login</a>
  </div>
</body>
</html>
