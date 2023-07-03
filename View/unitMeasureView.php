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
                $id = $_GET['id'];

                if( ! isset($id) || !is_numeric($id) ) response(['js'=>'$.toast(\'Ops, ocorreu um erro, recarregue a pagina!\',\'warning\')']);

                $result = getItem('unit_measure', $id)[0];

                if( $result['id'] ){
                    $html = form_padrao([
                        'fields' =>[
                            [
                                '', 'textarea', 'description', $result['description'], 'placeholder="Descrição"' ,''
                            ],
                            [
                                '', 'text', 'symbol', $result['symbol'], 'placeholder="Simbolo"' ,''
                            ],
                            [
                                'Fracionavel', 'checkbox', 'fractions', $result['fractions'] ,''
                            ],
                            [
                                '', 'hidden', 'id', $result['id'] ,''
                            ]
                        ],
                        'action' =>'?item=measure&action=edit',
                        'button' => btn('Salvar', 'btn-success')
                    ]);

                    response(['html' => $html]);

                } else{
                    $js = '$.toast(\'Registro não encontrado, recarregue a página!\', \'warning\');';
                    response(['js'=>$js]);
                }
            break;
        }
    }

    switch($_SESSION['permission']){
        case 'edit': 
        case 'full': 
            $page = file_get_contents('./View/home.html');
            $page .= get_content('./View/measuresAdd.html');
            $list = getListMeasures($_SESSION['permission']);


            $page .= '
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-10">'.$list.'</div>
                    </div>
                <div>';

            response(['page'=>$page]);
        break; 

        case 'read':
            $list = getListMeasures($_SESSION['permission']);
            $page = file_get_contents('./View/home.html');
            $page .= '
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-10">'.$list.'</div>
                    </div>
                <div>';

            response(['page'=>$page]);
        break;
        default:
            response(['page'=>file_get_contents('./pageNotFound.html')]);
        break; 
    }


    function getListMeasures($permission){
        $result = getItem('unit_measure');

        $items = '';
        if($result){
            $options = '';
            foreach($result as $row ) {
                switch($permission){
                    case 'full':
                        $options = '<div class="col-2">Opções</div>';
                        $items.= '
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-1">'.$row['id'].'</div>
                                    <div class="col-2">'.$row['description'].'</div>
                                    <div class="col-2">'.$row['symbol'].'</div>
                                    <div class="col-2">'.$row['fractions'].'</div>
                                    <div class="col-2">
                                        <button href="/unidades-medida/?item=measure&action=delete&id='.$row['id'].'" class="btn-danger p-1">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <button content_modal="/unidades-medida?item=measure&component=edit&id='.$row['id'].'"  class="btn-success p-1">
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
                                    <div class="col-1">'.$row['id'].'</div>
                                    <div class="col-2">'.$row['description'].'</div>
                                    <div class="col-2">'.$row['symbol'].'</div>
                                    <div class="col-2">'.$row['fractions'].'</div>
                                    <div class="col-2">
                                        <button content_modal="/unidades-medida?item=measure&component=edit&id='.$row['id'].'"  class="btn-success p-1">
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
                                    <div class="col-1">'.$row['id'].'</div>
                                    <div class="col-2">'.$row['description'].'</div>
                                    <div class="col-2">'.$row['symbol'].'</div>
                                    <div class="col-2">'.$row['fractions'].'</div>
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
                            <div class="col-2">Símbolo</div>
                            <div class="col-2">Fracionável</div>'.
                            $options.'
                        </div>
                    </li>'.$items.'
                </ul>
            ';
        }

        return $items;
    }

?>