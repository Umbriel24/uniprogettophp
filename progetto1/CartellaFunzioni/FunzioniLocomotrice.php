<?php

use Cassandra\Date;

require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/FunzioniCarrozze.php';
require_once __DIR__ . '/FunzioniStazione.php';
require_once __DIR__ . '/FunzioniSubtratta.php';
require_once __DIR__ . '/FunzioniTreno.php';
require_once __DIR__ . '/FunzioniConvoglio.php';
require_once __DIR__ . '/FunzioniLocomotrice.php';

function getLocomotriceBy_ref_locomotrice($ref_locomotrice)
{
    $query = "SELECT * FROM progetto1_ComposizioneLocomotrice as c
            LEFT JOIN progetto1_Locomotiva as l on c.riferimentoLocomotiva = l.id_locomotrice
            LEFT JOIN progetto1_Automotrice as a on c.riferimentoAutomotiva = a.id_automotrice
            WHERE c.id_locomotrice = $ref_locomotrice";
    $result = EseguiQuery($query);

    while ($row = $result->FetchRow()) {
        if ($row['codice_locomotiva'] != null) {
            return $row['codice_locomotiva'];
        } else return $row['codice_automotrice'];

    }
    throw new Exception("Locomotrice non trovabile tramite riferimento");

}

function getId_locomotrice_By_Codice($codice_locomotrice)
{

    $query = "SELECT * FROM progetto1_ComposizioneLocomotrice as c
            LEFT JOIN progetto1_Locomotiva as l on c.riferimentoLocomotiva = l.id_locomotrice
            LEFT JOIN progetto1_Automotrice as a on c.riferimentoAutomotiva = a.id_automotrice";

    $result = EseguiQuery($query); //sono 5 record (ne sono sicuro)


    //Chiedo scusa all'umanità, penso sia il codice più brutto abbia mai scritto
    while ($row = $result->FetchRow()) {
        if ($row['codice_locomotiva'] == $codice_locomotrice) {
            return $row["id_locomotrice"];

        } else if ($row["codice_automotrice"] == $codice_locomotrice) {
            return $row["Id_locomotrice"]; //Colonna con lo stesso nome, la mette in mauscolo...
        }

    }
    throw new Exception("Errore, locomotrice non trovata con quel codice");
}


function stampaLocomotrici()
{
    $query = "SELECT * FROM progetto1_Locomotiva";
    $result = EseguiQuery($query);

    echo '<table>';
    echo '<tr><th>ID</th><th>Numero di serie</th><th>Posti</th></tr>';

    while ($row = $result->FetchRow()) {

        //Locomotiva senza posti
        echo '<tr>';
        echo '<td>' . $row['codice_locomotiva'] . '</td>';
        echo '<td>' . $row['nome'] . '</td>';
        echo '<td>' . '0' . '</td>';
        echo '</tr>';
    }

    $query2 = "SELECT * FROM progetto1_Automotrice";

    $result2 = EseguiQuery($query2);
    while ($row2 = $result2->FetchRow()) {
        echo '<tr>';
        echo '<td>' . $row2['codice_automotrice'] . '</td>';
        echo '<td>' . 'N/A' . '</td>';
        echo '<td>' . $row2['posti_a_sedere'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';


}

function UpdateAttivitaLocomotrice($codice_locomotrice)
{
    $id_da_updatare = getId_locomotrice_By_Codice($codice_locomotrice);
    if ($id_da_updatare != 0) {
        $query2 = "UPDATE progetto1_ComposizioneLocomotrice SET in_attivita = 'si' WHERE id_locomotrice = $id_da_updatare";
        return EseguiQuery($query2);
    } else throw new Exception("Errore: Nessuna locomotrice selezionata per l'update. " . $id_da_updatare . " E' l'id da updatare e " . $codice_locomotrice . " ");
}

function Check_LocomotivaGiaInUso($oraPartenzaTreno, $oraArrivoTreno, $id_convoglio)
{
    try {
        $dataPartenzaRichiesta = new DateTime($oraPartenzaTreno);
        $dataArrivoRichiesta = new DateTime($oraArrivoTreno);

        $timestampPartenzaRichiesta = $dataPartenzaRichiesta->getTimestamp();
        $timestampArrivoRichiesta = $dataArrivoRichiesta->getTimestamp();

        // Recupera la locomotiva assegnata al convoglio richiesto
        $id_ref_locomotiva = getid_ref_LocomotivaByConvoglio($id_convoglio);
        if ($id_ref_locomotiva === null) {
            throw new Exception("Errore: locomotiva non trovata per il convoglio $id_convoglio.");
        }

        // Cerca tutti i treni che usano la stessa locomotiva
        $query = "
            SELECT t.id_ref_convoglio, t.ora_di_partenza, t.ora_di_arrivo 
            FROM progetto1_Treno t
            JOIN progetto1_Convoglio c ON t.id_ref_convoglio = c.id_convoglio
            WHERE c.id_ref_locomotiva = $id_ref_locomotiva
        ";

        $result = EseguiQuery($query);

        while ($row = $result->FetchRow()) {
            $partenzaAltroTreno = new DateTime($row['ora_di_partenza']);
            $arrivoAltroTreno = new DateTime($row['ora_di_arrivo']);

            $timestampPartenzaAltro = $partenzaAltroTreno->getTimestamp();
            $timestampArrivoAltro = $arrivoAltroTreno->getTimestamp();

            // Calcola le differenze in ore (float)
            $diffOrePartenza = abs($timestampPartenzaRichiesta - $timestampPartenzaAltro) / 3600;
            $diffOreArrivo = abs($timestampArrivoRichiesta - $timestampArrivoAltro) / 3600;

            // Conflitto se differenza < 30 minuti (0.5 ore)
            if ($diffOrePartenza <= 0.5 || $diffOreArrivo <= 0.5) {
                throw new Exception("Errore: la locomotiva è già in uso entro 30 minuti da un altro treno.");
            }
        }

        return true;

    } catch (Exception $e) {
        die("Errore nella funzione Check_LocomotivaGiaInUso: " . $e->getMessage());
    }
}


function getid_ref_LocomotivaByConvoglio($id_convoglio)
{

    $query = "SELECT id_ref_locomotiva FROM progetto1_Convoglio where id_convoglio = $id_convoglio";
    $result = EseguiQuery($query);

    if ($result->RecordCount() == 0) {
        throw new Exception("Errore, locomotrice non trovata. " . $id_convoglio . " ");
    }
    $row = $result->FetchRow();
    return $row['id_ref_locomotiva'];

}

?>
