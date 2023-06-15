<?php
    require_once('config.php');

    switch (true) {
        case $_GET['action'] == 'register':        
            extract($_POST);

            if(!$email || !$password) response(['js'=>'$.toast(\'Informe E-mail e Senha\',\'error\')']);
            else{

                // primeiro valida se existe um usuario com esse email
                $sql = 'select * from user where email = "'.$email.'"';
                $result = $conn->query($sql);
                
                if(!$result->fetch_assoc()['id']){
                    $token = md5(PARCIAL_TOKEN.$password);
                    $sql = 'INSERT INTO user (email, token, name) VALUES ("'.$email.'","'.$token.'","'.$name.'")';
                    $result = $conn->query($sql);
                    $sql = 'select * from user where email = "'.$email.'"';
                    $result = $conn->query($sql);
                    $result = $result->fetch_assoc();
                    
                    if(isset($result['email'])){
                        $_SESSION['email'] = $result['email'];
                        $_SESSION['permission'] = $result['permission'];
                        $_SESSION['name'] = $result['name'];
                        $name = $result['name'];
                        
                        response(['js'=>'$.toast(\'Seja bem vindo(a) '.$name.'\',\'success\');goTo(\'/\')']);
                    }
                }
            }

        break;
        
        case $_GET['action'] == 'login':
        default:
            if(isset($_SESSION['email'])){
                response(['js'=> 'goTo("/")']);
            }

            extract($_POST);
            if(!$email || !$password) response(['js'=>'$.toast(\'Informe E-mail e Senha\',\'error\')']);
            else{
                $sql = 'SELECT * FROM user where email = "'. $email.'"';
                $result = $conn->query($sql);

                if($result && $result->num_rows > 0){
                    $result = $result->fetch_assoc();
                    
                    if($result['token'] == md5(PARCIAL_TOKEN.$password)){
                        $_SESSION['email'] = $result['email'];
                        $_SESSION['permission'] = $result['permission'];
                        $_SESSION['name'] = $result['name'];
                        $name = $result['name'];
                        
                        response(['js'=>'$.toast(\'Seja bem vindo(a) '.$name.'\',\'success\');goTo(\'/\')']);
                    }
                }
                response(['js'=>'$.toast(\'Verifique E-mail e Senha\',\'error\');goTo(\'/login\')']);
            }
            
        break;
    }