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


function Check_CarrozzeGiaInUso($oraPartenzaTreno, $oraArrivoSubTreno, $id_convoglio){

    $carrozzeDelConvoglio = array();

    $query = "SELECT nome_carrozza FROM progetto1_ComposizioneCarrozza WHERE id_ref_convoglio = $id_convoglio";
    $result = EseguiQuery($query);
    $i = 0;
    while ($row = $result->FetchRow()) {
        $carrozzeDelConvoglio[$i] = $row['nome_carrozza'];
        $i++;
    }

    if(count($carrozzeDelConvoglio) == 0){
        echo '<br>';
        echo 'Nessuna carrozza associata al convoglio';
        echo '<br>';

    }

    //Da Treno prendo id_ref_convoglio
    //Per ogni id ref convoglio prendo nome_carrozza
    $dataGiornoPartenzaCompleta = new DateTime($oraPartenzaTreno);
    $dataGiornoArrivoCompleta = new DateTime($oraArrivoSubTreno);

    $giornoPartenza = date('Y-m-d', strtotime($oraPartenzaTreno));
    $giornoArrivo = date('Y-m-d', strtotime($oraArrivoSubTreno));
    $oraPartenza = substr($giornoPartenza, 10, 3);


    $query1 = "SELECT DISTINCT * FROM progetto1_ComposizioneCarrozza pcc 
    LEFT JOIN progetto1_Treno t on t.id_ref_convoglio = pcc.id_ref_convoglio
    WHERE 
    ora_di_partenza >= '$giornoPartenza 00:00:00.000'
    AND
    ora_di_arrivo <= '$giornoArrivo 23:59:59.000'";

    $resul2t = EseguiQuery($query1);
    while ($row2 = $resul2t->FetchRow()) {
        $timeStampPartenza1 = $dataGiornoPartenzaCompleta->getTimestamp();
        $timeStampPartenza2Temp = new DateTime($row2['ora_di_partenza']);
        $timeStampPartenza2 = $timeStampPartenza2Temp->getTimestamp();

        $timeStampArrivo1 = $dataGiornoArrivoCompleta->getTimestamp();
        $timeStampArrivo2Temp = new DateTime($row2['ora_di_arrivo']);
        $timeStampArrivo2 = $timeStampArrivo2Temp->getTimestamp();

        $diffPartenza = abs($timeStampPartenza1 - $timeStampPartenza2)/3600;
        $diffArrivo = abs($timeStampArrivo1 - $timeStampArrivo2)/3600;


        $intervalloDif = $dataGiornoPartenzaCompleta->diff($timeStampPartenza2Temp);
        echo $intervalloDif->format('%h');

        //vuol dire che sono prossimi in arrivo o partenza. diviso 3600 per rendere la differenza in ore
        if($diffPartenza < 2 || $diffArrivo < 2){
           for($j = 0; $j < count($carrozzeDelConvoglio); $j++){
               if($carrozzeDelConvoglio[$j] == $row2['nome_carrozza']){
                   throw new Exception("Carrozza già in uso a quell'ora. Riprova con un altro orario");
               }
           }
        }

    }

    return true;
}
?>