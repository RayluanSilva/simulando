<?php
session_start();
require_once 'conexao.php';

// Verificar se o usuário está logado como admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: loginadmin.php");
    exit();
}

function listarAlunos($pdo) {
    $stmt = $pdo->query("SELECT id, nome_usuario, email, rm, curso FROM alunos ORDER BY id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function cadastrarAluno($pdo, $dados) {
    $sql = "INSERT INTO alunos (nome_usuario, email, senha, rm, curso) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$dados['nome'], $dados['email'], $dados['senha'], $dados['rm'], $dados['curso']]);
}

function editarAluno($pdo, $dados) {
    if (!empty($dados['senha'])) {
        $sql = "UPDATE alunos SET nome_usuario = ?, email = ?, senha = ?, rm = ?, curso = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$dados['nome'], $dados['email'], $dados['senha'], $dados['rm'], $dados['curso'], $dados['id']]);
    } else {
        $sql = "UPDATE alunos SET nome_usuario = ?, email = ?, rm = ?, curso = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$dados['nome'], $dados['email'], $dados['rm'], $dados['curso'], $dados['id']]);
    }
}

function excluirAluno($pdo, $id) {
    // Primeiro deletar resultados relacionados
    $stmt = $pdo->prepare("DELETE FROM resultados WHERE id_aluno = ?");
    $stmt->execute([$id]);
    
    $stmt = $pdo->prepare("DELETE FROM alunos WHERE id = ?");
    return $stmt->execute([$id]);
}

function listarProfessores($pdo) {
    $stmt = $pdo->query("SELECT id, nome_usuario, materia, codigo_acesso FROM professores ORDER BY id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function cadastrarProfessor($pdo, $dados) {
    $sql = "INSERT INTO professores (nome_usuario, senha, materia, codigo_acesso) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$dados['nome'], $dados['senha'], $dados['materia'], $dados['codigo']]);
}

function editarProfessor($pdo, $dados) {
    if (!empty($dados['senha'])) {
        $sql = "UPDATE professores SET nome_usuario = ?, senha = ?, materia = ?, codigo_acesso = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$dados['nome'], $dados['senha'], $dados['materia'], $dados['codigo'], $dados['id']]);
    } else {
        $sql = "UPDATE professores SET nome_usuario = ?, materia = ?, codigo_acesso = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$dados['nome'], $dados['materia'], $dados['codigo'], $dados['id']]);
    }
}

function excluirProfessor($pdo, $id) {
    // Primeiro deletar questões relacionadas (e suas referências em simulados)
    $stmt = $pdo->prepare("SELECT id_questao FROM questoes WHERE id_professor = ?");
    $stmt->execute([$id]);
    $questoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($questoes as $q) {
        $stmtDelSim = $pdo->prepare("DELETE FROM questoes_simulado WHERE id_questao = ?");
        $stmtDelSim->execute([$q['id_questao']]);
        
        $stmtDelQuest = $pdo->prepare("DELETE FROM questoes WHERE id_questao = ?");
        $stmtDelQuest->execute([$q['id_questao']]);
    }
    
    $stmt = $pdo->prepare("DELETE FROM professores WHERE id = ?");
    return $stmt->execute([$id]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $acao = $_POST['acao'] ?? '';

    try {
        switch ($acao) {
            case 'listar_alunos':
                echo json_encode(['success' => true, 'dados' => listarAlunos($pdo)]);
                break;

            case 'listar_professores':
                echo json_encode(['success' => true, 'dados' => listarProfessores($pdo)]);
                break;

            case 'cadastrar_aluno':
                $resultado = cadastrarAluno($pdo, [
                    'nome' => $_POST['nome'],
                    'email' => $_POST['email'],
                    'senha' => $_POST['senha'],
                    'rm' => $_POST['rm'],
                    'curso' => $_POST['curso']
                ]);
                echo json_encode(['success' => $resultado]);
                break;

            case 'cadastrar_professor':
                $resultado = cadastrarProfessor($pdo, [
                    'nome' => $_POST['nome'],
                    'senha' => $_POST['senha'],
                    'materia' => $_POST['materia'],
                    'codigo' => $_POST['codigo']
                ]);
                echo json_encode(['success' => $resultado]);
                break;

            case 'editar_aluno':
                $resultado = editarAluno($pdo, [
                    'id' => $_POST['id'],
                    'nome' => $_POST['nome'],
                    'email' => $_POST['email'],
                    'senha' => $_POST['senha'] ?? '',
                    'rm' => $_POST['rm'],
                    'curso' => $_POST['curso']
                ]);
                echo json_encode(['success' => $resultado]);
                break;

            case 'editar_professor':
                $resultado = editarProfessor($pdo, [
                    'id' => $_POST['id'],
                    'nome' => $_POST['nome'],
                    'senha' => $_POST['senha'] ?? '',
                    'materia' => $_POST['materia'],
                    'codigo' => $_POST['codigo']
                ]);
                echo json_encode(['success' => $resultado]);
                break;

            case 'excluir_aluno':
                $resultado = excluirAluno($pdo, $_POST['id']);
                echo json_encode(['success' => $resultado]);
                break;

            case 'excluir_professor':
                $resultado = excluirProfessor($pdo, $_POST['id']);
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
    <title>Simulando - Gerenciamento de Usuários</title>
    <link rel="icon" type="image/x-icon" href="imagem/icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="usuarioadmin.css">
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
            max-width: 500px;
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

        .popup-form input, .popup-form select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            font-family: "Segoe UI", sans-serif;
        }

        .popup-form input:focus, .popup-form select:focus {
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

        .botoes-popup {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .botoes-popup button {
            flex: 1;
            padding: 12px;
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

        .cadastro-form {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn-cadastro {
            background-color: #74be6c;
            color: white;
            margin-bottom: 10px;
        }

        .btn-salvar {
            background-color: #245dc6;
            color: white;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="mobile-menu-popup" id="mobileMenuPopup">
        <a href="admin.php"><i class="fas fa-chart-line"></i> DASHBOARD</a>
        <a href="usuarioadmin.php" class="active"><i class="fas fa-users"></i> USUÁRIOS</a>
        <a href="questoesadmin.php"><i class="fas fa-question-circle"></i> QUESTÕES</a>
        <a href="simuladosadmin.php"><i class="fas fa-poll"></i> SIMULADOS</a>
    </div>

    <div id="topo">
        <a href="index.php"><img src="imagem/logo.png" alt="simulando" id="logo"></a>
        <div id="nav">
            <nav>
                <a href="admin.php"><i class="fas fa-chart-line"></i> DASHBOARD</a>
                <a href="#usuarios" class="active"><i class="fas fa-users"></i> USUÁRIOS</a>
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
        <h1 id="h1containerprincipal"><i class="fa-solid fa-users"></i> GERENCIAMENTO DE USUÁRIOS</h1>
        <p id="pcontainerprincipal">Controle total sobre alunos e professores cadastrados na plataforma.</p>

        <div class="tabs">
            <button class="tab-button active" id="btn-tab-alunos" onclick="openTab(event, 'alunos')">ALUNOS</button>
            <button class="tab-button" id="btn-tab-professores" onclick="openTab(event, 'professores')">PROFESSORES</button>
            <button class="tab-button" id="btn-tab-cadastrar" onclick="openTab(event, 'cadastrar')">CADASTRAR NOVO</button>
        </div>

        <div id="alunos" class="tab-content active">
            <div class="secao-filtro">
                <h2><i class="fas fa-filter"></i> FILTROS DE ALUNOS</h2>
                <div class="filtro-group">
                    <label for="filtro-curso">Curso:</label>
                    <select id="filtro-curso" onchange="filtrarAlunos()">
                        <option value="">Todos os Cursos</option>
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
            </div>

            <div class="secao-tabela">
                <h2><i class="fas fa-table"></i> ALUNOS CADASTRADOS</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome de Usuário</th>
                            <th>E-mail</th>
                            <th>RM</th>
                            <th>Curso</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-alunos">
                        <tr><td colspan="6" class="carregando">Carregando alunos...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="professores" class="tab-content" style="display:none;">
            <div class="secao-filtro">
                <h2><i class="fas fa-filter"></i> FILTROS DE PROFESSORES</h2>
                <div class="filtro-group">
                    <label for="filtro-materia">Matéria:</label>
                    <input type="text" id="filtro-materia" placeholder="Buscar por matéria..." onkeyup="filtrarProfessores()" style="padding: 10px; border-radius: 8px; border: 2px solid #5c5c5c;">
                </div>
            </div>

            <div class="secao-tabela">
                <h2><i class="fas fa-table"></i> PROFESSORES CADASTRADOS</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome de Usuário</th>
                            <th>Matéria</th>
                            <th>Código de Acesso</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-professores">
                        <tr><td colspan="5" class="carregando">Carregando professores...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="cadastrar" class="tab-content" style="display:none;">
            <div class="secao-cadastro">
                <h2><i class="fas fa-user-plus"></i> CADASTRAR NOVO USUÁRIO</h2>
                <div class="cadastro-opcoes">
                    <button class="btn-acao btn-cadastro" onclick="showForm('form-aluno-cad')">CADASTRAR ALUNO</button>
                    <button class="btn-acao btn-cadastro" onclick="showForm('form-professor-cad')">CADASTRAR PROFESSOR</button>
                </div>

                <form id="form-aluno-cad" class="cadastro-form" style="display: none;" onsubmit="cadastrarAluno(event)">
                    <h3>Cadastro de Aluno</h3>
                    <input type="text" id="cad-aluno-nome" placeholder="Nome de Usuário" required>
                    <input type="email" id="cad-aluno-email" placeholder="E-mail" required>
                    <input type="password" id="cad-aluno-senha" placeholder="Senha" required>
                    <input type="number" id="cad-aluno-rm" placeholder="RM (Registro de Matrícula)" required>
                    <select id="cad-aluno-curso" required>
                        <option value="">Selecione o Curso</option>
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
                    <button type="submit" class="btn-acao btn-salvar"><i class="fas fa-save"></i> Salvar Aluno</button>
                </form>

                <form id="form-professor-cad" class="cadastro-form" style="display: none;" onsubmit="cadastrarProfessor(event)">
                    <h3>Cadastro de Professor</h3>
                    <input type="text" id="cad-prof-nome" placeholder="Nome de Usuário" required>
                    <input type="password" id="cad-prof-senha" placeholder="Senha" required>
                    <input type="text" id="cad-prof-materia" placeholder="Matéria" required>
                    <input type="text" id="cad-prof-codigo" placeholder="Código de Acesso" required>
                    <button type="submit" class="btn-acao btn-salvar"><i class="fas fa-save"></i> Salvar Professor</button>
                </form>
            </div>
        </div>
    </div>

    <div id="popupEdicaoAluno" class="popup">
        <div class="popup-content">
            <span class="close-popup" onclick="fecharPopup('popupEdicaoAluno')">&times;</span>
            <form class="popup-form" id="form-edicao-aluno" onsubmit="salvarEdicaoAluno(event)">
                <h3>Editar Aluno</h3>
                <input type="hidden" id="edit-aluno-id">
                
                <label>Nome de Usuário:</label>
                <input type="text" id="edit-aluno-nome" required>
                
                <label>E-mail:</label>
                <input type="email" id="edit-aluno-email" required>
                
                <label>Senha (deixe em branco para manter):</label>
                <input type="password" id="edit-aluno-senha">
                
                <label>RM:</label>
                <input type="number" id="edit-aluno-rm" required>
                
                <label>Curso:</label>
                <select id="edit-aluno-curso" required>
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
                
                <div class="botoes-popup">
                    <button type="submit" class="btn-acao btn-editar"><i class="fas fa-save"></i> Salvar</button>
                    <button type="button" class="btn-acao btn-cancelar" onclick="fecharPopup('popupEdicaoAluno')"><i class="fas fa-times"></i> Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="popupEdicaoProfessor" class="popup">
        <div class="popup-content">
            <span class="close-popup" onclick="fecharPopup('popupEdicaoProfessor')">&times;</span>
            <form class="popup-form" id="form-edicao-professor" onsubmit="salvarEdicaoProfessor(event)">
                <h3>Editar Professor</h3>
                <input type="hidden" id="edit-prof-id">
                
                <label>Nome de Usuário:</label>
                <input type="text" id="edit-prof-nome" required>
                
                <label>Senha (deixe em branco para manter):</label>
                <input type="password" id="edit-prof-senha">
                
                <label>Matéria:</label>
                <input type="text" id="edit-prof-materia" required>
                
                <label>Código de Acesso:</label>
                <input type="text" id="edit-prof-codigo" required>
                
                <div class="botoes-popup">
                    <button type="submit" class="btn-acao btn-editar"><i class="fas fa-save"></i> Salvar</button>
                    <button type="button" class="btn-acao btn-cancelar" onclick="fecharPopup('popupEdicaoProfessor')"><i class="fas fa-times"></i> Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let alunos = [];
        let professores = [];
        let alunosFiltrados = [];
        let professoresFiltrados = [];

        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenuPopup');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tab-button");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";

            if (tabName === 'alunos') {
                carregarAlunos();
            } else if (tabName === 'professores') {
                carregarProfessores();
            }
        }

        function showForm(formId) {
            document.getElementById('form-aluno-cad').style.display = 'none';
            document.getElementById('form-professor-cad').style.display = 'none';
            document.getElementById(formId).style.display = 'flex';
        }

        function carregarAlunos() {
            document.getElementById('tabela-alunos').innerHTML = '<tr><td colspan="6" class="carregando">Carregando alunos...</td></tr>';
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'acao=listar_alunos'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alunos = data.dados;
                    alunosFiltrados = alunos;
                    exibirAlunos();
                } else {
                    document.getElementById('tabela-alunos').innerHTML = '<tr><td colspan="6">Erro ao carregar alunos</td></tr>';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('tabela-alunos').innerHTML = '<tr><td colspan="6">Erro ao carregar alunos</td></tr>';
            });
        }

        function carregarProfessores() {
            document.getElementById('tabela-professores').innerHTML = '<tr><td colspan="5" class="carregando">Carregando professores...</td></tr>';
            
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
                    professoresFiltrados = professores;
                    exibirProfessores();
                } else {
                    document.getElementById('tabela-professores').innerHTML = '<tr><td colspan="5">Erro ao carregar professores</td></tr>';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('tabela-professores').innerHTML = '<tr><td colspan="5">Erro ao carregar professores</td></tr>';
            });
        }

        function exibirAlunos() {
            const tbody = document.getElementById('tabela-alunos');
            if (alunosFiltrados.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6">Nenhum aluno encontrado</td></tr>';
                return;
            }

            let html = '';
            alunosFiltrados.forEach(aluno => {
                html += `
                    <tr>
                        <td>${aluno.id}</td>
                        <td>${aluno.nome_usuario}</td>
                        <td>${aluno.email}</td>
                        <td>${aluno.rm}</td>
                        <td>${aluno.curso}</td>
                        <td>
                            <button class="btn-acao btn-editar" onclick="abrirEdicaoAluno(${aluno.id})"><i class="fas fa-edit"></i> Editar</button>
                            <button class="btn-acao btn-cancelar" onclick="excluirAluno(${aluno.id})"><i class="fas fa-trash"></i> Excluir</button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        function exibirProfessores() {
            const tbody = document.getElementById('tabela-professores');
            if (professoresFiltrados.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5">Nenhum professor encontrado</td></tr>';
                return;
            }

            let html = '';
            professoresFiltrados.forEach(prof => {
                html += `
                    <tr>
                        <td>${prof.id}</td>
                        <td>${prof.nome_usuario}</td>
                        <td>${prof.materia}</td>
                        <td>${prof.codigo_acesso}</td>
                        <td>
                            <button class="btn-acao btn-editar" onclick="abrirEdicaoProfessor(${prof.id})"><i class="fas fa-edit"></i> Editar</button>
                            <button class="btn-acao btn-cancelar" onclick="excluirProfessor(${prof.id})"><i class="fas fa-trash"></i> Excluir</button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        function filtrarAlunos() {
            const curso = document.getElementById('filtro-curso').value;
            if (curso) {
                alunosFiltrados = alunos.filter(a => a.curso === curso);
            } else {
                alunosFiltrados = alunos;
            }
            exibirAlunos();
        }

        function filtrarProfessores() {
            const materia = document.getElementById('filtro-materia').value.toLowerCase();
            if (materia) {
                professoresFiltrados = professores.filter(p => p.materia.toLowerCase().includes(materia));
            } else {
                professoresFiltrados = professores;
            }
            exibirProfessores();
        }

        function cadastrarAluno(event) {
            event.preventDefault();
            const nome = document.getElementById('cad-aluno-nome').value;
            const email = document.getElementById('cad-aluno-email').value;
            const senha = document.getElementById('cad-aluno-senha').value;
            const rm = document.getElementById('cad-aluno-rm').value;
            const curso = document.getElementById('cad-aluno-curso').value;

            const formData = new URLSearchParams();
            formData.append('acao', 'cadastrar_aluno');
            formData.append('nome', nome);
            formData.append('email', email);
            formData.append('senha', senha);
            formData.append('rm', rm);
            formData.append('curso', curso);

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
                    alert('Aluno cadastrado com sucesso!');
                    document.getElementById('form-aluno-cad').reset();
                    document.getElementById('form-aluno-cad').style.display = 'none';
                    carregarAlunos();
                } else {
                    alert('Erro ao cadastrar aluno: ' + (data.erro || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao cadastrar aluno');
            });
        }

        function cadastrarProfessor(event) {
            event.preventDefault();
            const nome = document.getElementById('cad-prof-nome').value;
            const senha = document.getElementById('cad-prof-senha').value;
            const materia = document.getElementById('cad-prof-materia').value;
            const codigo = document.getElementById('cad-prof-codigo').value;

            const formData = new URLSearchParams();
            formData.append('acao', 'cadastrar_professor');
            formData.append('nome', nome);
            formData.append('senha', senha);
            formData.append('materia', materia);
            formData.append('codigo', codigo);

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
                    alert('Professor cadastrado com sucesso!');
                    document.getElementById('form-professor-cad').reset();
                    document.getElementById('form-professor-cad').style.display = 'none';
                    carregarProfessores();
                } else {
                    alert('Erro ao cadastrar professor: ' + (data.erro || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao cadastrar professor');
            });
        }

        function abrirEdicaoAluno(id) {
            const aluno = alunos.find(a => a.id == id);
            if (aluno) {
                document.getElementById('edit-aluno-id').value = aluno.id;
                document.getElementById('edit-aluno-nome').value = aluno.nome_usuario;
                document.getElementById('edit-aluno-email').value = aluno.email;
                document.getElementById('edit-aluno-rm').value = aluno.rm;
                document.getElementById('edit-aluno-curso').value = aluno.curso;
                document.getElementById('edit-aluno-senha').value = '';
                document.getElementById('popupEdicaoAluno').style.display = 'flex';
            }
        }

        function salvarEdicaoAluno(event) {
            event.preventDefault();
            
            const id = document.getElementById('edit-aluno-id').value;
            const nome = document.getElementById('edit-aluno-nome').value;
            const email = document.getElementById('edit-aluno-email').value;
            const senha = document.getElementById('edit-aluno-senha').value;
            const rm = document.getElementById('edit-aluno-rm').value;
            const curso = document.getElementById('edit-aluno-curso').value;

            const formData = new URLSearchParams();
            formData.append('acao', 'editar_aluno');
            formData.append('id', id);
            formData.append('nome', nome);
            formData.append('email', email);
            formData.append('senha', senha);
            formData.append('rm', rm);
            formData.append('curso', curso);

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
                    alert('Aluno editado com sucesso!');
                    fecharPopup('popupEdicaoAluno');
                    carregarAlunos();
                } else {
                    alert('Erro ao editar aluno: ' + (data.erro || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao editar aluno');
            });
        }

        function excluirAluno(id) {
            if (confirm('Tem certeza que deseja excluir este aluno? Esta ação removerá também seus resultados.')) {
                const formData = new URLSearchParams();
                formData.append('acao', 'excluir_aluno');
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
                        alert('Aluno excluído com sucesso!');
                        carregarAlunos();
                    } else {
                        alert('Erro ao excluir aluno: ' + (data.erro || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao excluir aluno');
                });
            }
        }

        function abrirEdicaoProfessor(id) {
            const professor = professores.find(p => p.id == id);
            if (professor) {
                document.getElementById('edit-prof-id').value = professor.id;
                document.getElementById('edit-prof-nome').value = professor.nome_usuario;
                document.getElementById('edit-prof-materia').value = professor.materia;
                document.getElementById('edit-prof-codigo').value = professor.codigo_acesso;
                document.getElementById('edit-prof-senha').value = '';
                document.getElementById('popupEdicaoProfessor').style.display = 'flex';
            }
        }

        function salvarEdicaoProfessor(event) {
            event.preventDefault();
            
            const id = document.getElementById('edit-prof-id').value;
            const nome = document.getElementById('edit-prof-nome').value;
            const senha = document.getElementById('edit-prof-senha').value;
            const materia = document.getElementById('edit-prof-materia').value;
            const codigo = document.getElementById('edit-prof-codigo').value;

            const formData = new URLSearchParams();
            formData.append('acao', 'editar_professor');
            formData.append('id', id);
            formData.append('nome', nome);
            formData.append('senha', senha);
            formData.append('materia', materia);
            formData.append('codigo', codigo);

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
                    alert('Professor editado com sucesso!');
                    fecharPopup('popupEdicaoProfessor');
                    carregarProfessores();
                } else {
                    alert('Erro ao editar professor: ' + (data.erro || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao editar professor');
            });
        }

        function excluirProfessor(id) {
            if (confirm('Tem certeza que deseja excluir este professor? Esta ação removerá também suas questões.')) {
                const formData = new URLSearchParams();
                formData.append('acao', 'excluir_professor');
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
                        alert('Professor excluído com sucesso!');
                        carregarProfessores();
                    } else {
                        alert('Erro ao excluir professor: ' + (data.erro || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao excluir professor');
                });
            }
        }

        function fecharPopup(id) {
            document.getElementById(id).style.display = 'none';
        }

        document.addEventListener("DOMContentLoaded", function() {
            carregarAlunos();
            
            document.addEventListener('click', function(e) {
                const menu = document.getElementById('mobileMenuPopup');
                const hamburgerBtn = document.getElementById('hamburguer-btn');
                
                if (menu && !menu.contains(e.target) && e.target !== hamburgerBtn && !hamburgerBtn.contains(e.target)) {
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
