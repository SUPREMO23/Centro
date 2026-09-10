<?php
session_start();
if (!isset($_SESSION['utente'])) {
    header('Location: login.php');
    exit();
}

include 'connessione.php';

$cliente_id = intval($_GET['cliente_id'] ?? 0);
if ($cliente_id <= 0) {
    header('Location: servizi_cliente_form.php?error=Seleziona un cliente valido');
    exit();
}

// Recupero dati cliente
$stmtCliente = $conn->prepare("SELECT nome, cognome FROM clienti WHERE id = ?");
$stmtCliente->bind_param("i", $cliente_id);
$stmtCliente->execute();
$resCliente = $stmtCliente->get_result();
$cliente = $resCliente->fetch_assoc();
if (!$cliente) {
    header('Location: servizi_cliente_form.php?error=Cliente non trovato');
    exit();
}

// Recupero servizi
$serviziStmt = $conn->prepare("SELECT id, nome_servizio FROM servizi ORDER BY nome_servizio");
$serviziStmt->execute();
$serviziResult = $serviziStmt->get_result();
$servizi = $serviziResult->fetch_all(MYSQLI_ASSOC);

// Messaggi
$messages = [];
if (!empty($_GET['success'])) $messages[] = ['type'=>'success', 'text'=>$_GET['success']];
if (!empty($_GET['error'])) $messages[] = ['type'=>'error', 'text'=>$_GET['error']];

// --- Gestione assegnazione nuovo servizio ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['servizio_id']) && !isset($_POST['record_id'])) {
    $servizio_id = intval($_POST['servizio_id']);
    $nota = trim($_POST['nota'] ?? '');

    $check = $conn->prepare("SELECT id FROM storico_servizi WHERE cliente_id=? AND servizio_id=? AND data_servizio=CURDATE()");
    $check->bind_param("ii", $cliente_id, $servizio_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO storico_servizi (cliente_id, servizio_id, data_servizio, note) VALUES (?, ?, CURDATE(), ?)");
        $insert->bind_param("iis", $cliente_id, $servizio_id, $nota);
        if ($insert->execute()) {
            $messages[] = ['type'=>'success', 'text'=>'Servizio assegnato correttamente'];
        } else {
            $messages[] = ['type'=>'error', 'text'=>'Errore durante l\'assegnazione: '.$conn->error];
        }
    } else {
        $messages[] = ['type'=>'error', 'text'=>'Questo servizio è già stato assegnato oggi a questo cliente.'];
    }
}

// --- Gestione modifica servizio ---
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
        $sql = "UPDATE storico_servizi SET ".implode(", ", $updates)." WHERE id=?";
        $params[] = $record_id;
        $types .= "i";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $messages[] = ['type'=>'success','text'=>'Servizio aggiornato correttamente'];
        } else {
            $messages[] = ['type'=>'error','text'=>'Errore durante l\'aggiornamento: '.$conn->error];
        }
    } else {
        $messages[] = ['type'=>'error','text'=>'Nessuna modifica effettuata.'];
    }
}

