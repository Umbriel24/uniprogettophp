<?php
require_once __DIR__ . '/CartellaFunzioni/FunzioniTreno.php';

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/light.css">
    <title>Società ferrovie Turistiche - SFT</title>

</head>
<body>
<div class="container">
    <h1>Società ferrovie Turistiche - SFT</h1>
    <p>Accedi o registrati se non hai ancora un account</p>

    <p>Hai già un account?<a href="PaginaLogin.php">Vai alla pagina di login</a></p>

    <p>Non hai ancora un account? <a href="PaginaRegistrazione.php"> Clicca qui per registrarti</a> </p>


    <h2>Lista corse dei treni</h2>
    <?php StampaTreniInCorsaPerIClienti()?>
</div>
</body>
</html>
