<?php
// filepath: c:\Users\Utente\.dbclient\storage\temp\dbclient\1750770024449\centro_massaggi\logout.php
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit();