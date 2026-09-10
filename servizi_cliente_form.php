<?php
session_start();
if (!isset($_SESSION['utente'])) {
    header('Location: login.php');
    exit();
}

include 'connessione.php';

// Recupero clienti
$query = "SELECT id, nome, cognome FROM clienti ORDER BY nome";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Servizi Cliente</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Space Grotesk', sans-serif;
    background: linear-gradient(135deg, #f9c5d1, #fddde6);
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    margin: 0;
    padding: 0;
}
.form-box {
    background: rgba(255,255,255,0.85);
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(255, 105, 180, 0.3);
    max-width: 400px;
    width: 100%;
    text-align: center;
}
h1 {
    color: #880e4f;
    margin-bottom: 20px;
}
form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}
label {
    font-weight: 500;
    text-align: left;
}
select, button {
    padding: 10px;
    font-size: 1rem;
    border-radius: 8px;
    border: 1px solid #ccc;
    width: 100%;
    box-sizing: border-box;
}
button {
    background-color: #e91e63;
    color: white;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
}
button:hover {
    background-color: #c2185b;
}
.back {
    margin-top: 20px;
}
.back a {
    text-decoration: none;
    color: #ad1457;
    font-weight: bold;
    transition: 0.3s;
}
.back a:hover {
    color: #880e4f;
}

/* Responsive */
@media(max-width:500px){
    .form-box { padding: 20px; }
    h1 { font-size: 1.6rem; }
}
</style>
</head>
<body>

<div class="form-box">
    <h1>Servizi Cliente</h1>
    <form action="servizi_cliente.php" method="GET">
        <label for="cliente_id">Seleziona Cliente</label>
        <select name="cliente_id" id="cliente_id" required>
            <option value="">-- Seleziona --</option>
            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <option value="<?= $row['id'] ?>">
                    <?= htmlspecialchars($row['nome'] . ' ' . $row['cognome']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Visualizza Servizi</button>
    </form>
    <div class="back">
        <a href="dashboard.php">&larr; Torna alla Dashboard</a>
    </div>
</div>

</body>
</html>
