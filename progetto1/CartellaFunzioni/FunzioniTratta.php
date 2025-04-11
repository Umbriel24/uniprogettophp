<?php
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/FunzioniCarrozze.php';
require_once __DIR__ . '/FunzioniStazione.php';
require_once __DIR__ . '/FunzioniSubtratta.php';
require_once __DIR__ . '/FunzioniTreno.php';
require_once __DIR__ . '/FunzioniConvoglio.php';
require_once __DIR__ . '/FunzioniLocomotrice.php';

function CalcolaPercorsoInteroByIdTreno($id_treno){
    $query1 = "SELECT * FROM progetto1_Subtratta WHERE id_rif_treno = $id_treno";
}

function CheckEsistenzaTratta($id_stazione_partenza, $id_stazione_arrivo, $giorno_partenza)
{
    $giorno_partenzaFiltrato = substr($giorno_partenza, 0, 10);
    $query = "SELECT * FROM progetto1_Subtratta WHERE id_stazione_partenza = $id_stazione_partenza";
    $result = EseguiQuery($query);

    $trenoIndividuato = 0;
    $ora_partenza = '';
    $ora_arrivo = '';
    $kmTotali = 0;

    while ($row = $result->FetchRow()){
        //filtriamo il giorno
        if(substr($row['ora_di_partenza'], 0, 10) != $giorno_partenzaFiltrato){
            continue;
        }


        //Ora prendiamo ogni singolo treno con una partenza a quella stazione e vediamo se ha una destinazione
        //uguale alla stazione di arrivo
        $trenoTemp = $row['id_rif_treno'];
        $query2 = "SELECT * FROM progetto1_Subtratta WHERE id_rif_treno = $trenoTemp && id_stazione_arrivo = $id_stazione_arrivo";
        $result2 = EseguiQuery($query2);

        if($result2->RecordCount() > 0){
            //Abbiamo trovato il treno che ha partenza e arrivo tra le subtratte
            $trenoIndividuato = $trenoTemp;
            $ora_partenza = $row['ora_di_partenza'];
            $ora_arrivo = $result2->FetchRow()['ora_di_arrivo'];
        }
    }

    if($trenoIndividuato == 0){
        echo 'Non esiste nessun treno con quelle fermate. Errore funzione tratta 40';
        return 0;

    } else if ($ora_partenza >= $ora_arrivo){
        echo 'Non esiste nessun treno con questi orari. ';
        return 0;

    } else {
        echo 'Treno trovato: Treno numero ' . $trenoIndividuato . '<br>';
        echo 'La partenza è alle ' . $ora_partenza . '<br>';
        echo 'Arrivi a destinazione alle ore ' . $ora_arrivo . '<br>';
        return $trenoIndividuato;
    }

}