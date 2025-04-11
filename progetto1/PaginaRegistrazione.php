<?php
require_once __DIR__ . '/ComandiSQL/SQLCreazioneUtente.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if(RegistraUtente($nome, $email, $password)){
        echo "Registrazione completata";
    } else {
        echo "Errore nella registrazione";
    }

}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/light.css">
    <title>Registrati</title>
</head>
<body>


<h2>Registrati</h2>
<p> <a href="index.php">Clicca qui per tornare indietro </a> </p>
<form method="post">
    <label for="nome"> Nome
        <input type="text" name="nome" required>
    </label>
    <br>
    <label for="email"> Email
        <input type="email" name="email" required><br>
    </label>
    <br>
    <label for="password"> Password
        <input type="password" name="password" required><br>
    </label>
    <input type="submit" value="Registrati">
</form>

<p> Registrandoti, accetti le condizioni</p>
<p>TODO AGGIUNGERE CONDIZIONI</p>

</body>
</html>
