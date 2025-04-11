<?php
require_once __DIR__ . '/SQLProgetto2/Sql_GetQuery.php';
require_once __DIR__ . '/SQLProgetto2/SQL_PostQuery.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<pre> Debug:";
    var_dump($_POST);
    echo "</pre>";

    //Obbligatori di utente
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $tipo_utente = $_POST['tipo_utente'];

    //Esclusivi acquirente o Esercente
    $codice_fiscale = $_POST['codice_fiscale'];
    $partita_iva = $_POST['partita_iva'];

    if (RegistraUtente($nome, $email, $password, $tipo_utente, $codice_fiscale, $partita_iva)) {
        echo "Registrazione completata! <a href='index.php'>Accedi</a>";
    } else {
        echo "Errore durante la registrazione";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/light.css">
    <title>Registrazione</title>
    <script>
        function togglePartitaIVA() {
            var checkbox = document.getElementById("tipo_utente");
            var partitaIVAField = document.getElementById("partitaIVAField");
            var codiceFiscaleField = document.getElementById("codicefiscaleField");
            var partitaIVAInput = document.querySelector('[name="partita_iva"]');
            var codiceFiscaleInput = document.querySelector('[name="codice_fiscale"]');

            if (checkbox.checked) {
                partitaIVAField.style.display = "block";
                codiceFiscaleField.style.display = "none";

                partitaIVAInput.setAttribute("required", "required");
                codiceFiscaleInput.removeAttribute("required");
            } else {
                partitaIVAField.style.display = "none";
                codiceFiscaleField.style.display = "block";

                codiceFiscaleInput.setAttribute("required", "required");
                partitaIVAInput.removeAttribute("required");
            }
        }
        // Inizializza gli attributi required al caricamento della pagina
        window.onload = togglePartitaIVA;
    </script>
</head>
<body>

<h2>Registrati</h2>
<p> <a href="index.php">Clicca qui per tornare indietro </a> </p>
<form method="post">

    <label>Nome:</label>
    <input type="text" name="nome" required><br>
    <label>Email:</label>
    <input type="email" name="email" required><br>
    <label>Password:</label>
    <input type="password" name="password" required><br>

    <input type="hidden" name="tipo_utente" value="acquirente">
    <label>Sei un esercente?</label>
    <input type="checkbox" name="tipo_utente" value="esercente" id="tipo_utente" onclick="togglePartitaIVA()">

    <div id="codicefiscaleField" style="display: block">
        <label>Codice Fiscale:</label>
        <input type="text" name="codice_fiscale"><br>
    </div>

    <div id="partitaIVAField" style="display: none;">
        <label>Partita IVA:</label>
        <input type="text" name="partita_iva"><br>
    </div>


    <input type="submit" value="Registrati">
</form>

<p> Registrandoti, accetti le condizioni</p>
<p>TODO AGGIUNGERE CONDIZIONI</p>
</body>
</html>
