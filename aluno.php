<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'aluno') {
    header("Location: loginaluno.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM alunos WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    session_destroy();
    header("Location: loginaluno.php");
    exit();
}

$stmt = $pdo->prepare("SELECT r.*, s.nome_simulado 
                      FROM resultados r 
                      JOIN simulado s ON r.id_simulado = s.id_simulado 
                      WHERE r.id_aluno = ? 
                      ORDER BY r.data_realizacao DESC 
                      LIMIT 2");
$stmt->execute([$_SESSION['usuario_id']]);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulando - Aluno Dashboard</title>
    <link rel="icon" type="image/x-icon" href="imagem/icon.png" id="icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="aluno.css">
    <style>
        #hamburguer-btn {
            display: none;
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
        }

        .mobile-menu-popup a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #74be6c;
        }

        @media (max-width: 1024px) {
            #hamburguer-btn {
                display: block;
            }

            nav {
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
        <a href="#dashboard"><i class="fa-solid fa-house"></i> DASHBOARD</a>
        <a href="simulado.php"><i class="fa-solid fa-pen"></i> SIMULADO</a>
        <a href="resultadosaluno.php"><i class="fas fa-poll"></i> RESULTADOS</a>
    </div>

    <div id="topo">
        <a href="index.php"><img src="imagem/logo.png" alt="simulando" id="logo"></a>
        <div id="nav">
            <nav>
                <a href="#dashboard"><i class="fa-solid fa-house"></i> DASHBOARD</a>
                <a href="simulado.php"><i class="fa-solid fa-pen"></i> SIMULADO</a>
                <a href="resultadosaluno.php"><i class="fas fa-poll"></i> RESULTADOS</a>
            </nav>
        </div>

        <div class="container-entrar">
            <a href="loginescolha.php" class="botao-entrar">SAIR</a>
            <div class="moldura-porta"></div>
            <div class="porta"></div>
            <div class="boneco"></div>
        </div>
    </div>
    <div id="topo2"></div>
     <div id="containerprincipal">
        <h1 id="h1containerprincipal"><i class="fa-solid fa-user-graduate"></i> PAINEL DO ALUNO</h1>
        <p id="pcontainerprincipal">Bem-vindo, <?php echo htmlspecialchars($aluno['nome_usuario']); ?>! Aqui você pode acessar o dashboard, realizar os simulados e ver seus resultados.</p>
        <div id="visaogeral">
            <div id="barra"></div>
            <h1><i class="fa-solid fa-address-book"></i> VISÃO GERAL</h1>
            <div id="visaogeralconteudo">
                <div id="divconteudo">
                    <span id="titulo">NOME:</span>
                    <span id="conteudo"><?php echo htmlspecialchars($aluno['nome_usuario']); ?></span>
                </div>
                <div id="divconteudo">
                    <span id="titulo">R.M:</span>
                    <span id="conteudo"><?php echo htmlspecialchars($aluno['rm']); ?></span>
                </div>
                <div id="divconteudo">
                    <span id="titulo">E-MAIL:</span>
                    <span id="conteudo"><?php echo htmlspecialchars($aluno['email']); ?></span>
                </div>
                <div id="divconteudo">
                    <span id="titulo">CURSO:</span>
                    <span id="conteudo"><?php echo htmlspecialchars($aluno['curso']); ?></span>
                </div>
            </div>
        </div>
        <div id="desempenho">
            <div id="barra"></div>
            <h1><i class="fas fa-medal"></i> DESEMPENHO RECENTE</h1>
            <div id="desempenhoconteudo">
                <?php foreach ($resultados as $index => $resultado): ?>
                <div class="desempenhoitem" onclick="abrirPopup(
                    '<?php echo htmlspecialchars($resultado['nome_simulado']); ?>',
                    '<?php echo $resultado['acertos']; ?>',
                    '<?php echo date('d/m/Y', strtotime($resultado['data_realizacao'])); ?>',
                    '<?php echo number_format($resultado['nota'], 1); ?>',
                    '<?php echo $resultado['acertos']; ?>',
                    '<?php echo round(($resultado['nota'] / 10) * 100); ?>%'
                )">
                    <span class="desempenhoturma"><?php echo htmlspecialchars($resultado['nome_simulado']); ?></span>
                    <span class="desempenhonota">Nota: <?php echo number_format($resultado['nota'], 1); ?>/10</span>
                    <div class="barradesempenho" style="width: <?php echo ($resultado['nota'] / 10) * 100; ?>%"></div>
                    <span class="desempenhodata">Data: <?php echo date('d/m/Y', strtotime($resultado['data_realizacao'])); ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (count($resultados) === 0): ?>
                <div class="desempenhoitem">
                    <span class="desempenhoturma">Nenhum simulado realizado</span>
                    <span class="desempenhonota">Nota: 0/0</span>
                    <div class="barradesempenho" style="width: 0%"></div>
                    <span class="desempenhodata">Data: --/--/----</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="popup-overlay" id="popupOverlay">
        <div class="popup-content">
            <span class="popup-close" onclick="fecharPopup()">&times;</span>
            <div class="popup-header">
                <i class="fas fa-chart-line"></i> DESEMPENHO DETALHADO
            </div>
            <div class="popup-info">
                <div class="popup-info-row">
                    <span class="popup-info-label">Simulado:</span>
                    <span class="popup-info-value" id="popupSimulado">Bimestre</span>
                </div>
                <div class="popup-info-row">
                    <span class="popup-info-label">Data:</span>
                    <span class="popup-info-value" id="popupData">dd/mm/aaaa</span>
                </div>
                <div class="popup-info-row">
                    <span class="popup-info-label">Nota Final:</span>
                    <span class="popup-info-value" id="popupNota">00</span>
                </div>
            </div>
            <div class="popup-stats">
                <div class="popup-stat">
                    <div class="popup-stat-value" id="popupAcertos">00</div>
                    <div class="popup-stat-label">Acertos</div>
                </div>
                <div class="popup-stat">
                    <div class="popup-stat-value" id="popupPontuacao">00</div>
                    <div class="popup-stat-label">Pontuação</div>
                </div>
                <div class="popup-stat">
                    <div class="popup-stat-value" id="popupPercentil">00%</div>
                    <div class="popup-stat-label">Percentil</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const topo = document.getElementById('topo');
            const nav = document.querySelector('nav');
            const entrarContainer = document.querySelector('.container-entrar');

            const hamburguer = document.createElement('button');
            hamburguer.innerHTML = '<i class="fas fa-bars"></i>';
            hamburguer.id = 'hamburguer-btn';
            hamburguer.style.display = 'none';
            hamburguer.style.background = 'none';
            hamburguer.style.border = 'none';
            hamburguer.style.color = 'white';
            hamburguer.style.fontSize = '24px';
            hamburguer.style.cursor = 'pointer';
            hamburguer.style.position = 'absolute';
            hamburguer.style.right = '20px';

            topo.appendChild(hamburguer);

            const mobileMenuPopup = document.getElementById('mobileMenuPopup');

            hamburguer.addEventListener('click', function(e) {
                e.stopPropagation();
                if (mobileMenuPopup.style.display === 'block') {
                    mobileMenuPopup.style.display = 'none';
                } else {
                    mobileMenuPopup.style.display = 'block';
                }
            });

            document.addEventListener('click', function(e) {
                if (!mobileMenuPopup.contains(e.target) && e.target !== hamburguer) {
                    mobileMenuPopup.style.display = 'none';
                }
            });

            function checkResponsive() {
                const isMobile = window.innerWidth <= 1024;

                if (isMobile) {
                    hamburguer.style.display = 'block';
                    nav.style.display = 'none';
                    entrarContainer.style.marginLeft = '0';
                    entrarContainer.style.transform = 'none';

                    if (window.innerWidth <= 640) {
                        entrarContainer.style.position = 'absolute';
                        entrarContainer.style.right = '60px';
                        entrarContainer.style.top = '15px';
                        document.querySelectorAll('.moldura-porta, .porta, .boneco').forEach(el => {
                            el.style.display = 'none';
                        });
                        document.querySelectorAll('.botao-entrar, .container-entrar a.botao-entrar').forEach(el => {
                            el.style.padding = '12px 20px';
                            el.style.minWidth = 'auto';
                        });
                    } else {
                        entrarContainer.style.position = 'static';
                    }
                } else {
                    hamburguer.style.display = 'none';
                    nav.style.display = 'flex';
                    mobileMenuPopup.style.display = 'none';
                    document.body.style.overflow = 'auto';

                    entrarContainer.style.position = 'relative';
                    entrarContainer.style.transform = 'translateX(-20px)';
                    document.querySelectorAll('.moldura-porta, .porta, .boneco').forEach(el => {
                        el.style.display = 'block';
                    });
                    document.querySelectorAll('.botao-entrar, .container-entrar a.botao-entrar').forEach(el => {
                        el.style.padding = '12px 30px 12px 70px';
                        el.style.minWidth = '150px';
                    });
                }
            }

            checkResponsive();
            window.addEventListener('resize', checkResponsive);

            let lastScrollTop = 0;
            const headerHeight = topo.offsetHeight;

            window.addEventListener('scroll', function() {
                if (window.innerWidth <= 1024) {
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                    if (scrollTop > lastScrollTop && scrollTop > headerHeight) {
                        topo.style.transform = 'translateY(-100%)';
                        topo.style.transition = 'transform 0.3s ease';
                        mobileMenuPopup.style.display = 'none';
                    } else {
                        topo.style.transform = 'translateY(0)';
                    }

                    lastScrollTop = scrollTop;
                }
            });
        });

        function abrirPopup(simulado, acertos, data, nota, pontuacao, percentil) {
            document.getElementById('popupSimulado').textContent = simulado;
            document.getElementById('popupData').textContent = data;
            document.getElementById('popupNota').textContent = nota;
            document.getElementById('popupAcertos').textContent = acertos;
            document.getElementById('popupPontuacao').textContent = pontuacao;
            document.getElementById('popupPercentil').textContent = percentil;
            document.getElementById('popupOverlay').style.display = 'flex';
        }

        function fecharPopup() {
            document.getElementById('popupOverlay').style.display = 'none';
        }

        document.getElementById('popupOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharPopup();
            }
        });
    </script>
</body>
</html>