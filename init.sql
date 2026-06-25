CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  token_compartilhamento VARCHAR(64) UNIQUE,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE palpites_jogos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  jogo_id VARCHAR(50) NOT NULL,
  resultado_escolhido VARCHAR(20) NOT NULL, -- 'CASA', 'VISITANTE' ou 'EMPATE'
  fase VARCHAR(50),
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  UNIQUE KEY unique_palpite (usuario_id, jogo_id) -- Evita que a pessoa vote duas vezes no mesmo jogo
);

CREATE TABLE palpites_campeao (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  time_campeao_id VARCHAR(50) NOT NULL,
  time_vice_id VARCHAR(50),
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  UNIQUE KEY unique_campeao (usuario_id) -- Cada usuário tem apenas 1 palpite de campeão
);
