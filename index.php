<?php
function getServerIP() {
    $ip = @file_get_contents("http://169.254.169.254/latest/meta-data/public-ipv4");
    return $ip !== false ? trim($ip) : 'localhost';
}

$ip = getServerIP();
echo 'form action="http://' . $ip . '/www/progetto2/api/ApiSITOPAGAMENTO.php" method="POST"';
?>


<h1>Index del progetto.</h1>
<p>Vai al progetto1: <a href="progetto1/index.php">Clicca qui</a> </p>
<p>Vai al progetto2: <a href="progetto2/index.php">Clicca qui</a></p>