<?php

    // Connects to the database
    require_once(__DIR__.'/MyPDO.php');

    $fileContent = file(__DIR__.'/.env');

    foreach($fileContent as $envVar) {
        putenv(trim($envVar));
    }

    $connection = new MyPDO();

?>