<?php
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniCarrozze.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniStazione.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniSubtratta.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniTreno.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniConvoglio.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniLocomotrice.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_convoglio = $_POST["id_convoglio"] ?? null;
    $id_stazione_partenza = $_POST["id_stazione_partenza"] ?? null;
    $id_stazione_arrivo = $_POST["id_stazione_arrivo"] ?? null;
    $dataOra_partenza = $_POST["dataOra_partenza"] ?? null;

    echo $id_convoglio;
    echo '<br>';
    echo $id_stazione_partenza;
    echo '<br>';

    echo $id_stazione_arrivo;
    echo '<br>';

    echo $dataOra_partenza;
    echo '<br>';

    echo '<br>';
    echo '<br>';

    //COME FUNZIONA
    /*
     *  1. Un convoglio diventa treno con una TRACCIA ORARIA TOTALE E UN PERCORSO
     *  2. Il treno va a 50km/h. Sono circa 13,9 m/s.
     */


    //Inizia transazione
    try {
        IniziaTransazione();

        //rendi le datetime compatbili
        $dataOra_partenza = RendiDateTimeCompatibile($dataOra_partenza);

        $distanzaTotaleKm = CalcolaDistanzaTotalePercorsa($id_stazione_arrivo, $id_stazione_partenza);
        $dataArrivo = CalcolaArrivoByTempoPartenzaEKMTotali($dataOra_partenza, $distanzaTotaleKm);
        $dataArrivo = RendiDateTimeCompatibile($dataArrivo);


        //QUERY progetto1_TRENO INSERT
        CreaTrenoParametrizzato($id_convoglio, $id_stazione_partenza, $id_stazione_arrivo, $dataOra_partenza, $dataArrivo);

        $id_treno = getIdTrenoFromConvoglioRef($id_convoglio);

        //Distanza totale + Ora iniziale calcoliamo il tempo di arrivo


        //La logica ora è: Calcoliamo l'orario a cui arriva ad ogni stazione.
        // Aspetta li 2 minuti e parte per la prossima stazione
        CalcolaPercorsoSubTratte($id_treno, $id_stazione_partenza, $id_stazione_arrivo, $dataOra_partenza);


        //Throw new Exception("Debug trime");
        //Throw new exception("Debug . non confermiamo il codice");
        CommittaTransazione();


    } catch (Exception $e) {
        RollbackTransazione();
        die("Errore nella query: " . $e->getMessage());
    }

}








