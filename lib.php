<?php
function response($ARR){
    if(!is_array($ARR)) return false;

    // if(!isset($ARR['page'])){
        header('Content-Type: application/json'); 
        die( json_encode( $ARR ) );
    // } else{
    //     $js ='';
    //     if(isset($ARR['js'])){
    //         $js = '<script>'.$ARR['js'].'</script>';
    //     }

    //     header('Content-Type: text/html'); 
    //     die(  $ARR['page'].$js );
    // }
}