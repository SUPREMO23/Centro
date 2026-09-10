<?php 
session_start();
if (!isset($_SESSION['utente'])) {
    header('Location: login.php');
    exit();
}

include 'connessione.php';

// Preparo dati per le statistiche
$serviziCount = [];
$serviziLabels = [];
$res = $conn->query("SELECT sv.nome_servizio, COUNT(*) as count 
                     FROM storico_servizi s 
                     JOIN servizi sv ON s.servizio_id=sv.id 
                     GROUP BY servizio_id");
while($r = $res->fetch_assoc()){
    $serviziLabels[] = $r['nome_servizio'];
    $serviziCount[] = $r['count'];
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dashboard Admin - Studio Nefertiti</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;700;900&display=swap" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body {
    font-family: 'Space Grotesk', sans-serif;
    background: linear-gradient(135deg, #f9c5d1, #fddde6);
    margin:0; 
    min-height:100vh; 
    display:flex; 
    flex-direction:row;
}
nav.sidebar {
    width: 250px; 
    background-color: #e91e63; 
    color: white; 
    display:flex; 
    flex-direction:column; 
    padding:2rem 1.5rem; 
    position:fixed; 
    top:0; bottom:0; left:0; 
    box-shadow:2px 0 10px rgba(0,0,0,0.1); 
    z-index:1000;
}
nav.sidebar .brand { font-size:1.8rem; font-weight:900; letter-spacing:0.12em; margin-bottom:3rem; text-align:center; }
nav.sidebar a { color:white; text-decoration:none; font-weight:700; padding:12px 20px; border-radius:10px; margin-bottom:1.2rem; display:block; transition:background-color 0.3s ease; }
nav.sidebar a:hover { background-color:#c2185b; }
main.content { margin-left:250px; padding:3rem 4rem; overflow-y:auto; flex-grow:1; box-sizing:border-box; }
main.content h1 { color:#880e4f; font-weight:900; font-size:3rem; margin-bottom:3rem; text-align:center; }
.card-dashboard { border-radius:20px; box-shadow:0 12px 25px rgba(233,30,99,0.15); transition: transform 0.3s ease, box-shadow 0.3s ease; cursor:pointer; border:none; height:150px; display:flex; flex-direction:column; justify-content:center; align-items:center; padding:1.5rem 2rem; background:white; margin-bottom:2.5rem; max-width:600px; margin-left:auto; margin-right:auto; }
.card-dashboard:hover { transform: scale(1.05); box-shadow:0 20px 50px rgba(233,30,99,0.35); }
.card-dashboard h2 { color:#ad1457; font-weight:700; font-size:1.9rem; margin-bottom:1rem; text-align:center; }
.btn-go { font-weight:700; text-transform:uppercase; color:#e91e63; border:2px solid #e91e63; padding:8px 32px; border-radius:30px; transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease; text-decoration:none; display:inline-block; margin-top:auto; font-size:1rem; }
.btn-go:hover { background-color:#e91e63; color:white; box-shadow:0 8px 25px rgba(233,30,99,0.7); }
.chart-container { max-width:800px; margin:40px auto; background:white; padding:20px; border-radius:20px; box-shadow:0 12px 25px rgba(233,30,99,0.15); height:auto; }
.chart-container canvas { width:100% !important; height:400px !important; }

/* Media Query per dispositivi piccoli */
@media (max-width: 768px) {
    body { flex-direction: column; }
    nav.sidebar { width: 100%; height:auto; position:relative; flex-direction:row; overflow-x:auto; padding:1rem; justify-content:space-around; }
    nav.sidebar a { margin-bottom:0; padding:10px; font-size:0.9rem; }
    main.content { margin-left:0; padding:2rem 1rem; }
    main.content h1 { font-size:2rem; margin-bottom:2rem; }
    .card-dashboard { height:auto; padding:1rem; margin-bottom:1.5rem; }
    .card-dashboard h2 { font-size:1.4rem; margin-bottom:0.8rem; }
    .btn-go { padding:6px 20px; font-size:0.9rem; }
    .chart-container { padding:15px; margin:20px 0; }
    .chart-container canvas { height:300px !important; }
}
</style>
</head>
<body>

<nav class="sidebar">
  <div class="brand">Studio Nefertiti</div>
  <a href="#" aria-current="page">Dashboard</a>
  <a href="logout.php">Logout</a>
</nav>

<main class="content">
  <h1>Dashboard Francesca</h1>

  <div class="card-dashboard" onclick="location.href='aggiungi_cliente.php'">
    <h2>Aggiungi Cliente</h2>
    <a href="aggiungi_cliente.php" class="btn-go">Vai</a>
  </div>

  <div class="card-dashboard" onclick="location.href='aggiungi_servizio.php'">
    <h2>Aggiungi Servizio</h2>
    <a href="aggiungi_servizio.php" class="btn-go">Vai</a>
  </div>

  <div class="card-dashboard" onclick="location.href='servizi_cliente_form.php'">
    <h2>Servizi di un Cliente</h2>
    <a href="servizi_cliente_form.php" class="btn-go">Vai</a>
  </div>

  <div class="card-dashboard" onclick="location.href='dati_completi.php'">
    <h2>Visualizza Tutti i Dati</h2>
    <a href="dati_completi.php" class="btn-go">Vai</a>
  </div>

  <div class="card-dashboard" onclick="location.href='calendario.php'">
    <h2>Calendario Appuntamenti</h2>
    <a href="calendario.php" class="btn-go">Apri calendario</a>
  </div>

  <!-- Sezione Statistiche -->
  <div class="chart-container">
    <h2 style="text-align:center; color:#ad1457;">Servizi Più Richiesti</h2>
    <canvas id="graficoServizi"></canvas>
  </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const ctx = document.getElementById('graficoServizi').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?= '"' . implode('","', $serviziLabels) . '"' ?>],
        datasets: [{
            label: 'Numero Servizi Erogati',
            data: [<?= implode(',', $serviziCount) ?>],
            backgroundColor: 'rgba(233,30,99,0.7)'
        }]
    },
    options: { 
        responsive:true, 
        maintainAspectRatio:false,
        plugins: { legend:{ display:true, position:'top' } },
        scales: { y:{ beginAtZero:true } }
    }
});
</script>

</body>
</html>
