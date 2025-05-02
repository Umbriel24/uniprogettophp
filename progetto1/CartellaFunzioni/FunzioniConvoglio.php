<?php
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/FunzioniCarrozze.php';
require_once __DIR__ . '/FunzioniStazione.php';
require_once __DIR__ . '/FunzioniSubtratta.php';
require_once __DIR__ . '/FunzioniTreno.php';
require_once __DIR__ . '/FunzioniConvoglio.php';
require_once __DIR__ . '/FunzioniLocomotrice.php';
function getConvogliCreati()
{
    $query = "SELECT * FROM progetto1_Convoglio";
    return EseguiQuery($query);
}

function StampaConvogli()
{
    $ConvogliList = getConvogliCreati();

    echo '<table>';
    echo '<tr><th>ID Convoglio </th><th>Locomotrice</th><th>Posti a sedere</th><th>Carrozze usate</th><th>Data/Ora creazione</th></tr>';
    while ($row = $ConvogliList->FetchRow()) {

        $id_temp = $row["id_convoglio"];

//        if (CheckConvoglioAttivita($id_temp) == true) {
//            continue;
//        }

        $locomotrice = getLocomotriceBy_ref_locomotrice($row['id_ref_locomotiva']);
        $dataOraTemp = $row['data_ora_creazione'];

        $tempListCarrozze = getCarrozzeByIdConvoglioAssociato($id_temp);

        $posti_a_sedere_temp = getPostiASedereFromConvoglio($id_temp);

        $codici_carrozze = "";

        //Fare in modo che convoglio abbia posti a sedere totali che verranno sottratti dai biglietti

        while ($row2 = $tempListCarrozze->FetchRow()) {
            //Abbiamo ogni carrozza associata all'id convoglio qui
            $codici_carrozze .= $row2["codice_carrozza"] . ", ";

        }

        echo '<tr>';
        echo '<td>' . $id_temp . '</td>';
        echo '<td>' . $locomotrice . '</td>';
        echo '<td>' . $posti_a_sedere_temp . '</td>';
        echo '<td>' . $codici_carrozze . '</td>';
        echo '<td>' . $dataOraTemp . '</td>';
        echo '</tr>';

    }
    echo '</table>';
}

function StampaConvogliInAttivita()
{
    $ConvogliList = getConvogliCreati();

    echo '<table>';
    echo '<tr><th>ID Convoglio </th><th>Locomotrice</th><th>Posti a sedere</th><th>Carrozze usate</th><th>Data/Ora creazione</th></tr>';
    while ($row = $ConvogliList->FetchRow()) {
        $id_temp = $row["id_convoglio"];

        if (CheckConvoglioAttivita($id_temp) == false) {
            continue;
        }

        $locomotrice = getlocomotriceBy_ref_locomotrice($row['id_ref_locomotiva']);
        $dataOraTemp = $row['data_ora_creazione'];
        $tempListCarrozze = getCarrozzeByIdConvoglioAssociato($id_temp);

        $posti_a_sedere_temp = getPostiASedereFromConvoglio($id_temp);
        $codici_carrozze = "";

        //Fare in modo che convoglio abbia posti a sedere totali che verranno sottratti dai biglietti

        while ($row2 = $tempListCarrozze->FetchRow()) {
            //Abbiamo ogni carrozza associata all'id convoglio qui

            $codici_carrozze .= $row2["codice_carrozza"] . ", ";
        }

        echo '<tr>';
        echo '<td>' . $id_temp . '</td>';
        echo '<td>' . $locomotrice . '</td>';
        echo '<td>' . $posti_a_sedere_temp . '</td>';
        echo '<td>' . $codici_carrozze . '</td>';
        echo '<td>' . $dataOraTemp . '</td>';
        echo '</tr>';

    }
    echo '</table>';
}

function CheckConvoglioAttivita($id_convoglio)
{
    $query = "SELECT * FROM progetto1_Treno t
LEFT JOIN progetto1_Convoglio c on c.id_convoglio  = t.id_ref_convoglio";

    $result = EseguiQuery($query);
    while ($row = $result->FetchRow()) {
        if ($row['id_ref_convoglio'] == $id_convoglio) {
            //Quel convoglio è in attività in un treno
            return true;
        }
    }
    return false;
}


