<?php
function response($ARR){
    if(!is_array($ARR)) return false;

    if(isset($_SESSION['email'])){
        if(!isset($ARR['js']))  $ARR['js'] ='';
        $ARR['js'] = $ARR['js']. ';window.user = {email:\''.$_SESSION['email'].'\'};';
    }

    header('Content-Type: application/json'); 
    die( json_encode( $ARR ) );
}

function get_content($path){
    if(file_exists($path)) return file_get_contents($path);
    if(file_exists($path)) return false;
}

function div($content, $class='', $extra_attr = ''){
    if(strlen($class)) $class= 'class="'.$class.'"';
    return '<div '.$class.' '.$extra_attr.'>'.$content.'</div>';
}


function form_padrao($data){
    if(!isset($data['action'])) return false;
    if(!isset($data['method'])) $method = 'POST';
    else $method = $data['method'];
    
    
    $button = '';
    if(isset($data['button'])) $button = $data['button'];

    $action = $data['action'];

    $html_inputs = '';
    foreach($data['fields'] as $input){
        $html_inputs .= input($input);
    }

    return 
        '<form action="'.$action.'" method="'.$method.'">'.
            $html_inputs.
            $button
        .'</form>';

}

function input($input){
    $extra = '';
    // $html_inputs .=  json_encode($input);
    // continue;
    list($label, $type, $name, $value) = $input;
    if(isset($input[4])) $extra = $input[4];
    $id_input = ++$_SESSION['counter'];

    switch ($type) {
        case 'hidden':
            $field = '<input name="'.$name.'" value="'.$value.'" type="'.$type.'" id="'.$id_input.'" '.$extra.'>';
        break;
        case 'select':
            if(strlen($label)){
                $label = '<label  class="form-label ms-1" for="'.$id_input.'"> '.$label.' </label>';
            }

            $options = '';
            foreach($extra as $option){
                if($option[0] == $value){
                    if($value == '' || !$value){
                        $options .= '<option value="'.$option[0].'" selected disabled>'.$option[1].'</option>';
                    } else{
                        $options .= '<option value="'.$option[0].'" selected>'.$option[1].'</option>';
                    }
                } else{
                    $options .= '<option value="'.$option[0].'">'.$option[1].'</option>';
                }
            }

            $select = '<select name="'.$name.'" id="'.$id_input.'" class="form-select">'.$options.'</select>';
            $field = div($label.$select, 'form-group');
        break;
        case 'checkbox':
            if(strlen($label)){
                $label = '<label  class="form-label ms-1" for="'.$id_input.'"> '.$label.' </label>';
            }
            if($value){
                $checkbox = '<input type="hidden" name="'.$name.'" value="0" >
                <input type="checkbox" name="'.$name.'" value="1" checked id="'.$id_input.'">';
            } else{
                $checkbox = '<input type="hidden" name="'.$name.'" checked value="0" >
                <input type="checkbox" name="'.$name.'" value="1"  id="'.$id_input.'">';
            }
            $field = div(
                    $checkbox.
                    $label
                ,'form-group');
        break;
        case 'textarea':
            if(strlen($label)){
                $label = '<label  class="form-label" for="'.$id_input.'">'.$label.'</label>';
            }

            $field = div(
                    $label.
                    '<textarea class="form-control"  id="'.$id_input.'"  name="'.$name.'" rows="2" '.$extra.'>'.$value.'</textarea>'
                ,'form-group');
        break;
        default:
            // text,number, etc
            if(strlen($label)){
                $label = '<label  class="form-label" for="'.$id_input.'">'.$label.'</label>';
            }

            $field = div(
                    $label.
                    '<input class="form-control" name="'.$name.'" value="'.$value.'" type="'.$type.'" id="'.$id_input.'"  '.$extra.'>'
                ,'form-group');
        break;
    }

    return $field;
}


function btn($content , $class='', $extra_attr='') {
    return '<button class="btn '.$class.'" '.$extra_attr.'>'.$content.'</button>';
}
