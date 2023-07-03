<?php
    require('./Model/config.php');

    switch($_GET['action']){
        case 'add':
            extract($_POST);

            if (
                !isset($description) || 
                !strlen(trim($description)) || 
                !isset($stock) || 
                !is_numeric(trim($stock)) ||
                !isset($measures_unit_code) || 
                !strlen(trim($measures_unit_code)) ||
                !isset($value) || 
                !strlen(trim($value))
            ) response(['js'=>'$.toast(\'Preencha todos os campos do formulário!\',\'warning\')']);

            // remove as virgulas e etc e adiciona o . nas duas casas decimais
            $value = preg_replace('/([,])/', '', $value);
            $value = (float) substr($value,0,-2) .'.'. substr($value,-2) ;

            // insere o produto
            $sql = 'INSERT INTO product(description, value_sale, id_unit_measure, stock) VALUES ';
            $sql .= '("'.$description.'",'.$value.','.$measures_unit_code.','.$stock.')';
            
            $result = $conn->query($sql);
            
            // se consegui inserir vai retornar
            if($result){
                $js = '$.toast(\'Produto inserido com sucesso #'.($conn->insert_id).'\', \'success\');';
                $js .= 'goTo(window.location.pathname);';
                response(['js'=>$js]);
            } else{
                $js = '$.toast(\'Ocorreu um erro ao inserir o produto\', \'error\');';
                $js .= 'console.log(\''.$sql.'\');';
                response(['js'=>$js]);
            }
        break;

        case 'edit':
            extract($_POST);

            if( 
                !isset( $code ) ||  
                !is_numeric( $code ) ||
                !isset($description) || 
                !strlen(trim($description)) || 
                !isset($value) || 
                !is_numeric(trim($value)) || 
                !isset($measures_unit_code) || 
                !is_numeric(trim($measures_unit_code)) || 
                !isset($stock) || 
                !is_numeric(trim($stock))
            ) response(['js'=>'$.toast(\'Informe todos os campos!\',\'warning\')']);
            
            // Verifica se o produto que esta sendo editado existe
            $sql = 'SELECT code FROM product where code = '.$code;
            
            $result = $conn->query($sql);
            $result = $result->fetch_assoc();
            // se existe
            if( $result['code'] ){
                // formata o valor
                $value = preg_replace('/([,])/', '', $value);
                $value = (float) substr($value,0,-2) .'.'. substr($value,-2) ;

                // atualiza o produto
                $sql = 'UPDATE product SET '.
                'description = "'.$description.'",'.
                'stock = "'.$stock.'",'.
                'id_unit_measure = "'.$measures_unit_code.'",'.
                'value_sale = "'.$value.'"'.
                'WHERE code = '.$code;;
                
                $result = $conn->query($sql);

                // se deu certo retorna
                if( $result ) {
                    $js = '$.toast(\'Produto atualizado com sucesso\', \'success\');';
                    $js .= 'goTo( window.location.pathname )';
                    response(['js'=>$js]);
                }
                // se não retorna o erro
                $js = '$.toast(\'Ocorreu um erro ao editar, recarregue a página!\', \'error\');';
                $js = 'console.log(\''.$sql.'\');';
                response(['js'=>$js]);

            } else{
                $js = '$.toast(\'Registro não encontrado, recarregue a página!\', \'warning\');';
                $js .= 'console.log(\''.$sql.'\')';
                response(['js'=>$js]);
            }
        break;

        case 'delete':
            $code = $_GET['code'];

            if( !isset( $code ) ||  !is_numeric( $code ) ) response(['js'=>'$.toast(\'Ocorreu um erro, recarregue a página!\',\'warning\')']);

            $sql = 'SELECT code FROM product where code = '.$code;
            
            $result = $conn->query($sql);
            $result = $result->fetch_assoc();
            
            if( $result['code'] ){
                $sql = 'DELETE FROM product where code = '.$code;
                
                $result = $conn->query($sql);

                if( $result ) {
                    $js = '$.toast(\'Produto deletado com sucesso\', \'success\');';
                    $js .= 'goTo( window.location.pathname )';
                    response(['js'=>$js]);
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