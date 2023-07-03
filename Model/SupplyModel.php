<?php
    require('./Model/config.php');

    switch($_GET['action']){
        case 'add':
            extract($_POST);

            if (
                !isset($date) || 
                !isset($provider_supply) || 
                !is_numeric($provider_supply) ||
                !isset($PRODUCTS) || 
                !is_array($PRODUCTS) 
            ) response(['js'=>'$.toast(\'Preencha todos os campos do formulário!\',\'warning\')']);
            
            $sql = 'SELECT id from provider where id_legal_person = '.$provider_supply;
            $result = $conn->query($sql);
            $result = $result->fetch_assoc();
            $provider_supply = $result['id'];

            $value_total = 0;
            // insere o produto
            $sql = 'INSERT INTO supply(date, total, id_provider) VALUES ';
            $sql .= '("'.date("Y-m-d H:i:s", strtotime($date)).'",'.$value_total.','.$provider_supply.')';
            
            $result = $conn->query($sql);

            // se consegui inserir vai retornar
            if($result && $conn->insert_id || 1){
                $id_supply = $conn->insert_id;
                while(sizeof($PRODUCTS) ) {
                    
                    list($id, $quantity, $value) = $PRODUCTS;
                    for ($i = 0; $i < 3; $i++) array_shift($PRODUCTS);

                    $id = $id ['id'];
                    $quantity = $quantity ['quantity'];
                    $value = $value ['value'];

                    // remove as virgulas e etc e adiciona o . nas duas casas decimais
                    $value = preg_replace('/([,])/', '', $value);
                    $value = (float) substr($value,0,-2) .'.'. substr($value,-2) ;

                    $sql = 'INSERT INTO product_supply (code_product, id_supply, quantity, value_single) VALUES';
                    $sql .= '('.$id.','. $id_supply.','.$quantity.','. $value.');';

                    $conn->query($sql);
                    
                }
                
                $sql = 'call update_supply('.$id_supply.');';

                $result = $conn->query($sql);
                if($result){
                    $js = '$.toast(\'Fornecimento inserido com sucesso #'.($id_supply).'\', \'success\');';
                    $js .= 'goTo(window.location.pathname);';
                    response(['js'=>$js]);
                }
            } 

            $js = '$.toast(\'Ocorreu um erro ao criar o fornecimento\', \'error\');';
            $js .= 'console.log(\''.$sql.'\');';
            $js .= 'console.log(\''.$conn->error.'\');';
            response(['js'=>$js]);
        break;

        case 'delete':
            $id = $_GET['id'];

            if( !isset( $id ) ||  !is_numeric( $id ) ) response(['js'=>'$.toast(\'Ocorreu um erro, recarregue a página!\',\'warning\')']);

            $sql = 'SELECT id FROM supply where id = '.$id;
            
            $result = $conn->query($sql);
            $result = $result->fetch_assoc();
            
            if( $result['id'] ){
                $sql = 'DELETE FROM product_supply where id_supply = '.$id;
                
                $result = $conn->query($sql);
                
                if( $result ) {
                    $sql = 'DELETE FROM supply where id = '.$id;
                    
                    $result = $conn->query($sql);
                    
                    if( $result ) {
                        $js = '$.toast(\'Produto deletado com sucesso\', \'success\');';
                        $js .= 'goTo( window.location.pathname )';
                        response(['js'=>$js]);
                    }
                }

                $js = '$.toast(\'Ocorreu um erro ao deletar, recarregue a página!\', \'error\');';
                $js .= 'console.log(\''.$sql.'\')';
                response(['js'=>$js]);

            } else{
                $js = '$.toast(\'Registro não encontrado, recarregue a página!\', \'warning\');';
                $js .= 'console.log(\''.$sql.'\')';
                response(['js'=>$js]);
            }

        break;

        default:
            $js = '$.toast(\'Ocorreu um erro, recarregue a pagina\', \'warning\');';
            response(['js'=>$js]);
        break;
    }
?>