<?php
    require_once('config.php');
    
    if(isset($_POST['action'])){
        switch($_GET){
            case 'add':
                
            break;
        }
    }
    


    switch($_SESSION['permission']){
        case 'read':
        case 'full':
            $sql = 'select * from product;';
            
            $result = $conn->query($sql);
            $page = '';
            
            // if($result && $result->num_rows > 0){
                
            //     // while( $result = $result->fetch_assoc() ) {

            //     // }
            // }

            $page .= file_get_contents('../View/home.html');
            $page ='opa';


            response(['page'=>$page]);

        break; 
        default:
            response(['page'=>file_get_contents('./View/pageNotFound.html')]);
        break; 
    }





