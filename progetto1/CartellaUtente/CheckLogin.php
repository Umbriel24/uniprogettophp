<?php
require_once __DIR__ . '/../CartellaDB/database.php';


function CheckLoginEsatto($email, $password)
{
    try {
        $query = "SELECT * FROM progetto1_Utente
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
        } else return true;
    } catch (Exception $e) {
        echo 'Login sbagliato, Riprova';
    }
}