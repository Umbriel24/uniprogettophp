<?php
require_once __DIR__ . '/../SQLProgetto2/Sql_GetQuery.php';
require_once __DIR__ . '/../SQLProgetto2/SQL_PostQuery.php';
require_once __DIR__ . '/../CartellaDBSito2/database.php';


function getSaldoEsercente()
{
    return $saldo = getSaldoById($_SESSION['id_utente']);
}
//printaMovimenti

function StampaMovimentiInAttesa()
{

    $id_contoCorrente = getIdContoByIdUtente($_SESSION['id_utente']);

    $queryMovimentiInAttesa = getMovimentiInAttesaEsercente($id_contoCorrente);

    if ($queryMovimentiInAttesa != null && $queryMovimentiInAttesa->RecordCount() > 0) {
        echo '<table>';
        echo '<tr><th>ID</th><th>Importo</th><th>Data</th><th>Stato</th></tr>';

        //corrisponde al foreach di c#
        while ($row = $queryMovimentiInAttesa->FetchRow()) {
            echo '<tr>';
            echo '<td>' . $row['id_transazione'] . '</td>';
            echo '<td>' . number_format($row['importo'], 2) . ' €</td>';
            echo '<td>' . date('d/m/Y H:i', strtotime($row['data_e_ora'])) . '</td>';
            echo '<td>' . $row['esito_transazione'] . '</td>';
            echo '</tr>';
        }

        echo '</table>';
    } else {
        echo '<div class="alert">Nessuna transazione in attesa</div>';
    }
}

function StampaMovimentiConfermati()
{

    $id_contoCorrente = getIdContoByIdUtente($_SESSION['id_utente']);

    $queryMovimentiConfermati = getMovimentiConfermatiEsercente($id_contoCorrente);

    if ($queryMovimentiConfermati != null && $queryMovimentiConfermati->RecordCount() > 0) {
        echo '<table>';
        echo '<tr><th>ID</th><th>Importo</th><th>Data</th><th>Stato</th></tr>';

        //corrisponde al foreach di c#
        while ($row = $queryMovimentiConfermati->FetchRow()) {
            echo '<tr>';
            echo '<td>' . $row['id_transazione'] . '</td>';
            echo '<td>' . number_format($row['importo'], 2) . ' €</td>';
            echo '<td>' . date('d/m/Y H:i', strtotime($row['data_e_ora'])) . '</td>';
            echo '<td>' . $row['esito_transazione'] . '</td>';
            echo '</tr>';
        }

        echo '</table>';
    } else {
        echo '<div class="alert">Nessuna transazione confermata</div>';
    }
}

function StampaMovimentiRifiutati()
{
    $id_contoCorrente = getIdContoByIdUtente($_SESSION['id_utente']);


    $queryMovimentiRifiutati = getMovimentiRifiutatiEsercente($id_contoCorrente);


    if ($queryMovimentiRifiutati != null && $queryMovimentiRifiutati->RecordCount() > 0) {
        echo '<table>';
        echo '<tr><th>ID</th><th>Importo</th><th>Data</th><th>Stato</th></tr>';

        //corrisponde al foreach di c#
        while ($row = $queryMovimentiRifiutati->FetchRow()) {
            echo '<tr>';
            echo '<td>' . $row['id_transazione'] . '</td>';
            echo '<td>' . number_format($row['importo'], 2) . ' €</td>';
            echo '<td>' . date('d/m/Y H:i', strtotime($row['data_e_ora'])) . '</td>';
            echo '<td>' . $row['esito_transazione'] . '</td>';
            echo '</tr>';
        }

        echo '</table>';
    } else {
        echo '<div class="alert">Nessuna transazione rifiutata</div>';
    }
}

?>


