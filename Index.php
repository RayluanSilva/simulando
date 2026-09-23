<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="imagem/icon.png" id="icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <title>Simulando - Plataforma de Estudos</title>
    <link rel="stylesheet" href="index.css">
    <style>
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background-color: #245dc6;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background-color: #3a6bc6;
            transform: translateY(-3px);
        }

        .back-to-top::after {
            content: "↑";
            font-size: 24px;
            font-weight: bold;
        }

        .cascade {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease-out;
        }

        .cascade.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .cascade-delay-1 {
            transition-delay: 0.1s;
        }

        .cascade-delay-2 {
            transition-delay: 0.2s;
        }

        .cascade-delay-3 {
            transition-delay: 0.3s;
        }

        .cascade-delay-4 {
            transition-delay: 0.4s;
        }

        footer{
            font-family: 'Segoe UI',sans-serif;
            background-color: #245dc6;
            padding: 25px;
        }
        .mb-2 {
            color: aliceblue;
        }
        .mb-3 {
            color: aliceblue;
        }
        .mb-0 {
            color: aliceblue;
        }
    </style>
</head>

<body>
    <div id="topo">
        <a href="index.php"><img src="imagem/logo.png" alt="simulando" id="logo"> </a>
                <div id="nav">
                    <nav>
                        <a href="#sobre">SOBRE</a>
                        <a href="#funcionamento">COMO FUNCIONA</a>
                        <a href="#recursos">RECURSOS</a>
                        <a href="#somos">QUEM SOMOS</a>
                    </nav>
                    <div class="container-entrar">
                        <a href="loginescolha.php" class="botao-entrar">ENTRAR
                        <div class="moldura-porta"></div>
                        <div class="porta"></div>
                        <div class="boneco"></div></a>
                    </div>
                </div>
    </div>
    <div id="topo2"></div>
    <div id="meio1">
        <h1 id="h1menu1" class="cascade cascade-delay-1">ESTUDE PARA O VESTIBULAR DE FORMA PRÁTICA E DIVERTIDA!</h1>
        <p id="pmenu1" class="cascade cascade-delay-2">O Simulando é uma plataforma gratuita que oferece a interação
            entre professores e alunos através
            de simulados com correção automática e análise de
            desempenho para ajuda-los a se prepararem para o ENEM e outros vestibulares de forma eficiente e
            acessível.</p>
        <div></div>
        <div>
            <a href="loginescolha.php" id="btcomecar" class="cascade cascade-delay-3">COMEÇAR AGORA</a>
            <a href="#funcionamento" id="btsaibamais" class="cascade cascade-delay-4">SAIBA MAIS</a>
        </div>
    </div>
    </div>
    <div id="sobre">
        <h1 id="h1meio2" class="cascade cascade-delay-1">SOBRE O PROJETO</h1>
        <p id="pmeio2" class="cascade cascade-delay-2">O Simulando surgiu para transformar a preparação para
            vestibulares através da interação entre
            professores e alunos. Nossa plataforma conecta educadores e estudantes em um ambiente colaborativo, onde
            simulados personalizados se tornam ferramentas poderosas de aprendizagem.</p>
        <p id="pmeio2" class="cascade cascade-delay-2">Professores podem criar provas adaptadas às necessidades de suas
            turmas, enquanto os alunos
            praticam com questões alinhadas ao ENEM e principais vestibulares, recebendo correções automáticas e
            feedback detalhado. Essa dinâmica permite identificar exatamente quais conteúdos precisam de reforço,
            tornando o estudo mais direcionado e eficiente.</p>
        <p id="pmeio2" class="cascade cascade-delay-3">Acreditamos que essa conexão entre orientação docente e prática
            discente é o caminho para
            resultados excepcionais - democratizando o acesso a uma educação de qualidade, sem custos, em qualquer lugar
            do Brasil.</p>

        <div id="card1" class="cascade cascade-delay-1">
            <div id="cardinteratividade"><img src="imagem/profaluno.png" alt="" id="cardinteratividade"></div>
            <h3><i class="fa-solid fa-people-group"></i> Interatividade</h3>
            <p>Relação direta entre professores e alunos através das avaliações.</p>
        </div>
        <div id="card2" class="cascade cascade-delay-2">
            <div id="cardanalise"><img src="imagem/grafico.png" alt="" id="cardanalise"></div>
            <h3><i class="fa-solid fa-bars-progress"></i> Análise de Desempenho</h3>
            <p>Relatórios detalhados mostrando seus pontos fortes e fracos em cada área do conhecimento.</p>
        </div>
        <div id="card3" class="cascade cascade-delay-3">
            <div id="cardcorreção"><img src="imagem/verifi.png" alt="" id="cardcorreção"></div>
            <h3><i class="fa-solid fa-robot"></i> Correção Automática</h3>
            <p>Resultado imediato das suas avaliações com correção automática.</p>
        </div>
    </div>
    <div id="funcionamento">
        <h1 id="h1meio3" class="cascade cascade-delay-1">COMO FUNCIONA</h1>
        <p id="pmeio3" class="cascade cascade-delay-2">Nosso sistema foi projetado para ser simples e eficiente. Em
            poucos passos, você pode começar a
            melhorar seu desempenho nos vestibulares</p>
        <div id="card1meio3" class="cascade cascade-delay-1">
            <div id="cardn1"><img src="imagem/n1.png" alt="" id="cardn1"></div>
            <h3><i class="fa-solid fa-envelope"></i> Crie sua conta</h3>
            <p>Cadastre-se como professor ou aluno completamente de graça.</p>
        </div>
        <div id="card2meio3" class="cascade cascade-delay-2">
            <div id="cardn2"><img src="imagem/n2.png" alt="" id="cardn2"></div>
            <h3><i class="fa-solid fa-file"></i> Realize seu simulado</h3>
            <p>Selecione entre simulados ja criados pelos professores e teste seus conhecimentos.</p>
        </div>
        <div id="card3meio3" class="cascade cascade-delay-3">
            <div id="cardn3"><img src="imagem/n3.png" alt="" id="cardn3"></div>
            <h3><i class="fa-solid fa-circle-question"></i> Resolva as questões</h3>
            <p>Nossa interface limpa e organizada permite que você se concentre apenas no que importa: aprender.</p>
        </div>
        <div id="card4meio3" class="cascade cascade-delay-4">
            <div id="cardn4"><img src="imagem/n4.png" alt="" id="cardn4"></div>
            <h3><i class="fa-solid fa-chart-simple"></i> Analise seus resultados</h3>
            <p>Receba imediatamente seu desempenho detalhado com dicas para melhorar em cada área.</p>
        </div>
        <p id="pmeio3" class="cascade cascade-delay-3">Além disso, você pode acompanhar sua evolução ao longo do tempo,
            comparar seu desempenho com outros
            estudantes e receber recomendações de conteúdo para estudo baseadas em suas dificuldades.</p>
    </div>
    <div id="recursos">
        <h1 id="h1meio4" class="cascade cascade-delay-1">RECURSOS</h1>
        <p id="pmeio4" class="cascade cascade-delay-2">Nosso sistema foi projetado para ser simples e eficiente. Em
            poucos passos, você pode começar a
            melhorar seu desempenho nos vestibulares</p>
        <div id="card1meio4" class="cascade cascade-delay-1">
            <div id="cardcronometro"><img src="imagem/cronometro.png" alt="" id="cardcronometro"></div>
            <h3><i class="fa-solid fa-hourglass-end"></i> Cronômetro Inteligente</h3>
            <p>Treine seu tempo de resposta como no dia da prova real, com alertas quando estiver gastando muito tempo
                em
                uma questão.</p>
        </div>
        <div id="card2meio4" class="cascade cascade-delay-2">
            <div id="cardevolucao"><img src="imagem/evolucao.png" alt="" id="cardevolucao"></div>
            <h3><i class="fa-solid fa-chart-line"></i> Evolução Temporal</h3>
            <p>Gráficos mostrando sua melhora em cada disciplina ao longo das semanas e meses.</p>
        </div>
        <div id="card3meio4" class="cascade cascade-delay-3">
            <div id="cardranking"><img src="imagem/ranking.png" alt="" id="cardranking"></div>
            <h3><i class="fa-solid fa-ranking-star"></i> Ranking Motivacional</h3>
            <p>Compare seu desempenho com outros estudantes de forma saudável e motivadora.</p>
        </div>
        <div id="card4meio4" class="cascade cascade-delay-4">
            <div id="cardmobile"><img src="imagem/mobile.png" alt="" id="cardmobile"></div>
            <h3><i class="fa-solid fa-mobile"></i> Acesso Mobile</h3>
            <p>Estude onde e quando quiser com nossa plataforma totalmente adaptada para celulares e tablets.</p>
        </div>
    </div>
    <div id="somos">
        <h1 id="h1meio5" class="cascade cascade-delay-1">QUEM SOMOS</h1>
        <div id="somospng" class="cascade cascade-delay-2">
            <img src="imagem/somos.jpg" alt="" id="somospng">
        </div>
        <div id="divpmeio5">
            <p id="pmeio5" class="cascade cascade-delay-3">Somos um grupo de estudantes apaixonados por educação e
                tecnologia, que acredita no poder da
                colaboração para transformar o aprendizado. Nossa missão é democratizar o acesso a uma educação de
                qualidade, conectando professores e alunos em uma plataforma inovadora e acessível.</p>
            <p id="pmeio5" class="cascade cascade-delay-4">Com o Simulando, queremos criar um ambiente onde todos possam
                aprender, ensinar e evoluir juntos,
                superando barreiras geográficas e financeiras.</p>
            <p id="pmeio5" class="cascade cascade-delay-5">Utilizamos a tecnologia para personalizar a experiência de
                aprendizado, oferecendo simulados dinâmicos, análises de desempenho e recursos pensados para o estudante
                moderno. Aqui, cada jornada é única, e o conhecimento é construído de forma colaborativa e contínua.</p>
        </div>
    </div>

    <div class="back-to-top"></div>
    <footer #"footer">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <img src="imagem/logo.png" alt="LOGO" class="img-fluid mb-3" style="max-height: 50px;">
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-4">
                <h5 class="mb-3"><i class="fas fa-home me-2"></i> Início</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-white text-decoration-none"><i class="fas fa-chevron-right me-2"></i> Home</a></li>
                    <li class="mb-2"><a href="#sobre" class="text-white text-decoration-none"><i class="fas fa-chevron-right me-2"></i> Sobre</a></li>
                    <li><a href="#somos" class="text-white text-decoration-none"><i class="fas fa-chevron-right me-2"></i> Quem somos</a></li>
                </ul>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-4">
                <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i> Sobre nós</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#sobre" class="text-white text-decoration-none"><i class="fas fa-chevron-right me-2"></i> Informações da Empresa</a></li>
                    <li class="mb-2"><a href="#footer" class="text-white text-decoration-none"><i class="fas fa-chevron-right me-2"></i> Contato</a></li>
                    <li><a href="#sobre" class="text-white text-decoration-none"><i class="fas fa-chevron-right me-2"></i> Blog</a></li>
                </ul>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-4">
                <h5 class="mb-3"><i class="fas fa-headset me-2"></i> Suporte</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#footer" class="text-white text-decoration-none"><i class="fas fa-chevron-right me-2"></i> FAQ</a></li>
                    <li class="mb-2"><a href="#footer" class="text-white text-decoration-none"><i class="fas fa-phone me-2"></i> Telefones</a></li>
                    <li><a href="#footer" class="text-white text-decoration-none"><i class="fas fa-comments me-2"></i> Chat</a></li>
                </ul>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-4">
                <h5 class="mb-3"><i class="fas fa-id-card me-2"></i> Contato</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-envelope me-2"></i> simulando@gmail.com</li>
                    <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> Cabrália Paulista, Brasil</li>
                    <li class="mb-2"><i class="fas fa-clock me-2"></i> Seg-Sex: 9h às 18h</li>
                </ul>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12 text-center pt-3 border-top">
                <p class="mb-0">2025 Copyright - Simulando</p>
            </div>
        </div>
    </div>
</footer>
    <script>
        document.querySelector('.container-entrar').addEventListener('click', function() {
            this.classList.add('clicado');
            setTimeout(() => {
                this.classList.remove('clicado');
            }, 1000);
        });

        document.addEventListener('DOMContentLoaded', function() {
            const backToTopButton = document.querySelector('.back-to-top');

            const cascadeElements = document.querySelectorAll('.cascade');

            function checkVisibility() {
                cascadeElements.forEach(element => {
                    const elementTop = element.getBoundingClientRect().top;
                    const elementBottom = element.getBoundingClientRect().bottom;

                    if (elementTop < window.innerHeight - 100 && elementBottom > 0) {
                        element.classList.add('visible');
                    }
                });
            }
            checkVisibility();
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 100) {
                    backToTopButton.classList.add('visible');
                } else {
                    backToTopButton.classList.remove('visible');
                }

                checkVisibility();
            });

            backToTopButton.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>

</html>