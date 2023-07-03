<?php
    require('./Model/config.php');

    switch($_GET['action']){
        case 'add':
            extract($_POST);

            if(
                !isset($description) || 
                !isset($symbol) || 
                !strlen(trim($description)) || 
                !strlen(trim($symbol))
            ) response(['js'=>'$.toast(\'Informe Descrição e Simbolo!\',\'warning\')']);

            $sql = 'INSERT INTO unit_measure(description, symbol, fractions) VALUES ';
            $sql .= '("'.$description.'", "'.$symbol.'" ';
            
            if( isset($fractions) && $fractions) {
                $sql .= ',1)';
            } else{
                $sql .= ',0)';
            }

            $result = $conn->query($sql);

            if($result){
                $js = '$.toast(\'Unidade de medida inserida com sucesso #'.($conn->insert_id).'\', \'success\');';
                $js .= 'goTo(window.location.pathname);';
                response(['js'=>$js]);
            } else{
                $js = '$.toast(\'Ocorreu um erro ao inserir, \'error\');';
                $js = 'console.log(\''.$sql.'\')';
                response(['js'=>$js]);
            }

        break;

        case 'edit':
            extract($_POST);

            if( !isset( $id ) ||  
                !is_numeric( $id ) ||
                !isset($description) || 
                !isset($symbol) || 
                !strlen(trim($description)) || 
                !strlen(trim($symbol))
            ) response(['js'=>'$.toast(\'Informe todos os campos!\',\'warning\')']);

            $result = getItem('unit_measure', $id)[0];
            
            if( $result['id'] ){
                $sql = 'UPDATE unit_measure SET
                description = "'.$description.'",
                symbol = "'.$symbol.'",
                fractions = "'.$fractions.'" WHERE id = '.$id;;
                
                $result = $conn->query($sql);

                if( $result ) {
                    $js = '$.toast(\'Unidade de medida atualizada com sucesso\', \'success\');';
                    $js .= 'goTo( window.location.pathname )';
                    response(['js'=>$js]);
                }

                $js = '$.toast(\'Ocorreu um erro ao deletar, recarregue a página!\', \'error\');';
                $js = 'console.log(\''.$sql.'\');';
                response(['js'=>$js]);

            } else{
                $js = '$.toast(\'Registro não encontrado, recarregue a página!\', \'warning\');';
                $js .= 'console.log(\''.$sql.'\')';
                response(['js'=>$js]);
            }
        break;

        case 'delete':
            $id = $_GET['id'];

            if( !isset( $id ) ||  !is_numeric( $id ) ) response(['js'=>'$.toast(\'Ocorreu um erro, recarregue a página!\',\'error\')']);

            $result = getItem('unit_measure', $id)[0];
            
            if( $result['id'] ){
                $sql = 'DELETE FROM unit_measure where id = '.$id;
                
                $result = $conn->query($sql);

                if( $result ) {
                    $js = '$.toast(\'Unidade de medida deletada com sucesso\', \'success\');';
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