// Query storico servizi del cliente
$query = "
SELECT s.id, sv.nome_servizio, sv.prezzo, s.data_servizio, s.note, s.servizio_id
FROM storico_servizi s
LEFT JOIN servizi sv ON s.servizio_id = sv.id
WHERE s.cliente_id = ?
ORDER BY s.data_servizio DESC, s.id DESC
";
$stmt = $conn->prepare($query);
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
body { font-family:'Space Grotesk',sans-serif; background:linear-gradient(135deg,#f9c5d1,#fddde6); padding:20px; color:#111; }
h1 { text-align:center; color:#880e4f; margin-bottom:25px; }
form.assegna-servizio, form.modifica-servizio { background:#fff; padding:20px; border-radius:12px; box-shadow:0 0 15px rgba(255,105,180,0.3); max-width:500px; margin:0 auto 30px auto; }
form label { display:block; margin-bottom:5px; font-weight:500; color:#880e4f; }
form select, form input, form textarea, form button { width:100%; padding:10px; margin-bottom:10px; border-radius:8px; border:1px solid #f8bbd0; box-sizing:border-box; }
form button { background:#e91e63; color:#fff; font-weight:700; cursor:pointer; border:none; transition:0.3s; }
form button:hover { background:#c2185b; }
.messaggio { max-width:500px; margin:10px auto 30px; font-weight:700; text-align:center; }
.messaggio.success { color:green; }
.messaggio.error { color:red; }
table { width:100%; border-collapse: collapse; background:rgba(255,255,255,0.9); border-radius:12px; overflow:hidden; box-shadow:0 0 15px rgba(255,105,180,0.3); font-size:14px; margin-bottom:30px; }
th, td { padding:12px; border:1px solid #f8bbd0; text-align:left; }
th { background:#fce4ec; color:#880e4f; }
tbody tr:nth-child(even){ background:#fde7f0; }
.back { margin-top:20px; text-align:center; }
.back a { background:#e91e63; color:#fff; text-decoration:none; font-weight:600; padding:10px 20px; border-radius:8px; margin:0 5px; display:inline-block; transition:0.3s; }
.back a:hover { background:#c2185b; }
.no-data { text-align:center; font-style:italic; color:#ad1457; }

/* Responsive table */
@media (max-width:600px){
  table, thead, tbody, th, td, tr { display:block; }
  th { display:none; }
  td { position: relative; padding-left:50%; margin-bottom:10px; }
  td:before { position:absolute; left:10px; top:12px; font-weight:bold; content:attr(data-label); }
}
.row { display:flex; justify-content:space-between; align-items:center; gap:10px; margin-top:10px; }
.row input, .row select, .row textarea { flex:1; }
</style>
</head>
<body>

<h1>Servizi per <?= htmlspecialchars($cliente['nome'].' '.$cliente['cognome']) ?></h1>

<?php foreach($messages as $m): ?>
    <p class="messaggio <?= $m['type'] ?>"><?= htmlspecialchars($m['text']) ?></p>
<?php endforeach; ?>

<!-- Form assegnazione nuovo servizio -->
<form class="assegna-servizio" method="post">
    <label>Assegna un nuovo servizio a <?= htmlspecialchars($cliente['nome']) ?>:</label>
    <select name="servizio_id" id="servizio_id" required>
        <option value="">-- scegli servizio --</option>
        <?php foreach ($servizi as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nome_servizio']) ?></option>
        <?php endforeach; ?>
    </select>

    <div id="notaBox" style="display:none;">
        <label for="nota">Nota (opzionale):</label>
        <textarea name="nota" id="nota" rows="3"></textarea>
    </div>

    <button type="submit">Assegna servizio</button>
</form>

<!-- Tabella storico servizi -->
<?php if ($result->num_rows > 0): ?>
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
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td data-label="Servizio"><?= htmlspecialchars($row['nome_servizio']) ?></td>
            <td data-label="Prezzo"><?= number_format($row['prezzo'],2,',','.') ?></td>
            <td data-label="Data"><?= htmlspecialchars($row['data_servizio']) ?></td>
            <td data-label="Note"><?= htmlspecialchars($row['note']) ?></td>
            <td data-label="Azioni">
                <a href="#modifica<?= $row['id'] ?>" onclick="toggleForm('modifica<?= $row['id'] ?>')">Modifica</a>
                <form id="modifica<?= $row['id'] ?>" class="modifica-servizio" method="post" style="display:none;">
                    <input type="hidden" name="record_id" value="<?= $row['id'] ?>">
                    <div class="row">
                        <select name="mod_servizio_id">
                            <?php foreach($servizi as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= $s['id']==$row['servizio_id']?'selected':'' ?>>
                                    <?= htmlspecialchars($s['nome_servizio']) ?>
                                </option>
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
<p class="no-data">Nessun servizio trovato per questo cliente.</p>
<?php endif; ?>

<div class="back">
    <a href="servizi_cliente_form.php">&larr; Seleziona un altro cliente</a>
    <a href="dashboard.php">Torna alla Dashboard</a>
</div>

<script>
const servizioSelect = document.getElementById('servizio_id');
const notaBox = document.getElementById('notaBox');
const notaInput = document.getElementById('nota');

servizioSelect.addEventListener('change', function(){
    if(this.value){
        notaBox.style.display='block';
    } else {
        notaBox.style.display='none';
        notaInput.value='';
    }
});

function toggleForm(id){
    const form = document.getElementById(id);
    form.style.display = (form.style.display==='none' || form.style.display==='') ? 'block' : 'none';
}
</script>

</body>
</html>
