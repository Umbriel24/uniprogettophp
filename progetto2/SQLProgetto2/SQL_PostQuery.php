<?php
require_once __DIR__ . '/../CartellaDBSito2/database.php';

// POST QUERY
function RegistraUtente($nome, $email, $password, $tipo_utente, $codice_fiscale = null, $partita_iva = null){
    //in utente
    $email = strtolower($email);
    $query = "INSERT INTO progetto2_Utente (nome, email, password) VALUES('$nome', '$email', '$password')";
    $risultato = EseguiQuery($query);

    if(!$risultato) return false;

    //Prendiamo l'id.
    $id_utente = getConnessioneDb()->Insert_ID();

    //Registriamo in base al ruolo
    if($tipo_utente == 'acquirente' && !empty($codice_fiscale)) {
        $query2 = "INSERT INTO progetto2_Acquirente (id_acquirente, codice_fiscale) VALUES ($id_utente, '$codice_fiscale')";
    } else if ($tipo_utente == 'esercente' && !empty($partita_iva)) {
        $query2 = "INSERT INTO progetto2_Esercente(id_esercente, partita_iva) VALUES ($id_utente, '$partita_iva')";
    } else {
        return false;
    }
    return EseguiQuery($query2);
}