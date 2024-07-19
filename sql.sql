CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    criado_por INT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);
CREATE TABLE chat_participantes (
    chat_id INT,
    usuario_id INT,
    adicionado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (chat_id, usuario_id),
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
CREATE TABLE mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT,
    usuario_id INT,
    mensagem TEXT NOT NULL,
    enviado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);


-- Para recuperar todas as mensagens de um grupo de conversa específico:
SELECT m.id, m.mensagem, m.enviado_em, u.nome AS usuario_nome
FROM mensagens m
JOIN usuarios u ON m.usuario_id = u.id
WHERE m.chat_id = 1
ORDER BY m.enviado_em;
-- Para listar todos os grupos de conversa de um usuário específico:
SELECT c.id, c.nome, c.criado_em
FROM chats c
JOIN chat_participantes cp ON c.id = cp.chat_id
WHERE cp.usuario_id = 1;
-- Para adicionar um novo usuário a um grupo de conversa:
-- INSERT INTO chat_participantes (chat_id, usuario_id) VALUES (1, 2);

