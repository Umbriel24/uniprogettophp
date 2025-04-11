<?php
require_once __DIR__ . '/CartellaFunzioni/FunzioniCarrozze.php';
require_once __DIR__ . '/CartellaFunzioni/FunzioniStazione.php';
require_once __DIR__ . '/CartellaFunzioni/FunzioniSubtratta.php';
require_once __DIR__ . '/CartellaFunzioni/FunzioniTreno.php';
require_once __DIR__ . '/CartellaFunzioni/FunzioniConvoglio.php';
require_once __DIR__ . '/CartellaFunzioni/FunzioniLocomotrice.php';
require_once __DIR__ . '/Amministrazione/FunzioniAmministrazione.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/light.css">
    <title>Società ferrovie Turistiche - Account esercizio SFT</title>

    <style>
        .container {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* 2 colonne di uguale larghezza */
            gap: 20px; /* Spazio tra gli elementi */
            padding: 20px; /* Spaziatura interna */
        }

        /* Stile opzionale per gli elementi figli */
        .container > * {
            background-color: #f5f5f5; /* Colore di sfondo */
            padding: 15px; /* Spaziatura interna elementi */
            border-radius: 8px; /* Bordi arrotondati */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Ombreggiatura leggera */
        }
    </style>

</head>
<body>
<nav>
    <a href="PaginaEsercizio.php">Gestione esercizio</a>
    <a href="PaginaEsercizioGestioneCorse.php">Gestione Corse</a>
    <a href="index.php">Esci</a>
</nav>

<section>
    <h2>Richiesta Backoffice Amministrativo</h2>
    <h4>Richiesta treno extra</h4>
    <?php StampaRichiesteTrenoExtra(); ?>
    <h4> Richiesta eliminazione treno</h4>
    <?php StampaRichiesteEliminazioneTreno(); ?>
</section>
<br> <br>

<h1>Gestione Corse</h1>
<section>
    <h3>Convogli creati</h3>
    <?php StampaConvogli(); ?>
</section>

<section>
    <h3>Convogli con corse attive</h3>
    <?php StampaConvogliInAttivita(); ?>
</section>

<br>
<div>
    <section>
        <h3>Treni - Orario - Partenza e arrivo</h3>
        <?php StampaTreniInCorsa() ?>
    </section>
</div>

<div class="container">
    <section>
        <h3>Crea un treno da un convoglio</h3>
        <form method="POST" action="GestioneCorse/CreaCorsa&Treno.php">
            <label> Id Convoglio
                <input type="number" name="id_convoglio" required>
            </label>
            <br>
            <label> ID Stazione di partenza
                <input type="number" name="id_stazione_partenza" required>
            </label>

            <label> ID Stazione di arrivo
                <input type="number" name="id_stazione_arrivo" required>
            </label>
            <br>
            <label> Orario di partenza
                <input type="datetime-local" name="dataOra_partenza" required>
            </label>
            <button type="submit">Conferma</button>
        </form>
    </section>
    <?php StampaListaStazioni(); ?>
</div>

<div class="container">
    <section>
        <h3>Elimina treno con le sue tappe</h3>
        <form method="POST" action="GestioneCorse/EliminaCorsaTreno.php">
            <label>id treno da eliminare
                <input type="number" name="id_treno" required>
            </label>
            <button type="submit">Conferma</button>
        </form>
    </section>



    <section>
        <h3>Modifica Treno con la sua corsa</h3>
        <form method="POST" action="GestioneCorse/ModificaCorsaTreno.php">
            <label>Inserisci ID Treno da modificare
                <input type="number" name="id_treno" required>
            </label>
            <label>Inserisci Id della nuova stazione di partenza
                <input type="number" name="id_staz_partenza" required>
            </label>
            <label>Inserisci Id della nuova stazione di arrivo
                <input type="number" name="id_staz_arrivo" required>
            </label>
            <label>Inserisci il nuovo l'orario di partenza
                <input type="datetime-local" name="dataOra" required>
            </label>
            <button type="submit">Conferma Modifica</button>
        </form>
    </section>
</div>


</body>
