<?php
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniCarrozze.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniStazione.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniSubtratta.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniTreno.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniConvoglio.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniLocomotrice.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numero_verifica = $_POST["numero"] ?? null;

    if($numero_verifica != 789){
        echo 'Errore, numero non valido';
        return;
    } else {
        try {
            IniziaTransazione();

            EliminaTuttiBiglietti();
            EliminaTuttiTreni();
            EliminaTuttiConvogli();

            CommittaTransazione();

        } catch (Exception $e) {
            RollbackTransazione();
            echo 'Errore nell`eliminazione: ' . $e->getMessage();
        }
    }
}

function EliminaTuttiBiglietti()
{
    $query = "DELETE FROM progetto1_Biglietto";
    EseguiQuery($query);
}

function EliminaTuttiTreni()
{
    $query = "DELETE FROM progetto1_Treno";
    EseguiQuery($query);

}

function EliminaTuttiConvogli()
{
    $query = "DELETE FROM progetto1_Convoglio";
    EseguiQuery($query);
}