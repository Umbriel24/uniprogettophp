<?php
require_once __DIR__ . '/../CartellaDBSito2/database.php';

function CheckEsistenzaUtenteByEmail($email){
    $query = "SELECT * from progetto2_Utente where email = '$email'";
    $result = EseguiQuery($query);

    if($result->RecordCount() > 0){
        return true;
    }
}