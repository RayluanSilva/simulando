<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'professor') {
    header("Location: loginprofessor.php");
    exit();
}

$filtroTurma = isset($_GET['turma']) ? $_GET['turma'] : 'todas';
$filtroSimulado = isset($_GET['simulado']) ? $_GET['simulado'] : 'todas';
$filtroPeriodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'todas';

$sql = "SELECT r.*, a.nome_usuario, a.curso, s.nome_simulado 
        FROM resultados r 
        JOIN alunos a ON r.id_aluno = a.id 
        JOIN simulado s ON r.id_simulado = s.id_simulado 
        WHERE 1=1";

$params = [];

if ($filtroTurma !== 'todas') {
    $sql .= " AND a.curso LIKE ?";
    $params[] = '%' . $filtroTurma . '%';
}

if ($filtroSimulado !== 'todas') {
    $sql .= " AND s.nome_simulado = ?";
    $params[] = $filtroSimulado;
}

if ($filtroPeriodo !== 'todas') {
    $sql .= " AND YEAR(r.data_realizacao) = ?";
    $params[] = $filtroPeriodo;
}

$sql .= " ORDER BY r.data_realizacao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$todosResultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sqlMelhores = "SELECT r.*, a.nome_usuario, a.curso, s.nome_simulado 
               FROM resultados r 
               JOIN alunos a ON r.id_aluno = a.id 
               JOIN simulado s ON r.id_simulado = s.id_simulado 
               WHERE 1=1";

$paramsMelhores = [];

if ($filtroTurma !== 'todas') {
    $sqlMelhores .= " AND a.curso LIKE ?";
    $paramsMelhores[] = '%' . $filtroTurma . '%';
}

if ($filtroSimulado !== 'todas') {
    $sqlMelhores .= " AND s.nome_simulado = ?";
    $paramsMelhores[] = $filtroSimulado;
}

if ($filtroPeriodo !== 'todas') {
    $sqlMelhores .= " AND YEAR(r.data_realizacao) = ?";
    $paramsMelhores[] = $filtroPeriodo;
}

$sqlMelhores .= " ORDER BY r.nota DESC LIMIT 3";

$stmtMelhores = $pdo->prepare($sqlMelhores);
$stmtMelhores->execute($paramsMelhores);
$melhoresResultados = $stmtMelhores->fetchAll(PDO::FETCH_ASSOC);

$stmtTurmas = $pdo->query("SELECT DISTINCT curso FROM alunos");
$turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

