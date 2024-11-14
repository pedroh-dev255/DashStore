<?php
// Define o timezone do PHP
date_default_timezone_set('America/Araguaina');

// Carrega as variáveis de ambiente do arquivo .env
$DB_HOST="localhost";
$DB_USER="root";
$DB_PASS="";
$DB_NAME="controle_lojinha";

// Bloco try-catch para tratar a exceção ao conectar
try {
    // Conecta ao banco de dados
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    
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
