<?php
    require('./Model/config.php');

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
                response(['html'=>'Indisponivel']);

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
        $result = getItem('legal_person join provider on legal_person.id = provider.id_legal_person;');
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
    