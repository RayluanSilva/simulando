<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_usuario = $_POST['usuario'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confsenha'] ?? '';
    $rm = $_POST['codigo'] ?? '';
    $curso = $_POST['curso'] ?? '';

    if (empty($nome_usuario) || empty($email) || empty($senha) || empty($confirmar_senha) || empty($rm) || empty($curso)) {
        $erro = "Todos os campos são obrigatórios!";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem!";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM alunos WHERE nome_usuario = ? OR email = ?");
        $stmt->execute([$nome_usuario, $email]);
        if ($stmt->rowCount() > 0) {
            $erro = "Nome de usuário ou e-mail já cadastrado!";
        } else {


            $stmt = $pdo->prepare("INSERT INTO alunos (nome_usuario, email, senha, rm, curso) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$nome_usuario, $email, $senha, $rm, $curso])) {
                $sucesso = "Cadastro realizado com sucesso!";
                header("Refresh: 2; url=loginaluno.php");
            } else {
                $erro = "Erro ao cadastrar. Tente novamente.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulando - Cadastro Aluno</title>
    <link rel="icon" type="image/x-icon" href="imagem/icon.png" id="icon">
    <link rel="stylesheet" href="cadastroaluno.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div id="topo">
        <a href="index.php"><img src="imagem/logo.png" alt="simulando" id="logo"></a>
    </div>
    <div id="topo2"></div>
    <div id="containerform">
        <div id="aluno">
            <div id="formaluno">
                <?php if (isset($erro)): ?>
                    <div style="color: red; text-align: center; margin-bottom: 20px; font-family:'Segoe UI', sans-serif; text-transform:uppercase; font-weight:bold;"><?php echo $erro; ?></div>
                <?php elseif (isset($sucesso)): ?>
                    <div style="color: green; text-align: center; margin-bottom: 20px; font-family:'Segoe UI', sans-serif; text-transform:uppercase; font-weight:bold;"><?php echo $sucesso; ?></div>
                <?php endif; ?>

                <form action="cadastroaluno.php" method="post">
                    <h1 id="h1formaluno">FALE MAIS SOBRE VOCÊ!</h1>
                    <label for="usuario" id="lbusuario"><i class="fa-solid fa-user"></i> USUÁRIO:</label>
                    <input type="text" id="ipusuario" name="usuario" placeholder="Digite seu nome de usuário" required>
                    <label for="email" id="lbemail"><i class="fa-solid fa-envelope"></i> E-MAIL:</label>
                    <input type="email" id="ipemail" name="email" placeholder="Digite seu e-mail" required>
                    <div class="senha-container">
                        <div class="senha-item">
                            <label for="senha" id="lbsenha"><i class="fa-solid fa-key"></i> SENHA:</label>
                            <div class="senha-input-container">
                                <input type="password" id="ipsenha" name="senha" placeholder="Digite sua senha" required>
                                <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('ipsenha', this)"></i>
                            </div>
                        </div>
                        <div class="senha-item">
                            <label for="senha" id="lbconfsenha"><i class="fa-solid fa-key"></i> CONFIRMAR SENHA:</label>
                            <div class="senha-input-container">
                                <input type="password" id="ipconfsenha" name="confsenha" placeholder="Repita sua senha" required>
                                <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('ipconfsenha', this)"></i>
                            </div>
                        </div>
                    </div>
                    <label for="codigo" id="lbcodigo"><i class="fa-solid fa-lock-open"></i> R.M:</label>
                    <input type="number" id="ipcodigo" name="codigo" placeholder="Digite seu R.M escolar" required>
                    <label for="curso" id="lbcurso"><i class="fa-solid fa-people-group"></i> CURSO:</label>
                    <select name="curso" id="slccurso">
                        <option value="todas">Selecione sua turma</option>
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
                    <button type="submit" id="btncadastrar"><i class="fa-solid fa-right-to-bracket"></i> CADASTRAR</button>
                    <h1 id="h1cadastro">JÁ POSSUI UMA CONTA?</h1>
                    <button type="button" id="btnlogar" onclick="window.location.href='loginaluno.php'"> LOGAR</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>