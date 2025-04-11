<?php
require_once 'SQLProgetto2/Sql_GetQuery.php';
require_once 'SQLProgetto2/SQL_PostQuery.php';


if(!isset($_SESSION['id_utente']) && !isset($_SESSION['nome'])){
    //Se non è loggato l'utente ritorna al login. si evita quindi che cambia pagina in autonomia tramite url? credo...
    header('Location: index.php');
    exit;
}

//printa il saldo
$saldo = getSaldoById($_SESSION['id_utente']);
//printaMovimenti

//Ottieni id conto da id utente
$id_contoCorrente = getIdContoByIdUtente($_SESSION['id_utente']);

//Ottieni movimenti da conto
$queryMovimentiInAttesa = getMovimentiInAttesa($id_contoCorrente);
$queryMovimentiConfermati =  getMovimentiConfermati($id_contoCorrente);
$queryMovimentiRifiutati = getMovimentiRifiutati($id_contoCorrente);


?>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/light.css">
</head>
<h1>Benvenuto alla homepage, <?php echo $_SESSION['nome']; ?></h1>
<p>Se vuoi uscire dalla sessione <a href="login.php">Clicca qui</a> </p>

<p>Ecco i tuoi dati <br> Saldo: <?php echo $saldo ?>€</p>

<div>
    <p>Movimenti in attesa:</p>
    <?php
    if($queryMovimentiInAttesa != null && $queryMovimentiInAttesa->RecordCount() > 0){
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
    ?>
</div>
<div>
    <p>Movimenti confermati:
        <?php
        if($queryMovimentiConfermati != null && $queryMovimentiConfermati->RecordCount() > 0){
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
        ?>
    </p>
</div>
<div>
    <p> Movimenti rifiutati</p>
    <?php
    if($queryMovimentiRifiutati != null && $queryMovimentiRifiutati->RecordCount() > 0){
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
    ?>
</div>
