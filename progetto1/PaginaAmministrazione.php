<?php

require_once  __DIR__. '/Amministrazione/FunzioniAmministrazione.php';
require_once __DIR__ . '/CartellaFunzioni/FunzioniCarrozze.php';
require_once __DIR__ . '/CartellaFunzioni/FunzioniStazione.php';
require_once __DIR__ . '/CartellaFunzioni/FunzioniSubtratta.php';
require_once __DIR__ . '/CartellaFunzioni/FunzioniTreno.php';
require_once __DIR__ . '/CartellaFunzioni/FunzioniConvoglio.php';
require_once __DIR__ . '/CartellaFunzioni/FunzioniLocomotrice.php';

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

<h1>Pagina Amministrazione</h1>

<section>
    <h3>Convogli creati liberi</h3>
    <?php StampaConvogli(); ?>
</section>

<section>
    <h3>Convogli in attività</h3>
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
        <h3>Richiedi dei treni all'esercizio</h3>
        <form method="POST" action="Amministrazione/RichiestaTreniStraordinari.php">
            <label>Quanti posti richiedi:
            <input type="number" name="posti_richiesti" required>
            </label>

            <label>Data e ora della partenza del treno
            <input type="datetime-local" name="data_partenza" required>
            </label>

            <label>Stazione di partenza
                <input type="number" name="id_stazione_partenza" required>
            </label>

            <label>Stazione di arrivo
                <input type="number" name="id_stazione_arrivo" required>
            </label>

            <button type="submit">Invia</button>
        </form>

    </section>

    <section>
        <h3>Richiedi cessazione del  treno all'esercizio</h3>
        <form method="POST" action="Amministrazione/RichiestaEliminazioneTreno.php">
            <label>Id treno
            <input type="number" name="id_treno" required>
            </label>
            <button type="submit">Conferma</button>
        </form>

    </section>
</div>

<h3>Visualizza richieste effettuate</h3>
<h4>Richiesta  treni extra</h4>
<section>
    <div>
       <?php StampaRichiesteTrenoExtra(); ?>
    </div>
</section>

<h4>Richiesta eliminazione treni</h4>
<section>
    <div>
        <?php StampaRichiesteEliminazioneTreno(); ?>
    </div>
</section>

<br>
<div class="container">

<section>
    <h3>Elimina una richiesta</h3>
    <form method="POST" action="Amministrazione/EliminaRichiestaAmministrazione.php">
        <label>id richiesta da eliminare
            <input type="number" name="id_richiesta" required>
        </label>
        <button type="submit">Conferma</button>
    </form>
</section>
</div>

</body>
</html>
