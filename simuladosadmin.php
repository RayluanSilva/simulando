<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: loginadmin.php");
    exit();
}

function listarSimulados($pdo) {
    $stmt = $pdo->query("SELECT id_simulado, nome_simulado FROM simulado ORDER BY nome_simulado");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listarCursos($pdo) {
    $stmt = $pdo->query("SELECT DISTINCT curso FROM alunos WHERE curso IS NOT NULL AND curso != '' ORDER BY curso");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listarResultados($pdo) {
    $sql = "SELECT r.*, a.nome_usuario as nome_aluno, a.curso as curso_aluno, s.nome_simulado 
            FROM resultados r 
            INNER JOIN alunos a ON r.id_aluno = a.id 
            INNER JOIN simulado s ON r.id_simulado = s.id_simulado 
            ORDER BY r.data_realizacao DESC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buscarResultado($pdo, $id) {
    $sql = "SELECT r.*, a.nome_usuario as nome_aluno, a.curso as curso_aluno, s.nome_simulado 
            FROM resultados r 
            INNER JOIN alunos a ON r.id_aluno = a.id 
            INNER JOIN simulado s ON r.id_simulado = s.id_simulado 
            WHERE r.id_resultado = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado && $resultado['respostas_detalhadas']) {
        $resultado['respostas_detalhadas'] = json_decode($resultado['respostas_detalhadas'], true);
    }
    
    return $resultado;
}

function excluirResultado($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM resultados WHERE id_resultado = ?");
    return $stmt->execute([$id]);
}

function buscarQuestoesSimulado($pdo, $id_resultado, $id_simulado) {
    $sql = "SELECT q.* FROM questoes q 
            INNER JOIN questoes_simulado qs ON q.id_questao = qs.id_questao 
            WHERE qs.id_simulado = ? 
            ORDER BY qs.id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_simulado]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $acao = $_POST['acao'] ?? '';

    try {
        switch ($acao) {
            case 'listar_simulados':
                echo json_encode(['success' => true, 'dados' => listarSimulados($pdo)]);
                break;

            case 'listar_cursos':
                echo json_encode(['success' => true, 'dados' => listarCursos($pdo)]);
                break;

            case 'listar_resultados':
                echo json_encode(['success' => true, 'dados' => listarResultados($pdo)]);
                break;

            case 'buscar_resultado':
                $resultado = buscarResultado($pdo, $_POST['id']);
                echo json_encode(['success' => true, 'dados' => $resultado]);
                break;

            case 'buscar_questoes_simulado':
                $questoes = buscarQuestoesSimulado($pdo, $_POST['id_resultado'], $_POST['id_simulado']);
                $resultado = buscarResultado($pdo, $_POST['id_resultado']);
                
                $respostasDetalhadas = [];
                if ($resultado && $resultado['respostas_detalhadas']) {
                    $respostasDetalhadas = $resultado['respostas_detalhadas'];
                }
                
                $questoesFormatadas = [];
                foreach ($questoes as $index => $questao) {
                    $respostaAluno = isset($respostasDetalhadas[$index]) ? $respostasDetalhadas[$index]['respostaAluno'] : 'N/A';
                    $correto = isset($respostasDetalhadas[$index]) ? $respostasDetalhadas[$index]['correto'] : false;
                    
                    $questoesFormatadas[] = [
                        'id' => $questao['id_questao'],
                        'enunciado' => $questao['enunciado'],
                        'alternativas' => [
                            'A' => $questao['alternativa_a'],
                            'B' => $questao['alternativa_b'],
                            'C' => $questao['alternativa_c'],
                            'D' => $questao['alternativa_d']
                        ],
                        'resposta_correta' => $questao['resposta_correta'],
                        'resposta_aluno' => $respostaAluno,
                        'correto' => $correto
                    ];
                }
                
                echo json_encode([
                    'success' => true, 
                    'dados' => [
                        'nome_simulado' => $resultado['nome_simulado'],
                        'nome_aluno' => $resultado['nome_aluno'],
                        'questoes' => $questoesFormatadas
                    ]
                ]);
                break;

            case 'excluir_resultado':
                $resultado = excluirResultado($pdo, $_POST['id']);
                echo json_encode(['success' => $resultado]);
                break;

            default:
                echo json_encode(['success' => false, 'erro' => 'Ação inválida']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'erro' => $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulando - Resultados dos Alunos</title>
    <link rel="icon" type="image/x-icon" href="imagem/icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="simuladosadmin.css">
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
            background-color: aliceblue;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            max-height: 90vh;
            overflow-y: auto;
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

        .close-popup:hover {
            color: #e74c3c;
        }

        .info-resultado {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            font-family: "Segoe UI", sans-serif;
        }

        .info-resultado p {
            margin-bottom: 8px;
            font-size: 16px;
        }

        .info-resultado strong {
            color: #245dc6;
        }

        .lista-respostas {
            margin-top: 15px;
        }

        .item-resposta {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            border-left: 5px solid #ccc;
            background: #f9f9f9;
            font-family: "Segoe UI", sans-serif;
        }

        .item-resposta.correto {
            background-color: #e8f5e9;
            border-left-color: #2ecc71;
        }

        .item-resposta.incorreto {
            background-color: #ffebee;
            border-left-color: #e74c3c;
        }

        .item-resposta p {
            margin-bottom: 5px;
        }

        .item-resposta i {
            margin-left: 5px;
        }

        .item-resposta .fa-check {
            color: #27ae60;
        }

        .item-resposta .fa-xmark {
            color: #e74c3c;
        }

        .btn-visualizar {
            background-color: #2ecc71;
            color: white;
        }

        .btn-visualizar:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(46, 204, 113, 0.3);
        }

        .btn-detalhes {
            background-color: #3498db;
            color: white;
        }

        .btn-detalhes:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }

        .btn-excluir {
            background-color: #e74c3c;
            color: white;
        }

        .btn-excluir:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
        }

        .nota-alta {
            color: #27ae60;
            font-weight: bold;
        }

        .nota-baixa {
            color: #e74c3c;
            font-weight: bold;
        }

        .botoes-popup {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }

        .botoes-popup button {
            padding: 12px 30px;
        }

        .carregando {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
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
        
        #popupSimuladoDetalhado {
            display: none;
        }
        
        #popupSimuladoDetalhado .popup-content {
            max-width: 900px;
        }
        
        #questoes-detalhadas-container {
            margin-top: 20px;
            max-height: 60vh;
            overflow-y: auto;
            padding-right: 10px;
        }
        
        .aluno-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .aluno-info p {
            margin: 5px 0;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="mobile-menu-popup" id="mobileMenuPopup">
        <a href="admin.php"><i class="fas fa-chart-line"></i> DASHBOARD</a>
        <a href="usuarioadmin.php"><i class="fas fa-users"></i> USUÁRIOS</a>
        <a href="questoesadmin.php"><i class="fas fa-question-circle"></i> QUESTÕES</a>
        <a href="simuladosadmin.php" class="active"><i class="fas fa-poll"></i> RESULTADOS</a>
    </div>

    <div id="topo">
        <a href="index.php"><img src="imagem/logo.png" alt="simulando" id="logo"></a>
        <div id="nav">
            <nav>
                <a href="admin.php"><i class="fas fa-chart-line"></i> DASHBOARD</a>
                <a href="usuarioadmin.php"><i class="fas fa-users"></i> USUÁRIOS</a>
                <a href="questoesadmin.php"><i class="fas fa-question-circle"></i> QUESTÕES</a>
                <a href="#resultados" class="active"><i class="fas fa-poll"></i> RESULTADOS</a>
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
        <h1 id="h1containerprincipal"><i class="fa-solid fa-poll"></i> RESULTADOS DOS ALUNOS</h1>
        <p id="pcontainerprincipal">Acompanhe o desempenho detalhado dos alunos nos simulados realizados.</p>

        <div id="resultados-lista" class="tab-content active" style="display: block;">
            <div class="secao-filtro">
                <h2><i class="fas fa-filter"></i> FILTRAR RESULTADOS</h2>
                <div class="filtro-group">
                    <label for="filtro-simulado">Simulado:</label>
                    <select id="filtro-simulado" onchange="filtrarResultados()">
                        <option value="">Todos os Simulados</option>
                    </select>
                </div>
                <div class="filtro-group">
                    <label for="filtro-curso">Curso:</label>
                    <select id="filtro-curso" onchange="filtrarResultados()">
                        <option value="">Todos os Cursos</option>
                    </select>
                </div>
            </div>

            <div class="secao-tabela">
                <h2><i class="fas fa-graduation-cap"></i> DESEMPENHO DOS ALUNOS</h2>
                <table id="tabela-resultados">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Aluno</th>
                            <th>Simulado</th>
                            <th>Data</th>
                            <th>Acertos</th>
                            <th>Erros</th>
                            <th>Nota</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-resultados-body">
                        <tr><td colspan="8" class="carregando">Carregando resultados...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="popupResultado" class="popup">
        <div class="popup-content">
            <span class="close-popup" onclick="fecharPopup('popupResultado')">&times;</span>
            <h2 id="resultado-titulo" style="font-family: 'Segoe UI', sans-serif; color: #245dc6; margin-bottom: 20px;">Detalhes do Resultado</h2>
            <div id="resultado-conteudo"></div>
            <div class="botoes-popup">
                <button class="btn-acao btn-visualizar" onclick="fecharPopup('popupResultado')"><i class="fas fa-check"></i> Fechar</button>
            </div>
        </div>
    </div>

    <div id="popupSimuladoDetalhado" class="popup">
        <div class="popup-content">
            <span class="close-popup" onclick="fecharPopup('popupSimuladoDetalhado')">&times;</span>
            <h2 id="simulado-detalhado-titulo" style="font-family: 'Segoe UI', sans-serif; color: #245dc6; margin-bottom: 20px;">Simulado Detalhado</h2>
            <div id="simulado-detalhado-conteudo">
                <div id="aluno-info-container" class="aluno-info"></div>
                <div id="questoes-detalhadas-container"></div>
            </div>
            <div class="botoes-popup">
                <button class="btn-acao btn-visualizar" onclick="fecharPopup('popupSimuladoDetalhado')"><i class="fas fa-check"></i> Fechar</button>
            </div>
        </div>
    </div>

    <script>
        let resultados = [];
        let simulados = [];
        let cursos = [];
        let resultadosFiltrados = [];

        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenuPopup');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

        function carregarSimulados() {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'acao=listar_simulados'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    simulados = data.dados;
                    const select = document.getElementById('filtro-simulado');
                    simulados.forEach(sim => {
                        const option = document.createElement('option');
                        option.value = sim.id_simulado;
                        option.textContent = sim.nome_simulado;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Erro:', error));
        }

        function carregarCursos() {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'acao=listar_cursos'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cursos = data.dados;
                    const select = document.getElementById('filtro-curso');
                    cursos.forEach(curso => {
                        const option = document.createElement('option');
                        option.value = curso.curso;
                        option.textContent = curso.curso;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Erro:', error));
        }

        function carregarResultados() {
            document.getElementById('tabela-resultados-body').innerHTML = '<tr><td colspan="8" class="carregando">Carregando resultados...</td></tr>';
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'acao=listar_resultados'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultados = data.dados;
                    resultadosFiltrados = resultados;
                    exibirResultados();
                } else {
                    document.getElementById('tabela-resultados-body').innerHTML = '<tr><td colspan="8">Erro ao carregar resultados</td></tr>';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('tabela-resultados-body').innerHTML = '<tr><td colspan="8">Erro ao carregar resultados</td></tr>';
            });
        }

        function exibirResultados() {
            const tbody = document.getElementById('tabela-resultados-body');
            if (resultadosFiltrados.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8">Nenhum resultado encontrado</td></tr>';
                return;
            }

            let html = '';
            resultadosFiltrados.forEach(r => {
                const data = new Date(r.data_realizacao).toLocaleDateString('pt-BR');
                const notaClass = r.nota >= 6 ? 'nota-alta' : 'nota-baixa';
                
                html += `
                    <tr data-simulado="${r.id_simulado}" data-curso="${r.curso_aluno || ''}">
                        <td>${r.id_resultado}</td>
                        <td>${r.nome_aluno}</td>
                        <td>${r.nome_simulado}</td>
                        <td>${data}</td>
                        <td>${r.acertos}</td>
                        <td>${r.erros}</td>
                        <td class="${notaClass}">${r.nota}</td>
                        <td>
                            <button class="btn-acao btn-visualizar" onclick="visualizarResultado(${r.id_resultado})"><i class="fas fa-file-lines"></i> Resumo</button>
                            <button class="btn-acao btn-detalhes" onclick="visualizarSimuladoDetalhado(${r.id_resultado}, ${r.id_simulado})"><i class="fas fa-search"></i> Detalhado</button>
                            <button class="btn-acao btn-excluir" onclick="excluirResultado(${r.id_resultado})"><i class="fas fa-trash"></i> Excluir</button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        function filtrarResultados() {
            const simuladoFiltro = document.getElementById('filtro-simulado').value;
            const cursoFiltro = document.getElementById('filtro-curso').value;

            resultadosFiltrados = resultados.filter(r => {
                const matchSimulado = !simuladoFiltro || r.id_simulado == simuladoFiltro;
                const matchCurso = !cursoFiltro || r.curso_aluno === cursoFiltro;
                return matchSimulado && matchCurso;
            });

            exibirResultados();
        }

        function visualizarResultado(id) {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'acao=buscar_resultado&id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.dados) {
                    const r = data.dados;
                    const dataFormatada = new Date(r.data_realizacao).toLocaleDateString('pt-BR') + ' ' + 
                                        new Date(r.data_realizacao).toLocaleTimeString('pt-BR');

                    let respostasHtml = '<div class="info-resultado">';
                    respostasHtml += `<p><strong>Aluno:</strong> ${r.nome_aluno}</p>`;
                    respostasHtml += `<p><strong>Simulado:</strong> ${r.nome_simulado}</p>`;
                    respostasHtml += `<p><strong>Data:</strong> ${dataFormatada}</p>`;
                    respostasHtml += `<p><strong>Acertos:</strong> ${r.acertos}</p>`;
                    respostasHtml += `<p><strong>Erros:</strong> ${r.erros}</p>`;
                    respostasHtml += `<p><strong>Nota:</strong> ${r.nota}</p>`;
                    respostasHtml += '</div>';

                    if (r.respostas_detalhadas && Array.isArray(r.respostas_detalhadas)) {
                        respostasHtml += '<h3 style="font-family: \'Segoe UI\', sans-serif; margin: 20px 0 10px; color: #333;">Respostas Detalhadas:</h3>';
                        respostasHtml += '<div class="lista-respostas">';
                        
                        r.respostas_detalhadas.forEach((q, index) => {
                            const classe = q.correto ? 'correto' : 'incorreto';
                            const icone = q.correto ? '<i class="fas fa-check"></i>' : '<i class="fas fa-xmark"></i>';
                            
                            respostasHtml += `
                                <div class="item-resposta ${classe}">
                                    <p><strong>Questão ${index + 1}:</strong> ${q.pergunta || 'Pergunta não disponível'}</p>
                                    <p>Resposta do Aluno: ${q.respostaAluno || 'N/A'} | Resposta Correta: ${q.respostaCorreta || 'N/A'} ${icone}</p>
                                </div>
                            `;
                        });
                        
                        respostasHtml += '</div>';
                    }

                    document.getElementById('resultado-titulo').innerText = `Detalhes do Resultado #${r.id_resultado}`;
                    document.getElementById('resultado-conteudo').innerHTML = respostasHtml;
                    document.getElementById('popupResultado').style.display = 'flex';
                }
            })
            .catch(error => console.error('Erro:', error));
        }

        function visualizarSimuladoDetalhado(idResultado, idSimulado) {
            const formData = new URLSearchParams();
            formData.append('acao', 'buscar_questoes_simulado');
            formData.append('id_resultado', idResultado);
            formData.append('id_simulado', idSimulado);

            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.dados) {
                    const dados = data.dados;
                    
                    document.getElementById('simulado-detalhado-titulo').innerText = `Simulado: ${dados.nome_simulado}`;
                    
                    const alunoInfo = document.getElementById('aluno-info-container');
                    alunoInfo.innerHTML = `
                        <p><strong><i class="fas fa-user-graduate"></i> Aluno:</strong> ${dados.nome_aluno}</p>
                    `;
                    
                    const questoesContainer = document.getElementById('questoes-detalhadas-container');
                    questoesContainer.innerHTML = '';
                    
                    dados.questoes.forEach((questao, index) => {
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
                    
                    document.getElementById('popupSimuladoDetalhado').style.display = 'flex';
                }
            })
            .catch(error => console.error('Erro:', error));
        }

        function excluirResultado(id) {
            if (confirm('Tem certeza que deseja excluir este resultado?')) {
                const formData = new URLSearchParams();
                formData.append('acao', 'excluir_resultado');
                formData.append('id', id);

                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Resultado excluído com sucesso!');
                        carregarResultados();
                    } else {
                        alert('Erro ao excluir resultado');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao excluir resultado');
                });
            }
        }

        function fecharPopup(id) {
            document.getElementById(id).style.display = 'none';
        }

        document.addEventListener("DOMContentLoaded", function() {
            carregarSimulados();
            carregarCursos();
            carregarResultados();

            document.addEventListener('click', function(e) {
                const menu = document.getElementById('mobileMenuPopup');
                const hamburgerBtn = document.getElementById('hamburguer-btn');
                
                if (!menu.contains(e.target) && e.target !== hamburgerBtn && !hamburgerBtn.contains(e.target)) {
                    menu.style.display = 'none';
                }
            });

            window.onclick = function(event) {
                if (event.target.classList.contains('popup')) {
                    event.target.style.display = 'none';
                }
            };
        });
    </script>
</body>
</html>