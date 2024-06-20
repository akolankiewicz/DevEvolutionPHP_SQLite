<?php
//definindo formato de horário
date_default_timezone_set('America/Sao_Paulo');
//conexao com banco 
$db = new SQLite3('database.sqlite');


function CadastrarProduto(object $db){

    $nome = readline('Nome do produto: ');
    echo 'Para o preço, utilize ponto quando não for inteiro...'.PHP_EOL;
    $preco = readline('Preço do produto: ');
    $dataCadastro = date('Y-m-d H:i:s');

    if($nome != '' && is_numeric($preco)){ // validando se o nome existe e se o preço é um numero 
  //$nome optei por diferente de vazio pois pensei que poderia existir algum produto com o nome sendo um numero Exemplo: 51
        $stmt = $db->prepare('INSERT INTO produtos (nome, preco, data_criacao) VALUES (:nome, :preco, :data_criacao)');
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':preco', $preco, SQLITE3_FLOAT);
        $stmt->bindValue(':data_criacao', $dataCadastro);
        $xyz = ($stmt->execute()); // variavel apenas fazendo a inserção no banco
        echo 'Produto Cadastrado!'.PHP_EOL;

    } else {
        echo "Algo deu errado!! Lembre-se, Digite um nome e um preço válido.".PHP_EOL;
    }
}


function ListarProdutos(object $db){

    $entrouNoWhile = false;
    $results = $db->query('SELECT * FROM produtos');//puxando infos do banco

    while ($row = $results->fetchArray()) {
        echo "ID: ".$row['id'].PHP_EOL;
        echo "Nome: ".$row['nome'].PHP_EOL;
        echo "Preço: ".$row['preco'].PHP_EOL;
        echo "Criação: ".$row['data_criacao'].PHP_EOL;
        if($row['data_atualizacao'] != ''){ //validando se existe update ou não
          echo "Data de atualização: ".$row['data_atualizacao'].PHP_EOL;
        }
        echo ''.PHP_EOL; // exibindo um vazio para melhor vizualizacao no terminal

        $entrouNoWhile = true; // variavel que ira definir se a condição abaixo sera executada
    }

    if($entrouNoWhile==false){//so existe se n entrar no loop, para dizer que a tabela está vazia
        echo "A tabela está vazia!".PHP_EOL;
    }
}    



function PesquisarProduto(object $db){

    $prodId = readline("Qual ID deseja procurar? ");
    $stmt = $db->prepare('SELECT * FROM produtos WHERE id = :id');
    $stmt->bindValue(':id', $prodId); // dizendo que o que será buscado é o id inserido
    $result = $stmt->execute();
    $descr = $result ->fetchArray(SQLITE3_ASSOC);//captando informações do produto e armazenando-as
    
    if($descr){//mostrando informações e vendo se o id existe na tabela

        echo "ID: ".$descr['id'].PHP_EOL;
        echo "Nome: ".$descr['nome'].PHP_EOL;
        echo "Preço: ".$descr['preco'].PHP_EOL;
        echo "Criação: ".$descr['data_criacao'].PHP_EOL;
        if($descr['data_atualizacao'] != ''){
            echo "Atualização".$descr['data_atualizacao'].PHP_EOL;
        }
    } else {
        echo "Esse ID não existe".PHP_EOL;
    }
    
}


function AtualizarProduto(object $db){

    $prodId = readline("Qual ID deseja atualizar? ");
    $stmt = $db->prepare('SELECT * FROM produtos WHERE id = :id');
    $stmt->bindValue(':id', $prodId); // dizendo que o que será atualizado é o id inserido
    $result = $stmt->execute();
    $descr = $result ->fetchArray(SQLITE3_ASSOC);//captando informações do produto e armazenando-as
    
    if($descr){ 
        //mostrando o produto
        echo "Nome: ".$descr['nome'].PHP_EOL;
        echo "Preço: ".$descr['preco'].PHP_EOL;
        if($descr['data_atualizacao'] != ''){
           echo "[AVISO] - Esse produto já foi atualizado anteriormente!".PHP_EOL; // apenas um aviso, n acontece nada demais
        }

        //começando atualização
        $descr['nome'] = readline("Digite o novo nome: ");
        $descr['preco'] = readline("Digite o novo preço: ");
        $descr['data_atualizacao'] = date('Y-m-d H:i:s');

        if($descr['nome'] != '' && is_numeric($descr['preco'])){//validando
            $stmt = $db->prepare("UPDATE produtos SET nome = :nome, preco = :preco, data_atualizacao = :update_prod WHERE id = :id"); //updatando o produto
            $stmt->bindValue(':id', $descr['id']);
            $stmt->bindValue(':nome', $descr['nome']);
            $stmt->bindValue(':preco', $descr['preco']);
            $stmt->bindValue(':update_prod', $descr['data_atualizacao']);
            $xyz = $stmt->execute();//variavel apenas fazendo a mudança no banco
            echo "Produto atualizado com sucesso!!".PHP_EOL;
        } else {
            echo "Erro ao atualizar produto, verifique se o Nome e o Preço estão corretos.".PHP_EOL;
        }

    } else {
        echo "Esse ID não existe".PHP_EOL;
    }
}


