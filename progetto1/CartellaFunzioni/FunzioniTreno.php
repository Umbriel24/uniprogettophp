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

function PrendiUltimoIdInserito(){
    $query = "SELECT id_treno FROM progetto1_Treno ORDER BY id_treno DESC LIMIT 1";
    $result = EseguiQuery($query);

    $row = $result->fetchRow();
    if($row == 0 || $row == null){
        Throw new Exception("Errore nella query: " . $query . " Impossibile prendere l'ultimo id inserito");
    }
    return $row["id_treno"];

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

    //Check se esiste già un treno in quella giornata e ritorna false se lo stesso convoglio parte a distanza di meno di  tre ore.
    if(!CheckEsistenzaTrenoInGiornata($id_convoglio, $oraPart, $oraArr)){
        Throw new Exception("Errore, c'è lo stesso convoglio che parte a meno di 3 ore in quella giornata.");
    }

    if ($nome_stazione_partenza == $nome_stazione_arrivo) {
        throw new Exception("Errore nei dati. Stazione di partenza e arrivo coincidono");
    }

    if(CheckTrenoPartenzaNelPassato($oraPart)){
        throw new Exception("Il treno parte in una determinata data nel passato, riprova con una data odierna o futura.");
    }


    if(!Check_LocomotivaGiaInUso($oraPart, $oraArr, $id_convoglio)){
        throw new Exception("Errore: La locomotiva è già in uso a quell'ora. Riprova con un altro orario");
    }

    if(!Check_CarrozzeGiaInUso($oraPart, $oraArr, $id_convoglio)){
        throw new Exception("Errore: ");
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

    if(CheckTrenoPartenzaNelPassato($dataPart)){
        throw new Exception("Il treno modificato parte in una determinata data nel passato. Impossibile da creare");
    }

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


function CheckTrenoPartenzaNelPassato($dataPartenza)
{
    $localtime = strtotime(date('Y-m-d H:i:s'));
    $dateTimeTreno = strtotime($dataPartenza);


    if(($dateTimeTreno - $localtime) > 0){
        return false;
    } else return true;

}

function CheckEsistenzaTrenoInGiornata($id_convoglio, $oraPartTrenoRichiesto, $oraArrTrenoRichiesto)
{
    $dataPartenza = substr($oraPartTrenoRichiesto, 0, 10);
    $oraPartenza = substr($oraPartTrenoRichiesto, 10, 3);

    $dataArrivo = substr($oraArrTrenoRichiesto, 0, 10);
    $oraArrivo = substr($oraArrTrenoRichiesto, 10, 3);

    echo '<br>';

    echo ' data partenza: ' . $dataPartenza;
    echo ' data arrivo: ' . $dataArrivo;
    echo ' ora partenza ' . $oraPartenza;
    echo ' ora arrivo ' . $oraArrivo;

    echo '<br>';

    try {

        $query = "SELECT * FROM progetto1_Treno where id_ref_convoglio = $id_convoglio";
        $result = EseguiQuery($query);

        while ($row = $result->fetchRow()) {
            $dataPartenzaStessoConvoglio =  substr($row['ora_di_partenza'], 0, 10);
            $dataArrivoStessoConvoglio = substr($row['ora_di_arrivo'], 0, 10);

            echo '<br>';
            echo 'La data di partenza è di ' . $dataPartenza;
            echo '<br>';
            echo 'La data di partenza dello stesso convoglio è di ' . $dataPartenzaStessoConvoglio;
            echo '<br>';


            if($dataPartenza == $dataPartenzaStessoConvoglio || $dataArrivo == $dataArrivoStessoConvoglio){
                //i treni partono nella stessa giornata.  Bisogna verificare le 3 ore di differenza.
                $oraPartenzaStessoConvoglio = substr($row['ora_di_partenza'], 10, 3);
                $oraArrivoStessoConvoglio = substr($row['ora_di_arrivo'], 10, 3);

                if(abs((int)$oraPartenza - (int)$oraPartenzaStessoConvoglio) < 3 || abs((int)$oraArrivo - (int)$oraArrivoStessoConvoglio) < 3 ){
                    echo 'Lo stesso treno parte a meno di 3 ore di distanza dal precedente arrivo. Impossibile crearne uno';
                    echo 'I dati sono: ' . $oraPartenza . ' & ' . $oraArrivo;
                    echo 'I dati del treno già esistente sono ' . $oraPartenzaStessoConvoglio . ' & ' . $oraArrivoStessoConvoglio;


                    Throw new Exception("Treno non creabile");
                }
            }
        }
        return true;


    } catch (Exception $e){

        die("Errore nel programma.");
    }
}
?>