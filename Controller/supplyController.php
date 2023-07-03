<?php
    require_once('config.php');
    
    if(isset($_GET['action'])){
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
                $code = $_GET['code'];

                if( ! isset($code) || !is_numeric($code) ) response(['js'=>'$.toast(\'Ops, ocorreu um erro, recarregue a pagina!\',\'warning\')']);

                $sql = 'SELECT * FROM product where code = '.$code;
                
                $result = $conn->query($sql);
                $result = $result->fetch_assoc();
                $list_measures = getItem('unit_measure');
            
                $options_measures = [['', 'Selecione a unidade de medida']];
                foreach ($list_measures as  $value) {
                    $options_measures[] = [$value['id'], $value['description']];
                }

                $result['stock'] = substr($result['stock'], 0, -3);
    
                if( $result['code'] ){
                    $html = form_padrao([
                        'fields' =>[
                            [
                                '', 'textarea', 'description', $result['description'], 'placeholder="Descrição"' ,''
                            ],
                            [
                                '', 'number', 'stock', $result['stock'], 'placeholder="Estoque Inicial"' ,''
                            ],
                            [
                                '', 'select', 'measures_unit_code', $result['id_unit_measure'], $options_measures
                            ],
                            [
                                '', 'number', 'value', $result['value_sale'] ,''
                            ],
                            [
                                '', 'hidden', 'code', $result['code'] ,''
                            ]
                        ],
                        'action' =>'?item=product&action=edit',
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
            $page = get_content('./View/supplyAdd.html');
            
            $list_providers = getItem('provider, legal_person');
            $options_providers = [['', 'Selecione um fornecedor']];
            foreach ($list_providers as  $value) {
                $options_providers[] = [$value['id'], $value['name_fantasy']];
            }

            $select_measures = 
                input([
                    'Informe o fornecedor', 
                    'select',
                    'provider_supply',
                    '',
                    $options_providers
                ]);

            $list_products = getItem('product');
            $options_products = [['', 'Selecione o produto']];
            foreach ($list_products as  $value) {
                $options_products[] = [$value['code'], $value['description']];
            }

            $select_products = 
                input([
                    '', 
                    'select',
                    'PRODUCTS[][id]',
                    '',
                    $options_products
                ]);
        

            $list_supplies = getListSupply($_SESSION['permission']);
            $page = str_replace('{select-products}', $select_products, $page) ;
            $page = str_replace('{select-providers}', $select_measures, $page) ;
            $page = str_replace('{list_supplies}', $list_supplies   , $page) ;
            
            $page = get_content('./View/home.html').'
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-10">'.$page.'</div>
                    </div>
                <div>';

            response(['page'=>$page]);
        break; 
        
        case 'read': 
            $list_products = getListSupply($_SESSION['permission']);
            $page = $list_products;
            
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


    function getListSupply($permission){
        $result = getItem('supply');
        require('config.php');
        $items = '';
        if($result){
            $options = '';
            foreach($result as $row ) {
                $sql = 'select name_fantasy from legal_person where id = (select id_legal_person from provider where id = '. $row['id_provider'].')';
                $provider = $conn->query($sql);
                $provider = ($provider->fetch_assoc()['name_fantasy']);
                $date = date('d/m/Y',strtotime($row['date']));

                switch($permission){
                    case 'full':
                        $options = '<div class="col-2">Opções</div>';
                        $items.= '
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-1">'.$row['id'].'</div>
                                    <div class="col-3">'.$provider.'</div>
                                    <div class="col-2">'.$date.'</div>
                                    <div class="col-2">
                                        <button href="/fornecimentos/?item=supply&action=delete&id='.$row['id'].'" class="btn-danger p-1">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        ';
                    break;
                    case 'edit': 
                        $items.= '
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-1">'.$row['id'].'</div>
                                    <div class="col-3">'.$provider.'</div>
                                    <div class="col-2">'.$date.'</div>
                                </div>
                            </li>
                        ';
                    break;
                    case 'read':
                        $items.= '
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-1">'.$row['id'].'</div>
                                    <div class="col-3">'.$provider.'</div>
                                    <div class="col-2">'.$date.'</div>
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
                            <div class="col-1">#</div>
                            <div class="col-3">Fornecedor</div>
                            <div class="col-2">Data</div>'.
                            $options.'
                        </div>
                    </li>'.$items.'
                </ul>
            ';
        }
    
        return $items;
    }
    