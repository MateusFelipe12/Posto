<?php
    if(!isset($_SESSION)) session_start();
    define('REQUEST_URI', explode('?', $_SERVER['REQUEST_URI'])[0]);
    define('PARCIAL_TOKEN', 'Senha forte: ');
    // die('opa');
    require_once('./lib.php');
    
    // TRATANDO REQUISIÇÕES DE ARQUIVOS JS E CSS
    if(isset($_GET['file'])  && $_GET['file']){
        // se busca um arquivo
        // extensões liberadas
        $file = explode('?',REQUEST_URI )[0];
        $allowedExtensions = ['css', 'js', 'woff2', 'ttf'];
        $requestedFile = 'C:/xampp/htdocs'. $file ;
        
        // pega o ultimo conteudo depois do . ex: pega apenas .js
        // http://localhost/libs/js/bootstrap.bundle.min.js
        $uriParts = explode('.', $file );
        $extension = end($uriParts);
        
        // Verifica se o arquivo existe e possui uma extensão permitida
        if (file_exists($requestedFile) && in_array($extension,  $allowedExtensions)) {
            header('Content-Type: text/'. $extension );
            readfile($requestedFile);
            die();
        } else {
            echo('URL NÃO ENCONTRADA'.$extension);
            die();
        }
    }

    // faz o logout e vai pra pagina inicial
    if(REQUEST_URI == '/logout'){
        require('./Controller/userController.php');
    }
     
    if( !isset($_GET['get']) ){
        $content = file_get_contents('./index.html');
        if(isset($_SESSION['email'])){
            $content .= '<script>window.user = {email:\''.$_SESSION['email'].'\'};console.log(user)</script>';
        }
        
        header('Content-Type: text/html'); 
        die($content);
    }


    if(isset($_GET['action'])){
        switch ($_GET['item']) {
            case 'user':
                if(isset($_SESSION['email'])){
                    response(['js'=> 'goTo("/")']);
                }
                require_once('./Controller/userController.php');
            break;
            case 'measure':
                if(!isset($_SESSION['email']) || !$_SESSION['email']){
                    response(['js'=> 'goTo("/")']);
                }
                require_once('./Controller/measureController.php');
            break;
        }
    }

    switch (true) {
        case REQUEST_URI == '/registre-se':
            if(isset($_SESSION['email'])){
                response(['page'=>get_content('./View/home.html')]);
            }
            response(['page' => get_content('./View/userRegister.html')]);

            break;
        case REQUEST_URI == '/login':
            if(isset($_SESSION['email'])){
                response(['page'=>get_content('./View/home.html')]);
            }
            response(['page' => get_content('./View/userLogin.html')]);

        break;
        case REQUEST_URI == '/' && $_SESSION['email']:
            response(['page'=>get_content('./View/home.html')]);
        break;
        case REQUEST_URI == '/produtos' && $_SESSION['email']:
            require_once('./Controller/productController.php');
        break;
        case REQUEST_URI == '/unidades-medida' && $_SESSION['email']:
            require_once('./Controller/measureController.php');
        break;
        default:
            if(!sizeof($_GET)){
                if(isset($_SESSION['email'])){
                    response(['page' => get_content('./index.html')]);
                } else{
                    header('Location: /login');
                }
            } else{
                response(['html'=>'ola']);
            }
        break;
    }
?>