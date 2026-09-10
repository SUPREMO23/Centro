<?php
session_start();
if (!isset($_SESSION['utente'])) {
    header('Location: login.php');
    exit();
}
include 'connessione.php';

// Gestione eliminazione record
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['elimina_cliente_id'])) {
        $id = intval($_POST['elimina_cliente_id']);
        $stmt = $conn->prepare("DELETE FROM clienti WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    if (isset($_POST['elimina_servizio_id'])) {
        $id = intval($_POST['elimina_servizio_id']);
        $stmt = $conn->prepare("DELETE FROM servizi WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    if (isset($_POST['elimina_storico_id'])) {
        $id = intval($_POST['elimina_storico_id']);
        $stmt = $conn->prepare("DELETE FROM storico_servizi WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

// Recupero dati aggiornati
$clienti = mysqli_query($conn, "SELECT * FROM clienti");
$servizi = mysqli_query($conn, "SELECT * FROM servizi");
$storico = mysqli_query($conn, "
  SELECT s.id, c.nome AS cliente_nome, c.cognome, sv.nome_servizio, s.data_servizio, s.note
  FROM storico_servizi s
  LEFT JOIN clienti c ON s.cliente_id = c.id
  LEFT JOIN servizi sv ON s.servizio_id = sv.id
  ORDER BY s.data_servizio DESC
");
?>
<!DOCTYPE html>
<html lang="it" >
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dati Completi - Studio Nefertiti</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;700;900&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Space Grotesk', sans-serif; background: linear-gradient(135deg, #f9c5d1, #fddde6); margin: 0; height: 100vh; overflow: hidden; display: flex; }
    nav.sidebar { width: 250px; background-color: #e91e63; color: white; display: flex; flex-direction: column; padding: 2rem 1.5rem; position: fixed; top: 0; bottom: 0; left: 0; box-shadow: 2px 0 10px rgba(0,0,0,0.1); z-index: 1000; }
    nav.sidebar .brand { font-size: 1.8rem; font-weight: 900; letter-spacing: 0.12em; margin-bottom: 3rem; user-select: none; text-align: center; }
    nav.sidebar a { color: white; text-decoration: none; font-weight: 700; padding: 12px 20px; border-radius: 10px; margin-bottom: 1.2rem; display: block; transition: background-color 0.3s ease; user-select: none; }
    nav.sidebar a:hover, nav.sidebar a:focus { background-color: #c2185b; outline: none; }
    main.content { margin-left: 250px; padding: 2rem 3rem; overflow-y: auto; flex-grow: 1; height: 100vh; box-sizing: border-box; color: #111; }
    main.content h1 { color: #880e4f; font-weight: 900; font-size: 2.8rem; margin-bottom: 2rem; letter-spacing: 0.15em; text-transform: uppercase; user-select: none; text-align: center; }
    h2.section-title { color: #ad1457; margin-top: 3rem; margin-bottom: 1rem; border-bottom: 3px solid #e91e63; padding-bottom: 6px; user-select: none; }
    .table-wrapper { background: rgba(255,255,255,0.85); border-radius: 12px; box-shadow: 0 0 15px rgba(255,105,180,0.3); overflow-x: auto; margin-bottom: 3rem; }
    table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 14px; }
    th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #f8bbd0; }
    th { background: #fce4ec; color: #880e4f; position: sticky; top: 0; z-index: 5; user-select: none; }
    tbody tr:nth-child(even) { background-color: #fde7f0; }
    tbody tr:hover { background-color: #fbc6d1; cursor: default; }
    .back { text-align: center; margin-bottom: 2rem; }
    .back a { text-decoration: none; color: #ad1457; font-weight: 700; border: 2px solid #ad1457; padding: 8px 25px; border-radius: 30px; transition: background-color 0.3s ease, color 0.3s ease; user-select: none; display: inline-block; }
    .back a:hover, .back a:focus { background-color: #ad1457; color: white; outline: none; }
    .btn-elimina { background:#f44336; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; }
    .btn-elimina:hover { background:#d32f2f; }
  </style>
</head>
<body>
  <nav class="sidebar" aria-label="Menu principale">
    <div class="brand" tabindex="0">Studio Nefertiti</div>
    <a href="dashboard.php" tabindex="0" aria-current="page">Dashboard</a>
    <a href="logout.php" tabindex="0">Logout</a>
  </nav>

  <main class="content" role="main" tabindex="-1">
    <h1>Dati Completi</h1>

    <section>
      <h2 class="section-title">Clienti</h2>
      <div class="table-wrapper" tabindex="0">
        <table>
          <thead>
            <tr><th>ID</th><th>Nome</th><th>Cognome</th><th>Email</th><th>Telefono</th><th>Azioni</th></tr>
          </thead>
          <tbody>
          <?php while ($row = mysqli_fetch_assoc($clienti)) : ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['nome']) ?></td>
              <td><?= htmlspecialchars($row['cognome']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['telefono']) ?></td>
              <td>
                <form method="post" style="display:inline;">
                  <input type="hidden" name="elimina_cliente_id" value="<?= $row['id'] ?>">
                  <button type="submit" class="btn-elimina" onclick="return confirm('Eliminare questo cliente?');">Elimina</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section>
      <h2 class="section-title">Servizi Disponibili</h2>
      <div class="table-wrapper" tabindex="0">
        <table>
          <thead>
            <tr><th>ID</th><th>Nome Servizio</th><th>Descrizione</th><th>Prezzo</th><th>Azioni</th></tr>
          </thead>
          <tbody>
          <?php while ($row = mysqli_fetch_assoc($servizi)) : ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['nome_servizio']) ?></td>
              <td><?= htmlspecialchars($row['descrizione']) ?></td>
              <td><?= number_format($row['prezzo'], 2, ',', '.') ?> €</td>
              <td>
                <form method="post" style="display:inline;">
                  <input type="hidden" name="elimina_servizio_id" value="<?= $row['id'] ?>">
                  <button type="submit" class="btn-elimina" onclick="return confirm('Eliminare questo servizio?');">Elimina</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section>
      <h2 class="section-title">Storico Servizi</h2>
      <div class="table-wrapper" tabindex="0">
        <table>
          <thead>
            <tr><th>ID</th><th>Cliente</th><th>Servizio</th><th>Data</th><th>Note</th><th>Azioni</th></tr>
          </thead>
          <tbody>
          <?php while ($row = mysqli_fetch_assoc($storico)) : ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['cliente_nome'] . ' ' . $row['cognome']) ?></td>
              <td><?= htmlspecialchars($row['nome_servizio']) ?></td>
              <td><?= htmlspecialchars($row['data_servizio']) ?></td>
              <td><?= htmlspecialchars($row['note']) ?></td>
              <td>
                <form method="post" style="display:inline;">
                  <input type="hidden" name="elimina_storico_id" value="<?= $row['id'] ?>">
                  <button type="submit" class="btn-elimina" onclick="return confirm('Eliminare questo record dello storico?');">Elimina</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>

    <div class="back" tabindex="0">
      <a href="dashboard.php">&larr; Torna alla Dashboard</a>
    </div>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
