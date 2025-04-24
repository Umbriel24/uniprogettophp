<?php
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/FunzioniCarrozze.php';
require_once __DIR__ . '/FunzioniStazione.php';
require_once __DIR__ . '/FunzioniSubtratta.php';
require_once __DIR__ . '/FunzioniTreno.php';
require_once __DIR__ . '/FunzioniConvoglio.php';
require_once __DIR__ . '/FunzioniLocomotrice.php';


function getCarrozzeByIdConvoglioAssociato($id_convoglio)
{
    $query = "SELECT codice_carrozza, posti_a_sedere from progetto1_Carrozza ca
    JOIN progetto1_ComposizioneCarrozza co on ca.codice_carrozza = co.nome_carrozza 
    where co.id_ref_convoglio = $id_convoglio";

    return EseguiQuery($query);
}

function stampaCarrozze()
{
    $query = "SELECT * FROM progetto1_Carrozza";
    $result = EseguiQuery($query);
    //corrisponde al foreach di c#
    echo '<table>';
    while ($row = $result->FetchRow()) {
        echo '<tr>';
        echo '<td>' . $row['codice_carrozza'] . '</td>';
        echo '<td>' . $row['numero_di_serie'] . '</td>';
        echo '<td>' . $row['posti_a_sedere'] . '</td>';
        echo '</tr>';
    }

    echo '</table>';
}

function InserisciRow_ComposizioneCarrozza($codice_carrozza, $id_convoglio)
{
    echo 'Test: ';
    echo $codice_carrozza;
    echo $id_convoglio;

    $query = "INSERT INTO progetto1_ComposizioneCarrozza(nome_carrozza, id_ref_convoglio) VALUES('$codice_carrozza', $id_convoglio)";
    return EseguiQuery($query);

}


function CalcolaPostiASedereComplessivi(array $codice_carrozza)
{
    $totale = 0;
    if (empty($codice_carrozza)) {
        return 0;
    } else
        for ($i = 0; $i < count($codice_carrozza); $i++) {
            $totale += getPostoASedereDaSingolaCarrozza($codice_carrozza[$i]);
        }
    return $totale;
}

function getPostoASedereDaSingolaCarrozza($codice_carrozza)
{
    $query = "SELECT posti_a_sedere FROM progetto1_Carrozza WHERE codice_carrozza = '$codice_carrozza'";
    $result = EseguiQuery($query);
    if ($row = $result->FetchRow()) {
        return $row['posti_a_sedere'];
    }
}


function Check_CarrozzeGiaInUso($oraPartenzaTreno, $oraArrivoSubTreno, $id_convoglio)
{
    $carrozzeDelConvoglio = [];

    $query = "SELECT nome_carrozza FROM progetto1_ComposizioneCarrozza WHERE id_ref_convoglio = $id_convoglio";
    $result = EseguiQuery($query);
    while ($row = $result->FetchRow()) {
        $carrozzeDelConvoglio[] = $row['nome_carrozza'];
    }


    if (empty($carrozzeDelConvoglio)) {
        echo '<br>Nessuna carrozza associata al convoglio<br>';
        return true;
    }

    $partenza1 = new DateTime($oraPartenzaTreno);
    $arrivo1 = new DateTime($oraArrivoSubTreno);

    // Margine di sicurezza di 30 minuti
    $partenza1_buffer_inizio = (clone $partenza1)->modify('-30 minutes');
    $arrivo1_buffer_fine = (clone $arrivo1)->modify('+30 minutes');

    $giornoInizio = $partenza1->format('Y-m-d');
    $giornoFine = $arrivo1->format('Y-m-d');


    echo '<br>';
    $query1 = "SELECT pcc.nome_carrozza, t.ora_di_partenza, t.ora_di_arrivo FROM progetto1_ComposizioneCarrozza pcc 
               LEFT JOIN progetto1_Treno t ON t.id_ref_convoglio = pcc.id_ref_convoglio
               WHERE t.ora_di_partenza >= '$giornoInizio 00:00:00'
                 AND t.ora_di_arrivo <= '$giornoFine 23:59:59'";

    echo $query1;


    $result2 = EseguiQuery($query1);

    if ($result2->RecordCount() > 0) {
        while ($row2 = $result2->FetchRow()) {
            $nomeCarrozza = $row2['nome_carrozza'];

            // Se non è una delle carrozze in uso dal convoglio attuale, salta
            if (!in_array($nomeCarrozza, $carrozzeDelConvoglio)) continue;

            $partenza2 = new DateTime($row2['ora_di_partenza']);
            $arrivo2 = new DateTime($row2['ora_di_arrivo']);

            // Verifica sovrapposizione con margine di 30 minuti
            if (VerificaSovrapposizioneOrariaCarrozza($partenza1_buffer_inizio, $arrivo1_buffer_fine, $partenza2, $arrivo2)) {
                throw new Exception("Carrozza '$nomeCarrozza' già in uso in un intervallo di 30 min. Cambia orario.");
            }
        }
    }
    return true;
}

function VerificaSovrapposizioneOrariaCarrozza($inizio1, $fine1, $inizio2, $fine2)
{

    if (!($inizio1 instanceof DateTime)) $inizio1 = new DateTime($inizio1);
    if (!($fine1 instanceof DateTime)) $fine1 = new DateTime($fine1);
    if (!($inizio2 instanceof DateTime)) $inizio2 = new DateTime($inizio2);
    if (!($fine2 instanceof DateTime)) $fine2 = new DateTime($fine2);

    return ($inizio1 <= $fine2) && ($fine1 >= $inizio2);
}

?>