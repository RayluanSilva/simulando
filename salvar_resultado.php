<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'aluno') {
    header("Location: loginaluno.php");
    exit();
}

function getBimestreAtual() {
    $mes = date('n');
    
    if ($mes >= 2 && $mes <= 4) {
        return '1° Bimestre';
    } elseif ($mes >= 5 && $mes <= 7) {
        return '2° Bimestre';
    } elseif ($mes >= 8 && $mes <= 10) {
        return '3° Bimestre';
    } else {
        return '4° Bimestre';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_aluno = $_POST['id_aluno'];
    $respostas_aluno = [];
    
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'resposta_') === 0) {
            $id_questao = str_replace('resposta_', '', $key);
            $respostas_aluno[$id_questao] = $value;
        }
    }
    
    $acertos = 0;
    $total = count($respostas_aluno);
    $respostas_detalhadas = json_encode($respostas_aluno);
    
    foreach ($respostas_aluno as $id_questao => $resposta) {
        $stmt = $pdo->prepare("SELECT resposta_correta FROM questoes WHERE id_questao = ?");
        $stmt->execute([$id_questao]);
        $questao = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($questao && $questao['resposta_correta'] === $resposta) {
            $acertos++;
        }
    }
    
    $nota = ($acertos / $total) * 10;
    $bimestre = getBimestreAtual();
    $nomeSimulado = "Simulado do " . $bimestre . " - " . date('Y');
    
    $nomeSimuladoUnico = $nomeSimulado . " - " . date('d/m/Y H:i:s');
    
    $stmt = $pdo->prepare("INSERT INTO simulado (nome_simulado) VALUES (?)");
    $stmt->execute([$nomeSimuladoUnico]);
    $id_simulado = $pdo->lastInsertId();
    
    foreach ($respostas_aluno as $id_questao => $resposta) {
        $stmt = $pdo->prepare("INSERT INTO questoes_simulado (id_simulado, id_questao) VALUES (?, ?)");
        $stmt->execute([$id_simulado, $id_questao]);
    }
    
    $stmt = $pdo->prepare("INSERT INTO resultados (id_aluno, id_simulado, data_realizacao, nota, acertos, erros, respostas_detalhadas) VALUES (?, ?, NOW(), ?, ?, ?, ?)");
    $stmt->execute([$id_aluno, $id_simulado, $nota, $acertos, ($total - $acertos), $respostas_detalhadas]);
    
    echo json_encode([
        'success' => true,
        'acertos' => $acertos,
        'total' => $total,
        'nota' => number_format($nota, 2),
        'bimestre' => $bimestre
    ]);
    exit();
}

echo json_encode(['success' => false]);