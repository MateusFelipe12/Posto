<?php

    if(isset($_SESSION['email']) && isset($_SESSION['permission']) && $_SESSION['permission'] != ''){
        $conn = new mysqli('localhost', $_SESSION['email'], $_SESSION['password'], 'posto');
    } else{
        $conn = new mysqli('localhost', 'server', 'postodegasolina123', 'posto');
    }

    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }


    // MEASURES - METHOD
    if (!function_exists('getItem')) {
        function getItem($item, $id='') {
            require('config.php');
            $sql = 'select * from '.$item;
            if(strlen($id)) $sql .= ' where id ='.$id.';';
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $all_rows = [];
                while ( $row = $result->fetch_assoc()) {
                    $all_rows[] = $row;
                }
                return $all_rows;
            }
            return false;
        }
    }
