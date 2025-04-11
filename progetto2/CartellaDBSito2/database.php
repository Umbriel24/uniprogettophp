<?php
require_once 'dbAccess.php';
//Staticamente otteniamo il $db
if(!function_exists('getConnessioneDb')){

    session_start();
    include  __DIR__ . '/../ADOdb-5.22.8/adodb.inc.php';
    date_default_timezone_set('Europe/Rome');

    function getConnessioneDb(){
        static $db = null;

        if($db === null){
            $driver = 'mysqli';
            $db = newAdoConnection($driver);


            $db->connect('localhost', 'root', '', 'um_gargiulo');

            if (!$db->isconnected()) {
                echo "Errore di connessione al database: " . $db->ErrorMsg();
                exit;
            }
        }
        return $db;
    }
}


function IniziaTransazione(){
    $db = getConnessioneDb();
    $db->BeginTrans();
}

function CommittaTransazione()
{
    $db = getConnessioneDb();
    if (!$db->CommitTrans()) {
        throw new Exception("Eccezione trovata. " . $db->ErrorMsg());
    }
}

function RollbackTransazione()
{
    $db = getConnessioneDb();
    $db->rollbackTrans();
}



function EseguiQueryConParametri($query, $parametri = []){
    $db = getConnessioneDb();

    $risultato = $db->Execute($query, $parametri);
    if (!$risultato) {
        die("Errore nella query: " . $db->ErrorMsg());
    }
    return $risultato;
}

function EseguiQuery( $query){
    $db = getConnessioneDb();

    $risultato = $db->Execute($query);
    if (!$risultato) {
        die("Errore nella query: " . $db->ErrorMsg());
    }
    return $risultato;
}