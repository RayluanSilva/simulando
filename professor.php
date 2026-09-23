<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'professor') {
    header("Location: loginprofessor.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM professores WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$professor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    session_destroy();
    header("Location: loginprofessor.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_questao'])) {
    $questao_id = $_POST['questao_id'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT id_professor FROM questoes WHERE id_questao = ?");
        $stmt->execute([$questao_id]);
        $questao = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($questao && $questao['id_professor'] == $_SESSION['usuario_id']) {
            $stmt = $pdo->prepare("DELETE FROM questoes_simulado WHERE id_questao = ?");
            $stmt->execute([$questao_id]);
            
            $stmt = $pdo->prepare("DELETE FROM questoes WHERE id_questao = ?");
            $stmt->execute([$questao_id]);
            
            $pdo->commit();
            header("Location: professor.php");
            exit();
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Erro ao excluir questão: " . $e->getMessage());
    }
}

$stmt = $pdo->prepare("SELECT * FROM questoes WHERE id_professor = ? ORDER BY data_criacao DESC");
$stmt->execute([$_SESSION['usuario_id']]);
$questoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtTurmas = $pdo->query("SELECT DISTINCT curso FROM alunos");
$turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

$desempenhoTurmas = [];
foreach ($turmas as $turma) {
    $stmtDesempenho = $pdo->prepare("SELECT AVG(r.nota) as media, MAX(s.nome_simulado) as ultimo_simulado 
                                    FROM resultados r 
                                    JOIN alunos a ON r.id_aluno = a.id 
                                    JOIN simulado s ON r.id_simulado = s.id_simulado 
                                    WHERE a.curso LIKE ? 
                                    GROUP BY a.curso 
                                    ORDER BY r.data_realizacao DESC 
                                    LIMIT 1");
    $stmtDesempenho->execute(['%' . $turma['curso'] . '%']);
    $desempenho = $stmtDesempenho->fetch(PDO::FETCH_ASSOC);

    if ($desempenho) {
        $desempenhoTurmas[] = [
            'turma' => $turma['curso'],
            'media' => $desempenho['media'],
            'ultimo_simulado' => $desempenho['ultimo_simulado']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulando - Professor Dashboard</title>
    <link rel="icon" type="image/x-icon" href="imagem/icon.png" id="icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="professor.css">
    <style>
        .questaoitem {
            position: relative;
            cursor: pointer;
        }

        .excluir-questao {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #aaa;
            background: none;
            border: none;
            font-size: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
            padding: 5px;
            line-height: 1;
            z-index: 1;
        }

        .excluir-questao:hover {
            color: #666;
        }

        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .popup-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }

        .close-popup {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #333;
        }

        #desempenhoconteudo {
            max-height: 270px;
            overflow-y: auto;
            padding-right: 10px;
        }

        #desempenhoconteudo::-webkit-scrollbar {
            width: 6px;
        }

        #desempenhoconteudo::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        #desempenhoconteudo::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        #desempenhoconteudo::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

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

        .popup-imagem {
            margin: 15px 0;
            text-align: center;
        }

        .popup-imagem img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
                padding: 12px 10px;
                min-width: auto;
            }
        }
    </style>
</head>

<body>
    <div id="questaoPopup" class="popup">
        <div class="popup-content">
            <span class="close-popup">&times;</span>
            <div class="popup-header">
                <span class="popup-titulo">Título da Questão</span>
                <span class="popup-data">Criada em: dd/mm/aa</span>
                <span class="popup-disciplina">Disciplina</span>
            </div>
            <div class="popup-body">
                <div class="popup-enunciado">
                    <h3>Enunciado:</h3>
                    <p></p>
                    <div class="popup-imagem" id="popup-imagem-container" style="display: none;">
                        <img id="popup-imagem" src="" alt="Imagem da questão">
                    </div>
                </div>
                <div class="popup-alternativas">
                    <h3>Alternativas:</h3>
                    <div class="alternativas-container"></div>
                </div>
                <div class="popup-resposta">
                    <span>Resposta correta: </span>
                </div>
            </div>
        </div>
    </div>

    <div class="mobile-menu-popup" id="mobileMenuPopup">
        <a href="#dashboard"><i class="fas fa-chart-line"></i> DASHBOARD</a>
        <a href="gerador.php"><i class="fas fa-question-circle"></i> GERADOR</a>
        <a href="resultados.php"><i class="fas fa-poll"></i> RESULTADOS</a>
    </div>

    <div id="topo">
        <a href="index.php"><img src="imagem/logo.png" alt="simulando" id="logo"></a>
        <div id="nav">
            <nav>
                <a href="#dashboard"><i class="fas fa-chart-line"></i> DASHBOARD</a>
                <a href="gerador.php"><i class="fas fa-question-circle"></i> GERADOR</a>
                <a href="resultados.php"><i class="fas fa-poll"></i> RESULTADOS</a>
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
        <h1 id="h1containerprincipal"><i class="fa-solid fa-user-tie"></i> PAINEL DO PROFESSOR</h1>
        <p id="pcontainerprincipal">Bem-vindo, <?php echo htmlspecialchars($professor['nome_usuario']); ?>! Aqui você pode acessar o dashboard, gerar provas e ver os resultados dos alunos.</p>
        <div id="visaogeral">
            <div id="barra"></div>
            <h1><i class="fa-solid fa-address-book"></i> VISÃO GERAL</h1>
            <div id="visaogeralconteudo">
                <div id="divconteudo">
                    <span id="titulo">NOME:</span>
                    <span id="conteudo"><?php echo htmlspecialchars($professor['nome_usuario']); ?></span>
                </div>
                <div id="divconteudo">
                    <span id="titulo">CÓDIGO:</span>
                    <span id="conteudo"><?php echo htmlspecialchars($professor['codigo_acesso']); ?></span>
                </div>
                <div id="divconteudo">
                    <span id="titulo">MATÉRIA:</span>
                    <span id="conteudo"><?php echo htmlspecialchars($professor['materia'] ?? 'Não definida'); ?></span>
                </div>
            </div>
        </div>
        <div id="questoes">
            <div id="barra"></div>
            <h1><i class="fa-solid fa-pen-to-square"></i> QUESTÕES</h1>
            <div class="questoesconteudo">
                <?php foreach ($questoes as $questao): ?>
                    <div class="questaoitem"
                        data-id="<?php echo $questao['id_questao']; ?>"
                        data-titulo="<?php echo htmlspecialchars($questao['topico']); ?>"
                        data-disciplina="<?php echo htmlspecialchars($questao['disciplina']); ?>"
                        data-data="<?php echo date('d/m/Y', strtotime($questao['data_criacao'])); ?>"
                        data-enunciado="<?php echo htmlspecialchars($questao['enunciado']); ?>"
                        data-alternativas='["A) <?php echo htmlspecialchars($questao['alternativa_a']); ?>", "B) <?php echo htmlspecialchars($questao['alternativa_b']); ?>", "C) <?php echo htmlspecialchars($questao['alternativa_c']); ?>", "D) <?php echo htmlspecialchars($questao['alternativa_d']); ?>"]'
                        data-resposta="<?php echo $questao['resposta_correta']; ?>"
                        data-imagem="<?php echo !empty($questao['imagem']) ? 'data:image/jpeg;base64,' . $questao['imagem'] : ''; ?>">
                        <button class="excluir-questao" onclick="confirmarExclusao(event, <?php echo $questao['id_questao']; ?>)">&times;</button>
                        <span class="questaotitulo"><?php echo htmlspecialchars($questao['topico']); ?></span>
                        <span class="questaodata">Criada em: <?php echo date('d/m/Y', strtotime($questao['data_criacao'])); ?></span>
                        <span class="questaodisciplina"><?php echo htmlspecialchars($questao['disciplina']); ?></span>
                        <span class="questaodata" style="color: #74be6c; font-weight: bold;">Curso: <?php echo htmlspecialchars($questao['curso']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div id="desempenho">
            <div id="barra"></div>
            <h1><i class="fa-solid fa-square-poll-horizontal"></i> DESEMPENHO</h1>
            <div id="desempenhoconteudo">
                <?php if (!empty($desempenhoTurmas)): ?>
                    <?php foreach ($desempenhoTurmas as $turma): ?>
                        <div id="desempenhoitem">
                            <span id="desempenhoturma"><?php echo htmlspecialchars($turma['turma']); ?></span>
                            <span id="desempenhonota">Média: <?php echo number_format($turma['media'], 1); ?></span>
                            <div id="barradesempenho" style="--width: <?php echo ($turma['media'] / 10) * 100; ?>%"></div>
                            <span id="desempenhodata">Simulado: <?php echo htmlspecialchars($turma['ultimo_simulado']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div id="desempenhoitem">
                        <span id="desempenhoturma">Nenhum dado de desempenho disponível</span>
                        <span id="desempenhonota">Média: 0.0</span>
                        <div id="barradesempenho" style="--width: 0%"></div>
                        <span id="desempenhodata">Simulado: Nenhum</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <form id="form-excluir" method="post" style="display: none;">
        <input type="hidden" name="questao_id" id="questao-id-excluir">
        <input type="hidden" name="excluir_questao" value="1">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const questoes = document.querySelectorAll('.questaoitem');
            const popup = document.getElementById('questaoPopup');
            const popupTitulo = document.querySelector('.popup-titulo');
            const popupDisciplina = document.querySelector('.popup-disciplina');
            const popupData = document.querySelector('.popup-data');
            const popupEnunciado = document.querySelector('.popup-enunciado p');
            const popupAlternativas = document.querySelector('.alternativas-container');
            const popupResposta = document.querySelector('.popup-resposta span');
            const popupImagemContainer = document.getElementById('popup-imagem-container');
            const popupImagem = document.getElementById('popup-imagem');
            const closeBtn = document.querySelector('.close-popup');

            questoes.forEach(questao => {
                questao.addEventListener('click', function(e) {
                    if (e.target.classList.contains('excluir-questao')) {
                        return;
                    }

                    popupTitulo.textContent = this.dataset.titulo;
                    popupDisciplina.textContent = this.dataset.disciplina;
                    popupData.textContent = 'Criada em: ' + this.dataset.data;
                    popupEnunciado.textContent = this.dataset.enunciado;

                    if (this.dataset.imagem) {
                        popupImagem.src = this.dataset.imagem;
                        popupImagemContainer.style.display = 'block';
                    } else {
                        popupImagemContainer.style.display = 'none';
                    }

                    const alternativas = JSON.parse(this.dataset.alternativas);
                    popupAlternativas.innerHTML = '';
                    alternativas.forEach(alt => {
                        const div = document.createElement('div');
                        div.className = 'alternativa';
                        div.textContent = alt;
                        popupAlternativas.appendChild(div);
                    });

                    popupResposta.textContent = 'Resposta correta: ' + this.dataset.resposta;
                    popup.style.display = 'flex';
                });
            });

            closeBtn.addEventListener('click', function() {
                popup.style.display = 'none';
            });

            window.addEventListener('click', function(event) {
                if (event.target === popup) {
                    popup.style.display = 'none';
                }
            });

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

        function confirmarExclusao(event, questaoId) {
            event.stopPropagation();

            if (confirm('Tem certeza que deseja excluir esta questão? Esta ação não pode ser desfeita.')) {
                document.getElementById('questao-id-excluir').value = questaoId;
                document.getElementById('form-excluir').submit();
            }
        }
    </script>

</body>

</html>