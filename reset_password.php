<?php
session_start();
include 'connessione.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    // ✅ Qui imposti l'unica email autorizzata
    $allowed_email = "giaconfilippo9@gmail.com"; // <-- CAMBIA con la tua mail

    if ($email && $email === $allowed_email) {
        // Verifica che l'email esista nel DB
        $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $token = bin2hex(random_bytes(32)); // token sicuro
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Aggiorna il token e la scadenza
            $update = $conn->prepare("UPDATE admin SET reset_token = ?, token_expiry = ? WHERE email = ?");
            $update->bind_param("sss", $token, $expiry, $email);
            $update->execute();

            // Costruisci link reset
            $link = "https://butacu5256.altervista.org/centro_massaggi/update_password.php?token=$token";

            // Invia email
            $subject = "Reset password Studio Nefertiti";
            $body = "Clicca qui per resettare la password (valido 1 ora): $link";
            $headers = "From: no-reply@butacu5256.altervista.org";

            if (mail($email, $subject, $body, $headers)) {
                $message = "Controlla la tua email per resettare la password.";
            } else {
                $message = "Errore nell'invio dell'email.";
            }

        } else {
            $message = "Email non trovata nel database.";
        }

        $stmt->close();
    } else {
        $message = "Non sei autorizzato a fare il reset.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password - Studio Nefertiti</title>
  <style>
    * { box-sizing: border-box; }
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
      overflow: hidden;
    }
    @keyframes animateBG {
      0% {background-position: 0% 50%;}
      50% {background-position: 100% 50%;}
      100% {background-position: 0% 50%;}
    }
    .login-box {
      backdrop-filter: blur(15px);
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.3);
      padding: 45px 35px;
      border-radius: 20px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 10px 40px rgba(255, 105, 180, 0.3);
      animation: fadeIn 1.2s ease forwards;
      opacity: 0;
      transform: translateY(20px);
    }
    @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
    .login-title {
      text-align: center;
      color: #d6006c;
      font-size: 30px;
      font-weight: bold;
      margin-bottom: 35px;
      letter-spacing: 1.5px;
      animation: floatTitle 2.5s ease-in-out infinite alternate;
    }
    @keyframes floatTitle { from { transform: translateY(0px); } to { transform: translateY(-6px); } }
    .input-group { position: relative; margin-bottom: 30px; }
    .input-group input {
      width: 100%;
      padding: 14px 12px;
      background: rgba(255, 255, 255, 0.25);
      border: 2px solid #ff69b4;
      border-radius: 12px;
      color: #333;
      font-size: 16px;
      outline: none;
      transition: all 0.3s ease;
    }
    .input-group input:focus { background: white; box-shadow: 0 0 5px #ff69b4; }
    .input-group label {
      position: absolute;
      top: 14px;
      left: 14px;
      color: #666;
      font-size: 14px;
      pointer-events: none;
      background: rgba(255,255,255,0.7);
      padding: 0 6px;
      border-radius: 5px;
      transition: all 0.3s ease;
    }
    .input-group input:focus + label,
    .input-group input:not(:placeholder-shown) + label {
      top: -10px;
      font-size: 12px;
      color: #d6006c;
    }
    .login-button {
      width: 100%;
      padding: 14px;
      background: #ff69b4;
      color: white;
      font-weight: bold;
      font-size: 16px;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease;
    }
    .login-button:hover { background: #ff1493; animation: pulse 1s infinite; }
    @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.03); } 100% { transform: scale(1); } }
    .message { text-align: center; margin-bottom: 20px; color: #d6006c; font-weight: bold; }
  </style>
</head>
<body>

  <div class="login-box">
    <div class="login-title">Studio Nefertiti</div>
    <?php if($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="input-group">
        <input type="email" name="email" placeholder=" " required>
        <label>Email registrata</label>
      </div>
      <button type="submit" class="login-button">Invia link</button>
    </form>
    <div style="text-align:center; margin-top:20px;">
      <a href="login.php" style="color:#d6006c; text-decoration:underline; font-size:15px;">Torna al login</a>
    </div>
  </div>

</body>
</html>