$stmtSimulados = $pdo->query("SELECT DISTINCT nome_simulado FROM simulado");
$simulados = $stmtSimulados->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['exportar']) && $_GET['exportar'] == 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="relatorio_simulados_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo '<table border="1">';
    echo '<tr style="background-color: #4CAF50; color: white; font-weight: bold;">';
    echo '<th>Nome do Aluno</th>';
    echo '<th>Turma</th>';
    echo '<th>Simulado</th>';
    echo '<th>Nota</th>';
    echo '<th>Acertos</th>';
    echo '<th>Erros</th>';
    echo '<th>Desempenho</th>';
    echo '<th>Data</th>';
    echo '</tr>';

    foreach ($todosResultados as $resultado) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($resultado['nome_usuario']) . '</td>';
        echo '<td>' . htmlspecialchars($resultado['curso']) . '</td>';
        echo '<td>' . htmlspecialchars($resultado['nome_simulado']) . '</td>';
        echo '<td>' . number_format($resultado['nota'], 1) . '</td>';
        echo '<td>' . $resultado['acertos'] . '</td>';
        echo '<td>' . $resultado['erros'] . '</td>';
        echo '<td>' . round(($resultado['nota'] / 10) * 100) . '%</td>';
        echo '<td>' . $resultado['data_realizacao'] . '</td>';
        echo '</tr>';
    }

    echo '</table>';
    exit();
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulando - Resultados Professor</title>
    <link rel="icon" type="image/x-icon" href="imagem/icon.png" id="icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="resultados.css">
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

            .moldura-porta,
            .porta,
            .boneco {
                display: none;
            }

            .botao-entrar,
            .container-entrar a.botao-entrar {
                padding: 12px 20px;
                min-width: auto;
            }
        }

        .questao-container {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .questao-enunciado {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 1.1em;
            color: #333;
            font-family: "Segoe UI", sans-serif;
        }

        .alternativa {
            padding: 8px 10px;
            border-radius: 4px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
            font-family: "Segoe UI", sans-serif;
            color: #555;
        }

        .alternativa-letra {
            font-weight: bold;
            margin-right: 10px;
            width: 20px;
            text-align: center;
            color: #245dc6;
        }

        .alternativa-texto {
            flex-grow: 1;
        }

        .correta {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .selecionada {
            font-weight: bold;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
        }

        .selecionada.errada {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .feedback-icon {
            margin-left: 10px;
            font-size: 1.2em;
        }

        .feedback-icon.acerto {
            color: #28a745;
        }

        .feedback-icon.erro {
            color: #dc3545;
        }

        .popup-content {
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        #simulado-detalhado-container {
            margin-top: 30px;
            margin-left: 15px;
            margin-right: 15px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        #simulado-detalhado-container h3 {
            color: #245dc6;
            font-family: "Segoe UI", sans-serif;
            font-weight: bold;
            border-bottom: 2px solid #589bd2;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .botao-visualizar {
            width: 100%;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            border: none;
            padding: 4px 20px;
            font-size: 16px;
            text-align: center;
            cursor: pointer;
            margin-top: 15px;
            gap: 5px;
            background-color: #539e4a;
            color: aliceblue;
            font-weight: bold;
            box-shadow: 2px 3px 0px 1px rgb(2, 120, 16);
            transition: all 0.4s ease;
        }

        .botao-visualizar:hover {
            transform: scale(1.01);
            background-color: #74be6c;
        }

        .botao-visualizar:focus {
            transform: scale(0.99);
        }

        #simulado-detalhado-container h3 {
            color: #000000;
            font-family: "Segoe UI", sans-serif;
            font-weight: bold;
            border-bottom: 2px solid #589bd2;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="mobile-menu-popup" id="mobileMenuPopup">
        <a href="professor.php"><i class="fas fa-chart-line"></i> DASHBOARD</a>
        <a href="gerador.php"><i class="fas fa-question-circle"></i> GERADOR</a>
        <a href="#resultados"><i class="fas fa-poll"></i> RESULTADOS</a>
    </div>

    <div id="topo">
        <a href="index.php"><img src="imagem/logo.png" alt="simulando" id="logo"></a>
        <div id="nav">
            <nav>
                <a href="professor.php"><i class="fas fa-chart-line"></i> DASHBOARD</a>
                <a href="gerador.php"><i class="fas fa-question-circle"></i> GERADOR</a>
                <a href="#resultados"><i class="fas fa-poll"></i> RESULTADOS</a>
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
    <div id="containerresultados">
        <h1 id="h1ctresultados"><i class="fas fa-poll"></i> RESULTADOS DOS SIMULADOS</h1>
        <p id="pctresultados">Analise o desempenho das turmas e alunos em seus simulados</p>

        <form id="filtroForm" method="get">
            <div id="containerfiltro">
                <div id="ftturma">
                    <label for="turma"><i class="fas fa-users"></i> TURMAS</label>
                    <select name="turma" id="turma">
                        <option value="todas">TODAS AS TURMAS</option>
                        <?php foreach ($turmas as $turma): ?>
                            <option value="<?php echo htmlspecialchars($turma['curso']); ?>"
                                <?php echo $filtroTurma === $turma['curso'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($turma['curso']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="ftsimulado">
                    <label for="simulado"><i class="fas fa-graduation-cap"></i> SIMULADO</label>
                    <select name="simulado" id="simulado">
                        <option value="todas">TODOS OS SIMULADOS</option>
                        <?php foreach ($simulados as $simulado): ?>
                            <option value="<?php echo htmlspecialchars($simulado['nome_simulado']); ?>"
                                <?php echo $filtroSimulado === $simulado['nome_simulado'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($simulado['nome_simulado']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="ftperiodo">
                    <label for="periodo"><i class="fas fa-calendar-alt"></i> PERÍODO</label>
                    <select name="periodo" id="periodo">
                        <option value="todas">TODOS OS PERÍODOS</option>
                        <option value="2025" <?php echo $filtroPeriodo === '2025' ? 'selected' : ''; ?>>2025</option>
                        <option value="2026" <?php echo $filtroPeriodo === '2026' ? 'selected' : ''; ?>>2026</option>
                    </select>
                </div>
                <button type="submit" id="btfiltro"><i class="fas fa-filter"></i> APLICAR FILTRO</button>
            </div>
        </form>

        <div id="containerconteudo">
            <div id="ctnotasgeral">
                <h1><i class="fa-solid fa-file-lines"></i> NOTAS GERAL</h1>
                <table>
                    <tr>
                        <th id="th1">NOME</th>
                        <th id="th2">NOTA</th>
                    </tr>
                    <?php if (!empty($todosResultados)): ?>
                        <?php foreach ($todosResultados as $resultado): ?>
                            <tr onclick="abrirPopup(
                            '<?php echo htmlspecialchars($resultado['nome_usuario']); ?>',
                            '<?php echo htmlspecialchars($resultado['curso']); ?>',
                            '<?php echo htmlspecialchars($resultado['nome_simulado']); ?>',
                            '<?php echo $resultado['acertos']; ?>',
                            '<?php echo $resultado['erros']; ?>',
                            '<?php echo round(($resultado['nota'] / 10) * 100); ?>%',
                            '<?php echo $resultado['id_aluno']; ?>',
                            '<?php echo $resultado['id_simulado']; ?>'
                        )">
                                <td id="td1"><?php echo htmlspecialchars($resultado['nome_usuario']); ?></td>
                                <td id="td2"><?php echo number_format($resultado['nota'], 1); ?>/10</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center;">Nenhum resultado encontrado</td>
                        </tr>
                    <?php endif; ?>
                </table>
                <div class="popup-overlay" id="popupOverlay">
                    <div class="popup-content">
                        <span class="popup-close" id="popupClose">&times;</span>
                        <div class="popup-header">DESEMPENHO DETALHADO</div>
                        <div class="popup-body" id="popupBody">
                            <div class="popup-row">
                                <span class="popup-label">ALUNO:</span>
                                <span id="popupNome">Nome aqui</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">TURMA:</span>
                                <span id="popupTurma">Turma aqui</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">SIMULADO:</span>
                                <span id="popupSimulado">Bimestre aqui</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">ACERTOS:</span>
                                <span id="popupAcertos">00</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">ERROS:</span>
                                <span id="popupErros">00</span>
                            </div>
                            <div class="popup-header2">RESULTADO FINAL</div>
                            <div class="popup-row">
                                <span class="popup-label">DESEMPENHO:</span>
                                <span id="popupDesempenho">00%</span>
                            </div>

                            <button id="btVisualizarDetalhado" class="botao-visualizar" style="display: none;">
                                <i class="fas fa-eye"></i> VISUALIZAR SIMULADO COMPLETO
                            </button>

                            <div id="simulado-detalhado-container">
                                <h3>VISUALIZAÇÃO DETALHADA DO SIMULADO</h3>
                                <div id="questoes-container">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="ctmelhoresnotas">
                <h1><i class="fa-solid fa-medal"></i> MELHORES NOTAS</h1>
                <div class="podio">
                    <?php if (count($melhoresResultados) >= 2): ?>
                        <div class="posicao segundo">
                            <div class="medalha"><i class="fas fa-medal"></i></div>
                            <div class="lugar">2°</div>
                            <div class="info-aluno">
                                <div class="nome"><?php echo htmlspecialchars($melhoresResultados[1]['nome_usuario']); ?></div>
                                <div class="nota"><?php echo round(($melhoresResultados[1]['nota'] / 10) * 100); ?>%</div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($melhoresResultados)): ?>
                        <div class="posicao primeiro">
                            <div class="medalha"><i class="fas fa-crown"></i></div>
                            <div class="lugar">1°</div>
                            <div class="info-aluno">
                                <div class="nome"><?php echo htmlspecialchars($melhoresResultados[0]['nome_usuario']); ?></div>
                                <div class="nota"><?php echo round(($melhoresResultados[0]['nota'] / 10) * 100); ?>%</div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (count($melhoresResultados) >= 3): ?>
                        <div class="posicao terceiro">
                            <div class="medalha"><i class="fas fa-medal"></i></div>
                            <div class="lugar">3°</div>
                            <div class="info-aluno">
                                <div class="nome"><?php echo htmlspecialchars($melhoresResultados[2]['nome_usuario']); ?></div>
                                <div class="nota"><?php echo round(($melhoresResultados[2]['nota'] / 10) * 100); ?>%</div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($melhoresResultados)): ?>
                        <div style="text-align: center; width: 100%; padding: 20px;">
                            Nenhum resultado encontrado
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <button type="button" id="btExportar" onclick="exportarParaExcel()">
                <i class="fas fa-file-excel"></i> EXPORTAR RESULTADO
            </button>
        </div>
    </div>
    <script>
        let idAlunoAtual = null;
        let idSimuladoAtual = null;

        document.addEventListener('DOMContentLoaded', function() {
            const topo = document.getElementById('topo');
            const nav = document.querySelector('nav');
            const entrarContainer = document.querySelector('.container-entrar');
            const btVisualizarDetalhado = document.getElementById('btVisualizarDetalhado');
            const simuladoDetalhadoContainer = document.getElementById('simulado-detalhado-container');
            const questoesContainer = document.getElementById('questoes-container');

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

            function renderizarQuestoes(questoes) {
                questoesContainer.innerHTML = '';

                questoes.forEach((questao, index) => {
                    const questaoDiv = document.createElement('div');
                    questaoDiv.className = 'questao-container';

                    let html = `<div class="questao-enunciado">Questão ${index + 1}: ${questao.enunciado}</div>`;

                    const alternativas = ['A', 'B', 'C', 'D'];

                    alternativas.forEach(letra => {
                        const alternativaTexto = questao.alternativas[letra];
                        if (!alternativaTexto) return;

                        let classe = 'alternativa';
                        let iconHtml = '';

                        const isCorreta = letra === questao.resposta_correta;
                        const isSelecionada = letra === questao.resposta_aluno;

                        if (isCorreta) {
                            classe += ' correta';
                            iconHtml = `<i class="fas fa-check feedback-icon acerto"></i>`;
                        }

                        if (isSelecionada) {
                            classe += ' selecionada';
                            if (!isCorreta) {
                                classe += ' errada';
                                iconHtml = `<i class="fas fa-times feedback-icon erro"></i>`;
                            }
                        }

                        html += `
                            <div class="${classe}">
                                <span class="alternativa-letra">${letra})</span>
                                <span class="alternativa-texto">${alternativaTexto}</span>
                                ${iconHtml}
                            </div>
                        `;
                    });

                    questaoDiv.innerHTML = html;
                    questoesContainer.appendChild(questaoDiv);
                });
            }

            function carregarSimuladoCompleto(idSimulado) {
                questoesContainer.innerHTML = '<p style="text-align: center; color: #245dc6;">Carregando detalhes do simulado...</p>';

                fetch(`get_simulado_detalhado.php?id_simulado=${idSimulado}&id_aluno=${idAlunoAtual}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelector('#simulado-detalhado-container h3').textContent = `VISUALIZAÇÃO DETALHADA DO SIMULADO: ${data.nome_simulado}`;
                            renderizarQuestoes(data.questoes);
                        } else {
                            questoesContainer.innerHTML = `<p style="text-align: center; color: #dc3545;">Erro ao carregar simulado: ${data.error || 'Ocorreu um erro desconhecido.'}</p>`;
                        }
                    })
                    .catch(error => {
                        console.error('Erro na requisição:', error);
                        questoesContainer.innerHTML = '<p style="text-align: center; color: #dc3545;">Erro de Conexão. Verifique o arquivo get_simulado_detalhado.php.</p>';
                    });
            }

            btVisualizarDetalhado.addEventListener('click', function() {
                if (idSimuladoAtual && idAlunoAtual) {
                    simuladoDetalhadoContainer.style.display = 'block';
                    btVisualizarDetalhado.style.display = 'none';
                    carregarSimuladoCompleto(idSimuladoAtual);
                } else {
                    alert('Erro: ID do aluno ou simulado não encontrado.');
                }
            });

            const popupClose = document.getElementById('popupClose');
            const popupOverlay = document.getElementById('popupOverlay');

            popupClose.addEventListener('click', function() {
                popupOverlay.style.display = 'none';
                simuladoDetalhadoContainer.style.display = 'none';
                btVisualizarDetalhado.style.display = 'block';
            });

            popupOverlay.addEventListener('click', function(e) {
                if (e.target === popupOverlay) {
                    popupOverlay.style.display = 'none';
                    simuladoDetalhadoContainer.style.display = 'none';
                    btVisualizarDetalhado.style.display = 'block';
                }
            });

            const containerConteudo = document.getElementById('containerconteudo');
            const btnFiltro = document.getElementById('btfiltro');

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.toString() !== '') {
                containerConteudo.style.display = 'flex';
                setTimeout(function() {
                    containerConteudo.style.opacity = '1';
                }, 10);
            }

            btnFiltro.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('filtroForm').submit();
            });

            function adjustContainerHeight() {
                const windowHeight = window.innerHeight;
                const topoHeight = document.getElementById('topo').offsetHeight;
                const topo2Height = document.getElementById('topo2').offsetHeight;
                const container = document.getElementById('containerresultados');

                if (window.innerWidth < 768) {
                    container.style.minHeight = (windowHeight - topoHeight - topo2Height - 30) + 'px';
                } else {
                    container.style.minHeight = 'auto';
                }
            }

            adjustContainerHeight();
            window.addEventListener('resize', adjustContainerHeight);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const popup = document.getElementById('popupOverlay');
                    if (popup.style.display === 'flex') {
                        popup.style.display = 'none';
                        simuladoDetalhadoContainer.style.display = 'none';
                        btVisualizarDetalhado.style.display = 'block';
                    }
                }
            });
        });

        function abrirPopup(nome, turma, simulado, acertos, erros, desempenho, idAluno, idSimulado) {
            idAlunoAtual = idAluno;
            idSimuladoAtual = idSimulado;

            document.getElementById('popupNome').textContent = nome;
            document.getElementById('popupTurma').textContent = turma;
            document.getElementById('popupSimulado').textContent = simulado;
            document.getElementById('popupAcertos').textContent = acertos;
            document.getElementById('popupErros').textContent = erros;
            document.getElementById('popupDesempenho').textContent = desempenho;

            document.getElementById('btVisualizarDetalhado').style.display = 'block';
            document.getElementById('simulado-detalhado-container').style.display = 'none';
            document.getElementById('popupOverlay').style.display = 'flex';
        }

        function exportarParaExcel() {
            const turma = document.getElementById('turma').value;
            const simulado = document.getElementById('simulado').value;
            const periodo = document.getElementById('periodo').value;

            window.location.href = window.location.pathname +
                '?exportar=excel&turma=' + turma +
                '&simulado=' + simulado +
                '&periodo=' + periodo;
        }
    </script>
</body>

</html>