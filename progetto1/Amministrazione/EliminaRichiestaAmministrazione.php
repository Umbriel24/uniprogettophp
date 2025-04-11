<?php
require_once __DIR__ . '/../CartellaFunzioni/FunzioniCarrozze.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniStazione.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniSubtratta.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniTreno.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniConvoglio.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniLocomotrice.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_richiesta = $_POST["id_richiesta"] ?? null;

    try {
        IniziaTransazione();

        CheckEsistenzaRichiesta($id_richiesta);
        EliminaRichiestabyId($id_richiesta);

        CommittaTransazione();
        echo 'Richiesta eliminata con successo';
        echo '<a href="../PaginaAmministrazione.php">Torna indietro</a> <br>';
    } catch (Exception $e){
        RollbackTransazione();
        echo '<a href="../PaginaAmministrazione.php">Torna indietro</a> <br>';
        die("Errore nell'eliminazione della richiesta. " . $e->getMessage());
    }
}

function EliminaRichiestabyId($id_amministrazione) {
    $query = "DELETE FROM progetto1_Amministrazione WHERE id_amministrazione = $id_amministrazione";
    return EseguiQuery($query);

}

function CheckEsistenzaRichiesta($id_amministrazione)
{
    $query = "SELECT * FROM progetto1_Amministrazione WHERE id_amministrazione = $id_amministrazione";
    $result = EseguiQuery($query);

    if($result->RecordCount() == 0){
        Throw new Exception("Richiesta non trovata. ");
    }
}