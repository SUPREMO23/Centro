<?php
session_start();
if (!isset($_SESSION['utente'])) {
    header('Location: login.php');
    exit();
}

include 'connessione.php';

// Includiamo TCPDF
require_once('tcpdf_min/tcpdf.php');

// Gestione generazione PDF
if(isset($_POST['tipo_pdf'])){
    $tipo = $_POST['tipo_pdf'];

    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Studio Nefertiti');
    $pdf->SetTitle('Report '.$tipo);
    $pdf->SetMargins(15, 20, 15);
    $pdf->AddPage();

    $html = '<h1 style="color:#e91e63;text-align:center;">Studio Nefertiti - Report '.$tipo.'</h1><br>';

    if($tipo=='Clienti'){
        $res = $conn->query("SELECT * FROM clienti");
        $html .= '<table border="1" cellpadding="5"><tr style="background-color:#fce4ec;"><th>ID</th><th>Nome</th><th>Cognome</th><th>Email</th><th>Telefono</th></tr>';
        while($r = $res->fetch_assoc()){
            $html .= '<tr><td>'.$r['id'].'</td><td>'.$r['nome'].'</td><td>'.$r['cognome'].'</td><td>'.$r['email'].'</td><td>'.$r['telefono'].'</td></tr>';
        }
        $html .= '</table>';
    } elseif($tipo=='Servizi'){
        $res = $conn->query("SELECT * FROM servizi");
        $html .= '<table border="1" cellpadding="5"><tr style="background-color:#fce4ec;"><th>ID</th><th>Nome Servizio</th><th>Descrizione</th><th>Prezzo</th></tr>';
        while($r = $res->fetch_assoc()){
            $html .= '<tr><td>'.$r['id'].'</td><td>'.$r['nome_servizio'].'</td><td>'.$r['descrizione'].'</td><td>'.$r['prezzo'].' €</td></tr>';
        }
        $html .= '</table>';
    } elseif($tipo=='Storico'){
        $res = $conn->query("
            SELECT s.id, c.nome AS cliente_nome, c.cognome, sv.nome_servizio, s.data_servizio, s.note
            FROM storico_servizi s
            LEFT JOIN clienti c ON s.cliente_id=c.id
            LEFT JOIN servizi sv ON s.servizio_id=sv.id
            ORDER BY s.data_servizio DESC
        ");
        $html .= '<table border="1" cellpadding="5"><tr style="background-color:#fce4ec;"><th>ID</th><th>Cliente</th><th>Servizio</th><th>Data</th><th>Note</th></tr>';
        while($r = $res->fetch_assoc()){
            $html .= '<tr><td>'.$r['id'].'</td><td>'.$r['cliente_nome'].' '.$r['cognome'].'</td><td>'.$r['nome_servizio'].'</td><td>'.$r['data_servizio'].'</td><td>'.$r['note'].'</td></tr>';
        }
        $html .= '</table>';
    }

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('Report_'.$tipo.'.pdf', 'D');
    exit();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Report PDF - Studio Nefertiti</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family: 'Space Grotesk', sans-serif; background: linear-gradient(135deg,#f9c5d1,#fddde6); padding:30px; }
h1 { text-align:center; color:#880e4f; margin-bottom:40px; }
.card { border-radius:20px; background:white; padding:40px 20px; text-align:center; box-shadow:0 12px 25px rgba(233,30,99,0.15); transition:transform 0.3s ease; margin-bottom:30px; cursor:pointer; }
.card:hover { transform:scale(1.05); }
.card h2 { color:#ad1457; font-size:2rem; margin-bottom:20px; }
.card button { background:#e91e63; color:white; border:none; padding:10px 30px; border-radius:30px; font-weight:700; cursor:pointer; transition:background 0.3s ease; }
.card button:hover { background:#c2185b; }
.back { text-align:center; margin-top:40px; }
.back a { color:#ad1457; text-decoration:none; font-weight:700; border:2px solid #ad1457; padding:8px 25px; border-radius:30px; transition:all 0.3s ease; }
.back a:hover { background:#ad1457; color:white; }
</style>
</head>
<body>

<h1>Genera Report PDF</h1>

<form method="post">
  <div class="card">
    <h2>Clienti</h2>
    <button type="submit" name="tipo_pdf" value="Clienti">Scarica PDF</button>
  </div>
  <div class="card">
    <h2>Servizi</h2>
    <button type="submit" name="tipo_pdf" value="Servizi">Scarica PDF</button>
  </div>
  <div class="card">
    <h2>Storico Servizi</h2>
    <button type="submit" name="tipo_pdf" value="Storico">Scarica PDF</button>
  </div>
</form>

<div class="back">
  <a href="dashboard.php">&larr; Torna alla Dashboard</a>
</div>

</body>
</html>
