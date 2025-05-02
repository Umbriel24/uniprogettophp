<?php
require_once __DIR__ . '/../CartellaDB/database.php';
require_once __DIR__ . '/FunzioniCarrozze.php';
require_once __DIR__ . '/FunzioniStazione.php';
require_once __DIR__ . '/FunzioniSubtratta.php';
require_once __DIR__ . '/FunzioniTreno.php';
require_once __DIR__ . '/FunzioniConvoglio.php';
require_once __DIR__ . '/FunzioniLocomotrice.php';

function CalcolaPercorsoSubTratte($id_treno, $id_staz_partenza, $id_staz_arrivo, $_dataOra_part)
{
    $_dataOra_part = RendiDateTimeCompatibile($_dataOra_part);
    $dataOra_partenza = $_dataOra_part;

    // branch salita
    if ($id_staz_partenza < $id_staz_arrivo) {
        $sql = "SELECT id_stazione
                FROM progetto1_Stazione
                WHERE id_stazione BETWEEN $id_staz_partenza AND $id_staz_arrivo
                ORDER BY id_stazione ASC";
        $rs = EseguiQuery($sql);

        while ($row = $rs->fetchRow()) {
            $curr = $row['id_stazione'];
            if ($curr == $id_staz_arrivo) continue;

            $next = $curr + 1;
            $km   = CalcolaKmTotaliSubtratta($curr, $next);
            $arr  = CalcolaTempoArrivoSubtratta($dataOra_partenza, $km);

            $col = Check_CollisioneCorsaTreno($next, $curr, $arr, $dataOra_partenza);
            if ($col !== null) {
                if ($col['chi_deve_aspettare'] === 2) {
                    // nuovo treno aspetta
                    $dataOra_partenza = $col['ora_arrivo_prioritario']
                        ->modify('+1 minute')
                        ->format('Y-m-d H:i:s');
                } else {
                    // vecchio treno aspetta
                    $ritardo = ceil(
                        ($col['ora_arrivo_prioritario']->getTimestamp()
                            - (new DateTime($dataOra_partenza))->getTimestamp())
                        / 60
                    );
                    RitardaSubtratteTreno($col['id_rif_treno_registrato'], $ritardo);
                    // nuovo parte subito
                    $dataOra_partenza = (new DateTime($dataOra_partenza))
                        ->format('Y-m-d H:i:s');
                }
                // ricalcolo arrivo
                $arr = CalcolaTempoArrivoSubtratta($dataOra_partenza, $km);
            }

            // inserisco
            $ins = "INSERT INTO progetto1_Subtratta
                    (km_totali, ora_di_partenza, ora_di_arrivo,
                     id_rif_treno, id_stazione_partenza, id_stazione_arrivo)
                    VALUES
                    ($km, '$dataOra_partenza', '$arr',
                     $id_treno, $curr, $next)";
            EseguiQuery($ins);

            // sosta 2'
            $dataOra_partenza = date(
                'Y-m-d H:i:s',
                strtotime("$arr +2 minutes")
            );
        }

    } else {
        // branch discesa
        $sql = "SELECT id_stazione
                FROM progetto1_Stazione
                WHERE id_stazione BETWEEN $id_staz_arrivo AND $id_staz_partenza
                ORDER BY id_stazione DESC";
        $rs = EseguiQuery($sql);

        while ($row = $rs->fetchRow()) {
            $curr = $row['id_stazione'];
            if ($curr == $id_staz_arrivo) continue;

            $next = $curr - 1;
            $km   = CalcolaKmTotaliSubtratta($curr, $next);
            $arr  = CalcolaTempoArrivoSubtratta($dataOra_partenza, $km);

            $col = Check_CollisioneCorsaTreno($next, $curr, $arr, $dataOra_partenza);
            if ($col !== null) {
                if ($col['chi_deve_aspettare'] === 2) {
                    $dataOra_partenza = $col['ora_arrivo_prioritario']
                        ->modify('+1 minute')
                        ->format('Y-m-d H:i:s');
                } else {
                    $ritardo = ceil(
                        ($col['ora_arrivo_prioritario']->getTimestamp()
                            - (new DateTime($dataOra_partenza))->getTimestamp())
                        / 60
                    );
                    RitardaSubtratteTreno($col['id_rif_treno_registrato'], $ritardo);
                    $dataOra_partenza = (new DateTime($dataOra_partenza))
                        ->format('Y-m-d H:i:s');
                }
                $arr = CalcolaTempoArrivoSubtratta($dataOra_partenza, $km);
            }

            $ins = "INSERT INTO progetto1_Subtratta
                    (km_totali, ora_di_partenza, ora_di_arrivo,
                     id_rif_treno, id_stazione_partenza, id_stazione_arrivo)
                    VALUES
                    ($km, '$dataOra_partenza', '$arr',
                     $id_treno, $curr, $next)";
            EseguiQuery($ins);

            $dataOra_partenza = date(
                'Y-m-d H:i:s',
                strtotime("$arr +2 minutes")
            );
        }
    }
}


