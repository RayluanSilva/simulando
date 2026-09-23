<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: loginprofessor.php");
    exit();
}

$stmt = $pdo->prepare("SELECT materia FROM professores WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$professor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    session_destroy();
    header("Location: loginprofessor.php");
    exit();
}

$disciplina_professor = $professor['materia'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assunto = $_POST['assunto'];
    $curso = $_POST['curso'];
    $enunciado = $_POST['enunciado'];
    $altA = $_POST['altA'];
    $altB = $_POST['altB'];
    $altC = $_POST['altC'];
    $altD = $_POST['altD'];
    $respostaCorreta = $_POST['respostaCorreta'];
    $id_professor = $_SESSION['usuario_id'];

    $imagem = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $imagemTmp = $_FILES['imagem']['tmp_name'];
        $imagemConteudo = file_get_contents($imagemTmp);
        $imagem = base64_encode($imagemConteudo);
    }

    $stmt = $pdo->prepare("INSERT INTO questoes (disciplina, topico, enunciado, alternativa_a, alternativa_b, alternativa_c, alternativa_d, resposta_correta, id_professor, curso, imagem) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$disciplina_professor, $assunto, $enunciado, $altA, $altB, $altC, $altD, $respostaCorreta, $id_professor, $curso, $imagem]);


    header("Location: professor.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulando - Gerador de questões</title>
    <link rel="icon" type="image/x-icon" href="imagem/icon.png" id="icon">
    <link rel="stylesheet" href="gerador.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .disciplina-info {
            background: linear-gradient(135deg, #4787bb 0%, #3a86bc 100%);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin: 20px 5% 30px 5%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border: 2px solid #5c5c5c;
        }

        .disciplina-info h2 {
            color: white;
            font-size: 24px;
            margin: 0;
            font-family: "Segoe UI", sans-serif;
            font-weight: bold;
        }

        .disciplina-info p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            margin: 10px 0 0 0;
            font-family: "Segoe UI", sans-serif;
        }

        .preview-questao-imagem {
            margin-top: 10px;
        }

        .preview-questao-imagem img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="mobile-menu-popup" id="mobileMenuPopup">
        <a href="professor.php"><i class="fas fa-chart-line"></i> DASHBOARD</a>
        <a href="#gerador"><i class="fas fa-question-circle"></i> GERADOR</a>
        <a href="resultados.php"><i class="fas fa-poll"></i> RESULTADOS</a>
    </div>

    <div id="topo">
        <a href="index.php"><img src="imagem/logo.png" alt="simulando" id="logo"></a>
        <div id="nav">
            <nav>
                <a href="professor.php"><i class="fas fa-chart-line"></i> DASHBOARD</a>
                <a href="#gerador"><i class="fas fa-question-circle"></i> GERADOR</a>
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

    <div id="containergerador">
        <form method="POST" action="gerador.php" enctype="multipart/form-data">
            <div id="btvoltar">
                <button type="button" onclick="window.location.href='professor.php'"><i class="fa-solid fa-backward"></i> VOLTAR</button>
            </div>

            <div class="disciplina-info">
                <h2><i class="fas fa-book"></i> <?php echo htmlspecialchars($disciplina_professor); ?></h2>
                <p>Criando questões para sua disciplina</p>
            </div>

            <h1 id="h1ctgerador"><i class="fas fa-magic"></i>GERADOR DE QUESTÕES</h1>
            <p id="pctgerador">Crie suas questões que serão utilizadas no simulado!</p>
            <div id="ctgerador2">
                <div class="input-group">
                    <label for="curso" id="lbcurso"><i class="fa-solid fa-people-group"></i> CURSO:</label>
                    <select name="curso" id="slccurso" required>
                        <option value="">Selecione a turma</option>
                        <option value="1° INFORMÁTICA">1° INFORMÁTICA</option>
                        <option value="1° AGROPECUÁRIA">1° AGROPECUÁRIA</option>
                        <option value="1° QUÍMICA">1° QUÍMICA</option>
                        <option value="2° INFORMÁTICA">2° INFORMÁTICA</option>
                        <option value="2° AGROPECUÁRIA">2° AGROPECUÁRIA</option>
                        <option value="2° ADMINISTRAÇÃO">2° ADMINISTRAÇÃO</option>
                        <option value="3° INFORMÁTICA">3° INFORMÁTICA</option>
                        <option value="3° AGROPECUÁRIA">3° AGROPECUÁRIA</option>
                        <option value="3° ADMINISTRAÇÃO">3° ADMINISTRAÇÃO</option>
                    </select>
                </div>
                <div id="topico">
                    <h1><i class="fa-solid fa-tags"></i> TÓPICO/ASSUNTO</h1>
                    <input type="text" id="assunto" name="assunto" placeholder="Ex: Teorema de Pitágoras, Segunda Guerra Mundial.." required>
                </div>
                <div id="enunciado">
                    <h1><i class="fa-solid fa-circle-question"></i> ENUNCIADO DA QUESTÃO</h1>
                    <textarea id="enunciadotxtarea" name="enunciado" placeholder="Digite o enunciado da questão..." required></textarea>
                </div>

                <div id="imagem">
                    <h1><i class="fa-solid fa-image"></i> IMAGEM DA QUESTÃO (OPCIONAL)</h1>
                    <input type="file" id="imagem-input" name="imagem" accept="image/*" onchange="previewImagem(event)">
                    <div class="preview-imagem" id="preview-imagem" style="display: none;">
                        <img id="imagem-preview-img" src="" alt="Preview da imagem">
                        <br>
                        <button type="button" class="remover-imagem" onclick="removerImagem()">
                            <i class="fas fa-trash"></i> REMOVER IMAGEM
                        </button>
                    </div>
                </div>

                <div id="alternativa">
                    <h1><i class="fa-solid fa-list-ul"></i> ALTERNATIVAS</h1>
                    <div id="containeralternativas">
                        <div class="alt">
                            <input type="text" id="altA" name="altA" placeholder="Alternativa A" required>
                        </div>
                        <div class="alt">
                            <input type="text" id="altB" name="altB" placeholder="Alternativa B" required>
                        </div>
                        <div class="alt">
                            <input type="text" id="altC" name="altC" placeholder="Alternativa C" required>
                        </div>
                        <div class="alt">
                            <input type="text" id="altD" name="altD" placeholder="Alternativa D" required>
                        </div>
                    </div>
                    <div id="resposta-correta">
                        <h1><i class="fas fa-check-circle"></i> RESPOSTA CORRETA</h1>
                        <select id="alternativa-correta" name="respostaCorreta" required>
                            <option value="">Selecione a alternativa correta</option>
                            <option value="A">Alternativa A</option>
                            <option value="B">Alternativa B</option>
                            <option value="C">Alternativa C</option>
                            <option value="D">Alternativa D</option>
                        </select>
                    </div>
                </div>
                <div id="preview">
                    <h1><i class="fas fa-eye"></i> PRÉ-VISUALIZAÇÃO</h1>
                    <div id="preview-content"></div>
                </div>
                <div id="btexcluir">
                    <button type="button" onclick="limparCampos()"><i class="fa-solid fa-eraser"></i> LIMPAR</button>
                </div>
                <div id="btconcluir">
                    <button type="submit"><i class="fa-solid fa-circle-check"></i> SALVAR QUESTÃO</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        let imagemSelecionada = null;
        const disciplinaProfessor = '<?php echo htmlspecialchars($disciplina_professor); ?>';

        function previewImagem(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('A imagem deve ter no máximo 5MB!');
                    event.target.value = '';
                    return;
                }

                if (!file.type.match('image.*')) {
                    alert('Por favor, selecione apenas arquivos de imagem!');
                    event.target.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    imagemSelecionada = e.target.result;
                    document.getElementById('imagem-preview-img').src = e.target.result;
                    document.getElementById('preview-imagem').style.display = 'block';
                    atualizarPreview();
                };
                reader.readAsDataURL(file);
            }
        }

        function removerImagem() {
            document.getElementById('imagem-input').value = '';
            document.getElementById('preview-imagem').style.display = 'none';
            imagemSelecionada = null;
            atualizarPreview();
        }

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

            atualizarPreview();
        });

        function ajustarAlturaTextarea() {
            const textarea = document.getElementById('enunciadotxtarea');
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
            atualizarPreview();
        }

        function limparCampos() {
            document.getElementById('assunto').value = '';
            document.getElementById('enunciadotxtarea').value = '';
            document.getElementById('altA').value = '';
            document.getElementById('altB').value = '';
            document.getElementById('altC').value = '';
            document.getElementById('altD').value = '';
            document.getElementById('alternativa-correta').value = '';
            removerImagem();
            ajustarAlturaTextarea();
        }

        function atualizarPreview() {
            const assunto = document.getElementById('assunto').value;
            const enunciado = document.getElementById('enunciadotxtarea').value;
            const altA = document.getElementById('altA').value;
            const altB = document.getElementById('altB').value;
            const altC = document.getElementById('altC').value;
            const altD = document.getElementById('altD').value;
            const respostaCorreta = document.getElementById('alternativa-correta').value;

            let imagemHTML = '';
            if (imagemSelecionada) {
                imagemHTML = `
                    <div class="preview-questao-imagem">
                        <img src="${imagemSelecionada}" alt="Imagem da questão">
                    </div>
                `;
            }

            let previewHTML = `
                <p><strong>Disciplina:</strong> ${disciplinaProfessor}</p>
                <p><strong>Assunto:</strong> ${assunto || 'Não informado'}</p>
                <div class="preview-enunciado">
                    <p><strong>Enunciado:</strong></p>
                    <p>${enunciado || 'Nenhum enunciado digitado'}</p>
                    ${imagemHTML}
                </div>
                <div class="preview-alternativas">
                    <p><strong>Alternativas:</strong></p>
                    <p>A) ${altA || 'Não preenchida'} ${respostaCorreta === 'A' ? '<span class="correta">(Correta)</span>' : ''}</p>
                    <p>B) ${altB || 'Não preenchida'} ${respostaCorreta === 'B' ? '<span class="correta">(Correta)</span>' : ''}</p>
                    <p>C) ${altC || 'Não preenchida'} ${respostaCorreta === 'C' ? '<span class="correta">(Correta)</span>' : ''}</p>
                    <p>D) ${altD || 'Não preenchida'} ${respostaCorreta === 'D' ? '<span class="correta">(Correta)</span>' : ''}</p>
                </div>
                ${respostaCorreta ? `<p><strong>Resposta correta:</strong> Alternativa ${respostaCorreta}</p>` : ''}
            `;

            document.getElementById('preview-content').innerHTML = previewHTML;
        }

        document.getElementById('assunto').addEventListener('input', atualizarPreview);
        document.getElementById('enunciadotxtarea').addEventListener('input', ajustarAlturaTextarea);
        document.getElementById('altA').addEventListener('input', atualizarPreview);
        document.getElementById('altB').addEventListener('input', atualizarPreview);
        document.getElementById('altC').addEventListener('input', atualizarPreview);
        document.getElementById('altD').addEventListener('input', atualizarPreview);
        document.getElementById('alternativa-correta').addEventListener('change', atualizarPreview);
        window.addEventListener('load', ajustarAlturaTextarea);
    </script>
</body>

</html>