<?php
    $conn = new mysqli('localhost','api','postodegasolina123', 'posto');
    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }
