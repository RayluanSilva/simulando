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
                      ORDER BY r.data_realizacao DESC");
$stmt->execute([$_SESSION['usuario_id']]);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ultimoResultado = !empty($resultados) ? $resultados[0] : null;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulando - Resultados Aluno</title>
    <link rel="icon" type="image/x-icon" href="imagem/icon.png" id="icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="resultadosaluno.css">
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
        }

        .questao-enunciado {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .alternativa {
            padding: 5px 0;
            border-radius: 4px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }

        .alternativa-letra {
            font-weight: bold;
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .alternativa-texto {
            flex-grow: 1;
        }

        .correta {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .errada {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        .selecionada {
            font-weight: bold;
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

        #simulado-completo-container {
            width: 80%;
            height: auto;
            border-radius: 20px;
            background: linear-gradient(135deg, #a2cdf0 0%, #b7defa 100%);
            display: flex;
            margin-left: auto;
            margin-right: auto;
            margin-top: 20px;
            margin-bottom: 20px;
            flex-direction: column;
        }

        #ctdetalhado {
            width: 100%;
            height: auto;
            display: flex;
            margin-left: auto;
            margin-right: auto;
            flex-direction: column;
            border-radius: 20px;
            background: linear-gradient(135deg, #589bd2 0%, #65a9dc 100%);
            margin-bottom: 20px;
            margin-top: 20px;
            padding: 20px;
        }

        #simulado-completo-container h2 {
            color: aliceblue;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .questao-container {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #cceeff;
        }

        #simulado-detalhado-container {
            margin-top: 30px;
            margin-left: 15px;
            margin-right: 15px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
        }
    </style>
</head>

<body>
    <div class="mobile-menu-popup" id="mobileMenuPopup">
        <a href="aluno.php"><i class="fa-solid fa-house"></i> DASHBOARD</a>
        <a href="simulado.php"><i class="fa-solid fa-pen"></i> SIMULADO</a>
        <a href="resultadosaluno.php"><i class="fas fa-poll"></i> RESULTADOS</a>
    </div>

    <div id="topo">
        <a href="index.php"><img src="imagem/logo.png" alt="simulando" id="logo"></a>
        <div id="nav">
            <nav>
                <a href="aluno.php"><i class="fa-solid fa-house"></i> DASHBOARD</a>
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
    <div id="containerresultados">
        <h1 id="h1ctresultados"><i class="fas fa-poll"></i> SEUS RESULTADOS</h1>
        <p id="pctresultados">Analise seu desempenho nos simulados realizados.</p>
        <div id="containerfiltro">
            <div id="ftsimulado">
                <label for="simulado"><i class="fas fa-graduation-cap"></i> SIMULADO</label>
                <select name="slcsimulado" id="simulado">
                    <option value="todas">SELECIONE UM SIMULADO</option>
                    <?php foreach ($resultados as $resultado): ?>
                        <option value="<?php echo $resultado['id_simulado']; ?>" data-acertos="<?php echo $resultado['acertos']; ?>" data-erros="<?php echo $resultado['erros']; ?>" data-nota="<?php echo $resultado['nota']; ?>">
                            <?php echo htmlspecialchars($resultado['nome_simulado']) . " (" . date('d/m/Y', strtotime($resultado['data_realizacao'])) . ")"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="ftperiodo">
                <label for="periodo"><i class="fas fa-calendar-alt"></i> PERÍODO</label>
                <select name="slcperiodo" id="periodo">
                    <option value="todas">ESCOLHA O PERÍODO</option>
                    <option value="2025">2025</option>
                    <option value="2026">2026</option>
                </select>
            </div>
            <button id="btfiltro"><i class="fas fa-filter"></i> APLICAR FILTRO</button>
        </div>
        <div id="containerconteudo">
            <div id="ctdesempenho">
                <h1><i class="fa-solid fa-file-lines"></i> DESEMPENHO DETALHADO</h1>
                <table id="tabela-desempenho">
                    <tr>
                        <th id="th1">PARAMETROS</th>
                        <th id="th2">DESEMPENHO</th>
                    </tr>
                    <tr>
                        <td id="td1">ACERTOS</td>
                        <td id="td2" data-campo="acertos"><?php echo $ultimoResultado ? $ultimoResultado['acertos'] : '0'; ?></td>
                    </tr>
                    <tr>
                        <td id="td1">ERROS</td>
                        <td id="td2" data-campo="erros"><?php echo $ultimoResultado ? $ultimoResultado['erros'] : '0'; ?></td>
                    </tr>
                    <tr>
                        <td id="td1">PORCENTAGEM FINAL</td>
                        <td id="td2" data-campo="porcentagem"><?php echo $ultimoResultado ? round(($ultimoResultado['nota'] / 10) * 100) . '%' : '0%'; ?></td>
                    </tr>
                </table>

                <button id="btvisualizar" style="margin-top: 20px; padding: 10px 20px; background-color: #74be6c; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; display: none;">
                    <i class="fas fa-eye"></i> VISUALIZAR SIMULADO COMPLETO
                </button>
            </div>
        </div>
        <div id="simulado-completo-container">
            <div id="ctdetalhado">
                <h2 id="simulado-titulo"></h2>
                <div id="questoes-container">
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const topo = document.getElementById('topo');
            const nav = document.querySelector('nav');
            const entrarContainer = document.querySelector('.container-entrar');
            const simuladoSelect = document.getElementById('simulado');
            const btVisualizar = document.getElementById('btvisualizar');
            const simuladoCompletoContainer = document.getElementById('simulado-completo-container');
            const questoesContainer = document.getElementById('questoes-container');
            const simuladoTitulo = document.getElementById('simulado-titulo');
            const tabelaDesempenho = document.getElementById('tabela-desempenho');

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

            function atualizarDesempenho(acertos, erros, nota) {
                const total = acertos + erros;
                const porcentagem = total > 0 ? Math.round((nota / 10) * 100) : 0;

                tabelaDesempenho.querySelector('[data-campo="acertos"]').textContent = acertos;
                tabelaDesempenho.querySelector('[data-campo="erros"]').textContent = erros;
                tabelaDesempenho.querySelector('[data-campo="porcentagem"]').textContent = porcentagem + '%';
            }

            simuladoSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const idSimulado = this.value;

                simuladoCompletoContainer.style.display = 'none';
                btVisualizar.style.display = 'none';

                if (idSimulado !== 'todas') {
                    const acertos = parseInt(selectedOption.getAttribute('data-acertos'));
                    const erros = parseInt(selectedOption.getAttribute('data-erros'));
                    const nota = parseFloat(selectedOption.getAttribute('data-nota'));

                    atualizarDesempenho(acertos, erros, nota);

                    btVisualizar.style.display = 'block';
                    document.getElementById('containerconteudo').classList.add('show-with-animation');
                } else {
                    const ultimoResultado = simuladoSelect.querySelector('option:nth-child(2)');
                    if (ultimoResultado) {
                        atualizarDesempenho(
                            parseInt(ultimoResultado.getAttribute('data-acertos')),
                            parseInt(ultimoResultado.getAttribute('data-erros')),
                            parseFloat(ultimoResultado.getAttribute('data-nota'))
                        );
                    } else {
                        atualizarDesempenho(0, 0, 0);
                    }
                    document.getElementById('containerconteudo').classList.remove('show-with-animation');
                }
            });

            btVisualizar.addEventListener('click', function() {
                const idSimulado = simuladoSelect.value;
                if (idSimulado === 'todas') {
                    alert('Por favor, selecione um simulado para visualizar.');
                    return;
                }

                carregarSimuladoCompleto(idSimulado);
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
                simuladoTitulo.textContent = 'Carregando detalhes do simulado...';
                questoesContainer.innerHTML = '<p>Aguarde...</p>';
                simuladoCompletoContainer.style.display = 'block';

                fetch(`get_simulado_detalhado.php?id_simulado=${idSimulado}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            simuladoTitulo.textContent = `Simulado: ${data.nome_simulado}`;
                            renderizarQuestoes(data.questoes);
                        } else {
                            simuladoTitulo.textContent = 'Erro ao carregar simulado';
                            questoesContainer.innerHTML = `<p>${data.error || 'Ocorreu um erro desconhecido.'}</p>`;
                        }
                    })
                    .catch(error => {
                        console.error('Erro na requisição:', error);
                        simuladoTitulo.textContent = 'Erro de Conexão';
                        questoesContainer.innerHTML = '<p>Não foi possível carregar os detalhes do simulado. Verifique a conexão ou o arquivo get_simulado_detalhado.php.</p>';
                    });
            }
            document.getElementById('btfiltro').addEventListener('click', function() {
                const simulado = document.getElementById('simulado').value;
                const periodo = document.getElementById('periodo').value;

                if (simulado !== 'todas' && periodo !== 'todas') {
                    document.getElementById('containerconteudo').classList.add('show-with-animation');
                } else if (simulado !== 'todas') {
                    document.getElementById('containerconteudo').classList.add('show-with-animation');
                    btVisualizar.style.display = 'block';
                } else {
                    alert('Por favor, selecione um simulado e um período antes de aplicar o filtro.');
                }
            });

            const primeiroSimulado = simuladoSelect.querySelector('option:nth-child(2)');
            if (primeiroSimulado) {
                atualizarDesempenho(
                    parseInt(primeiroSimulado.getAttribute('data-acertos')),
                    parseInt(primeiroSimulado.getAttribute('data-erros')),
                    parseFloat(primeiroSimulado.getAttribute('data-nota'))
                );
                btVisualizar.style.display = 'block';
            }
        });
    </script>
</body>

</html>