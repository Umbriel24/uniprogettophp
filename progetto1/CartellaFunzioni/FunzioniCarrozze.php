<?php
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/FunzioniCarrozze.php';
require_once __DIR__ . '/FunzioniStazione.php';
require_once __DIR__ . '/FunzioniSubtratta.php';
require_once __DIR__ . '/FunzioniTreno.php';
require_once __DIR__ . '/FunzioniConvoglio.php';
require_once __DIR__ . '/FunzioniLocomotrice.php';

function getCarrozzeByAttivita($attivita)
{
    $query = "SELECT * FROM progetto1_Carrozza WHERE in_attivita = '$attivita'";
    return EseguiQuery($query);
}

function getCarrozzeByIdConvoglioAssociato($id_convoglio)
{
    $query = "SELECT codice_carrozza, posti_a_sedere from progetto1_Carrozza where id_convoglio = $id_convoglio";
    return EseguiQuery($query);
}

function stampaCarrozzeInattive($carrozeInattive)
{
    if ($carrozeInattive != null && $carrozeInattive->RecordCount() > 0) {
        echo '<table>';
        echo '<tr><th>ID</th><th>Numero di serie</th><th>Posti</th></tr>';

        //corrisponde al foreach di c#
        while ($row = $carrozeInattive->FetchRow()) {
            echo '<tr>';
            echo '<td>' . $row['codice_carrozza'] . '</td>';
            echo '<td>' . $row['numero_di_serie'] . '</td>';
            echo '<td>' . $row['posti_a_sedere'] . '</td>';
            echo '</tr>';
        }

        echo '</table>';
    } else {
        echo '<div class="alert">Nessuna convoglio inattivo.</div>';
    }
}

function CheckCarrozzaAttività($id_carrozza)
{
    $query = "SELECT * FROM progetto1_Carrozza WHERE codice_carrozza = '$id_carrozza'";
    $result = EseguiQuery($query);
    while ($row = $result->FetchRow()) {
        if ($row['in_attivita'] == 'si') throw new Exception("Errore:  " . $id_carrozza . " Carrozza attiva. Smantella questo convoglio o scegli un'altra carrozza");
    }
}

function Updateid_convoglio_Di_Carrozza($codice_carrozza, $id_convoglio)
{
    //codice_carrozza = CD2
    //Id_convoglio = 3

    $query = "UPDATE progetto1_Carrozza 
            SET id_convoglio = $id_convoglio 
            WHERE codice_carrozza = '$codice_carrozza'";

    echo $query;
    return EseguiQuery($query);

}

function UpdateAttivitàCarrozza($attivita, $id_carrozza)
{
    echo '<br>' . $id_carrozza . ' Problema QUI';
    if ($attivita != 'si' && $attivita != 'no') die('Attività settata non consentita.');
    $query = "UPDATE progetto1_Carrozza SET in_attivita = '$attivita' WHERE codice_carrozza = '$id_carrozza'";
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

?>