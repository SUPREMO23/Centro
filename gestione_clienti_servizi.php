<?php
session_start();
if (!isset($_SESSION['utente'])) {
    header('Location: login.php');
    exit();
}

include 'connessione.php';

// --- Gestione eliminazione ---
$messaggio = '';

$cliente_id = intval($_GET['cliente_id'] ?? 0);

// 1️⃣ Eliminazione cliente intero
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_cliente'])) {
    $delete_id = intval($_POST['delete_cliente']);
    $stmt = $conn->prepare("DELETE FROM storico_servizi WHERE cliente_id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt = $conn->prepare("DELETE FROM clienti WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("Location: seleziona_cliente.php?success=Cliente e servizi eliminati");
        exit();
    } else {
        $messaggio = "Errore eliminazione cliente: ".$conn->error;
    }
}

// 2️⃣ Eliminazione servizio intero
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_servizio'])) {
    $delete_id = intval($_POST['delete_servizio']);
    $stmt = $conn->prepare("DELETE FROM storico_servizi WHERE servizio_id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt = $conn->prepare("DELETE FROM servizi WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $messaggio = "Servizio eliminato correttamente.";
    } else {
        $messaggio = "Errore eliminazione servizio: ".$conn->error;
    }
}

// 3️⃣ Eliminazione singolo servizio assegnato a cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_record'])) {
    $delete_id = intval($_POST['delete_record']);
    $stmt = $conn->prepare("DELETE FROM storico_servizi WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("Location: servizi_cliente.php?cliente_id=$cliente_id&success=Servizio eliminato");
        exit();
    } else {
        $messaggio = "Errore eliminazione record: ".$conn->error;
    }
}

// --- Gestione modifica singolo servizio assegnato ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_id'])) {
    $record_id = intval($_POST['record_id']);
    $updates = [];
    $params = [];
    $types = "";

    if (!isset($_POST['skip_servizio'])) {
        $updates[] = "servizio_id=?";
        $params[] = intval($_POST['mod_servizio_id']);
        $types .= "i";
    }

    if (!isset($_POST['skip_nota'])) {
        $updates[] = "note=?";
        $params[] = trim($_POST['mod_nota']);
        $types .= "s";
    }

    if ($updates) {
        $sql = "UPDATE storico_servizi SET " . implode(", ", $updates) . " WHERE id=?";
        $params[] = $record_id;
        $types .= "i";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            header("Location: servizi_cliente.php?cliente_id=$cliente_id&success=Modifica effettuata");
            exit();
        } else {
            $messaggio = "Errore modifica: ".$conn->error;
        }
    } else {
        $messaggio = "Tutti i campi sono stati saltati.";
    }
}

// --- Recupero dati cliente e storico servizi ---
$stmtCliente = $conn->prepare("SELECT id, nome, cognome FROM clienti WHERE id=?");
$stmtCliente->bind_param("i", $cliente_id);
$stmtCliente->execute();
$resCliente = $stmtCliente->get_result();
$cliente = $resCliente->fetch_assoc();
if (!$cliente) {
    die("Cliente non trovato.");
}

