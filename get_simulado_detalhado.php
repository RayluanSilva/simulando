<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Acesso negado.']);
    exit();
}

if (!isset($_GET['id_simulado']) || !is_numeric($_GET['id_simulado'])) {
    echo json_encode(['error' => 'ID do simulado inválido.']);
    exit();
}

if ($_SESSION['usuario_tipo'] === 'professor') {
    if (!isset($_GET['id_aluno']) || !is_numeric($_GET['id_aluno'])) {
        echo json_encode(['error' => 'ID do aluno não fornecido para o professor.']);
        exit();
    }
    $id_aluno = $_GET['id_aluno'];
} else {
    if ($_SESSION['usuario_tipo'] !== 'aluno') {
        echo json_encode(['error' => 'Tipo de usuário inválido.']);
        exit();
    }
    $id_aluno = $_SESSION['usuario_id'];
}
$id_simulado = $_GET['id_simulado'];

try {
    $stmt = $pdo->prepare("SELECT r.respostas_detalhadas, s.nome_simulado
                          FROM resultados r
                          JOIN simulado s ON r.id_simulado = s.id_simulado
                          WHERE r.id_aluno = ? AND r.id_simulado = ?");
    $stmt->execute([$id_aluno, $id_simulado]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resultado) {
        echo json_encode(['error' => 'Resultado não encontrado para este simulado.']);
        exit();
    }

    $respostas_aluno = json_decode($resultado['respostas_detalhadas'], true);
    $nome_simulado = $resultado['nome_simulado'];

    $stmt = $pdo->prepare("SELECT q.id_questao, q.enunciado, q.alternativa_a, q.alternativa_b, q.alternativa_c, q.alternativa_d, q.resposta_correta
                          FROM questoes_simulado qs
                          JOIN questoes q ON qs.id_questao = q.id_questao
                          WHERE qs.id_simulado = ?
                          ORDER BY q.id_questao ASC");
    $stmt->execute([$id_simulado]);
    $questoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $simulado_detalhado = [];
    foreach ($questoes as $questao) {
        $id_questao = $questao['id_questao'];
        $resposta_aluno = isset($respostas_aluno[$id_questao]) ? $respostas_aluno[$id_questao] : null;
        $correta = $questao['resposta_correta'];
        $acertou = ($resposta_aluno === $correta);

        $simulado_detalhado[] = [
            'id' => $id_questao,
            'enunciado' => $questao['enunciado'],
            'alternativas' => [
                'A' => $questao['alternativa_a'],
                'B' => $questao['alternativa_b'],
                'C' => $questao['alternativa_c'],
                'D' => $questao['alternativa_d'],

            ],
            'resposta_aluno' => $resposta_aluno,
            'resposta_correta' => $correta,
            'acertou' => $acertou
        ];
    }

    echo json_encode([
        'success' => true,
        'nome_simulado' => $nome_simulado,
        'questoes' => $simulado_detalhado
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro de Banco de Dados: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro Interno: ' . $e->getMessage()]);
}
?>