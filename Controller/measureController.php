<?php
    require('config.php');
    
    if(isset($_GET['action'])){
        switch($_GET['action']){
            case 'add':
                extract($_POST);

                if(
                    !isset($description) || 
                    !isset($symbol) || 
                    !strlen(trim($description)) || 
                    !strlen(trim($symbol))
                ) response(['js'=>'$.toast(\'Informe Descrição e Simbolo!\',\'error\')']);

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
                    response(['js'=>'console.log(\''.$sql.'\')']);
                }

            break;

            case 'edit':
                // $id = $_GET['id'];
                extract($_POST);

                if( !isset( $id ) ||  
                    !is_numeric( $id ) ||
                    !isset($description) || 
                    !isset($symbol) || 
                    !strlen(trim($description)) || 
                    !strlen(trim($symbol))
                ) response(['js'=>'$.toast(\'Informe todos os campos!\',\'warning\')']);

                $sql = 'SELECT id FROM unit_measure where id = '.$id;
                
                $result = $conn->query($sql);
                $result = $result->fetch_assoc();
                
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

                    $js = '$.toast(\'Ocorreu um erro ao deletar, recarregue a página!\', \'success\');';
                    response(['js'=>'console.log(\''.$sql.'\')']);

                } else{
                    $js = '$.toast(\'Registro não encontrado, recarregue a página!\', \'warning\');';
                    $js .= 'console.log(\''.$sql.'\')';
                    response(['js'=>$js]);
                }
            break;

            case 'delete':
                $id = $_GET['id'];

                if( !isset( $id ) ||  !is_numeric( $id ) ) response(['js'=>'$.toast(\'Ocorreu um erro, recarregue a página!\',\'error\')']);

                $sql = 'SELECT id FROM unit_measure where id = '.$id;
                
                $result = $conn->query($sql);
                $result = $result->fetch_assoc();
                
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
    }

    if(isset($_GET['component'])){
        switch($_GET['component']){
            case 'edit':
                $id = $_GET['id'];

                if( ! isset($id) || !is_numeric($id) ) response(['js'=>'$.toast(\'Ops, ocorreu um erro, recarregue a pagina!\',\'warning\')']);

                $sql = 'SELECT * FROM unit_measure where id = '.$id;
                
                $result = $conn->query($sql);
                $result = $result->fetch_assoc();

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
                    $js .= 'console.log(\''.$sql.'\')';
                    response(['js'=>$js]);
                }
            break;
        }
    }

    switch($_SESSION['permission'] = 'full'){
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
        case 'full':
            $page = file_get_contents('./View/home.html');
            $list = getListMeasures($_SESSION['permission']);


            $page .= '
                <div class="container">

                    <div class="row justify-content-center">
                        <div class="col-10"><h2>Unidades de Medida</h2></div>
                        <div class="col-10">'.$list.'</div>
                    </div>
                <div>';

            response(['page'=>$page]);
        break;
        default:
            response(['page'=>file_get_contents('./View/pageNotFound.html')]);
        break; 
    }


function getListMeasures($permission='read'){
    $result = getItem('unit_measure');

    $items = '';
    if($result){
        $options = '';
        foreach($result as $row ) {
            switch($permission){
                case 'full':
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
