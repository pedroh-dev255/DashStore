<?php
// Define o timezone do PHP
date_default_timezone_set('America/Araguaina');

// Carrega as variáveis de ambiente do arquivo .env
$env = parse_ini_file('.env');

// Verifica se o arquivo .env foi carregado corretamente
if ($env === false) {
    die("Erro ao carregar o arquivo .env");
}

// Bloco try-catch para tratar a exceção ao conectar
try {
    // Conecta ao banco de dados
    $conn = new mysqli($env['DB_HOST'], $env['DB_USER'], $env['DB_PASS'], $env['DB_NAME']);
    
    // Verifica a conexão
    checkConnection($conn, '/'); // Substitua pelo caminho correto da página de erro

    // Define o timezone da sessão MySQL para -03:00
    $conn->query("SET time_zone = '-03:00'");

} catch (Exception $e) {
    // Redireciona para a página de erro se a conexão falhar
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
