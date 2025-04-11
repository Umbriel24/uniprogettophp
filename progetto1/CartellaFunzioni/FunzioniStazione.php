<?php
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/FunzioniCarrozze.php';
require_once __DIR__ . '/FunzioniStazione.php';
require_once __DIR__ . '/FunzioniSubtratta.php';
require_once __DIR__ . '/FunzioniTreno.php';
require_once __DIR__ . '/FunzioniConvoglio.php';
require_once __DIR__ . '/FunzioniLocomotrice.php';
function getNomeStazioneFromId($id_stazione)
{
    $query = "SELECT nome FROM progetto1_Stazione WHERE id_stazione = $id_stazione";
    $result = EseguiQuery($query);
    $nomeArray = $result->FetchRow();
    if ($nomeArray == null) {
        throw new exception("Errore. Nome stazione non trovato tramite ID");
    } else return $nomeArray["nome"];
}

function getIdStazionefromNome($nome_stazione)
{
    $query = "SELECT id_stazione FROM progetto1_Stazione WHERE nome = '$nome_stazione'";
    $result = EseguiQuery($query);
    $idStazioneArray = $result->FetchRow();
    if ($idStazioneArray == null) {
        throw new exception("Errore. Nome stazione non trovato tramite ID");
    } else return $idStazioneArray["id_stazione"];
}

function getKmStazioneFromId($id_stazione)
{
    $query = "SELECT km from progetto1_Stazione WHERE id_stazione = $id_stazione";
    $result = EseguiQuery($query);
    $kmArray = $result->FetchRow();
    if ($kmArray == null) {
        throw new Exception("Km non trovati della stazione tramite ID");
    } else return $kmArray["km"];
}

function CalcolaDistanzaTotalePercorsa($id_stazione_partenza, $id_stazione_arrivo)
{
    $km1 = getKmStazioneFromId($id_stazione_partenza);
    $km2 = getKmStazioneFromId($id_stazione_arrivo);

    return $distanza = abs($km1 - $km2);

}

function VerificaNumeroStazioni($id_stazione_partenza, $id_stazione_arrivo)
{
    if ($id_stazione_arrivo == null || $id_stazione_arrivo == null) {
        throw new Exception("Stazioni non valide. sono inesistenti");
    } else if ($id_stazione_partenza < 0 || $id_stazione_arrivo < 0) {
        throw new Exception("Stazioni non valide. sono minori di 10");
    } else if ($id_stazione_partenza > 10 || $id_stazione_arrivo > 10) {
        throw new Exception("Stazioni non valide. sono maggiori di 10");
    } else if ($id_stazione_arrivo == $id_stazione_partenza) {
        throw new Exception("Partenza e destinazione corrispondono. ");
    } else {
        return true;
    }
}

function StampaListaStazioni()
{
    echo '<div>';
    echo '<p>Lista Stazioni</p>';
    echo '<ol>';
    echo '        <li>Torre Spaventa km 0,000</li>';
    echo '        <li>Prato Terra km 2,700</li>';
    echo '        <li>Rocca Pietrosa km 7.580</li>';
    echo '        <li>Villa Pietrosa km 12,680</li>';
    echo '        <li>Villa Santa Maria km 16,900</li>';
    echo '        <li>Pietra Santa Maria km 23,950</li>';
    echo '        <li>Castro Marino km 31,500</li>';
    echo '        <li>Porto spigola km 39,500</li>';
    echo '        <li>Porto San Felice km 46,000</li>';
    echo '        <li>Villa San Felice km 54,680</li>';
    echo '    </ol>';
    echo '</div>';
}

?>
