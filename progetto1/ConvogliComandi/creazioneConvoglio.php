<?php
require __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniCarrozze.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniStazione.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniSubtratta.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniTreno.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniConvoglio.php';
require_once __DIR__ . '/../CartellaFunzioni/FunzioniLocomotrice.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $locomotrice = $_POST["locomotrice"] ?? null;
    $carrozze = $_POST["carrozze"] ?? [];


    try{
        IniziaTransazione();
        if(empty($locomotrice)){
            Throw new Exception("Errore: Nessuna locomotrice selezionata");
        }
        if(empty($carrozze)){
            if($locomotrice != 'AN56.2' && $locomotrice != 'AN56.4')
            {
                Throw new Exception("Errore: Nessuna carrozza selezionata. Selezionane almeno 1 - " . $locomotrice);
            }
        }

        //In ordine
        // Comp.Locomotrice -> in_attività diventa si
        UpdateAttivitaLocomotrice($locomotrice);
        echo 'Attività locomotice updatata nel db';

        $posti_a_sedere_complessivi = CalcolaPostiASedereComplessivi($carrozze);

        if($locomotrice == 'AN56.2' || $locomotrice == 'AN56.4'){
            $posti_a_sedere_complessivi += 56;
        }

        // Aggiungiamo new entry in Convoglio con id_ref_locomotiva precedentemente trovata.
        CreazioneConvoglio($locomotrice, $posti_a_sedere_complessivi);
        $id_convoglioInserito = getLastInsertId();

        echo 'Convoglio creato nel db';

        $id_locomotrice = getId_locomotrice_By_Codice($locomotrice);
        $id_ref_locomotiva = $id_locomotrice;

        if(!empty($carrozze)){
            // Ogni singola carrozza viene associata all'id Convoglio
            for($i = 0; $i < count($carrozze); $i++){

                echo '<br>';
                echo $carrozze[$i];
                echo '<br>';
                echo $id_ref_locomotiva;
                echo '<br>';

                echo '<br>';

                InserisciRow_ComposizioneCarrozza($carrozze[$i], $id_convoglioInserito);

            }
            echo '<br>';
            sleep(1);
        }
        CommittaTransazione();
        echo 'Creazione convoglio con update nei table correttamente effettuate';


    } catch (Exception $e){
        RollbackTransazione();
        die("Errore nella creazione convoglio " . $e->getMessage());
    }
}
?>