function Check_CollisioneCorsaTreno($id_stazione_arrivo, $id_stazione_partenza, $ora_arrivo, $ora_partenza)
{
    $query = "SELECT * FROM progetto1_Subtratta";
    $result = EseguiQuery($query);

    while ($row = $result->fetchRow()) {
        $sp = $row['id_stazione_partenza'];
        $sa = $row['id_stazione_arrivo'];
        $op = $row['ora_di_partenza'];
        $oa = $row['ora_di_arrivo'];

        // caso 2: tratte opposte
        if ($sa == $id_stazione_partenza && $sp == $id_stazione_arrivo) {
            $d1p = new DateTime($ora_partenza);
            $d1a = new DateTime($ora_arrivo);
            $d2p = new DateTime($op);
            $d2a = new DateTime($oa);

            if ($d1p <= $d2a && $d2p <= $d1a) {
                // collisione: chi arriva prima
                if ($d1a < $d2a) {
                    return [
                        'chi_deve_aspettare'    => 1,
                        'ora_arrivo_prioritario'=> $d1a,
                        'id_rif_treno_registrato'=> $row['id_rif_treno']
                    ];
                } else {
                    return [
                        'chi_deve_aspettare'    => 2,
                        'ora_arrivo_prioritario'=> $d2a,
                        'id_rif_treno_registrato'=> $row['id_rif_treno']
                    ];
                }
            }
        }
        // (il caso 1 di partenza allo stesso istante lo lasci invariato)
    }

    return null;
}

function CalcolaKmTotaliSubtratta($id_staz_part, $id_stazione_arr)
{


    $query = "SELECT ABS(s4.km - s3.km) as kmSubtratta FROM progetto1_Stazione s3, progetto1_Stazione s4 
                                        WHERE s3.id_stazione = $id_staz_part AND s4.id_stazione = $id_stazione_arr";


    $result = EseguiQuery($query);
    $row = $result->fetchRow();
    if ($row['kmSubtratta'] <= 0 || $row['kmSubtratta'] == null) {
        throw new Exception("Errore nel calcolo dei km totali. I km calcolati sono: " . $row['SUM(km)']);
    } else return $row['kmSubtratta'];
}

function CalcolaTempoArrivoSubtratta($dataOra_Partenza, $kmTotaliSubtratta)
{
    //Il treno va a 50km/h. Sono circa 13,9 m/s.
    $V_kmh = 50;
    $oreTotali = $kmTotaliSubtratta / $V_kmh;
    $secondiTotali = round($oreTotali * 3600);

    try {
        $data_Partenza = new DateTime($dataOra_Partenza);
        $interval = new DateInterval("PT{$secondiTotali}S");

        $data_Partenza->add($interval);

        $data_Partenza->setTime(
            $data_Partenza->format('H'),
            $data_Partenza->format('i'),
            0  // Secondi a zero
        );

        return $data_Partenza->format('y-m-d H:i:s');
    } catch (Exception $e) {
        die("Errore nel calcolo del tempo . " . $e->getMessage() . "\n");
    }
}

function EliminaCorsaSubtrattaByIdTreno($id_treno)
{

    $query = "DELETE FROM progetto1_Subtratta WHERE id_rif_treno = $id_treno";
    $result = EseguiQuery($query);
    if (!$result) {
        throw new Exception("Errore nella query: " . $query . " Impossibile eliminare le corse subtratta");
    } else return $result;
}

function CheckEsistenzaSubtrattaByIdTreno($id_treno)
{
    $query = "SELECT * FROM progetto1_Subtratta WHERE id_rif_treno = $id_treno";
    $result = EseguiQuery($query);
    if (!$result) {
        throw new Exception("Non esistono percorsi del treno specificato. ");
    } else return true;
}

function StampaSubtrattePerStampaTreni()
{
    $query = "SELECT * FROM progetto1_Subtratta LIMIT 30";
    $result = EseguiQuery($query);

    if (!$result) {
        return false;
    } else
        while ($row = $result->fetchRow()) {

            $id_treno = $row["id_rif_treno"];
            $id_subtratta = $row["id_subtratta"];
            $Ora_partenza = $row['ora_di_partenza'];
            $Ora_arrivo = $row['ora_di_arrivo'];
            $stazione_partenza = getNomeStazioneFromId($row['id_stazione_partenza']);
            $stazione_arrivo = getNomeStazioneFromId($row['id_stazione_arrivo']);

            echo '<tr>';
            echo '<td>' . $id_treno . '</td>';
            echo '<td>' . $id_subtratta . '</td>';
            echo '<td>' . $Ora_partenza . '</td>';
            echo '<td>' . $Ora_arrivo . '</td>';
            echo '<td>' . $stazione_partenza . '</td>';
            echo '<td>' . $stazione_arrivo . '</td>';
            echo '</tr>';
        }
    return true;
}

function RitardaSubtratteTreno(int $id_rif_treno, int $minuti)
{
    // Prendi tutte le subtratte future di quel treno
    $sql = "SELECT id_subtratta, ora_di_partenza, ora_di_arrivo
            FROM progetto1_Subtratta
            WHERE id_rif_treno = $id_rif_treno
              AND ora_di_partenza >= NOW()
            ORDER BY ora_di_partenza ASC";
    $rs  = EseguiQuery($sql);

    while ($row = $rs->fetchRow()) {
        $dtPart = new DateTime($row['ora_di_partenza']);
        $dtArr  = new DateTime($row['ora_di_arrivo']);

        $dtPart->modify("+{$minuti} minutes");
        $dtArr->modify("+{$minuti} minutes");

        $upd = "UPDATE progetto1_Subtratta
                SET ora_di_partenza = '{$dtPart->format('Y-m-d H:i:s')}',
                    ora_di_arrivo   = '{$dtArr->format('Y-m-d H:i:s')}'
                WHERE id_subtratta = {$row['id_subtratta']}";
        EseguiQuery($upd);
    }
}


?>
