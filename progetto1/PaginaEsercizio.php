<?php
require_once __DIR__ . '/CartellaFunzioni/FunzioniCarrozze.php';
require_once __DIR__ . '/CartellaFunzioni/FunzioniStazione.php';
require_once __DIR__ . '/CartellaFunzioni/FunzioniSubtratta.php';
require_once __DIR__ . '/CartellaFunzioni/FunzioniTreno.php';
require_once __DIR__ . '/CartellaFunzioni/FunzioniConvoglio.php';
require_once __DIR__ . '/CartellaFunzioni/FunzioniLocomotrice.php';
require_once __DIR__ . '/Amministrazione/FunzioniAmministrazione.php';

//Compone e scompone convogli
//Costruisce le corse con le tratte e orari.
//Ogni corsa può essere modificata o cancellata.

?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/light.css">
    <title>Società ferrovie Turistiche - Account esercizio SFT</title>

    <style>
            /* Stile per allineare il testo sopra il checkbox */
        .checkbox-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .checkbox-container label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .checkbox-container input {
            margin-bottom: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<nav>
    <a href="PaginaEsercizio.php">Gestione esercizio</a>
    <a href="PaginaEsercizioGestioneCorse.php">Gestione Corse</a>
    <a href="index.php">Esci</a>

</nav>
<h1>Account backoffice esercizio</h1>

<section>
    <h2>Richiesta Backoffice Amministrativo</h2>
    <h4>Richiesta treno extra</h4>
    <?php StampaRichiesteTrenoExtra(); ?>
    <h4> Richiesta eliminazione treno</h4>
    <?php StampaRichiesteEliminazioneTreno(); ?>
</section>
<br> <br>

<section>
    <h2>Informazioni sui materiali disponibili</h2>
    <p> Carrozze libere:</p>
    <?php stampaCarrozzeInattive(getCarrozzeByAttivita('No')); ?>
</section>

<section>
    <p>Locomotrici libere</p>
    <?php stampaLocomotriciInattive(getLocomotriceByAttivita('No')); ?>
</section>

<section>
    <?php StampaConvogli(); ?>
    <p>Fine lista convogli creati</p>
</section>




<br>
<section>
    <h2>Crea convoglio</h2>
    <p>Regole: <br>Una locomotrice senza posti deve avere carrozze. <br>
        Una locomotrice con posti può viaggiare da sola </p>


    <form method="POST" action="ConvogliComandi/creazioneConvoglio.php">
        <div>
            <h3>Seleziona Locomotrice:</h3>
            <div class="radio-container">
                <!-- Radio button statici (Dinamici è da pazzi, chiedo venia. non so bene ne js ne php) -->
                <label>
                    <input type="radio" name="locomotrice" value="AN56.2">
                    AN56.2
                </label>

                <label>
                    <input type="radio" name="locomotrice" value="AN56.4">
                    AN56.4
                </label>

                <label>
                    <input type="radio" name="locomotrice" value="SFT.3">
                    SFT.3
                </label>

                <label>
                    <input type="radio" name="locomotrice" value="SFT.4">
                    SFT.4
                </label>

                <label>
                    <input type="radio" name="locomotrice" value="SFT.6">
                    SFT.6
                </label>
            </div>
        </div>

        <h3>Seleziona carrozze:</h3>
        <div class="checkbox-container">
            <label>
                <div>B1</div>
                <input type="checkbox" name="carrozze[]" value="B1">
            </label>
            <label>
                <div>B2</div>
                <input type="checkbox" name="carrozze[]" value="B2">
            </label>
            <label>
                <div>B3</div>
                <input type="checkbox" name="carrozze[]" value="B3">
            </label>
            <label>
                <div>C12</div>
                <input type="checkbox" name="carrozze[]" value="C12">
            </label>
            <label>
                <div>C6</div>
                <input type="checkbox" name="carrozze[]" value="C6">
            </label>
            <label>
                <div>C9</div>
                <input type="checkbox" name="carrozze[]" value="C9">
            </label>
            <label>
                <div>CD1</div>
                <input type="checkbox" name="carrozze[]" value="CD1">
            </label>
            <label>
                <div>CD2</div>
                <input type="checkbox" name="carrozze[]" value="CD2">
            </label>
        </div>
        <button type="submit">Crea il convoglio</button>
    </form>
</section>

<br>

<h3>Elimina Convoglio</h3>
<form method="POST" action="ConvogliComandi/EliminaConvoglio.php">
    <label>Inserisci ID convoglio da eliminare<input type="number" name="id_convoglio"></label>
    <br>
    <button type="submit">Conferma eliminazione</button>
</form>



</body>
</html>