$servizi = $conn->query("SELECT id, nome_servizio FROM servizi ORDER BY nome_servizio")->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("
    SELECT s.id, sv.nome_servizio, sv.prezzo, s.data_servizio, s.note
    FROM storico_servizi s
    LEFT JOIN servizi sv ON s.servizio_id = sv.id
    WHERE s.cliente_id=?
    ORDER BY s.data_servizio DESC, s.id DESC
");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Servizi Cliente - <?= htmlspecialchars($cliente['nome'].' '.$cliente['cognome']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
<style>
body { font-family: 'Space Grotesk', sans-serif; background: linear-gradient(135deg,#f9c5d1,#fddde6); padding:30px; min-height:100vh; color:#111; }
h1 { color:#880e4f; margin-bottom:25px; text-align:center; }
form, table { max-width:600px; margin:auto; margin-bottom:30px; }
form select, form textarea, form button, table select, table textarea { width:100%; padding:8px; border-radius:8px; border:1px solid #f8bbd0; box-sizing:border-box; margin-top:5px; }
form button, .back a { background-color:#e91e63; color:white; font-weight:700; border:none; border-radius:8px; cursor:pointer; text-decoration:none; padding:10px 15px; display:inline-block; margin-top:10px; }
form button:hover, .back a:hover { background-color:#c2185b; }
.messaggio { text-align:center; font-weight:bold; margin-bottom:20px; color:green; }
.messaggio.error { color:red; }
table { width:100%; border-collapse:collapse; background:rgba(255,255,255,0.9); border-radius:12px; box-shadow:0 0 15px rgba(255,105,180,0.3); font-size:14px; overflow:hidden; }
th, td { padding:12px; border:1px solid #f8bbd0; text-align:left; }
th { background-color:#fce4ec; color:#880e4f; }
tbody tr:nth-child(even){ background-color:#fde7f0; }
.modifica-servizio { margin-top:10px; display:none; background:#fff; padding:10px; border-radius:10px; box-shadow:0 0 10px rgba(255,105,180,0.3); }
.row { display:flex; gap:10px; align-items:center; }
</style>
</head>
<body>

<h1>Servizi per <?= htmlspecialchars($cliente['nome'].' '.$cliente['cognome']) ?></h1>

<?php if($messaggio): ?>
    <p class="messaggio <?= strpos($messaggio,'Errore')!==false?'error':'' ?>"><?= htmlspecialchars($messaggio) ?></p>
<?php endif; ?>

<!-- Pulsante elimina cliente -->
<form method="post" style="text-align:center; margin-bottom:20px;" onsubmit="return confirm('Eliminare cliente e tutti i servizi?');">
    <button type="submit" name="delete_cliente" value="<?= $cliente['id'] ?>">Elimina Cliente</button>
</form>

<!-- Tabella storico servizi -->
<?php if($result->num_rows>0): ?>
<table>
<thead>
<tr>
    <th>Servizio</th>
    <th>Prezzo (€)</th>
    <th>Data</th>
    <th>Note</th>
    <th>Azioni</th>
</tr>
</thead>
<tbody>
<?php while($row=$result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['nome_servizio']) ?></td>
    <td><?= number_format($row['prezzo'],2,',','.') ?></td>
    <td><?= htmlspecialchars($row['data_servizio']) ?></td>
    <td><?= htmlspecialchars($row['note']) ?></td>
    <td>
        <a href="javascript:void(0);" onclick="toggleForm('modifica<?= $row['id'] ?>')">Modifica</a>
        <form method="post" style="display:inline;" onsubmit="return confirm('Eliminare questo servizio assegnato?');">
            <button type="submit" name="delete_record" value="<?= $row['id'] ?>">Elimina</button>
        </form>

        <!-- Form modifica -->
        <form id="modifica<?= $row['id'] ?>" class="modifica-servizio" method="post">
            <input type="hidden" name="record_id" value="<?= $row['id'] ?>">
            <div class="row">
                <select name="mod_servizio_id">
                    <?php foreach($servizi as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $s['id']==$row['id']?'selected':'' ?>><?= htmlspecialchars($s['nome_servizio']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label><input type="checkbox" name="skip_servizio"> Non modificare</label>
            </div>
            <div class="row">
                <textarea name="mod_nota" rows="2"><?= htmlspecialchars($row['note']) ?></textarea>
                <label><input type="checkbox" name="skip_nota"> Non modificare</label>
            </div>
            <button type="submit">Salva Modifiche</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php else: ?>
<p style="text-align:center; color:#ad1457; font-style:italic;">Nessun servizio trovato per questo cliente.</p>
<?php endif; ?>

<div class="back" style="text-align:center; margin-top:20px;">
    <a href="seleziona_cliente.php">&larr; Seleziona un altro cliente</a>
    <a href="dashboard.php">Torna alla Dashboard</a>
</div>

<script>
function toggleForm(id){
    const f = document.getElementById(id);
    f.style.display = f.style.display==='none'?'block':'none';
}
</script>

</body>
</html>
