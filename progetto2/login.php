<?php
require_once __DIR__ . '/SQLProgetto2/Sql_GetQuery.php';



//non usiamo get per problemi di sicurezza (anche se è pura esercitazione universitaria, quindi ambiente sicuro)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //Evitiamo sql injection mettendo tutto in escape
    $email = strtolower(trim($_POST["email"]));
    $password = $_POST["password"];


    if (empty($email) || empty($password)) {
        die("Email e password sono obbligatorie");
    }


    $result = getRowUtenteById($email);

    if (!$result) {
        echo "Errore nella query al primo passaggio. ";
    } else {
        $row = $result->FetchRow();
        if ($row['password'] == $password) {
            echo "Login riuscito!";
            //Sessione e "trasportiamo" l'id utente.
            $_SESSION['id_utente'] = $row['id_utente'];
            $_SESSION['nome'] = $row['nome'];
            sleep(1);

            if(Verifica_UtenteEsercente($_SESSION['id_utente'])){
                //Se è esercente, viene trasferito alla sua homepage dedicata.
                header('Location: homepageEsercente.php');
                exit;
            } else {
                //Se non è esercente, il server trasferisce il client alla homepage classica
                header('Location: homepage.php');
                exit;
            }


        } else {
            echo "Login non valido!";
        }
    }
}
?>

<!-- Form HTML -->
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/light.css">
</head>
<p><a href="index.php">Torna all'index</a></p>

<p>Debug LOGIN ESERCENTE: abcunda@gmail.com </p>
<p>DEBUG PASS:  abcunda</p>
<div></div>
<p>Debug LOGIN ACQUIRENTE: doof@gmail.com </p>
<p>DEBUG PASS:  654</p>

<p>Debug LOGIN Ferrovia: Ferrovie@esercizio.it </p>
<p>DEBUG PASS:  777888999</p>

<form method="POST">
    <div>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit">Accedi</button>
</form>


