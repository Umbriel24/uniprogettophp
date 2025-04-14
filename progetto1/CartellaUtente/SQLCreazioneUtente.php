<?php
require __DIR__ .  '/../CartellaDB/database.php';

function getRowUtenteById($email){
    $query = "SELECT * FROM progetto1_Utente WHERE email = '$email'";
    $risultato = EseguiQuery($query);

    if($risultato->RecordCount() > 0){
        return $risultato->FetchRow();
    }
    return false;
}

function RegistraUtente($nome, $email, $password)
{
    $email = strtolower($email);
    $query = "INSERT INTO progetto1_Utente (nome, email, password) VALUES('$nome', '$email', '$password')";
    $risultato = EseguiQuery($query);

    if(!$risultato) return false;
    return true;

}