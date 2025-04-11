<?php

require_once __DIR__ . '/../SQLProgetto2/Sql_GetQuery.php';
require_once __DIR__ . '/../SQLProgetto2/SQL_PostQuery.php';
require_once __DIR__ . '/../CartellaDBSito2/database.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    try {

        $id_transazione = $_POST['id'] ?? null;
        $azione = $_POST['azione'] ?? null; // confermato - rifiutato;

        $query1 = "SELECT esito_transazione from progetto2_Transazione where id_transazione = $id_transazione";
        $result = EseguiQuery($query1);

        if ($result->RecordCount() == 0) {
            throw new Exception("Errore nella transazione. ");
        }

        $row = $result->FetchRow();
        if ($row['esito_transazione'] != 'in attesa') {
            throw new Exception("La transazione non è in stato di attesa. Azione non consentita");
        }


        if ($azione == 'conferma' && CheckSaldoAcquirente($id_transazione)) {
            UpdateTransazione($id_transazione, $azione);
        } else if ($azione == 'rifiuta') {
            UpdateTransazione($id_transazione, $azione);
        } else {
            Throw new Exception("Errore ");
        }

    } catch (Exception $e) {
        RollbackTransazione();
        die($e->getMessage());
    }


}

