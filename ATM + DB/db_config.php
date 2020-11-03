<?php
$dbConnection = mysqli_connect('127.0.0.1', 'root', 'root', 'atm_db');

if (!$dbConnection) {
    echo 'Cannot connect to DB';
    exit();
}

