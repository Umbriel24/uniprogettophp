<?php
require_once __DIR__ . '/../SQLProgetto2/Sql_GetQuery.php';
require_once __DIR__ . '/../SQLProgetto2/SQL_PostQuery.php';
require_once __DIR__ . '/../SQLProgetto2/SQL_Check.php';

header('Content-type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $esito = false;


    try {
        IniziaTransazione();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $data = $_POST;
        }

        $treno_id = $data['treno_id'] ?? null;
        $prezzoBiglietto = $data['prezzo'] ?? null;
        $utenteEmail = $data['utenteMail'] ?? null;
        $esercenteEmail = $data['esercente'] ?? null;
        $url_inviante = $data['url_inviante'] ?? null;

        if ($treno_id == null) {
            throw new Exception("Errore. Treno Id non è arrivato nel json pagamento non spedito nel json");
        } else if ($prezzoBiglietto == null) {
            throw new Exception("Errore. Prezzo non è arrivato  spedito nel json");
        } else if ($utenteEmail == null) {
            throw new Exception("Errore. Utente non è arrivato  spedito nel json");

        } else if ($esercenteEmail == null) {
            throw new Exception("Errore. Esercente non è arrivato  spedito nel json");
        } else if ($url_inviante == null) {
            throw new Exception("Errore. URL non è arrivato  spedito nel json");

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
            'message' => $esito ? 'Pagamento completato con successo' : 'Pagamento fallito',
            'prezzo' => $prezzoBiglietto,
            'emailUtente' => $utenteEmail,
            'id_treno' => $treno_id,
        ]);

        $encodedData = json_encode($response);
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