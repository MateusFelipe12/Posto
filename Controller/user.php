<div class="container py-5">

    <?php
        // incluido conexão com o MySql
        include('../config.php');
        // pega o value do input hidden action
        switch($_POST['action']){
            case 'create':
                // pega as informações presentes no formulario
                $name = $_POST['name'];
                $email = $_POST['email'];
                $telephone = $_POST['telephone'];
                $date_birth = $_POST['date_birth'];
                $password = $_POST['password'];
                // remove a formatação do cpf
                $cpf = str_replace('.','',$_POST['cpf']);
                // remove a formatação do cpf
                $cpf = str_replace('-','',$cpf);
                // data decriação do user
                $date_created = date('Y-m-d H:i:s');
                
                // busca pelo cpf pra ver se ja não existe um registro
                $sql = "SELECT * FROM person where cpf = {$cpf};";
                // faz a busca e guarda o resultado na variavel $result_cpf
                // fetch_object trasnforma o retorno do banco em um objeto
                $result_cpf = $conn->query($sql)->fetch_object();
                
                // se existir um retorno, pega o id, se não false
                $result_cpf = ($result_cpf) ? $result_cpf->id  : false;
                
                // busca pelo email pra ver se ja não existe um registro
                $sql = "SELECT * FROM person where email = '{$email}';";
                // faz a busca e guarda o resultado na variavel $result_email
                // fetch_object trasnforma o retorno do banco em um objeto
                $result_email = $conn->query($sql)->fetch_object();
                
                // se existir um retorno, pega o id, se não false
                $result_email = ($result_email) ? $result_email->id  : false;
                
                // busca pelo telephone pra ver se ja não existe um registro
                $sql = "SELECT * FROM person where number_telephone = '{$telephone}';";
                // faz a busca e guarda o resultado na variavel $result_telephone
                // fetch_object trasnforma o retorno do banco em um objeto
                $result_telephone = $conn->query($sql)->fetch_object();
                
                // se existir um retorno, pega o id, se não false
                $result_telephone = ($result_telephone) ? $result_telephone->id  : false;

                // então, se existir algum dado com o id, significa que ja existe um registro 
                // com esse dado, como são unicos, deve ser tratado
                if ($result_email || $result_cpf || $result_telephone){
                    // retorna que ja existe o registro ao user
                    echo('Ja existe um usuario cadastrado com esse E-mail, CPF ou Telefone');
                }
                else{
                    $token = md5($password.$email.$cpf);
                    // sql com as informações fornecidas no fomrulario de cadastro
                    $sql = "INSERT INTO person (name, email, number_telephone, cpf, date_birth, date_created, token) VALUES(
                    '{$name}', '{$email}', '{$telephone}', '{$cpf}', '{$date_birth}', '{$date_created}', '{$token}');";
                    echo $sql;

                    // envia o sql para a query do banco
                    $res = $conn->query($sql);
                    // se o retorno for true então acriaçãofoi um sucesso
                    // e da o retorno ao user
                    if( $res == true ) {
                        echo ('<script> alert("Cadastro realizado, faça login para continuar")</script>');
                        echo ('<script> window.location.href = "/login";</script>');
                        // adicionar confirm de login
                    }else{
                        echo ('<script> alert("Ocorreu um erro ao realizar o cadastro")</script>');
                    }
                }
                
                break;
            
            
            case 'list_person':
                // pega o id no post do form
                $id = $_POST["id_person"];
                
                // inicio do sql
                $sql = "SELECT * FROM person";
                
                //  se existe um id, ele faz a busca no banco como where
                if( $id && strlen($id) ){
                    $sql .= " WHERE ID = {$id};";
                }else{
                    // apenas adiciona o ; no sql
                    $sql .= ";";
                }
                //  faz a busca
                $res = $conn->query($sql);
                
                // contador de resultados
                $size = 0;

                // imprime a table com a thead
                echo '
                <table>
                    <thead>
                        <tr>
                            <td>#id</td>
                            <td>Nome</td>
                            <td>E-mail</td>
                        </tr>
                    </thead>
                    <tbody>';
                    while($row = $res->fetch_object() ){
                        //  adiciona uma row para cada resultado do banco com os respectivos campos
                        echo '<tr>';
                        echo '<td>'.$row->id.'</td>';
                        echo '<td>'.$row->name.'</td>';
                        echo '<td>'.$row->email.'</td>';
                        echo '</tr>';
                        // incrementa o contador
                        $size++;
                    }
                // fecha a table e imprime a quantidade de resultados
                echo '</tbody></table><br> <p>Encontrado '.$size.' itens.</p>';
                
            break;

            case 'login':
                $email = $_POST['email'];
                $password = $_POST['password'];
                
                // busca pelo cpf pra ver se ja não existe um registro
                $sql = 'SELECT * FROM person where email = '.'"'.$email.'";';
                // echo $
                // // fetch_object trasnforma o retorno do banco em um objeto
                $result_email = $conn->query($sql)->fetch_object();
                
                // se existir um retorno, pega o id, se não false
                $token = ($result_email) ? $result_email->token  : false;
                $cpf = ($result_email) ? $result_email->cpf  : false;
                $id = ($result_email) ? $result_email->id  : false;
                
                if(md5($password.$email.$cpf) == $token){
                    // user e senha incorreta
                    session_start([$id,$email]);    
                } else{
                    echo "dados incorretos";
                }

                break;
            default: 
                // a principio erro, mas deve apresentar a tela de list produtos
                echo ('<script> alert("Desculpe...Ocorreu um erro")</script>');
            break;
        }
    ?>
</div>