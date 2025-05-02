<?php
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/FunzioniCarrozze.php';
require_once __DIR__ . '/FunzioniStazione.php';
require_once __DIR__ . '/FunzioniSubtratta.php';
require_once __DIR__ . '/FunzioniTreno.php';
require_once __DIR__ . '/FunzioniConvoglio.php';
require_once __DIR__ . '/FunzioniLocomotrice.php';

function CalcolaPercorsoInteroByIdTreno($id_treno)
{
    $query1 = "SELECT * FROM progetto1_Subtratta WHERE id_rif_treno = $id_treno";
}

function CheckEsistenzaTratta($id_stazione_partenza, $id_stazione_arrivo, $giorno_partenza)
{
    $giorno_partenzaFiltrato = substr($giorno_partenza, 0, 10);
    $query = "SELECT * FROM progetto1_Subtratta WHERE id_stazione_partenza = $id_stazione_partenza";

    $result = EseguiQuery($query);

    $trenoTrovato = 0;
    $oraPartenza = '';
    $oraArrivo = '';



    while ($row = $result->FetchRow()) {
        //filtriamo il giorno
        if (substr($row['ora_di_partenza'], 0, 10) != $giorno_partenzaFiltrato) {
            continue;
        }

        //Ora prendiamo ogni singolo treno con una partenza a quella stazione e vediamo se ha una destinazione
        //uguale alla stazione di arrivo
        $rifTreno      = (int)$row['id_rif_treno'];
        $partenzaOrig  = $row['ora_di_partenza'];
        $tsPartenza    = strtotime($partenzaOrig);

        $query2 = "SELECT * FROM progetto1_Subtratta WHERE id_rif_treno = $rifTreno && id_stazione_arrivo = $id_stazione_arrivo";
        $result2 = EseguiQuery($query2);

        if ($result2->RecordCount() > 0) {
            while ($row2 = $result2->FetchRow()) {
                $arrivoOrig = $row2['ora_di_arrivo'];
                $tsArrivo   = strtotime($arrivoOrig);

                // Se arriva prima o uguale alla partenza, ignoro
                if ($tsArrivo <= $tsPartenza) {
                    continue;
                }

                // Trovato: salvo e interrompo tutti e due i loop
                $trenoTrovato  = $rifTreno;
                $oraPartenza   = $partenzaOrig;
                $oraArrivo     = $arrivoOrig;
                break 2;
            }
        } else {
            echo 'Nessun treno con quella partenza e destinazione. Ignorato. <br>';
        }
    }

    if ($trenoTrovato === 0) {
        echo 'Non esiste nessun treno con quelle fermate. Errore funzione tratta.';
        return 0;
    }

    // Output
    echo 'Treno trovato: Treno numero ' . $trenoTrovato . '<br>';
    echo 'La partenza è alle '    . $oraPartenza   . '<br>';
    echo 'Arrivi a destinazione alle ore ' . $oraArrivo . '<br>';

    return $trenoTrovato;

}