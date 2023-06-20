<?php
function response($ARR){
    if(!is_array($ARR)) return false;

    if(isset($_SESSION['email'])){
        if(!isset($ARR['js']))  $ARR['js'] ='';
        $ARR['js'] = $ARR['js']. ';window.user = {email:\''.$_SESSION['email'].'\'};';
    }

    header('Content-Type: application/json'); 
    die( json_encode( $ARR ) );
}


function get_content($path){
    if(file_exists($path)) return file_get_contents($path);
    if(file_exists($path)) return false;
}