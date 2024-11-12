<?php
date_default_timezone_set('America/Araguaina');
/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/
// Carrega as variáveis de ambiente
$env = parse_ini_file('.env');

// Verifica se o arquivo .env foi carregado corretamente
if ($env === false) {
    die("Erro ao carregar o arquivo .env");
}

// Bloco try-catch para tratar a exceção ao conectar
try {
    $conn = mysqli_connect($env['DB_HOST'], $env['DB_USER'], $env['DB_PASS'], $env['DB_NAME']);

    // Verificação da conexão personalizada
    checkConnection($conn, '/'); // Substitua '/' pelo caminho correto da raiz para a tela de erro

} catch (mysqli_sql_exception $e) {
    // Redireciona para a tela de erro se a conexão falhar
    header("Location: /error.php"); // Ajuste o caminho para a localização real da tela de erro
    exit();
}

// Função para verificar a conexão (continua a mesma)
function checkConnection($conn, $ponto) {
    if (!$conn) {
        header("Location: ".$ponto."/error.php"); // Redireciona para a página de erro
        exit(); // Encerra o script para evitar execuções adicionais
    }
}

?>