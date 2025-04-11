<?php
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniStazione.php';


function StampaRichiesteTrenoExtra(){
    $db = getConnessioneDb();
    try {
        $db->BeginTrans();

        $query = "SELECT * FROM progetto1_Amministrazione WHERE id_treno_eliminare is null";
        $result = $db->Execute($query);

        if(!$result || $result->RecordCount() == 0){
            echo 'Nessuna richiesta treno extra';
            $db->RollbackTrans();
            return;
        }




    } catch (Exception $e){
        RollbackTransazione();
        echo 'Errore nella stampa di richiesta treno';
    }


    echo '<table>';
    echo '<tr><th>id_richiesta</th><th>Posti richiesti</th><th>Data partenza</th><th>Stazione di partenza</th><th>Stazione di arrivo</th></tr>';
    while($row = $result->FetchRow()){
        echo '<tr>';
        echo '<td>' . $row['id_amministrazione'] . '</td>';
        echo '<td>' . $row['posti_richiesti'] . '</td>';
        echo '<td>' . $row['data_partenza'] . '</td>';
        echo '<td>' . getNomeStazioneFromId($row['id_stazione_partenza']) . '</td>';
        echo '<td>' . getNomeStazioneFromId($row['id_stazione_arrivo']) . '</td>';
    }

    echo '<table>';
}

function StampaRichiesteEliminazioneTreno(){
    $query = "SELECT * FROM progetto1_Amministrazione WHERE id_treno_eliminare is not null";
    $result = EseguiQuery($query);

    if(!$result->RecordCount()) {
        echo 'Nessuna richiesta eliminazione treno ';
        return;
    }

    echo '<table>';
    echo '<tr><th>id Richiesta</th><th>id Treno</th></tr>';
    while($row = $result->FetchRow()){
        echo "<tr>";
        echo '<td>' . $row['id_amministrazione'] . '</td>';
        echo '<td>' . $row['id_treno_eliminare'] . '</td>';
    }
    echo '</table>';
}



