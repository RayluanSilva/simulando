<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: loginadmin.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM administradores WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    session_destroy();
    header("Location: loginadmin.php");
    exit();
}

$total_professores = $pdo->query("SELECT COUNT(*) FROM professores")->fetchColumn();
$total_alunos = $pdo->query("SELECT COUNT(*) FROM alunos")->fetchColumn();
$total_questoes = $pdo->query("SELECT COUNT(*) FROM questoes")->fetchColumn();
$total_simulados = $pdo->query("SELECT COUNT(*) FROM simulado")->fetchColumn();
$total_resultados = $pdo->query("SELECT COUNT(*) FROM resultados")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulando - Painel do Administrador</title>
    <link rel="icon" type="image/x-icon" href="imagem/icon.png" id="icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        #hamburguer-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1001;
        }

        .mobile-menu-popup {
            display: none;
            position: fixed;
            top: 80px;
            right: 20px;
            background-color: #245dc6;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            z-index: 2000;
            padding: 15px;
            min-width: 200px;
        }

        .mobile-menu-popup a {
            display: block;
            color: white;
            text-decoration: none;
            font-family: "Segoe UI", sans-serif;
            font-weight: bold;
            font-size: 18px;
            padding: 12px 15px;
            transition: all 0.3s;
            border-radius: 5px;
            margin-bottom: 5px;
        }

        .mobile-menu-popup a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #74be6c;
        }

        .mobile-menu-popup a.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: #74be6c;
        }

        @media (max-width: 1024px) {
            #hamburguer-btn {
                display: block;
            }

            #nav nav {
                display: none;
            }
        }

        @media (max-width: 640px) {
            .container-entrar {
                position: absolute;
                right: 60px;
                top: 15px;
            }

            .moldura-porta, .porta, .boneco {
                display: none;
            }

            .botao-entrar, .container-entrar a.botao-entrar {
                padding: 12px 20px;
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="mobile-menu-popup" id="mobileMenuPopup">
        <a href="#dashboard" class="active"><i class="fas fa-chart-line"></i> DASHBOARD</a>
        <a href="usuarioadmin.php"><i class="fas fa-users"></i> USUÁRIOS</a>
        <a href="questoesadmin.php"><i class="fas fa-question-circle"></i> QUESTÕES</a>
        <a href="simuladosadmin.php"><i class="fas fa-poll"></i> SIMULADOS</a>
    </div>

    <div id="topo">
        <a href="index.php"><img src="imagem/logo.png" alt="simulando" id="logo"></a>
        <div id="nav">
            <nav>
                <a href="#dashboard" class="active"><i class="fas fa-chart-line"></i> DASHBOARD</a>
                <a href="usuarioadmin.php"><i class="fas fa-users"></i> USUÁRIOS</a>
                <a href="questoesadmin.php"><i class="fas fa-question-circle"></i> QUESTÕES</a>
                <a href="simuladosadmin.php"><i class="fas fa-poll"></i> SIMULADOS</a>
            </nav>
        </div>
        <div class="container-entrar">
            <a href="loginescolha.php" class="botao-entrar">SAIR</a>
            <div class="moldura-porta"></div>
            <div class="porta"></div>
            <div class="boneco"></div>
        </div>
        <button id="hamburguer-btn" onclick="toggleMobileMenu()"><i class="fas fa-bars"></i></button>
    </div>
    <div id="topo2"></div>

    <div id="containerprincipal">
        <h1 id="h1containerprincipal"><i class="fa-solid fa-user-shield"></i> PAINEL DO ADMINISTRADOR</h1>
        <p id="pcontainerprincipal">Bem-vindo, <?php echo htmlspecialchars($admin['nome_usuario']); ?>! Aqui você tem controle total da plataforma.</p>
        
        <div id="visaogeral" class="secao-dashboard">
            <div id="barra"></div>
            <h1><i class="fa-solid fa-address-book"></i> VISÃO GERAL</h1>
            <div id="visaogeralconteudo">
                <div id="divconteudo">
                    <span id="titulo">ADMINISTRADOR:</span>
                    <span id="conteudo"><?php echo htmlspecialchars($admin['nome_usuario']); ?></span>
                </div>
                <div id="divconteudo">
                    <span id="titulo">TOTAL DE PROFESSORES:</span>
                    <span id="conteudo"><?php echo $total_professores; ?></span>
                </div>
                <div id="divconteudo">
                    <span id="titulo">TOTAL DE ALUNOS:</span>
                    <span id="conteudo"><?php echo $total_alunos; ?></span>
                </div>
                <div id="divconteudo">
                    <span id="titulo">QUESTÕES CADASTRADAS:</span>
                    <span id="conteudo"><?php echo $total_questoes; ?></span>
                </div>
                <div id="divconteudo">
                    <span id="titulo">SIMULADOS CRIADOS:</span>
                    <span id="conteudo"><?php echo $total_simulados; ?></span>
                </div>
                <div id="divconteudo">
                    <span id="titulo">RESULTADOS REGISTRADOS:</span>
                    <span id="conteudo"><?php echo $total_resultados; ?></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenuPopup');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

        document.addEventListener("DOMContentLoaded", function() {
            document.addEventListener('click', function(e) {
                const menu = document.getElementById('mobileMenuPopup');
                const hamburgerBtn = document.getElementById('hamburguer-btn');
                
                if (!menu.contains(e.target) && e.target !== hamburgerBtn && !hamburgerBtn.contains(e.target)) {
                    menu.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>