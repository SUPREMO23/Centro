<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['utente'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Sessione scaduta']);
    exit;
}
include 'connessione.php';

function fail($message, $status = 400) {
    http_response_code($status);
    echo json_encode(['error' => $message]);
    exit;
}
function eventRow($row) {
    return [
        'id' => (string)$row['id'],
        'title' => $row['titolo'],
        'start' => str_replace(' ', 'T', $row['inizio']),
        'end' => str_replace(' ', 'T', $row['fine']),
        'extendedProps' => [
            'cliente_id' => $row['cliente_id'], 'servizio_id' => $row['servizio_id'],
            'note' => $row['note'] ?? '', 'google_event_id' => $row['google_event_id']
        ]
    ];
}
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $result = $conn->query('SELECT id, cliente_id, servizio_id, titolo, inizio, fine, note, google_event_id FROM appuntamenti ORDER BY inizio');
    if (!$result) fail('Tabella appuntamenti non trovata: importa prima calendario.sql', 500);
    $events = [];
    while ($row = $result->fetch_assoc()) $events[] = eventRow($row);
    echo json_encode($events);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) fail('Dati non validi');
if ($method === 'DELETE') {
    $id = (int)($input['id'] ?? 0);
    if ($id < 1) fail('Appuntamento non valido');
    $stmt = $conn->prepare('DELETE FROM appuntamenti WHERE id = ?');
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) fail('Impossibile eliminare l\'appuntamento', 500);
    echo json_encode(['ok' => true]);
    exit;
}
$title = trim($input['title'] ?? '');
$start = str_replace('T', ' ', $input['start'] ?? '');
$end = str_replace('T', ' ', $input['end'] ?? '');
$clientId = ($input['cliente_id'] ?? '') === '' ? null : (int)$input['cliente_id'];
$serviceId = ($input['servizio_id'] ?? '') === '' ? null : (int)$input['servizio_id'];
$notes = trim($input['note'] ?? '');
if ($title === '' || strtotime($start) === false || strtotime($end) === false || strtotime($end) <= strtotime($start)) fail('Inserisci titolo, data e orari validi');
if ($method === 'POST') {
    $stmt = $conn->prepare('INSERT INTO appuntamenti (cliente_id, servizio_id, titolo, inizio, fine, note) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('iissss', $clientId, $serviceId, $title, $start, $end, $notes);
    if (!$stmt->execute()) fail('Impossibile salvare l\'appuntamento', 500);
    echo json_encode(['ok' => true, 'id' => $conn->insert_id]);
    exit;
}
if ($method === 'PUT') {
    $id = (int)($input['id'] ?? 0);
    if ($id < 1) fail('Appuntamento non valido');
    $stmt = $conn->prepare('UPDATE appuntamenti SET cliente_id=?, servizio_id=?, titolo=?, inizio=?, fine=?, note=? WHERE id=?');
    $stmt->bind_param('iissssi', $clientId, $serviceId, $title, $start, $end, $notes, $id);
    if (!$stmt->execute()) fail('Impossibile aggiornare l\'appuntamento', 500);
    echo json_encode(['ok' => true]);
    exit;
}
fail('Metodo non supportato', 405);
