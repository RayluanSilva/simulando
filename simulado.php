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

$curso_aluno = $aluno['curso'];

// Buscar questões apenas do curso do aluno
$stmt = $pdo->prepare("SELECT * FROM questoes WHERE curso = ? ORDER BY RAND() LIMIT 10");
$stmt->execute([$curso_aluno]);
$questoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar se há questões disponíveis
$questoes_disponiveis = count($questoes) > 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulando - Página do Simulado</title>
    <link rel="icon" type="image/x-icon" href="imagem/icon.png" id="icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="simulado.css">
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

        .questao-imagem {
            margin: 15px 0;
            text-align: center;
        }

        .questao-imagem img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .alerta-questoes {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin: 40px auto;
            max-width: 600px;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
            font-family: "Segoe UI", sans-serif;
        }

        .alerta-questoes h2 {
            font-size: 24px;
            margin-bottom: 15px;
        }

        .alerta-questoes p {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .alerta-questoes a {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }

        .alerta-questoes a:hover {
            background-color: rgba(255, 255, 255, 0.3);
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

    <?php if (!$questoes_disponiveis): ?>
        <div id="containerprincipal" style="display: flex; align-items: center; justify-content: center; min-height: 60vh;">
            <div class="alerta-questoes">
                <h2><i class="fas fa-exclamation-triangle"></i> Nenhuma Questão Disponível</h2>
                <p>Desculpe, não há questões disponíveis para seu curso (<strong><?php echo htmlspecialchars($curso_aluno); ?></strong>) no momento.</p>
                <p>Por favor, aguarde enquanto os professores criam questões para sua turma.</p>
                <a href="aluno.php"><i class="fas fa-arrow-left"></i> Voltar ao Dashboard</a>
            </div>
        </div>
    <?php else: ?>
        <div id="containerpresimulado">
            <div id="containerpresimulado2">
                <h1>PREENCHA OS CAMPOS!</h1>
                <p>Para iniciar o simulado, preencha os campos abaixo com suas informações.</p>
                <input type="text" name="nome" id="nome" placeholder="Digite seu nome completo" value="<?php echo htmlspecialchars($aluno['nome_usuario']); ?>">
                <select name="slcturma" id="turma">
                    <option value="">Selecione sua turma</option>
                    <option value="<?php echo htmlspecialchars($curso_aluno); ?>" selected><?php echo htmlspecialchars($curso_aluno); ?></option>
                </select>
                <button type="button" id="iniciarSimulado"><i class="fa-solid fa-arrow-right"></i> INICIAR SIMULADO</button>
            </div>
        </div>
        <div id="containersimulado" style="display: none;">
            <h1><i class="fas fa-pencil-alt"></i> SIMULADO BIMESTRAL</h1>
            <p>Responda as questões com atenção. Bom simulado!</p>
            <div class="aluno-info">
                <div class="aluno-info-item">
                    <i class="fas fa-user aluno-info-icon"></i>
                    <div>
                        <span class="aluno-info-label">ALUNO:</span>
                        <span class="aluno-info-value"><?php echo htmlspecialchars($aluno['nome_usuario']); ?></span>
                    </div>
                </div>
                <div class="aluno-info-item">
                    <i class="fas fa-graduation-cap aluno-info-icon"></i>
                    <div>
                        <span class="aluno-info-label">CURSO:</span>
                        <span class="aluno-info-value"><?php echo htmlspecialchars($aluno['curso']); ?></span>
                    </div>
                </div>
            </div>

            <div class="simulado-info">
                <div class="simulado-info">
                    <div class="timer">
                        <i class="fas fa-clock"></i>
                        <span id="tempo-restante">60:00</span>
                        <div class="progress-container">
                            <div class="progress-bar">
                                <div class="progress-fill" id="progresso-questao"></div>
                            </div>
                        </div>
                    </div>
                    <div class="progress-text">Questão <span id="questao-atual">1</span> de <span id="total-questoes"><?php echo count($questoes); ?></span></div>
                </div>
                <form id="formSimulado">
                    <div id="containerquestoes">
                        <?php foreach ($questoes as $index => $questao): ?>
                            <div class="questao-container" data-questao-id="<?php echo $questao['id_questao']; ?>" style="<?php echo $index > 0 ? 'display:none;' : ''; ?>">
                                <div id="barra"></div>
                                <div id="questaoinfo">
                                    <div id="questaonumero">QUESTÃO <?php echo $index + 1; ?></div>
                                    <div id="questaodisciplina"><?php echo htmlspecialchars($questao['disciplina']); ?></div>
                                </div>
                                <div id="questaoenunciado">
                                    <?php echo htmlspecialchars($questao['enunciado']); ?>
                                    <?php if (!empty($questao['imagem'])): ?>
                                        <div class="questao-imagem">
                                            <img src="data:image/jpeg;base64,<?php echo $questao['imagem']; ?>" alt="Imagem da questão">
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div id="questaoalternativas">
                                    <div class="alternativa">
                                        <input type="radio" name="resposta_<?php echo $questao['id_questao']; ?>" id="a_<?php echo $questao['id_questao']; ?>" value="A">
                                        <label for="a_<?php echo $questao['id_questao']; ?>"><?php echo htmlspecialchars($questao['alternativa_a']); ?></label>
                                    </div>
                                    <div class="alternativa">
                                        <input type="radio" name="resposta_<?php echo $questao['id_questao']; ?>" id="b_<?php echo $questao['id_questao']; ?>" value="B">
                                        <label for="b_<?php echo $questao['id_questao']; ?>"><?php echo htmlspecialchars($questao['alternativa_b']); ?></label>
                                    </div>
                                    <div class="alternativa">
                                        <input type="radio" name="resposta_<?php echo $questao['id_questao']; ?>" id="c_<?php echo $questao['id_questao']; ?>" value="C">
                                        <label for="c_<?php echo $questao['id_questao']; ?>"><?php echo htmlspecialchars($questao['alternativa_c']); ?></label>
                                    </div>
                                    <div class="alternativa">
                                        <input type="radio" name="resposta_<?php echo $questao['id_questao']; ?>" id="d_<?php echo $questao['id_questao']; ?>" value="D">
                                        <label for="d_<?php echo $questao['id_questao']; ?>"><?php echo htmlspecialchars($questao['alternativa_d']); ?></label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="id_aluno" value="<?php echo $_SESSION['usuario_id']; ?>">
                    <div id="containerbotoes">
                        <button type="button" id="botao-anterior" class="botao-simulado"><i class="fas fa-arrow-left"></i> ANTERIOR</button>
                        <button type="button" id="botao-proximo" class="botao-simulado">PRÓXIMO <i class="fas fa-arrow-right"></i></button>
                        <button type="button" id="botao-finalizar" class="botao-simulado" style="display:none;">FINALIZAR SIMULADO <i class="fa-solid fa-flag-checkered"></i></button>
                    </div>
                </form>
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

                const containerPreSimulado = document.getElementById('containerpresimulado');
                const containerSimulado = document.getElementById('containersimulado');
                const iniciarSimuladoBtn = document.getElementById('iniciarSimulado');
                const formSimulado = document.getElementById('formSimulado');
                const totalQuestoesSpan = document.getElementById('total-questoes');
                const questaoAtualSpan = document.getElementById('questao-atual');
                const botaoAnterior = document.getElementById('botao-anterior');
                const botaoProximo = document.getElementById('botao-proximo');
                const botaoFinalizar = document.getElementById('botao-finalizar');
                const questaoContainers = document.querySelectorAll('.questao-container');

                let questaoAtual = 0;
                const tempoTotal = 60 * 60;
                let tempoRestante = tempoTotal;
                let timer;

                function atualizarBarraProgresso() {
                    const progresso = ((questaoAtual + 1) / questaoContainers.length) * 100;
                    document.getElementById('progresso-questao').style.width = `${progresso}%`;
                }

                iniciarSimuladoBtn.addEventListener('click', function() {
                    const turma = document.getElementById('turma').value;
                    if (turma === '' || turma === 'todas') {
                        alert('Por favor, selecione sua turma');
                        return;
                    }

                    containerPreSimulado.classList.add('fade-out');

                    setTimeout(() => {
                        containerPreSimulado.style.display = 'none';
                        containerSimulado.style.display = 'flex';
                        containerSimulado.classList.add('fade-in');
                        iniciarCronometro();
                        atualizarBarraProgresso();

                        if (questaoContainers.length === 1) {
                            botaoProximo.style.display = 'none';
                            botaoFinalizar.style.display = 'inline-block';
                        }
                    }, 400);
                });

                function iniciarCronometro() {
                    atualizarTempo();
                    timer = setInterval(atualizarTempo, 1000);
                }

                function atualizarTempo() {
                    const minutos = Math.floor(tempoRestante / 60);
                    const segundos = tempoRestante % 60;

                    document.getElementById('tempo-restante').textContent = `${minutos.toString().padStart(2,'0')}:${segundos.toString().padStart(2,'0')}`;

                    if (tempoRestante <= 0) {
                        finalizarSimulado();
                    } else {
                        tempoRestante--;
                    }
                }

                botaoAnterior.addEventListener('click', function() {
                    if (questaoAtual > 0) {
                        questaoContainers[questaoAtual].style.display = 'none';
                        questaoAtual--;
                        questaoContainers[questaoAtual].style.display = 'block';
                        questaoAtualSpan.textContent = questaoAtual + 1;
                        atualizarBarraProgresso();

                        if (questaoAtual === 0) {
                            botaoAnterior.style.display = 'inline-block';
                        }

                        if (questaoAtual < questaoContainers.length - 1) {
                            botaoProximo.style.display = 'inline-block';
                            botaoFinalizar.style.display = 'none';
                        }
                    }
                });

                botaoProximo.addEventListener('click', function() {
                    if (questaoAtual < questaoContainers.length - 1) {
                        questaoContainers[questaoAtual].style.display = 'none';
                        questaoAtual++;
                        questaoContainers[questaoAtual].style.display = 'block';
                        questaoAtualSpan.textContent = questaoAtual + 1;
                        atualizarBarraProgresso();

                        if (questaoAtual > 0) {
                            botaoAnterior.style.display = 'inline-block';
                        }

                        if (questaoAtual === questaoContainers.length - 1) {
                            botaoProximo.style.display = 'none';
                            botaoFinalizar.style.display = 'inline-block';
                        }
                    }
                });

                botaoFinalizar.addEventListener('click', function() {
                    if (confirm('Tem certeza que deseja finalizar o simulado?')) {
                        finalizarSimulado();
                    }
                });

                function finalizarSimulado() {
                    clearInterval(timer);

                    const formData = new FormData(formSimulado);

                    fetch('salvar_resultado.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const resultadoHTML = `
                    <div id="resultado-modal" style="position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.7);z-index:2000;display:flex;justify-content:center;align-items:center;">
                        <div style="background:linear-gradient(135deg,#3e9fed 0%,#2697e7 100%);padding:30px;border-radius:20px;width:80%;max-width:500px;text-align:center;box-shadow:0 5px 15px rgba(0,0,0,0.3);">
                            <h3 style="color:aliceblue;font-family:'Segoe UI',sans-serif;margin-bottom:20px;">
                                <i class="fas fa-check-circle"></i> Simulado Finalizado!
                            </h3>
                            <div class="loader" style="border:5px solid #f3f3f3;border-top:5px solid #74be6c;border-radius:50%;width:50px;height:50px;animation:spin 1s linear infinite;margin:0 auto;"></div>
                        </div>
                    </div>`;

                                document.body.insertAdjacentHTML('beforeend', resultadoHTML);

                                setTimeout(() => {
                                    window.location.href = 'aluno.php';
                                }, 3000);
                            } else {
                                alert('Ocorreu um erro ao salvar seu resultado.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Ocorreu um erro ao finalizar o simulado.');
                        });
                }
            });
        </script>
    <?php endif; ?>
</body>

</html>
