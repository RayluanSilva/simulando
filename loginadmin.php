<?php
session_start();
require_once 'conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    if (empty($nome_usuario) || empty($senha)) {
        $erro = "Todos os campos são obrigatórios!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM administradores WHERE nome_usuario = ? AND senha = ?");
        $stmt->execute([$nome_usuario, $senha]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            $_SESSION['usuario_id'] = $admin['id'];
            $_SESSION['usuario_nome'] = $admin['nome_usuario'];
            $_SESSION['usuario_tipo'] = 'admin';
            
            header("Location: admin.php");
            exit();
        } else {
            $erro = "Usuário ou senha incorretos!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulando - Login Admin</title>
    <link rel="icon" type="image/x-icon" href="imagem/icon.png" id="icon">
    <link rel="stylesheet" href="loginadmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div id="topo">
        <a href="index.php"><img src="imagem/logo.png" alt="simulando" id="logo"></a>
    </div>
    <div id="topo2"></div>
    <div id="containerform">
        <div id="boneco">
            <h1 id="h1form">BEM-VINDO, ADMINISTRADOR (A)!</h1>
            <div class="boneco admin">
                <div class="cabelo"></div>
                <div class="cabeca">
                    <div class="sobrancelha sobrancelha-esquerda"></div>
                    <div class="sobrancelha sobrancelha-direita"></div>
                    <div class="olho olho-esquerdo"></div>
                    <div class="olho olho-direito"></div>
                    <div class="boca"></div>
                </div>
                <div class="corpo">
                    <div class="gravata"></div>
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
        <div id="admin">
            <div id="formadmin">
                <?php if (!empty($erro)): ?>
                    <div style="color: red; text-align: center; margin-bottom: 20px; font-family:'Segoe UI', sans-serif; text-transform:uppercase; font-weight:bold;"><?php echo $erro; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <h1 id="h1formadmin">INSIRA SEUS DADOS!</h1>
                    <label for="usuario" id="lbusuario"><i class="fa-solid fa-user"></i> USUÁRIO:</label>
                    <input type="text" id="ipusuario" name="usuario" placeholder="Digite seu nome de usuário" required>
                    <label for="senha" id="lbsenha"><i class="fa-solid fa-key"></i> SENHA:</label>
                    <input type="password" id="ipsenha" name="senha" placeholder="Digite sua senha" required>
                    <button type="submit" id="btnentrar"><i class="fa-solid fa-right-to-bracket"></i> ENTRAR</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>