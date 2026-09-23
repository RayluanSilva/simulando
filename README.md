# Simulando

Plataforma web de simulados com correção automática, desenvolvida como **Trabalho de Conclusão de Curso** do técnico em Informática para Internet (ETEC Astor de Mattos Carvalho, 2025).

**Acesse online:** https://simulando.byethost15.com/

## O que a plataforma faz

O professor cadastra questões e monta simulados; o aluno faz a prova com tempo marcado e recebe a nota na hora; o administrador gerencia usuários e conteúdo.

**Aluno**
- Cadastro e login
- Simulados cronometrados (60 minutos) com correção automática
- Página de resultados com acertos, erros e desempenho detalhado

**Professor**
- Login com código de acesso
- Cadastro de questões (com imagem) e gerador de simulados
- Acompanhamento dos resultados da turma, com exportação para Excel

**Administrador**
- Painel com visão geral da plataforma
- Gerenciamento de alunos, professores, questões e simulados

## Tecnologias

- **Back-end:** PHP com PDO (consultas preparadas)
- **Banco de dados:** MySQL / MariaDB
- **Front-end:** HTML5, CSS3, JavaScript e Bootstrap 5
- **Ícones:** Font Awesome

## Estrutura

```
Index.php                  página inicial
loginescolha.php           escolha do tipo de acesso (aluno, professor ou admin)
loginaluno.php / cadastroaluno.php / loginprofessor.php / loginadmin.php
aluno.php                  painel do aluno
simulado.php               realização do simulado (cronômetro e correção)
resultadosaluno.php        resultados do aluno
professor.php              painel do professor
gerador.php                criação de questões e simulados
resultados.php             resultados da turma e exportação para Excel
admin.php                  painel do administrador
usuarioadmin.php / questoesadmin.php / simuladosadmin.php   gerenciamento
salvar_resultado.php / get_simulado_detalhado.php           endpoints usados via JavaScript
conexao.php                conexão com o banco de dados (PDO)
database.sql               estrutura das tabelas e usuários de exemplo
*.css                      estilos de cada página
imagem/                    logo e imagens
```

## Como rodar localmente

1. Instale o [XAMPP](https://www.apachefriends.org/) (Apache + MySQL).
2. Copie a pasta do projeto para `htdocs/simulando`.
3. No phpMyAdmin, crie um banco chamado `simulando` e importe o arquivo `database.sql`.
4. Se precisar, ajuste usuário e senha do banco em `conexao.php`.
5. Acesse `http://localhost/simulando/Index.php`.

Usuários de exemplo criados pelo `database.sql` (apenas para uso local):

| Perfil | Usuário | Senha | Código |
|---|---|---|---|
| Administrador | admin | admin123 | — |
| Professor | Professor | professor1234 | 1234 |

## Próximos passos

- Guardar as senhas com `password_hash()` e `password_verify()` em vez de texto puro
- Mover as credenciais do banco para variáveis de ambiente

## Autor

Rayluan Silva · [LinkedIn](https://www.linkedin.com/in/rayluan-silva) · [Portfólio](https://rayluansilva.github.io/Portfolio_Rayluan/)
