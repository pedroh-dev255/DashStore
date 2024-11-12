<?php
    date_default_timezone_set('America/Araguaina');
    $env = parse_ini_file('.env');


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../style/favicon.ico" type="image/x-icon">
    <title>Erro de Conexão</title>
    <script src="../js/clarity.js"></script>
    <style>
        /* Fundo com transição suave de cores */
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(270deg, #8a2be2, #ff0000, #0000ff);
            background-size: 600% 600%;
            animation: gradientAnimation 10s ease infinite;
            font-family: Arial, sans-serif;
        }

        /* Animação do gradiente de fundo */
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Caixa de erro no centro da tela */
        .error-box {
            width: 90%;
            max-width: 500px;
            background-color: #ffffff;
            border: 2px solid #00ffff; /* Ciano */
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.2);
        }

        /* Título */
        .error-box h1 {
            color: #333;
            font-size: 24px;
            margin-top: 0;
        }

        /* Informações do erro */
        .error-details {
            margin: 15px 0;
            color: #555;
            font-size: 16px;
        }

        /* Mensagem adicional */
        .additional-info {
            color: #333;
            font-size: 14px;
            margin-top: 10px;
        }
        /* Estilo do botão */
        .reconnect-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            background-color: #cccccc;
            color: #333;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 20px;
            position: relative;
        }

        .reconnect-btn:hover {
            background-color: #bbbbbb;
        }

        /* Ícone de reconexão */
        .reconnect-icon {
            width: 16px;
            height: 16px;
            border: 2px solid #333;
            border-top: 2px solid transparent;
            border-radius: 50%;
            margin-right: 8px;
            animation: spin 1s linear infinite;
        }

        /* Animação de rotação */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Estilo do timer dentro do botão */
        .timer {
            font-weight: bold;
            color: #333;
            margin-left: 10px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <?php
        // Informações de erro para exibição
        $dbHost = $env['DB_HOST']; // ou outro host
        $errorMessage = 'Erro ao conectar ao banco de dados.';
    ?>

    <div class="error-box">
        <h1>Erro de Conexão<br>com Banco de Dados</h1>
        <div class="error-details">
            <strong>Host:</strong> <?php echo htmlspecialchars($dbHost); ?><br>
            <strong>Erro:</strong> <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <div class="additional-info">
            Entre em contato com o administrador se o problema persistir.
        </div>

        <!-- Botão de reconexão com timer dentro -->
        <button class="reconnect-btn" onclick="redirectToLogin()">
            <div class="reconnect-icon"></div>
            Tentando Reconnectar em <span class="timer" id="countdown">10.0</span>
        </button>
    </div>

    <script>
        // Função de redirecionamento manual
        function redirectToLogin() {
            window.location.href = 'login.php';
        }

        // Temporizador de 10 segundos contando décimos de segundo
        let countdown = 10.0;
        const countdownElement = document.getElementById('countdown');

        const timer = setInterval(() => {
            countdown -= 0.1;
            countdownElement.textContent = countdown.toFixed(1); // Mostra com uma casa decimal

            // Redireciona automaticamente quando o timer chega a 0
            if (countdown <= 0) {
                clearInterval(timer);
                redirectToLogin();
            }
        }, 100); // Atualiza a cada décimo de segundo (100ms)
    </script>
</body>
</html>
