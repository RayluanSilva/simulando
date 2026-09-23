<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: loginadmin.php");
    exit();
}

function listarQuestoes($pdo) {
    $sql = "SELECT q.*, p.nome_usuario as nome_professor 
            FROM questoes q 
            LEFT JOIN professores p ON q.id_professor = p.id 
            ORDER BY q.id_questao DESC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listarProfessores($pdo) {
    $stmt = $pdo->query("SELECT id, nome_usuario FROM professores ORDER BY nome_usuario");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listarMaterias($pdo) {
    $stmt = $pdo->query("SELECT DISTINCT disciplina FROM questoes ORDER BY disciplina");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buscarQuestao($pdo, $id) {
    $stmt = $pdo->prepare("SELECT q.*, p.nome_usuario as nome_professor 
                           FROM questoes q 
                           LEFT JOIN professores p ON q.id_professor = p.id 
                           WHERE q.id_questao = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function editarQuestao($pdo, $dados) {
    $sql = "UPDATE questoes SET 
            topico = ?,
            enunciado = ?,
            alternativa_a = ?,
            alternativa_b = ?,
            alternativa_c = ?,
            alternativa_d = ?,
            resposta_correta = ?
            WHERE id_questao = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $dados['assunto'],
        $dados['enunciado'],
        $dados['altA'],
        $dados['altB'],
        $dados['altC'],
        $dados['altD'],
        $dados['correta'],
        $dados['id']
    ]);
    return $result;
}

function excluirQuestao($pdo, $id) {
    try {
        // Primeiro, deletar as referências em questoes_simulado
        $stmt = $pdo->prepare("DELETE FROM questoes_simulado WHERE id_questao = ?");
        $stmt->execute([$id]);
        
        // Depois, deletar a questão
        $stmt = $pdo->prepare("DELETE FROM questoes WHERE id_questao = ?");
        $result = $stmt->execute([$id]);
        
        return $result;
    } catch (Exception $e) {
        error_log("Erro ao excluir questão: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $acao = $_POST['acao'] ?? '';

    try {
        switch ($acao) {
            case 'listar_questoes':
                echo json_encode(['success' => true, 'dados' => listarQuestoes($pdo)]);
                break;

            case 'listar_professores':
                echo json_encode(['success' => true, 'dados' => listarProfessores($pdo)]);
                break;

            case 'listar_materias':
                echo json_encode(['success' => true, 'dados' => listarMaterias($pdo)]);
                break;

            case 'buscar_questao':
                $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
                if ($id > 0) {
                    $questao = buscarQuestao($pdo, $id);
                    echo json_encode(['success' => $questao !== false, 'dados' => $questao]);
                } else {
                    echo json_encode(['success' => false, 'erro' => 'ID inválido']);
                }
                break;

            case 'editar_questao':
                if (isset($_POST['id'], $_POST['assunto'], $_POST['enunciado'], $_POST['altA'], $_POST['altB'], $_POST['altC'], $_POST['altD'], $_POST['correta'])) {
                    $resultado = editarQuestao($pdo, [
                        'id' => intval($_POST['id']),
                        'assunto' => $_POST['assunto'],
                        'enunciado' => $_POST['enunciado'],
                        'altA' => $_POST['altA'],
                        'altB' => $_POST['altB'],
                        'altC' => $_POST['altC'],
                        'altD' => $_POST['altD'],
                        'correta' => $_POST['correta']
                    ]);
                    echo json_encode(['success' => $resultado]);
                } else {
                    echo json_encode(['success' => false, 'erro' => 'Dados incompletos']);
                }
                break;

            case 'excluir_questao':
                $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
                if ($id > 0) {
                    $resultado = excluirQuestao($pdo, $id);
                    echo json_encode(['success' => $resultado]);
                } else {
                    echo json_encode(['success' => false, 'erro' => 'ID inválido']);
                }
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
    <title>Simulando - Gerenciamento de Questões</title>
    <link rel="icon" type="image/x-icon" href="imagem/icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="questoesadmin.css">
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
            max-width: 600px;
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

        .popup-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .popup-form h3 {
            color: #000;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 10px;
            font-family: 'Segoe UI', sans-serif;
        }

        .popup-form input, 
        .popup-form select,
        .popup-form textarea {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            font-family: "Segoe UI", sans-serif;
        }

        .popup-form textarea {
            resize: vertical;
            min-height: 100px;
        }

        .popup-form input:focus,
        .popup-form select:focus,
        .popup-form textarea:focus {
            outline: none;
            border-color: #74be6c;
            box-shadow: 0 0 0 2px rgba(116, 190, 108, 0.2);
        }

        .popup-form label {
            font-weight: bold;
            color: #333;
            font-family: "Segoe UI", sans-serif;
            margin-bottom: -5px;
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

        .botoes-popup {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .botoes-popup button {
            flex: 1;
            padding: 12px;
        }

        .visualizacao-item {
            margin-bottom: 10px;
            padding: 8px;
            background: #f9f9f9;
            border-radius: 5px;
            font-family: "Segoe UI", sans-serif;
        }

        .visualizacao-item strong {
            color: #245dc6;
        }

        .correta {
            color: #27ae60;
            font-weight: bold;
            margin-left: 5px;
        }

        .popup-imagem {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 5px;
        }

        .btn-cancelar {
            background-color: #e74c3c;
            color: white;
        }

        .btn-cancelar:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
        }

        .carregando {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="mobile-menu-popup" id="mobileMenuPopup">
        <a href="admin.php"><i class="fas fa-chart-line"></i> DASHBOARD</a>
        <a href="usuarioadmin.php"><i class="fas fa-users"></i> USUÁRIOS</a>
        <a href="questoesadmin.php" class="active"><i class="fas fa-question-circle"></i> QUESTÕES</a>
        <a href="simuladosadmin.php"><i class="fas fa-poll"></i> SIMULADOS</a>
    </div>

    <div id="topo">
        <a href="index.php"><img src="imagem/logo.png" alt="simulando" id="logo"></a>
        <div id="nav">
            <nav>
                <a href="admin.php"><i class="fas fa-chart-line"></i> DASHBOARD</a>
                <a href="usuarioadmin.php"><i class="fas fa-users"></i> USUÁRIOS</a>
                <a href="#questoes" class="active"><i class="fas fa-question-circle"></i> QUESTÕES</a>
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
        <h1 id="h1containerprincipal"><i class="fa-solid fa-question-circle"></i> GERENCIAMENTO DE QUESTÕES</h1>
        <p id="pcontainerprincipal">Visualize, edite ou exclua todas as questões criadas pelos professores.</p>

        <div id="questoes-lista" class="tab-content active">
            <div class="secao-filtro">
                <h2><i class="fas fa-filter"></i> FILTROS DE QUESTÕES</h2>
                <div class="filtro-group">
                    <label for="filtro-materia">Matéria:</label>
                    <select id="filtro-materia" onchange="filtrarQuestoes()">
                        <option value="">Todas as Matérias</option>
                    </select>
                </div>
                <div class="filtro-group">
                    <label for="filtro-professor">Professor:</label>
                    <select id="filtro-professor" onchange="filtrarQuestoes()">
                        <option value="">Todos os Professores</option>
                    </select>
                </div>
            </div>

            <div class="secao-tabela">
                <h2><i class="fas fa-table"></i> LISTA DE QUESTÕES</h2>
                <table id="tabela-questoes">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Matéria</th>
                            <th>Professor</th>
                            <th>Assunto</th>
                            <th>Enunciado</th>
                            <th>Curso</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-questoes-body">
                        <tr><td colspan="7" class="carregando">Carregando questões...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="popupVisualizar" class="popup">
        <div class="popup-content">
            <span class="close-popup" onclick="fecharPopup('popupVisualizar')">&times;</span>
            <div class="popup-form">
                <h3 id="vis-titulo">Visualizar Questão</h3>
                <div id="visualizacao-conteudo"></div>
                <div class="botoes-popup">
                    <button class="btn-acao btn-visualizar" onclick="fecharPopup('popupVisualizar')"><i class="fas fa-check"></i> OK</button>
                </div>
            </div>
        </div>
    </div>

    <div id="popupEdicao" class="popup">
        <div class="popup-content">
            <span class="close-popup" onclick="fecharPopup('popupEdicao')">&times;</span>
            <form class="popup-form" id="form-edicao" onsubmit="salvarEdicao(event)">
                <h3 id="edit-titulo">Editar Questão</h3>
                <input type="hidden" id="edit-id">
                
                <label>Assunto:</label>
                <input type="text" id="edit-assunto" placeholder="assunto aqui" required>
                
                <label>Enunciado:</label>
                <textarea id="edit-enunciado" placeholder="enunciado aqui" required></textarea>
                
                <label>Alternativa A:</label>
                <input type="text" id="edit-altA" placeholder="alternativa a aqui" required>
                
                <label>Alternativa B:</label>
                <input type="text" id="edit-altB" placeholder="alternativa b aqui" required>
                
                <label>Alternativa C:</label>
                <input type="text" id="edit-altC" placeholder="alternativa c aqui" required>
                
                <label>Alternativa D:</label>
                <input type="text" id="edit-altD" placeholder="alternativa d aqui" required>
                
                <label>Resposta Correta:</label>
                <select id="edit-correta" required>
                    <option value="">Selecione</option>
                    <option value="A">Alternativa A</option>
                    <option value="B">Alternativa B</option>
                    <option value="C">Alternativa C</option>
                    <option value="D">Alternativa D</option>
                </select>
                
                <div class="botoes-popup">
                    <button type="submit" class="btn-acao btn-editar"><i class="fas fa-save"></i> Salvar</button>
                    <button type="button" class="btn-acao btn-cancelar" onclick="fecharPopup('popupEdicao')"><i class="fas fa-times"></i> Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let questoes = [];
        let professores = [];
        let materias = [];
        let questoesFiltradas = [];

        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenuPopup');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

        function carregarMaterias() {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'acao=listar_materias'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    materias = data.dados;
                    const select = document.getElementById('filtro-materia');
                    materias.forEach(materia => {
                        const option = document.createElement('option');
                        option.value = materia.disciplina;
                        option.textContent = materia.disciplina;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Erro:', error));
        }

        function carregarProfessores() {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'acao=listar_professores'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    professores = data.dados;
                    const select = document.getElementById('filtro-professor');
                    professores.forEach(prof => {
                        const option = document.createElement('option');
                        option.value = prof.id;
                        option.textContent = prof.nome_usuario;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Erro:', error));
        }

        function carregarQuestoes() {
            document.getElementById('tabela-questoes-body').innerHTML = '<tr><td colspan="7" class="carregando">Carregando questões...</td></tr>';
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'acao=listar_questoes'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    questoes = data.dados;
                    questoesFiltradas = questoes;
                    exibirQuestoes();
                } else {
                    document.getElementById('tabela-questoes-body').innerHTML = '<tr><td colspan="7">Erro ao carregar questões</td></tr>';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('tabela-questoes-body').innerHTML = '<tr><td colspan="7">Erro ao carregar questões</td></tr>';
            });
        }

        function exibirQuestoes() {
            const tbody = document.getElementById('tabela-questoes-body');
            if (questoesFiltradas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7">Nenhuma questão encontrada</td></tr>';
                return;
            }

            let html = '';
            questoesFiltradas.forEach(questao => {
                html += `
                    <tr data-materia="${questao.disciplina}" data-professor="${questao.id_professor || ''}">
                        <td>${questao.id_questao}</td>
                        <td>${questao.disciplina || 'N/A'}</td>
                        <td>${questao.nome_professor || 'N/A'}</td>
                        <td>${questao.topico || 'N/A'}</td>
                        <td>${questao.enunciado.substring(0, 30)}...</td>
                        <td>${questao.curso || 'N/A'}</td>
                        <td>
                            <button class="btn-acao btn-visualizar" onclick="visualizarQuestao(${questao.id_questao})" type="button"><i class="fas fa-eye"></i> Ver</button>
                            <button class="btn-acao btn-editar" onclick="editarQuestao(${questao.id_questao})" type="button"><i class="fas fa-edit"></i> Editar</button>
                            <button class="btn-acao btn-excluir" onclick="excluirQuestao(${questao.id_questao})" type="button"><i class="fas fa-trash"></i> Excluir</button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        function filtrarQuestoes() {
            const materiaFiltro = document.getElementById('filtro-materia').value;
            const profFiltro = document.getElementById('filtro-professor').value;

            questoesFiltradas = questoes.filter(questao => {
                const matchMateria = !materiaFiltro || questao.disciplina === materiaFiltro;
                const matchProfessor = !profFiltro || questao.id_professor == profFiltro;
                return matchMateria && matchProfessor;
            });

            exibirQuestoes();
        }

        function visualizarQuestao(id) {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'acao=buscar_questao&id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const q = data.dados;
                    
                    let imagemHtml = '';
                    if (q.imagem) {
                        imagemHtml = `<div class="visualizacao-item"><strong>Imagem:</strong><br><img src="${q.imagem}" class="popup-imagem"></div>`;
                    }

                    const html = `
                        <div class="visualizacao-item"><strong>Matéria:</strong> ${q.disciplina}</div>
                        <div class="visualizacao-item"><strong>Professor:</strong> ${q.nome_professor || 'N/A'}</div>
                        <div class="visualizacao-item"><strong>Curso:</strong> ${q.curso}</div>
                        <div class="visualizacao-item"><strong>Assunto:</strong> ${q.topico || 'N/A'}</div>
                        <div class="visualizacao-item"><strong>Enunciado:</strong> ${q.enunciado}</div>
                        <div class="visualizacao-item"><strong>Alternativas:</strong></div>
                        <div class="visualizacao-item">A) ${q.alternativa_a} ${q.resposta_correta === 'A' ? '<span class="correta">(Correta)</span>' : ''}</div>
                        <div class="visualizacao-item">B) ${q.alternativa_b} ${q.resposta_correta === 'B' ? '<span class="correta">(Correta)</span>' : ''}</div>
                        <div class="visualizacao-item">C) ${q.alternativa_c} ${q.resposta_correta === 'C' ? '<span class="correta">(Correta)</span>' : ''}</div>
                        <div class="visualizacao-item">D) ${q.alternativa_d} ${q.resposta_correta === 'D' ? '<span class="correta">(Correta)</span>' : ''}</div>
                        <div class="visualizacao-item"><strong>Resposta Correta:</strong> Alternativa ${q.resposta_correta}</div>
                    `;

                    document.getElementById('vis-titulo').innerText = `Visualizar Questão #${q.id_questao}`;
                    document.getElementById('visualizacao-conteudo').innerHTML = html;
                    document.getElementById('popupVisualizar').style.display = 'flex';
                }
            })
            .catch(error => console.error('Erro:', error));
        }

        function editarQuestao(id) {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'acao=buscar_questao&id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.dados) {
                    const q = data.dados;

                    document.getElementById('edit-titulo').innerText = `Editar Questão #${q.id_questao}`;
                    document.getElementById('edit-id').value = q.id_questao;
                    document.getElementById('edit-assunto').value = q.topico || '';
                    document.getElementById('edit-enunciado').value = q.enunciado;
                    document.getElementById('edit-altA').value = q.alternativa_a;
                    document.getElementById('edit-altB').value = q.alternativa_b;
                    document.getElementById('edit-altC').value = q.alternativa_c;
                    document.getElementById('edit-altD').value = q.alternativa_d;
                    document.getElementById('edit-correta').value = q.resposta_correta;

                    document.getElementById('popupEdicao').style.display = 'flex';
                } else {
                    alert('Erro ao carregar questão para edição');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao carregar questão para edição');
            });
        }

        function salvarEdicao(event) {
            event.preventDefault();
            
            const id = document.getElementById('edit-id').value;
            const assunto = document.getElementById('edit-assunto').value;
            const enunciado = document.getElementById('edit-enunciado').value;
            const altA = document.getElementById('edit-altA').value;
            const altB = document.getElementById('edit-altB').value;
            const altC = document.getElementById('edit-altC').value;
            const altD = document.getElementById('edit-altD').value;
            const correta = document.getElementById('edit-correta').value;

            if (!id || !assunto || !enunciado || !altA || !altB || !altC || !altD || !correta) {
                alert('Por favor, preencha todos os campos');
                return;
            }

            const formData = new URLSearchParams();
            formData.append('acao', 'editar_questao');
            formData.append('id', id);
            formData.append('assunto', assunto);
            formData.append('enunciado', enunciado);
            formData.append('altA', altA);
            formData.append('altB', altB);
            formData.append('altC', altC);
            formData.append('altD', altD);
            formData.append('correta', correta);

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
                    alert('Questão editada com sucesso!');
                    fecharPopup('popupEdicao');
                    carregarQuestoes();
                } else {
                    alert('Erro ao editar questão: ' + (data.erro || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao editar questão');
            });
        }

        function excluirQuestao(id) {
            if (confirm('Tem certeza que deseja excluir esta questão? Esta ação não pode ser desfeita.')) {
                const formData = new URLSearchParams();
                formData.append('acao', 'excluir_questao');
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
                        alert('Questão excluída com sucesso!');
                        carregarQuestoes();
                    } else {
                        alert('Erro ao excluir questão: ' + (data.erro || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao excluir questão');
                });
            }
        }

        function fecharPopup(id) {
            document.getElementById(id).style.display = 'none';
        }

        document.addEventListener("DOMContentLoaded", function() {
            carregarMaterias();
            carregarProfessores();
            carregarQuestoes();

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
