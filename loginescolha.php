<?php
session_start();
?>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulando - Escolha de Login</title>
    <link rel="icon" type="image/x-icon" href="imagem/icon.png" id="icon">
    <link rel="stylesheet" href="loginescolha.css">
    <style>
        body.professor-hover {
            background-color: #175499 !important;
        }

        body.aluno-hover {
            background-color: #2ea0d9 !important;
        }
        
        body.admin-hover {
            background-color: rgb(23, 65, 109) !important; 
        }
        
        .cards-container {
            position: relative;
            width: 100%;
            min-height: 100vh;
        }
    </style>
</head>

<body>
    <div id="topo">
        <a href="index.php"><img src="imagem/logo.png" alt="simulando" id="logo"></a>
    </div>
    <div id="topo2"></div>
    <div id="divh1">
        <h1 id="h1body">QUEM É VOCÊ</h1>
    </div>
    <div class="cards-container">
        <a href="loginprofessor.php">
            <div id="cardprofessor" onmouseover="document.body.classList.add('professor-hover')" onmouseout="document.body.classList.remove('professor-hover')">
                <h2 id="h2professor">PROFESSOR</h2>
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
                <p id="pdivprofessor">Crie e gerencie suas turmas, aplique provas e acompanhe o desempenho dos alunos.</p>
            </div>
        </a>
        
        <a href="loginadmin.php">
            <div id="cardadmin" onmouseover="document.body.classList.add('admin-hover')" onmouseout="document.body.classList.remove('admin-hover')">
                <h2 id="h2admin">ADMINISTRADOR</h2>
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
                <p id="pdivadmin">Controle total da plataforma: gerencie usuários, questões e resultados de simulados.</p>
            </div>
        </a>
        
        <a href="loginaluno.php">
            <div id="cardaluno" onmouseover="document.body.classList.add('aluno-hover')" onmouseout="document.body.classList.remove('aluno-hover')">
                <h2 id="h2aluno">ALUNO</h2>
                <div class="boneco aluno">
                    <div class="cabelo"></div>
                    <div class="cabeca">
                        <div class="sobrancelha sobrancelha-esquerda"></div>
                        <div class="sobrancelha sobrancelha-direita"></div>
                        <div class="olho olho-esquerdo"></div>
                        <div class="olho olho-direito"></div>
                        <div class="boca"></div>
                    </div>
                    <div class="corpo">
                        <div class="bolso"></div>
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
                <p id="pdivaluno">Acesse conteúdos, faça provas e acompanhe seu progresso de forma simples e intuitiva.</p>
            </div>
        </a>
    </div>
    
</body>

</html>