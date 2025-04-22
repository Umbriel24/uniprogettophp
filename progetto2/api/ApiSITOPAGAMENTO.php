<?php
require_once __DIR__ . '/../SQLProgetto2/Sql_GetQuery.php';
require_once __DIR__ . '/../SQLProgetto2/SQL_PostQuery.php';
require_once __DIR__ . '/../SQLProgetto2/SQL_Check.php';

header('Content-type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $esito = false;
    $messaggioErrore = ' ';


    try {
        IniziaTransazione();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $data = $_POST;
        }

        $treno_id = $data['treno_id'] ?? null;
        $prezzoBiglietto = $data['prezzo'] ?? null;
        $utenteEmail = $data['utenteMail'] ?? null;
        $password = $data['password'] ?? null;
        $esercenteEmail = $data['esercente'] ?? null;
        $url_inviante = $data['url_inviante'] ?? null;
        $id_stazione_partenza = $data['id_stazione_partenza'] ?? null;
        $id_stazione_arrivo = $data['id_stazione_arrivo'] ?? null;


        if ($treno_id == null) {
            $messaggioErrore .= "Errore treno id";
            throw new Exception("Errore. Treno Id non è arrivato nel json pagamento non spedito nel json");

        } else if ($prezzoBiglietto == null) {
            $messaggioErrore .= "Errore biglietto prezzo id";

            throw new Exception("Errore. Prezzo non è arrivato  spedito nel json");
        } else if ($utenteEmail == null) {
            $messaggioErrore .= "Errore utentemail id";
            throw new Exception("Errore. Utente non è arrivato  spedito nel json");

        } else if ($esercenteEmail == null) {
            $messaggioErrore .= "Errore esercentemail id";
            throw new Exception("Errore. Esercente non è arrivato  spedito nel json");
        } else if ($url_inviante == null) {
            $messaggioErrore .= "Errore url inviante id";
            throw new Exception("Errore. URL non è arrivato  spedito nel json");
        } else if ($password == null) {
            $messaggioErrore .= "Errore password id";
            throw new Exception("Errore, non è arrivata la password nel json");
        } else if ($id_stazione_partenza == null) {
            $messaggioErrore .= "Errore stazione partenza id";
            throw new Exception("Errore, non è arrivata la stazione di partenza nel json");
        } else if ($id_stazione_arrivo == null) {
            $messaggioErrore .= "Errore stazione arrivo id";
            throw new Exception("Errore, non è arrivata la stazione di arrivo nel json");

        }

        if(!CheckEsistenzaEmailPassword($utenteEmail, $password)){
            throw new Exception("Errore. Corrispondenza email-password non valida");
        }


        if (!CheckEsistenzaUtenteByEmail($utenteEmail)) {
            throw new Exception("Utente non valido");
        } else if (!CheckEsistenzaUtenteByEmail($esercenteEmail)) {
            throw new Exception("Esecente Utente non valido");
        }

        $id_utente = getIdUtenteByEmail($utenteEmail);
        $id_Esercente = getIdUtenteByEmail($esercenteEmail);

        $saldoUtente = getSaldoById($id_utente);

        $contoUtente = getIdContoByIdUtente($id_utente);
        $contoEsercente = getIdcontoByIdUtente($id_Esercente);

        if ($saldoUtente >= $prezzoBiglietto) {
            EffettuaTransazione($contoUtente, $contoEsercente, $prezzoBiglietto, date("Y-m-d H:i:s"), $url_inviante);
            $esito = true;
        } else {
            throw new Exception("Saldo insufficiente");
        }
        CommittaTransazione();

    } catch (Exception $e) {
        RollbackTransazione();
        echo 'Errore nella transazione. APISITOPAGAMENTO';

    } finally {
        $response = ([
            'success' => $esito,
            'message' => $esito ? 'Pagamento completato con successo' : 'Pagamento fallito' . $messaggioErrore,
            'prezzo' => $prezzoBiglietto,
            'emailUtente' => $utenteEmail,
            'id_treno' => $treno_id,
            'id_stazione_partenza' => $id_stazione_partenza,
            'id_stazione_arrivo' => $id_stazione_arrivo,
        ]);

        $encodedData = json_encode($response);

        //urlInviante corrisponde a PrenotaBiglietto
        $redirectURL = $url_inviante . (strpos($url_inviante, '?') === false ? '?' : '&')
            . 'payment_result=' . urlencode($encodedData);;


        header("Location: " . $redirectURL);
        exit;

    }
}

function EffettuaTransazione($id_conto_acquirente, $id_conto_esercente, $importo, $data_e_ora, $url_inviante)
{
    try {
        IniziaTransazione();
        $query = "INSERT INTO progetto2_Transazione(id_conto_acquirente, id_conto_esercente, importo, data_e_ora, url_inviante)
        VALUES($id_conto_acquirente, $id_conto_esercente, $importo, '$data_e_ora', '$url_inviante')";

        CommittaTransazione();
        return EseguiQuery($query);


    } catch (Exception $e){
        RollbackTransazione();
        echo 'Transazione non effettuata in sitoPagamento';
    }


}