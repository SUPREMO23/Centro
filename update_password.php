<?php
session_start();
include 'connessione.php';

$message = '';
$showForm = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Controlla se token valido
    $stmt = $conn->prepare("SELECT id, token_expiry FROM admin WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($id, $expiry);
    if ($stmt->fetch()) {
        if (new DateTime() < new DateTime($expiry)) {
            $showForm = true; // token valido, mostra form
        } else {
            $message = "Token scaduto.";
        }
    } else {
        $message = "Token non valido.";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    $password = $_POST['password'];
    $token = $_POST['token'];

    if (strlen($password) >= 6) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Aggiorna password e resetta token
        $stmt = $conn->prepare("UPDATE admin SET password_hash = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $password_hash, $token);
        if ($stmt->execute()) {
            $message = "Password aggiornata con successo. <a href='login.php'>Accedi</a>";
            $showForm = false;
        } else {
            $message = "Errore nell'aggiornamento.";
        }
        $stmt->close();
    } else {
        $message = "La password deve avere almeno 6 caratteri.";
        $showForm = true;
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Cambia Password - Studio Nefertiti</title>
<style>
* { box-sizing: border-box; margin:0; padding:0; }
body {
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
  width: 90%;
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
  font-size: 2rem;
  font-weight: bold;
  margin-bottom: 35px;
  letter-spacing: 1.5px;
  animation: floatTitle 2.5s ease-in-out infinite alternate;
}
@keyframes floatTitle { from { transform: translateY(0px); } to { transform: translateY(-6px); } }
.input-group { position: relative; margin-bottom: 25px; width: 100%; }
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
.message { text-align: center; margin-bottom: 20px; color: #d6006c; font-weight: bold; font-size: 0.95rem; word-wrap: break-word; }
@media(max-width: 450px){
  .login-title { font-size: 1.7rem; margin-bottom: 25px; }
  .login-box { padding: 35px 20px; }
  .input-group input { font-size: 14px; padding: 12px 10px; }
  .login-button { font-size: 14px; padding: 12px; }
}
</style>
</head>
<body>

<div class="login-box">
  <div class="login-title">Cambia Password</div>
  <?php if($message): ?>
    <div class="message"><?= $message ?></div>
  <?php endif; ?>

  <?php if($showForm): ?>
  <form method="post">
    <div class="input-group">
      <input type="password" name="password" placeholder=" " required>
      <label>Nuova password (min 6 caratteri)</label>
    </div>
    <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
    <button type="submit" class="login-button">Aggiorna Password</button>
  </form>
  <?php endif; ?>

  <div style="text-align:center; margin-top:20px;">
    <a href="login.php" style="color:#d6006c; text-decoration:underline; font-size:0.9rem;">Torna al login</a>
  </div>
</div>

</body>
</html>
