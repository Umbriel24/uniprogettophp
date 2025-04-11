<?php
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/FunzioniCarrozze.php';
require_once __DIR__ . '/FunzioniStazione.php';
require_once __DIR__ . '/FunzioniSubtratta.php';
require_once __DIR__ . '/FunzioniTreno.php';
require_once __DIR__ . '/FunzioniConvoglio.php';
require_once __DIR__ . '/FunzioniLocomotrice.php';

function PrintaBigliettiByIdUtente($id_utente){
    $query = "SELECT * FROM progetto1_Biglietto WHERE id_rif_utente = $id_utente";
    $result = EseguiQuery($query);
    if($result->RecordCount() == 0){
        echo 'Nessun biglietto prenotato';
    } else {

        echo '<table>';
        echo '<tr><th>ID</th><th>Posto</th><th>Treno numero</th></tr>';
        while ($row = $result->FetchRow()) {
            echo '<tr>';
            echo '<td>' . $row['id_biglietto'] . '</td>';
            echo '<td>' . $row['posto_biglietto'] . '</td>';
            echo '<td>' . $row['id_rif_treno'] . '</td>';

        }
        echo '</tr>';
    }
}

function CheckEsistenzaBigliettiPerIlTreno($id_treno){
    $query = "SELECT * FROM progetto1_Biglietto WHERE id_rif_treno = $id_treno";
    $result = EseguiQuery($query);
    if($result->RecordCount() != 0){
        Throw new Exception("Errore, il treno ha già biglietti.");
    }
}