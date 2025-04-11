<?php
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/FunzioniCarrozze.php';
require_once __DIR__ . '/FunzioniStazione.php';
require_once __DIR__ . '/FunzioniSubtratta.php';
require_once __DIR__ . '/FunzioniTreno.php';
require_once __DIR__ . '/FunzioniConvoglio.php';
require_once __DIR__ . '/FunzioniLocomotrice.php';
function StampaTreniInCorsa()
{

    echo '<table>';
    echo '<tr><th>Id Treno </th><th>Convoglio riferimento</th><th>Ora di partenza</th><th>Ora di arrivo</th><th>Stazione di partenza</th><th>Stazione di arrivo</th></tr>';

    $query = "SELECT * FROM progetto1_Treno";
    $result = EseguiQuery($query);
    while ($row = $result->FetchRow()) {
        $id_treno = $row["id_treno"];
        $Convoglio_rif = $row['id_ref_convoglio'];
        $Ora_partenza = $row['ora_di_partenza'];
        $Ora_arrivo = $row['ora_di_arrivo'];
        $stazione_partenza = $row['nome_stazione_partenza'];
        $stazione_arrivo = $row['nome_stazione_arrivo'];

        echo '<tr>';
        echo '<td>' . $id_treno . '</td>';
        echo '<td>' . $Convoglio_rif . '</td>';
        echo '<td>' . $Ora_partenza . '</td>';
        echo '<td>' . $Ora_arrivo . '</td>';
        echo '<td>' . $stazione_partenza . '</td>';
        echo '<td>' . $stazione_arrivo . '</td>';
        echo '</tr>';
    }

    echo '</table>';

}

function StampaTreniInCorsaPerIClienti()
{
    echo '<table>';
    echo '<tr><th>Treno </th><th>Numero corsa</th><th>Ora di partenza</th><th>Ora di arrivo</th><th>Stazione di partenza</th><th>Stazione di arrivo</th></tr>';
    StampaSubtrattePerStampaTreni();
    echo '</tr>';
}

function CalcolaArrivoByTempoPartenzaEKMTotali($OraPartenza, $kmTotali)
{
    //Ricordando che va a 50km/h
    $v_kmh = 50;

    $oraTotalePercorrenza = $kmTotali / $v_kmh;
    $secondiTotali = round($oraTotalePercorrenza * 3600);

    try {
        $data_Partenza = new Datetime($OraPartenza);
        $interval = new DateInterval("PT{$secondiTotali}S");

        $data_Arrivo = $data_Partenza->add($interval);

        $data_Arrivo->setTime(
            $data_Arrivo->format('H'),
            $data_Arrivo->format('i'),
            0
        );
        return $data_Arrivo->format('y-m-d H:i:s');
    } catch (Exception $e) {
        die("Errore nel calcolo del tempo di arrivo " . $e->getMessage());
    }
}

function getIdTrenoFromConvoglioRef($id_convoglio)
{
    $query = "SELECT id_treno from progetto1_Treno where id_ref_convoglio = $id_convoglio";
    $result = EseguiQuery($query);
    $resultArray = $result->FetchRow();
    if (!$resultArray) {
        throw new Exception("Errore nella query: Treno non trovato tramite id_convoglio");
    } else return $resultArray["id_treno"];
}

function EliminaTreno($id_treno)
{
    $query = "DELETE FROM progetto1_Treno WHERE id_treno = $id_treno";


    $result = EseguiQuery($query);
    if (!$result) {
        throw new Exception("Errore nella query: " . $query);
    } else return $result;
}

function CreaTrenoParametrizzato($id_convoglio, $id_s1, $id_s2, $oraPart, $oraArr)
{
    $nome_stazione_partenza = getNomeStazioneFromId($id_s1);
    $nome_stazione_arrivo = getNomeStazioneFromId($id_s2);
    $posti_disponibili = getPostiASedereFromConvoglio($id_convoglio);

    //Check se esiste già un treno in quella giornata
    CheckEsistenzaConvoglioTrenoInQuellaGiornata($id_convoglio, $oraPart, $oraArr);

    if ($nome_stazione_partenza == $nome_stazione_arrivo) {
        throw new Exception("Errore nei dati. Stazione di partenza e arrivo coincidono");
    }

    $query = "INSERT INTO progetto1_Treno 
          (ora_di_partenza, ora_di_arrivo, nome_stazione_partenza, nome_stazione_arrivo, id_ref_convoglio, posti_disponibili) 
          VALUES (?, ?, ?, ?, ?, ?)";

    EseguiQueryConParametri($query, [
        $oraPart,
        $oraArr,
        $nome_stazione_partenza,
        $nome_stazione_arrivo,
        $id_convoglio,
        $posti_disponibili
    ]);
}

function RendiDateTimeCompatibile($dateTimeHTML)
{
    return date('Y-m-d H:i:s', strtotime($dateTimeHTML));
}

function CheckEsistenzaTreno($id_treno)
{
    $query = "SELECT * FROM progetto1_Treno where id_treno = $id_treno";
    $result = EseguiQuery($query);
    $resultArray = $result->FetchRow();
    if (!$resultArray) {
        throw new Exception("Errore nella query: " . $query . " Treno non trovato con id");
    } else return true;
}

function ModificaTreno($id_treno, $id_staz_partenza, $id_staz_arrivo, $dataPart, $dataArrivo)
{
    $nomeStazionePartenza = getNomeStazioneFromId($id_staz_partenza);
    $nomeStazioneArrivo = getNomeStazioneFromId($id_staz_arrivo);

    $query = "UPDATE progetto1_Treno SET nome_stazione_partenza = '$nomeStazionePartenza', 
                           nome_stazione_arrivo = '$nomeStazioneArrivo',
                           ora_di_partenza = '$dataPart',
                           ora_di_arrivo = '$dataArrivo' 
                       WHERE id_treno = $id_treno";

    $result = EseguiQuery($query);
    if(!$result){
        throw new Exception("Errore nella query: " . $query . " Impossibile aggiornare il treno");
    }

}


function CheckEsistenzaConvoglioTrenoInQuellaGiornata($id_convoglio, $oraPart, $oraArr){
    $query = "SELECT DISTINCT c.ora_di_partenza, c.ora_di_arrivo FROM progetto1_Subtratta c
    LEFT JOIN progetto1_Treno t on c.id_rif_treno = t.id_treno
    where t.id_ref_convoglio = $id_convoglio";

    $dataPart = substr($oraPart, 0, 10);
    $dataArrivo = substr($oraArr, 0, 10);

    $result = EseguiQuery($query);
    while ($row = $result->fetchRow()) {

        echo $dataPart . " E' il giorno in cui parte";
        echo '<br>';
        echo $dataArrivo . " E' il giorno in cui arriva";
        echo '<br>';

        $dataPartIpotetica = substr($row['ora_di_partenza'], 0, 10);
        $dataIpoteticaArrivo = substr($row['ora_di_arrivo'], 0, 10);


        echo $dataPartIpotetica . " E' il giorno in cui parte lo stesso treno";
        echo '<br>';
        echo $dataIpoteticaArrivo . " E' il giorno in cui arriva lo stesso treno";
        echo '<br>';



        if($dataPart == $dataPartIpotetica || $dataArrivo == $dataIpoteticaArrivo){
            Throw new Exception("Impossibile creare un treno. E' già presente un treno in quella giornata.");
        }
    }
    return false;
}
?>