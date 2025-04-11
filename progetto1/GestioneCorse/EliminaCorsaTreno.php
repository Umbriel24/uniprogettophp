<?php
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniBiglietti.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniCarrozze.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniStazione.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniSubtratta.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniTreno.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniConvoglio.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniLocomotrice.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_treno = $_POST["id_treno"];

    try {
        IniziaTransazione();

        CheckEsistenzaBigliettiPerIlTreno($id_treno);

        EliminaCorsaSubtrattaByIdTreno($id_treno);

        EliminaTreno($id_treno);
        echo 'Treno eliminato correttamente';
        echo '<br>';
        echo '<a href="../PaginaEsercizioGestioneCorse.php">Torna Indietro </a>';

        CommittaTransazione();

    } catch (exception $e) {
        RollbackTransazione();
        die("Errore nella query: " . $e->getMessage());
    }

}





?>
