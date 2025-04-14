<?php
require_once __DIR__ . '/../CartellaDBSito2/database.php';

function CheckEsistenzaUtenteByEmail($email){
    $query = "SELECT * from progetto2_Utente where email = '$email'";
    $result = EseguiQuery($query);

    if($result->RecordCount() > 0){
        return true;
    }
}

function CheckEsistenzaEmailPassword($email, $password){

    try {
        $query = "SELECT * FROM progetto2_Utente
                WHERE
                (
                    (email = '$email')
                    AND
                    (password = '$password')
                )";

        $result = EseguiQuery($query);

        if ($result->RecordCount() == 0) {
            echo 'Login sbagliato';
            throw new Exception("Errore nel login");
        }
        else return true;

    } catch (Exception $e) {
        echo 'Login sbagliato, Riprova';
    }

    return false;
}