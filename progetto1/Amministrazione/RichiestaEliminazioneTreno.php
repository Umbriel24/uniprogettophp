<?php
require_once __DIR__ . '/../CartellaFunzioni/FunzioniCarrozze.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniStazione.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniSubtratta.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniTreno.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniConvoglio.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniLocomotrice.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_treno = $_POST["id_treno"] ?? null;

    echo $id_treno;

    try {
        IniziaTransazione();
        if(!CheckEsistenzaTreno($id_treno)){
            Throw new Exception("Treno inesistente");
        }


        Inserimento_Progetto1_AmministrazioneEliminazioneTreno($id_treno);

        CommittaTransazione();
    } catch (Exception $e) {
        RollbackTransazione();
        echo '<a href="../PaginaAmministrazione.php">Torna indietro</a> <br>';
        die("Errore nella creazione di richiesta eliminazione " . $e->getMessage());
    }
}

function Inserimento_Progetto1_AmministrazioneEliminazioneTreno($id_trenoEliminare)
{
    $query = "INSERT INTO progetto1_Amministrazione(id_treno_eliminare) VALUES($id_trenoEliminare)";
    $result = EseguiQuery($query);

    return $result;
}