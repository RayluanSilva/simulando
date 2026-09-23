CREATE TABLE administradores (
  id int(11) NOT NULL,
  nome_usuario varchar(50) NOT NULL,
  senha varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO administradores (id, nome_usuario, senha) VALUES
(1, 'admin', 'admin123');

CREATE TABLE alunos (
  id int(11) NOT NULL,
  nome_usuario varchar(50) NOT NULL,
  email varchar(100) NOT NULL,
  senha varchar(255) NOT NULL,
  rm varchar(20) NOT NULL,
  curso varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE professores (
  id int(11) NOT NULL,
  nome_usuario varchar(50) NOT NULL,
  senha varchar(255) NOT NULL,
  codigo_acesso varchar(20) NOT NULL,
  materia varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO professores (id, nome_usuario, senha, codigo_acesso, materia) VALUES
(1, 'Professor', 'professor1234', '1234', 'Exemplo');

CREATE TABLE questoes (
  id_questao int(11) NOT NULL,
  disciplina varchar(50) NOT NULL,
  topico varchar(100) DEFAULT NULL,
  enunciado text NOT NULL,
  alternativa_a text NOT NULL,
  alternativa_b text NOT NULL,
  alternativa_c text NOT NULL,
  alternativa_d text NOT NULL,
  resposta_correta char(1) NOT NULL,
  id_professor int(11) DEFAULT NULL,
  curso varchar(50) NOT NULL,
  data_criacao timestamp NOT NULL DEFAULT current_timestamp(),
  imagem longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE questoes_simulado (
  id int(11) NOT NULL,
  id_questao int(11) NOT NULL,
  id_simulado int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE resultados (
  id_resultado int(11) NOT NULL,
  id_aluno int(11) NOT NULL,
  id_simulado int(11) NOT NULL,
  data_realizacao datetime NOT NULL,
  nota decimal(5,2) NOT NULL,
  acertos int(11) NOT NULL,
  erros int(11) NOT NULL,
  respostas_detalhadas text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE simulado (
  id_simulado int(11) NOT NULL,
  nome_simulado varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


ALTER TABLE administradores
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY nome_usuario (nome_usuario);

ALTER TABLE alunos
  ADD PRIMARY KEY (id);

ALTER TABLE professores
  ADD PRIMARY KEY (id);

ALTER TABLE questoes
  ADD PRIMARY KEY (id_questao),
  ADD KEY id_professor (id_professor);

ALTER TABLE questoes_simulado
  ADD PRIMARY KEY (id),
  ADD KEY id_questao (id_questao);

ALTER TABLE resultados
  ADD PRIMARY KEY (id_resultado),
  ADD KEY id_aluno (id_aluno),
  ADD KEY id_simulado (id_simulado);

ALTER TABLE simulado
  ADD PRIMARY KEY (id_simulado);


ALTER TABLE administradores
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE alunos
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

ALTER TABLE professores
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE questoes
  MODIFY id_questao int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

ALTER TABLE questoes_simulado
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

ALTER TABLE resultados
  MODIFY id_resultado int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

ALTER TABLE simulado
  MODIFY id_simulado int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;


ALTER TABLE questoes
  ADD CONSTRAINT questoes_ibfk_1 FOREIGN KEY (id_professor) REFERENCES professores (id);

ALTER TABLE questoes_simulado
  ADD CONSTRAINT questoes_simulado_ibfk_2 FOREIGN KEY (id_questao) REFERENCES questoes (id_questao);

ALTER TABLE resultados
  ADD CONSTRAINT resultados_ibfk_1 FOREIGN KEY (id_aluno) REFERENCES alunos (id),
  ADD CONSTRAINT resultados_ibfk_2 FOREIGN KEY (id_simulado) REFERENCES simulado (id_simulado);
COMMIT;