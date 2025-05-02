<?php
require __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniCarrozze.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniStazione.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniSubtratta.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniTreno.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniConvoglio.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniLocomotrice.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_convoglio = $_POST["id_convoglio"] ?? null;

    echo $id_convoglio;

    //Inizia transazione
    try {
        IniziaTransazione();

        // Check se l'id del convoglio esiste.
        if (!CheckEsistenzaConvoglio($id_convoglio)) {
            throw new Exception('Convoglio non trovato');
        }

        // Elimiamo la tupla in Convoglio.
        EliminaTuplaConvoglioById($id_convoglio);


        CommittaTransazione();
        echo 'Transazione effettuata con successo';

    } catch (Exception $e) {
        RollbackTransazione();
        die("Errore nella query: " . $e->getMessage());
    }

}

function CheckEsistenzaConvoglio($id_convoglio)
{
    $query = "SELECT * FROM progetto1_Convoglio where id_convoglio = $id_convoglio";
    $result = EseguiQuery($query);

    if ($result && !$result->EOF) {
        return true;
    } else throw new Exception("Convoglio non trovato");
}

function EliminaTuplaConvoglioById($id_convoglio)
{
    $query = "DELETE FROM progetto1_Convoglio WHERE id_convoglio = $id_convoglio";
    $result = EseguiQuery($query);

    if ($result) {
        return true;
    } else throw new Exception("Eliminazione convoglio non riuscita.");
}

?>


