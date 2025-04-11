<?php
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/FunzioniCarrozze.php';
require_once __DIR__ . '/FunzioniStazione.php';
require_once __DIR__ . '/FunzioniSubtratta.php';
require_once __DIR__ . '/FunzioniTreno.php';
require_once __DIR__ . '/FunzioniConvoglio.php';
require_once __DIR__ . '/FunzioniLocomotrice.php';
function getLocomotriceByAttivita($attivita)
{
    $query = "SELECT * FROM progetto1_ComposizioneLocomotrice as c
            LEFT JOIN progetto1_Locomotiva as l on c.riferimentoLocomotiva = l.id_locomotrice
            LEFT JOIN progetto1_Automotrice as a on c.riferimentoAutomotiva = a.id_automotrice
            WHERE in_attività = '$attivita'";
    return EseguiQuery($query);
}

function getId_locomotrice_By_Codice($codice_locomotrice)
{

    $query = "SELECT * FROM progetto1_ComposizioneLocomotrice as c
            LEFT JOIN progetto1_Locomotiva as l on c.riferimentoLocomotiva = l.id_locomotrice
            LEFT JOIN progetto1_Automotrice as a on c.riferimentoAutomotiva = a.id_automotrice";

    $result = EseguiQuery($query); //sono 5 record (ne sono sicuro)


    //Chiedo scusa all'umanità, penso sia il codice più brutto abbia mai scritto
    while ($row = $result->FetchRow()) {
        if ($row['codice_locomotiva'] == $codice_locomotrice) {
            return $row["id_locomotrice"];

        } else if ($row["codice_automotrice"] == $codice_locomotrice) {
            return $row["Id_locomotrice"]; //Colonna con lo stesso nome, la mette in mauscolo...
        }

    }
    throw new Exception("Errore, locomotrice non trovata con quel codice");
}

function getLocomotriceBy_ref_locomotrice($ref_locomotrice)
{
    $query = "SELECT * FROM progetto1_ComposizioneLocomotrice as c
            LEFT JOIN progetto1_Locomotiva as l on c.riferimentoLocomotiva = l.id_locomotrice
            LEFT JOIN progetto1_Automotrice as a on c.riferimentoAutomotiva = a.id_automotrice
            WHERE c.id_locomotrice = $ref_locomotrice";
    $result = EseguiQuery($query);

    while ($row = $result->FetchRow()) {
        if ($row['codice_locomotiva'] != null) {
            return $row['codice_locomotiva'];
        } else return $row['codice_automotrice'];

    }
    Throw new Exception("Locomotrice non trovabile tramite riferimento");

}

function stampaLocomotriciInattive($locomotriciInattive)
{
    if ($locomotriciInattive != null && $locomotriciInattive->RecordCount() > 0) {

        echo '<table>';
        echo '<tr><th>ID</th><th>Numero di serie</th><th>Posti</th></tr>';

        while ($row = $locomotriciInattive->FetchRow()) {
            if ($row['id_locomotrice'] != null) {
                //Locomotiva senza posti
                echo '<tr>';
                echo '<td>' . $row['codice_locomotiva'] . '</td>';
                echo '<td>' . $row['nome'] . '</td>';
                echo '<td>' . '0' . '</td>';
                echo '</tr>';
            } else if ($row['id_automotrice'] != null) {
                echo '<tr>';
                echo '<td>' . $row['codice_automotrice'] . '</td>';
                echo '<td>' . 'N/A' . '</td>';
                echo '<td>' . $row['posti_a_sedere'] . '</td>';
                echo '</tr>';
            }
        }
    }
}

function UpdateAttivitàLocomotrice($codice_locomotrice)
{
    $id_da_updatare = getId_locomotrice_By_Codice($codice_locomotrice);
    if($id_da_updatare != 0){
        $query2 = "UPDATE progetto1_ComposizioneLocomotrice SET in_attività = 'si' WHERE id_locomotrice = $id_da_updatare";
        return EseguiQuery($query2);
    } else Throw new Exception("Errore: Nessuna locomotrice selezionata per l'update. " . $id_da_updatare . " E' l'id da updatare e " . $codice_locomotrice . " ");
}

function checkLocomotriceInattivaByCodice($codice)
{
    $query = "SELECT * FROM progetto1_ComposizioneLocomotrice as c
            LEFT JOIN progetto1_Locomotiva as l on c.riferimentoLocomotiva = l.id_locomotrice
            LEFT JOIN progetto1_Automotrice as a on c.riferimentoAutomotiva = a.id_automotrice";

    $result = EseguiQuery($query);

    while($row = $result->FetchRow()){
        if($row['codice_locomotiva'] == $codice || $row["codice_automotrice"] == $codice){
            //La row è quella
            if($row['in_attività'] == 'si') Throw new Exception("Errore:" . $codice . " Locomotrice già attiva");
        }
    }
}

?>
