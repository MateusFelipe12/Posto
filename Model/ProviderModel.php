<?php
    require('./Model/config.php');

    switch($_GET['action']){
        case 'add':
            extract($_POST);

            if (
                !isset($name_fantasy) || 
                !strlen(trim($name_fantasy)) || 
                !isset($agency) || 
                !strlen(trim($agency)) || 
                !isset($account) || 
                !strlen(trim($account)) || 
                !isset($cnpj) || 
                !isset($cep) || 
                !strlen(trim($cep)) 
            ) response(['js'=>'$.toast(\'Preencha todos os campos do formulário!\',\'warning\')']);

            $js = '';

            // insere o fornecedor
            $sql = 'INSERT INTO person(name) VALUES ("'.$name_fantasy.'")';

            $result_person = $conn->query($sql);
            $id_person = $conn->insert_id;

            $cnpj = str_replace(['.', '-', '/'], '', $cnpj);
            $state_registration = str_replace(['.', '-', '/'], '', $state_registration);

            $sql = 'INSERT INTO legal_person(name_fantasy, corporate_name, cnpj, state_registration, id_person) VALUES ';
            $sql .= '("'.$name_fantasy.'", "'.$corporate_name.'", "'.$cnpj.'", "'.$state_registration.'", "'.$id_person.'")';
            $result_legal_person = $conn->query($sql);
            $id_legal_person = $conn->insert_id;
            
            if(!$result_legal_person){
                $js = '$.toast(\'Ocorreu um erro ao inserir o fornecedor\', \'success\');';
                $js .= 'console.log(`'.$sql.'`);';
                $js .= 'console.log(`'.$conn->error.'`);';
                response(['js'=>$js]);
            } else{
                $js = '$.toast(\'Fornecedor inserido com sucesso\', \'success\');';
            }
            
            $sql = 'INSERT INTO provider(id_legal_person, agency, account) VALUES ';
            $sql .= '("'.$id_legal_person.'", "'.$agency.'", "'.$account.'")';
            $result_provider = $conn->query($sql);
            
            if(!$result_provider){
                $js .= '$.toast(\'Ocorreu um erro ao inserir o fornecedor\', \'success\');';
                $js .= 'console.log(`'.$sql.'`);';
                $js .= 'console.log(`'.$conn->error.'`);';
            }

            foreach($CONTACTS as $index => $contact){
                $type = $TYPE_CONTACTS[$index];
                
                $sql = 'INSERT INTO contact(type, value, id_person) VALUES ';
                $sql .= '("'.$type.'", "'.$contact.'", "'.$id_person.'");';
                $result_contact = $conn->query($sql);

                if(!$result_contact){
                    $js .= '$.toast(\'Ocorreu um erro ao inserir o contato '.$contact.'\', \'success\');';
                    $js .= 'console.log(`'.$sql.'`);';
                    $js .= 'console.log(`'.$conn->error.'`);';
                }
            }

            $sql = 'INSERT INTO address(id_person, uf, city, neighborhood, street, cep, country) VALUES ';
            $sql .= '("'.$id_person.'", "'.$uf.'", "'.$city.'", "'.$neighborhood.'", "'.$street.'" , "'.$cep.'", "'.$country.'")';
            $resul_address = $conn->query($sql);

            if(!$resul_address){
                $js .= '$.toast(\'Ocorreu um erro ao inserir o endereço\', \'success\');';
                $js .= 'console.log(`'.$sql.'`);';
                $js .= 'console.log(`'.$conn->error.'`);';
            }

            $js .= 'goTo(window.location.pathname);';
            response(['js'=>$js]);
        break;

        case 'edit':
            extract($_POST);

            if (
                !isset($name_fantasy) || 
                !strlen(trim($name_fantasy)) || 
                !isset($cep) || 
                !strlen(trim($cep)) ||
                !isset($id)
            ) response(['js'=>'$.toast(\'Preencha todos os campos do formulário!\',\'warning\')']);
            response(['html'=>'Indisponivel']);

            $js = '';

            $result_provider = getItem('provider', $id)[0];
            
            if(!$result_provider['id']){
                $js = '$.toast(\'Ocorreu um erro ao atualizar, recarregue a pagina\', \'success\');';
                $js .= 'console.log(`'.$sql.'`);';
                $js .= 'console.log(`'.$conn->error.'`);';
                response(['js'=>$js]);
            }
            
            $id_legal_person = $result_provider['id_legal_person'];
            
            $result_legal_person = getItem('legal_person', $id_legal_person)[0];
            
            if(!$result_legal_person['id']){
                $js = '$.toast(\'Ocorreu um erro ao atualizar, recarregue a pagina\', \'success\');';
                $js .= 'console.log(`'.$sql.'`);';
                $js .= 'console.log(`'.$conn->error.'`);';
                response(['js'=>$js]);
            }
            $id_person = $result_legal_person['id_person'];
            
            // ATUALIZA A PERSON
            $sql = 'UPDATE person SET name = ("'.$name_fantasy.'") where id = '.$id_person.' ';
            
            $result_person = $conn->query($sql);
            
            $state_registration = str_replace(['.', '-', '/'], '', $state_registration);

            $sql = 'UPDATE legal_person SET name_fantasy = "'.$name_fantasy.'", corporate_name = "'.$corporate_name.'", state_registration = "'.$state_registration.'" where id ='.$id_legal_person;
            $result_legal_person = $conn->query($sql);
            
            if(!$result_legal_person){
                $js = '$.toast(\'Ocorreu um erro ao inserir o fornecedor\', \'error\');';
                $js .= 'console.log(`'.$sql.'`);';
                $js .= 'console.log(`'.$conn->error.'`);';
                response(['js'=>$js]);
            } else{
                $js = '$.toast(\'Fornecedor atualizado com sucesso\', \'success\');';
            }
            
            $sql = 'UPDATE provider SET agency = "'.$agency.'", account = "'.$account.'"  where id_legal_person = '. $result_legal_person;
            $result_provider = $conn->query($sql);
            
            if(!$result_provider){
                $js .= '$.toast(\'Ocorreu um erro ao inserir o fornecedor\', \'success\');';
                $js .= 'console.log(`'.$sql.'`);';
                $js .= 'console.log(`'.$conn->error.'`);';
            }

            $js .= 'goTo(window.location.pathname);';
            response(['js'=>$js]);
        break;

        case 'delete':
            $id = $_GET['id'];

            if( !isset( $id ) ||  !is_numeric( $id ) ) response(['js'=>'$.toast(\'Ocorreu um erro, recarregue a página!\',\'warning\')']);

            $result = getItem('provider', $id)[0];
            $id_legal_person = $result['id_legal_person'];

            $sql = 'SELECT id_person FROM legal_person where id = '.$id_legal_person.' LIMIT 1;';
            
            $result = $conn->query($sql);
            $result =  getItem('legal_person', $id_legal_person.' LIMIT 1;');
            $id_person = $result['id_person'];

            if( $id_person ){
                $sql = 'DELETE FROM provider where id = '.$id;
                $conn->query($sql);
                $sql = 'DELETE FROM legal_person where id_person = '.$id_person;
                $conn->query($sql);
                $sql = 'DELETE FROM contact where id_person = '.$id_person;
                $conn->query($sql);
                $sql = 'DELETE FROM address where id_person = '.$id_person;
                $conn->query($sql);
                $sql = 'DELETE FROM person where id = '.$id_person;
                $conn->query($sql);
                

                if( $result ) {
                    $js = '$.toast(\'Registros deletados com sucesso\', \'success\');';
                    $js .= 'goTo( window.location.pathname )';
                    response(['js'=>$js]);
                }

                $js = '$.toast(\'Ocorreu um erro ao deletar, recarregue a página!\', \'error\');';
                $js .= 'console.log(\''.$sql.'\')';
                $js .= 'console.log(\''.$conn->error.'\')';
                response(['js'=>$js]);
                
            } else{
                $js = '$.toast(\'Registro não encontrado, recarregue a página!\', \'warning\');';
                $js .= 'console.log(\''.$sql.'\')';
                $js .= 'console.log(\''.$conn->error.'\')';
                response(['js'=>$js]);
            }

        break;

        default:
            $js = '$.toast(\'Ocorreu um erro, recarregue a pagina\', \'warning\');';
            response(['js'=>$js]);
        break;
    }
?>