function ExcluirProduto($db){

    $prodId = readline("Qual ID deseja excluir? ");
    $stmt = $db->prepare('SELECT * FROM produtos WHERE id = :id');
    $stmt->bindValue(':id', $prodId); // dizendo que o que será excluido é o id inserido
    $result = $stmt->execute();
    $descr = $result ->fetchArray(SQLITE3_ASSOC);//captando informações do produto e armazenando-as
    
    if($descr){

        echo "Nome: ".$descr['nome'].PHP_EOL;
        echo "Preço: ".$descr['preco'].PHP_EOL;
        echo "Tem certeza que deseja excluir este produto? (Ação irreversível!)".PHP_EOL;
        $excluir = readline("Digite 'SIM': "); // confirmando exclusão do produto

        if($excluir == 'SIM'){
            $sql = "DELETE FROM produtos WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $prodId);
            $stmt->execute();
            echo "Produto removido com sucesso!".PHP_EOL;
        } else {
            echo "Exclusão do produto " . $descr['id'] . " cancelada".PHP_EOL;
        }

    } else {
        echo "Esse ID não existe".PHP_EOL;
    }
}


function LimparTabela($db){

    echo "CUIDADO!!!".PHP_EOL;          //verificando se o usuário realmente quer apagar tudo
    echo "Ao confirmar a limpeza da tabela, todos os dados serão removidos para sempre".PHP_EOL;
    echo "(Para sempre é muito tempo!)".PHP_EOL;
    echo "Recomendamos que faça um backup antes de excluir tudo...".PHP_EOL;

    $limpeza = readline("Digite 'SIM' para confirmar: ");
    
    if($limpeza == 'SIM'){
        $sql = "DELETE FROM produtos"; // excluindo todos os dados da tabela, a formatação permanece
        $stmt = $db->prepare($sql);
        $stmt->execute();
        echo "Sua tabela está vazia!".PHP_EOL;
    } else {
        echo "Exclusão de tabela cancelada.".PHP_EOL;
    }
    
}


function ExibirMenu(){ //funcao apenas exibe as informações do CRUD
    echo ''.PHP_EOL;
    echo "Escolha uma opção a ser realizada.".PHP_EOL;
    echo "1 -> Cadastrar um produto".PHP_EOL;
    echo "2- > Listagem de produtos".PHP_EOL;
    echo "3 -> Escolher produto específico por ID".PHP_EOL;
    echo "4 -> Atualizar produto por ID".PHP_EOL;
    echo "5 -> Excluir produto por ID".PHP_EOL;
    echo "6 -> Limpar tabela de produtos".PHP_EOL;
    echo 'Sair -> Encerrar execução do programa'.PHP_EOL;
    echo ''.PHP_EOL;// linha em branco para melhor visualizacao
}


//Execucao do programa
while(TRUE){
    ExibirMenu();
    $opcao = readline("Escolha uma opção: ");
    echo ''.PHP_EOL;

    switch($opcao){
        case 1:
            CadastrarProduto($db);
            break;
        case 2:
            ListarProdutos($db);
            break;
        case 3:
            PesquisarProduto($db);
            break;
        case 4:
            AtualizarProduto($db);
            break;
        case 5:
            ExcluirProduto($db);
            break;
        case 6:
            LimparTabela($db);
            break;
        case 'Sair':
            echo 'Programa Encerrado!'.PHP_EOL;
            exit;
        case 'sair': //formato para tentar evitar erros
            echo 'Programa Encerrado!'.PHP_EOL; 
            exit;
    }
}

?>