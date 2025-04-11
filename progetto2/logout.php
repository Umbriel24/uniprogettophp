<?php
session_start();

$_SESSION = array(); //Svuota tutti i dati della sessione

session_destroy(); //La distrugge. Ipoteticamente in questo ambiente sono superflui...

header("Location: index.php"); //Torniamo alla home
exit();
?>