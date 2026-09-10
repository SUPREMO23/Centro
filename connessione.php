<?php
//Disabilita la generazione automatica delle eccezzioni
mysqli_report(MYSQLI_REPORT_OFF);

//Parametri per la connessione
$hostname = "localhost";
$username = "butacu5256";
$password = "";
$database = "my_butacu5256";

//funzione che crea connessione con i parametri sopra
$conn = mysqli_connect($hostname, $username, $password, $database);

//gestisco il caso in cui la connessione non vada a buon fine
if (!$conn){
	die("Connessione fallita. Motivo dell'errore: ".mysqli_connect_error());
}
?>