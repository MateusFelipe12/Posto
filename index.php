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
        $allowedExtensions = ['css', 'js'];
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
            echo('URL NÃO ENCONTRADA');
            die();
        }
    }

    // faz o logout e vai pra pagina inicial
    if(REQUEST_URI == '/logout'){
        unset($_SESSION['email']);
        unset($_SESSION['permission']);
        unset($_SESSION['name']);

        header('Content-Type: text/html'); 
        die(file_get_contents('./index.html').
            '<script>$.toast(\'Deslogou\');goTo(\'/login\')</script></body>'
        );
    }
     
    if( !isset($_GET['get']) ){
        $content = file_get_contents('./index.html');
        header('Content-Type: text/html'); 
        die($content);
    }


    if(isset($_GET['action'])){
        switch ($_GET['action']) {
            case 'login':
            case 'register':
                if(isset($_SESSION['email'])){
                    response(['js'=> 'goTo("/")']);
                }
                require_once('./Controller/UserController.php');
            break;
        }
    }

    switch (true) {
        case REQUEST_URI == '/registre-se':
            if(isset($_SESSION['email'])){
                response(['page'=>file_get_contents('./index.html')]);
            }
            response(['page' => file_get_contents('./View/userRegister.html')]);
        break;
        case REQUEST_URI == '/login':
            if(isset($_SESSION['email'])){
                response(['page'=>file_get_contents('./index.html')]);
            }

            response(['page' => file_get_contents('./View/userLogin.html')]);

        break;
        case REQUEST_URI == '/' && $_SESSION['email']:
            require_once('./index.html');


        break;
        default:
            if(!sizeof($_GET)){
                if(isset($_SESSION['email'])){
                    response(['page' => require_once('./index.html')]);
                } else{
                    header('Location: /login');
                }
            } else{
                response(['html'=>'ola']);
            }
        break;
    }
?>