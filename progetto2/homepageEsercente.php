<?php
require_once './Operazioni/EsercenteOperazioni.php';
?>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/light.css">
</head>

<body>
<p>DEBUG: Stai in homepage Esercente</p>

<h1>Benvenuto alla homepage, <?php echo $_SESSION['nome']; ?></h1>
<p>Se vuoi uscire dalla sessione <a href="login.php">Clicca qui</a> </p>

<h3>Ecco i tuoi dati <br> Saldo: <?php echo getSaldoEsercente() ?>€</h3>

<div>
    <p>Movimenti in attesa:</p>
    <?php StampamovimentiInAttesa(); ?>


</div>
<div>
    <p>Movimenti confermati:
        <?php
        StampamovimentiConfermati(); ?>


    </p>
</div>
<div>
    <p> Movimenti rifiutati</p>
    <?php
    StampaMovimentiRifiutati();
    ?>

</div>

<h2>Effettua operazioni:</h2>

<h3>Gestisci transazioni in attesa:</h3>

<form method="POST" action="Operazioni/EsercenteDbOperazioni.php">
    <div class="mb-3">

        <label for="Id transazione" >
            <input type="number" name="id" class="form-control" required>
        </label>
    </div>
    <div>
        <label><input type="radio" name="azione" value="conferma" required>Conferma</label>
        <label><input type="radio" name="azione" value="rifiuta"  >Rifiuta</label>
    </div>
    <button type="submit" name="submit">Conferma</button>
</form>
</body>
</html>
