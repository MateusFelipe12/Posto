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
            
            $list_providers = getItem('legal_person join provider on legal_person.id = provider.id_legal_person;');
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
        require('./Model/config.php');

        $result = getItem('supply');
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
?>
