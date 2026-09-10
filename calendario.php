<?php
session_start();
if (!isset($_SESSION['utente'])) { header('Location: login.php'); exit; }
include 'connessione.php';
$clienti = $conn->query('SELECT id, nome, cognome FROM clienti ORDER BY cognome, nome');
$servizi = $conn->query('SELECT id, nome_servizio FROM servizi ORDER BY nome_servizio');
?>
<!doctype html><html lang="it"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Calendario appuntamenti</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<style>
body{font-family:Arial,sans-serif;background:#fddde6;margin:0;color:#4a1030}.top{background:#e91e63;color:#fff;padding:16px 24px;display:flex;gap:20px;align-items:center}.top h1{font-size:22px;margin:0}.top a{color:#fff;font-weight:bold}.page{max-width:1200px;margin:28px auto;padding:0 18px}#calendar{background:#fff;border-radius:14px;padding:18px;box-shadow:0 4px 18px #b76b8a55}.modal{display:none;position:fixed;inset:0;background:#0008;align-items:center;justify-content:center;padding:16px}.modal.open{display:flex}.box{background:#fff;border-radius:12px;padding:22px;max-width:490px;width:100%}.box label{display:block;font-weight:bold;margin-top:10px}.box input,.box select,.box textarea{width:100%;padding:9px;box-sizing:border-box;margin-top:4px}.actions{display:flex;gap:10px;margin-top:18px}.actions button{padding:10px 15px;border:0;border-radius:7px;background:#e91e63;color:#fff;font-weight:bold;cursor:pointer}.actions .delete{background:#b71c1c}.actions .cancel{background:#777}
</style></head><body>
<header class="top"><h1>Studio Nefertiti · Calendario</h1><a href="dashboard.php">← Dashboard</a></header>
<main class="page"><div id="calendar"></div></main>
<div class="modal" id="modal"><form class="box" id="eventForm">
<h2 id="formTitle">Nuovo appuntamento</h2><input type="hidden" id="eventId">
<label>Cliente</label><select id="cliente"><option value="">Non associato</option><?php while($c=$clienti->fetch_assoc()): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['cognome'].' '.$c['nome']) ?></option><?php endwhile; ?></select>
<label>Servizio</label><select id="servizio"><option value="">Non associato</option><?php while($s=$servizi->fetch_assoc()): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nome_servizio']) ?></option><?php endwhile; ?></select>
<label>Titolo</label><input id="titolo" required maxlength="160" placeholder="Es. Massaggio · Maria Rossi">
<label>Inizio</label><input id="inizio" type="datetime-local" required><label>Fine</label><input id="fine" type="datetime-local" required>
<label>Note</label><textarea id="note" rows="3"></textarea>
<div class="actions"><button type="submit">Salva</button><button class="delete" type="button" id="deleteBtn" hidden>Elimina</button><button class="cancel" type="button" id="cancelBtn">Annulla</button></div>
</form></div>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
const modal=document.getElementById('modal'), form=document.getElementById('eventForm'), api='calendario_api.php';
const $=id=>document.getElementById(id), local=v=>v ? v.slice(0,16) : '';
function openForm(event, start, end){ form.reset(); $('eventId').value=event?.id||''; $('formTitle').textContent=event?'Modifica appuntamento':'Nuovo appuntamento'; $('deleteBtn').hidden=!event; $('titolo').value=event?.title||''; $('inizio').value=local(event?.startStr||start); $('fine').value=local(event?.endStr||end); $('cliente').value=event?.extendedProps.cliente_id||''; $('servizio').value=event?.extendedProps.servizio_id||''; $('note').value=event?.extendedProps.note||''; modal.classList.add('open'); }
function payload(){return {id:$('eventId').value,title:$('titolo').value,start:$('inizio').value,end:$('fine').value,cliente_id:$('cliente').value,servizio_id:$('servizio').value,note:$('note').value};}
async function call(method,data){const r=await fetch(api,{method,headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});const x=await r.json();if(!r.ok)throw new Error(x.error||'Errore');return x;}
document.addEventListener('DOMContentLoaded',()=>{const cal=new FullCalendar.Calendar($('calendar'),{locale:'it',initialView:'timeGridWeek',firstDay:1,nowIndicator:true,selectable:true,editable:true,headerToolbar:{left:'prev,next today',center:'title',right:'dayGridMonth,timeGridWeek,timeGridDay'},events:api,select:i=>openForm(null,i.startStr,i.endStr),eventClick:i=>openForm(i.event),eventDrop:async i=>{try{await call('PUT',{...i.event.extendedProps,id:i.event.id,title:i.event.title,start:i.event.startStr,end:i.event.endStr});}catch(e){alert(e.message);i.revert();}},eventResize:async i=>{try{await call('PUT',{...i.event.extendedProps,id:i.event.id,title:i.event.title,start:i.event.startStr,end:i.event.endStr});}catch(e){alert(e.message);i.revert();}}});cal.render();form.onsubmit=async e=>{e.preventDefault();try{await call($('eventId').value?'PUT':'POST',payload());modal.classList.remove('open');cal.refetchEvents();}catch(e){alert(e.message);}};$('deleteBtn').onclick=async()=>{if(confirm('Eliminare questo appuntamento?')){try{await call('DELETE',payload());modal.classList.remove('open');cal.refetchEvents();}catch(e){alert(e.message);}}};$('cancelBtn').onclick=()=>modal.classList.remove('open');});
</script></body></html>
