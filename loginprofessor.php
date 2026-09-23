<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $codigo = $_POST['codigo'] ?? '';

    if (empty($nome_usuario) || empty($senha) || empty($codigo)) {
        $erro = "Todos os campos são obrigatórios!";
    } else {

        $stmt = $pdo->prepare("SELECT * FROM professores WHERE nome_usuario = ? AND codigo_acesso = ?");
        $stmt->execute([$nome_usuario, $codigo]);
        $professor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($professor && $senha === $professor['senha']) {

            $_SESSION['usuario_id'] = $professor['id'];
            $_SESSION['usuario_nome'] = $professor['nome_usuario'];
            $_SESSION['usuario_tipo'] = 'professor';
            
            header("Location: professor.php");
            exit();
        } else {
            $erro = "Credenciais inválidas!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulando - Login Professor</title>
    <link rel="icon" type="image/x-icon" href="imagem/icon.png" id="icon">
    <link rel="stylesheet" href="loginprofessor.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div id="topo">
        <a href="index.php"><img src="imagem/logo.png" alt="simulando" id="logo"></a>
    </div>
    <div id="topo2"></div>
    <div id="containerform">
        <div id="boneco">
            <h1 id="h1form">BEM-VINDO, PROFESSOR(A)!</h1>
            <div class="boneco professor">
                <div class="cabelo"></div>
                <div class="cabeca">
                    <div class="sobrancelha sobrancelha-esquerda"></div>
                    <div class="sobrancelha sobrancelha-direita"></div>
                    <div class="olho olho-esquerdo"></div>
                    <div class="olho olho-direito"></div>
                    <div class="boca"></div>
                </div>
                <div class="corpo">
                    <div class="colarinho"></div>
                </div>
                <div class="braco braco-esquerdo">
                    <div class="antebraco"></div>
                    <div class="mao">
                        <div class="dedo"></div>
                        <div class="dedo"></div>
                        <div class="dedo"></div>
                        <div class="dedo"></div>
                    </div>
                </div>
                <div class="braco braco-direito">
                    <div class="antebraco"></div>
                    <div class="mao">
                        <div class="dedo"></div>
                        <div class="dedo"></div>
                        <div class="dedo"></div>
                        <div class="dedo"></div>
                    </div>
                </div>
                <div class="perna-container">
                    <div class="perna perna-esquerda">
                        <div class="coxa"></div>
                        <div class="canela"></div>
                        <div class="pe"></div>
                    </div>
                    <div class="perna perna-direita">
                        <div class="coxa"></div>
                        <div class="canela"></div>
                        <div class="pe"></div>
                    </div>
                </div>
            </div>
        </div>
        <div id="professor">
            <div id="formprofessor">
                <?php if (isset($erro)): ?>
                    <div style="color: red; text-align: center; margin-bottom: 20px; font-family:'Segoe UI', sans-serif; text-transform:uppercase; font-weight:bold;"><?php echo $erro; ?></div>
                <?php endif; ?>
                
                <form action="loginprofessor.php" method="post">
                    <h1 id="h1formprofessor">INSIRA SEUS DADOS!</h1>
                    <label for="usuario" id="lbusuario"><i class="fa-solid fa-user"></i> USUÁRIO:</label>
                    <input type="text" id="ipusuario" name="usuario" placeholder="Digite seu nome de usuário" required>
                    <label for="senha" id="lbsenha"><i class="fa-solid fa-key"></i> SENHA:</label>
                    <input type="password" id="ipsenha" name="senha" placeholder="Digite sua senha" required>
                    <label for="codigo" id="lbcodigo"><i class="fa-solid fa-lock-open"></i> CÓDIGO:</label>
                    <input type="number" id="ipcodigo" name="codigo" placeholder="Digite seu código de acesso" required>
                    <button type="submit" id="btnentrar"><i class="fa-solid fa-right-to-bracket"></i>   ENTRAR</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>