function CreazioneConvoglio($codice_locomotrice, $posti_a_sedere_complessivi)
{
    //parte 1: Insert
    $id_locomotrice = getId_locomotrice_By_Codice($codice_locomotrice);


    $query = "INSERT INTO progetto1_Convoglio(id_ref_locomotiva, data_ora_creazione, posti_a_sedere) 
    VALUES($id_locomotrice, NOW(), '$posti_a_sedere_complessivi');";
    EseguiQuery($query);
}

function getLastInsertId()
{
    $query = "SELECT LAST_INSERT_ID()";
    $result = EseguiQuery($query);
    $row = $result->FetchRow();
    if ($row == null) {
        throw new Exception("Errore, nessun id trovato");
    }
    return $row[0];
}

function getPostiASedereFromConvoglio($id_convoglio)
{
    $query = "SELECT posti_a_sedere FROM progetto1_Convoglio WHERE id_convoglio = $id_convoglio";
    $result = EseguiQuery($query);
    $row = $result->FetchRow();
    return $row["posti_a_sedere"];
}

function getPostiASedereDisponibiliFromTreno($id_treno)
{
    $query = "SELECT posti_disponibili FROM progetto1_Treno WHERE id_treno = $id_treno";
    $result = EseguiQuery($query);

    if ($result->RecordCount() == 0) {
        throw new Exception("Posti del treno non trovati. Errore FunzioniConvoglio 142");
    }

    $row = $result->FetchRow();
    return $row["posti_disponibili"];
}

function getConvoglioById_Treno($id_treno)
{
    $query = "SELECT id_ref_convoglio FROM progetto1_Treno WHERE id_treno = $id_treno";
    $result = EseguiQuery($query);

    if ($result->RecordCount() > 0) {
        $row = $result->FetchRow();
        return $row["id_ref_convoglio"];
    } else throw new Exception("Convoglio non trovato con id treno $id_treno");
}

function CheckCombinazioneGiaEsistente($id_convoglio)
{
    $id_locomotiva = 0;
    $nomi_carrozze = [];
    $id_convogli_da_checkare = [];

    //prendi locomotiva
    $query1 = "SELECT id_ref_locomotiva from progetto1_Convoglio where id_convoglio = $id_convoglio";
    $result = EseguiQuery($query1);
    if ($result->RecordCount() == 0) {
        throw new Exception("Errore, il treno non esiste");
    } else {
        $row = $result->FetchRow();
        $id_locomotiva = $row["id_ref_locomotiva"];
    }

    //prendi carrozze
    $query2 = "SELECT nome_carrozza FROM progetto1_ComposizioneCarrozza WHERE id_ref_convoglio = $id_convoglio ";
    $result2 = EseguiQuery($query2);
    if ($result2->RecordCount() != 0) {
        while ($row = $result2->FetchRow()) {
            $nomi_carrozze[] = $row["nome_carrozza"];
        }
    }

    //prendiamo tutti i convogli con la stessa locomotiva TRANNE QUELLA PRESA IN CONSIDERAZIONE ORA
    $query3 = "SELECT *  FROM progetto1_Convoglio where 
                                    id_ref_locomotiva = $id_locomotiva 
                                  AND 
                                    id_convoglio != $id_convoglio";

    echo '<br>';
    echo $query3;
    echo '<br>';
    $result3 = EseguiQuery($query3);
    if ($result3->RecordCount() != 0) {
        while ($row = $result3->FetchRow()) {
            $id_convogli_da_checkare[] = $row["id_convoglio"];
        }
    }

    $carrozzeNumero = count($nomi_carrozze);


    foreach ($id_convogli_da_checkare as $id_convoglio) {
        $query4 = "SELECT nome_carrozza FROM progetto1_Convoglio co
        LEFT JOIN progetto1_ComposizioneCarrozza ca ON co.id_convoglio = ca.id_ref_convoglio
        WHERE id_convoglio = $id_convoglio";

        $result4 = EseguiQuery($query4);

        $carrozze_del_convoglio = [];

        while ($row = $result4->FetchRow()) {
            $carrozze_del_convoglio[] = $row['nome_carrozza'];
        }

        // Ordina entrambi per confrontarli in modo preciso
        sort($carrozze_del_convoglio);
        $carrozze_attese = $nomi_carrozze;
        sort($carrozze_attese);





        if ($carrozze_del_convoglio === $carrozze_attese) {
            // Esiste già un convoglio identico
            return false;
        }
    }

    return true; // Nessun convoglio identico trovato
}

?>
