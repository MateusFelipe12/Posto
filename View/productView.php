<?php
    // nesse precisa pois o select precisa ser via query
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
            $page = get_content('./View/productAdd.html');
            
            $list_measures = getItem('unit_measure');
            
            $options_measures = [['', 'Selecione a unidade de medida']];
            foreach ($list_measures as  $value) {
                $options_measures[] = [$value['id'], $value['description']];
            }

            $select_measures = 
                input([
                    '', 
                    'select',
                    'measures_unit_code',
                    '',
                    $options_measures
                ]);

            $list_products = getListProduct($_SESSION['permission']);
            $page = str_replace('{select-measures}', $select_measures, $page) ;
            $page = str_replace('{list_products}', $list_products, $page) ;
            
            $page = get_content('./View/home.html').'
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-10">'.$page.'</div>
                    </div>
                <div>';

            response(['page'=>$page]);
        break; 
        
        case 'read': 
            $list_products = getListProduct($_SESSION['permission']);
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


    function getListProduct($permission){
        $result = getItem('unit_measure');
        $UNIT_MEASURES = [];
        foreach ($result as $value) {
            $UNIT_MEASURES[$value['id']] = $value; 
        }
        
        $result = getItem('product');
        $items = '';
        if($result){
            $options = '';
            foreach($result as $row ) {
                $row['stock'] = substr($row['stock'], 0, -3);
                $row['value_sale'] = 'R$ '.str_replace('.',',',$row['value_sale']);
                switch($permission){
                    case 'full':
                        $options = '<div class="col-2">Opções</div>';
                        $items.= '
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-1">'.$row['code'].'</div>
                                    <div class="col-2">'.$row['description'].'</div>
                                    <div class="col-2">'.$row['stock'].'</div>
                                    <div class="col-2">'.$row['value_sale'].'</div>
                                    <div class="col-3">'.$UNIT_MEASURES[$row['id_unit_measure']]['description'].'</div>
                                    <div class="col-2">
                                        <button href="/produtos/?item=product&action=delete&code='.$row['code'].'" class="btn-danger p-1">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <button content_modal="/produtos?item=product&component=edit&code='.$row['code'].'"  class="btn-success p-1">
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
                                    <div class="col-1">'.$row['code'].'</div>
                                    <div class="col-2">'.$row['description'].'</div>
                                    <div class="col-2">'.$row['stock'].'</div>
                                    <div class="col-2">'.$row['value_sale'].'</div>
                                    <div class="col-3">'.$UNIT_MEASURES[$row['id_unit_measure']]['description'].'</div>
                                    <div class="col-2">
                                        <button content_modal="/produtos?item=product&component=edit&code='.$row['code'].'"  class="btn-success p-1">
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
                                    <div class="col-1">'.$row['code'].'</div>
                                    <div class="col-2">'.$row['description'].'</div>
                                    <div class="col-2">'.$row['stock'].'</div>
                                    <div class="col-2">'.$row['value_sale'].'</div>
                                    <div class="col-3">'.$UNIT_MEASURES[$row['id_unit_measure']]['description'].'</div>
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
                            <div class="col-2">Descrição</div>
                            <div class="col-2">Estoque</div>
                            <div class="col-2">Valor</div>
                            <div class="col-3">Unidade de Medida</div>'.
                            $options.'
                        </div>
                    </li>'.$items.'
                </ul>
            ';
        }
    
        return $items;
    }
?>