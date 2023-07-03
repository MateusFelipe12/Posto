<?php
    require_once('config.php');
    
    if(isset($_GET['action'])){
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
                    !isset($agency) || 
                    !strlen(trim($agency)) || 
                    !isset($account) || 
                    !strlen(trim($account)) || 
                    !isset($cnpj) || 
                    !isset($cep) || 
                    !strlen(trim($cep)) ||
                    !isset($id)
                ) response(['js'=>'$.toast(\'Preencha todos os campos do formulário!\',\'warning\')']);

                $js = '';


                $sql = 'select * from provider where id = '.$id.' ';

                $result_provider = $conn->query($sql);
                $result_provider = $result_provider->fetch_assoc();
                
                if(!$result_provider['id']){
                    $js = '$.toast(\'Ocorreu um erro ao atualizar, recarregue a pagina\', \'success\');';
                    $js .= 'console.log(`'.$sql.'`);';
                    $js .= 'console.log(`'.$conn->error.'`);';
                    response(['js'=>$js]);
                }
                
                $id_legal_person = $result_provider['id_legal_person'];
                
                $sql = 'select * from legal_person where id = '.$id_legal_person.' ';

                $result_legal_person = $conn->query($sql);
                $result_legal_person = $result_legal_person->fetch_assoc();
                
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
                    $js = '$.toast(\'Ocorreu um erro ao inserir o fornecedor\', \'success\');';
                    $js .= 'console.log(`'.$sql.'`);';
                    $js .= 'console.log(`'.$conn->error.'`);';
                    response(['js'=>$js]);
                } else{
                    $js = '$.toast(\'Fornecedor atualizado com sucesso\', \'success\');';
                }
                
                $sql = 'UPDATE provider SET agency = "'.$agency.'", account = "'.$account.'"  ';
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

                $sql = 'SELECT id_legal_person FROM provider where id = '.$id;
                
                $result = $conn->query($sql);
                $result = $result->fetch_assoc();
                $id_legal_person = $result['id_legal_person'];

                $sql = 'SELECT id_person FROM legal_person where id = '.$id_legal_person.' LIMIT 1;';
                
                $result = $conn->query($sql);
                $result = $result->fetch_assoc();
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
    }

    if(isset($_GET['component'])){
        switch($_SESSION['permission']){
            case 'full':
            case 'edit':
                // continua  fluxo
            break;
            default:
                response(['js' => 'Permissão negada', 'html' => 'Você não tem permissão para editar']); 
            break;
        }
        switch($_GET['component']){
            case 'edit':
                $id = $_GET['id'];

                if( ! isset($id) || !is_numeric($id) ) response(['js'=>'$.toast(\'Ops, ocorreu um erro, recarregue a pagina!\',\'warning\')']);

                $sql = 'SELECT p.id as provider_id,p.*,lp.*,person.*, a.* FROM 
                provider as p 
                join legal_person as lp on p.id_legal_person = lp.id 
                join person on person.id = lp.id_person
                join address as a on person.id = a.id_person
                where p.id = '.$id;

                $result = $conn->query($sql);
                $result = $result->fetch_assoc();

                $result['cnpj']  = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $result['cnpj']);
                $result['state_registration']  = preg_replace('/(\d{3})(\d{3})(\d{3})/', '$1.$2.$3', $result['state_registration']);
                if( $result['id'] ){
                    $html = form_padrao([
                        'fields' =>[
                            ['', 'text', 'name_fantasy', $result['name_fantasy'], 'placeholder="Nome Fantasia"' ,''],
                            ['', 'text', 'corporate_name', $result['corporate_name'], 'placeholder="Razão social"' ,''],
                            ['', 'text', 'cnpj', $result['cnpj'], 'placeholder="CNPj" disabled' ,''],
                            ['', 'text', 'state_registration', $result['state_registration'], 'placeholder="Inscrição Estadual"' ,''],
                            ['', 'text', 'agency', $result['agency'], 'placeholder="Agencia"' ,''],
                            ['', 'text', 'account', $result['account'], 'placeholder="Conta"' ,''],
                            ['', 'text', 'cep', $result['cep'], 'placeholder="Cep"' ,''],
                            ['', 'text', 'street', $result['street'], 'placeholder="Rua"' ,''],
                            ['', 'text', 'neighborhood', $result['neighborhood'], 'placeholder="Bairro"' ,''],
                            ['', 'text', 'city', $result['city'], 'placeholder="Cidade"' ,''],
                            ['', 'text', 'uf', $result['uf'], 'placeholder="Estado"' ,''],
                            ['', 'text', 'country', $result['country'], 'placeholder="País"' ,''],
                            ['', 'hidden', 'id', $result['provider_id'] ,'']
                        ],
                        'action' =>'?item=provider&action=edit',
                        'button' => btn('Salvar', 'btn-success')
                    ]);

                    response(['html' => $html]);

                } else{
                    $js = '$.toast(\'Registro não encontrado, recarregue a página!\', \'warning\');';
                    $js .= 'console.log(\''.$sql.'\')';
                    response(['js'=>$js]);
                }
            break;
        }
    }

    switch($_SESSION['permission']){
        case 'edit': 
        case 'full': 
            $page = get_content('./View/providerAdd.html');

            $list_products = getListProviders($_SESSION['permission']);
            $page = str_replace('{list_providers}', $list_products, $page) ;
            
            $page = get_content('./View/home.html').'
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-10">'.$page.'</div>
                    </div>
                <div>';

            response(['page'=>$page]);
        break; 

        case 'read': 

            $list_products = getListProviders($_SESSION['permission']);
            $page =  $list_products;
            
            $page = get_content('./View/home.html').'
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-10">'.$page.'</div>
                    </div>
                <div>';

            response(['page'=>$page]);
        break; 

        default:
            response(['page'=>get_content('./pageNotFound.html')]);
        break; 
    }


    function getListProviders($permission){
        $result = getItem('legal_person, provider');
        $items = '';
        if($result){
            $options = '';
            
            foreach($result as $row ) {
                $row['cnpj']  = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $row['cnpj']);
                switch($permission){
                    case 'full':
                        $options = '<div class="col-2">Opções</div>';
                        $items.= '
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-3">'.$row['cnpj'].'</div>
                                    <div class="col-3">'.$row['name_fantasy'].'</div>
                                    <div class="col-2">'.$row['agency'].'</div>
                                    <div class="col-2">'.$row['account'].'</div>
                                    <div class="col-2">
                                        <button href="/fornecedores/?item=provider&action=delete&id='.$row['id'].'" class="btn-danger p-1">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <button content_modal="/fornecedores?item=provider&component=edit&id='.$row['id'].'"  class="btn-success p-1">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        ';
                    break;
                    case 'edit': 
                        $options = '<div class="col-2">Opções</div>';
                        $items.= '
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-3">'.$row['cnpj'].'</div>
                                    <div class="col-3">'.$row['name_fantasy'].'</div>
                                    <div class="col-2">'.$row['agency'].'</div>
                                    <div class="col-2">'.$row['account'].'</div>
                                    <div class="col-2">
                                        <button content_modal="/fornecedores?item=provider&component=edit&id='.$row['id'].'"  class="btn-success p-1">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        ';
                    break;
                    case 'read':
                        $items.= '
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-3">'.$row['cnpj'].'</div>
                                    <div class="col-3">'.$row['name_fantasy'].'</div>
                                    <div class="col-2">'.$row['agency'].'</div>
                                    <div class="col-2">'.$row['account'].'</div>
                                </div>
                            </li>
                        ';
                    break;
                }
    
            }
            $items = '
                <ul class="list-group">
                    <li class="list-group-item list-group-item-primary">
                        <div class="row">
                            <div class="col-3">CNPj</div>
                            <div class="col-3">Nome Fantasia</div>
                            <div class="col-2">Agencia</div>
                            <div class="col-2">Conta</div>'.
                            $options.'
                        </div>
                    </li>'.$items.'
                </ul>
            ';
        }
    
        return $items;
    }
    