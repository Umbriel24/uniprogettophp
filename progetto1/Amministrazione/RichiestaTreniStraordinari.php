<?php
require_once __DIR__ . '/../CartellaFunzioni/FunzioniCarrozze.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniStazione.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniSubtratta.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniTreno.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniConvoglio.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniLocomotrice.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $posti_richiesti = $_POST["posti_richiesti"] ?? null;
    $data_partenza = $_POST["data_partenza"] ?? null;
    $id_stazione_partenza = $_POST["id_stazione_partenza"] ?? null;
    $id_stazione_arrivo = $_POST["id_stazione_arrivo"] ?? null;


    try {
        IniziaTransazione();
        $data_partenza = RendiDateTimeCompatibile($data_partenza);

        if(!VerificaNumeroStazioni($id_stazione_partenza, $id_stazione_arrivo)){
            Throw new Exception("Stazioni non valide. ");
        }

        Inserimento_progetto1_Amministrazione($posti_richiesti, $data_partenza ,$id_stazione_partenza, $id_stazione_arrivo);
        CommittaTransazione();
        echo 'Inserimento avvenuto con successo';
        echo '<a href="../PaginaAmministrazione.php">Torna indietro</a>';

    } catch (Exception $e){
        RollbackTransazione();
        echo '<a href="../PaginaAmministrazione.php">Torna indietro</a> <br>';
        die("Errore nell'inserimento della richiesta treno straordinario " . $e->getMessage());
    }

}


function Inserimento_progetto1_Amministrazione($posti_richiesti, $data_partenza, $id_stazione_partenza, $id_stazione_arrivo){
    $query = "INSERT INTO progetto1_Amministrazione(posti_richiesti, data_partenza, id_stazione_partenza, id_stazione_arrivo)
    VALUES($posti_richiesti, '$data_partenza', $id_stazione_partenza, $id_stazione_arrivo)";

    $result = EseguiQuery($query);
    if(!$result){
        Throw new Exception("Errore nell'INSERT. ");
    }
}

?>

