<?php
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniBiglietti.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniCarrozze.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniStazione.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniSubtratta.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniTreno.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniConvoglio.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniLocomotrice.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_treno = $_POST["id_treno"] ?? null;
    $id_stazione_partenza = $_POST["id_staz_partenza"] ?? null;
    $id_stazione_arrivo = $_POST["id_staz_arrivo"] ?? null;
    $dataOra_partenza = $_POST["dataOra"] ?? null;

    echo $id_treno;
    echo '<br>';
    echo $id_stazione_partenza;
    echo '<br>';

    echo $id_stazione_arrivo;
    echo '<br>';

    echo $dataOra_partenza;
    echo '<br>';

    echo '<br>';
    echo '<br>';




    //Inizia transazione
    try {
        IniziaTransazione();

        //Check se treno esiste
        if(!CheckEsistenzaTreno($id_treno)){
            throw new Exception("Errore Treno non trovato. ");
        }

        //check se ha biglietti
        //in corso

        //Check esistenza percorso
        if(!CheckEsistenzaSubtrattaByIdTreno($id_treno)){
            throw new Exception("Errore. Il treno non ha nessun percorso");
        }

        $dataOra_partenza = RendiDateTimeCompatibile($dataOra_partenza);
        $distanzaTotaleKm = CalcolaDistanzaTotalePercorsa($id_stazione_arrivo, $id_stazione_partenza);
        $dataArrivo = CalcolaArrivoByTempoPartenzaEKMTotali($dataOra_partenza, $distanzaTotaleKm);
        $dataArrivo = RendiDateTimeCompatibile($dataArrivo);


        //Se il treno ha già biglietti non si può modificare
        CheckEsistenzaBigliettiPerIlTreno($id_treno);

        //Se arriva qui,  treno e subtratte esistono
        EliminaCorsaSubtrattaByIdTreno($id_treno);



        //Modifichiamo il treno
        ModificaTreno($id_treno, $id_stazione_partenza, $id_stazione_arrivo, $dataOra_partenza, $dataArrivo);

        $id_convoglio = getConvoglioById_Treno($id_treno);
        $oraPart = $dataOra_partenza;
        $oraArr = $dataArrivo;

        CheckEsistenzaConvoglioTrenoInQuellaGiornata($id_convoglio, $oraPart, $oraArr);

        //Creiamo nuove subtratte
        CalcolaPercorsoSubTratte($id_treno, $id_stazione_partenza, $id_stazione_arrivo, $dataOra_partenza);


        CommittaTransazione();

    } catch (Exception $e) {
        RollbackTransazione();
        die("Errore nella query: " . $e->getMessage());
    }
}
