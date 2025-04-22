<?php
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniCarrozze.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniStazione.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniSubtratta.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniTreno.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniConvoglio.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniLocomotrice.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniTratta.php';

function CreaBigliettoDaiDati($prezzo, $id_rif_utente, $id_treno, $id_stazione_partenza, $id_stazione_arrivo)
{


    try {
        IniziaTransazione();

        echo ' Tenta di updatare il treno';

        $posto_biglietto = UpdataPostiTreno($id_treno);
        if($posto_biglietto == null){
            throw new Exception("Errore. Impossibile creare il biglietto. il posto non esiste");
        }

        echo ' Tenta di inserire il biglietto';
        Insert_progetto1_Biglietto($posto_biglietto, $prezzo, $id_rif_utente, $id_treno, $id_stazione_partenza, $id_stazione_arrivo);


        CommittaTransazione();
    } catch (Exception $e) {
        RollbackTransazione();
        die("Errore, impossibile creare biglietto " . $e->getMessage());
    }


}

function Insert_progetto1_Biglietto($posto_biglietto, $prezzo, $id_rif_utente, $id_treno, $id_stazione_partenza, $id_stazione_arrivo)
{

        $query = "INSERT INTO progetto1_Biglietto(posto_biglietto, prezzo, id_rif_utente, id_rif_treno, id_stazione_partenza, id_stazione_arrivo) 
        VALUES($posto_biglietto, $prezzo, $id_rif_utente, $id_treno, $id_stazione_partenza, $id_stazione_arrivo)";
        echo 'query: ' . $query;

        EseguiQuery($query);
}

//Diminuiamo di 1 i posti disponibili
function UpdataPostiTreno($id_treno)
{

    try {
        $query = "SELECT * FROM progetto1_Treno where id_treno = $id_treno";

        $result = EseguiQuery($query);

        if ($result->RecordCount() == 0) {
            throw new Exception("Errore: Treno non trovato. Errore in CreaBiglietto");

        }

        $row = $result->FetchRow();
        $posti_a_sedere = $row['posti_disponibili'];

        $posti_a_sedere -= 1;

        $query2 = "UPDATE progetto1_Treno SET posti_disponibili = $posti_a_sedere WHERE id_treno = $id_treno";
        EseguiQuery($query2);

        //Sarà il numero biglietto acquirente
        $posti_a_sedere += 1;

        CommittaTransazione();

        return $posti_a_sedere;

    } catch (Exception $e) {
        RollbackTransazione();
        echo $e->getMessage() . " Errore  CreaBiglietto";
    }

}

?>

