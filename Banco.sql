/* Definir a timezone do Brasil */
SET time_zone = "-03:00";

create DATABASE controle_lojinha
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_general_ci;
USE controle_lojinha;


CREATE TABLE usuarios (
  id int AUTO_INCREMENT NOT NULL,
  nome varchar(40) NOT NULL,
  email varchar(200) NOT NULL,
  senha varchar(200) NOT NULL,
  PRIMARY KEY(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

/*
    Criação do usuario padrão

login: admin@sistema.com
senha: senha123

 */
INSERT INTO `usuarios`(`nome`, `email`, `senha`) VALUES ("Admin","admin@sistema.com","$2y$10$ly6xgRjCMpUsc574bqP5KO3uSKXP6Ly07F914d3ijz9CDIFRtpb.2");

CREATE TABLE produtos (
  id int AUTO_INCREMENT NOT NULL,
  nome varchar(200) NOT NULL,
  PRIMARY KEY(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;


CREATE TABLE estoque (
  id int AUTO_INCREMENT NOT NULL,
  id_prod int NOT NULL,
  vlr_compra float NOT NULL,
  status int(1) NOT NULL,
  vlr_efetivo float NOT NULL,
  vlr_venda float NOT NULL,
  dt_compra date NOT NULL,
  PRIMARY KEY(id),
  FOREIGN KEY(id_prod) REFERENCES produtos(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

create table clientes(
	id int AUTO_INCREMENT NOT NULL,
    nome varchar(200) NOT NULL,
    cpf varchar(15),
    enedereco varchar(400),
    status int NOT NULL,
    telefone varchar(20) NOT NUll,
    PRIMARY KEY(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

CREATE TABLE pedidos (
  id int AUTO_INCREMENT NOT NULL,
  id_cliente int NOT NULL,
  data_pedido date NOT NULL,
  status int NOT NULL,
  PRIMARY KEY(id),
  FOREIGN KEY(id_cliente) REFERENCES clientes(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;


CREATE TABLE pedido_produtos (
  id_pedido int NOT NULL,
  id_produto int NOT NULL,
  preco float NOT NULL,
  PRIMARY KEY(id_pedido, id_produto),
  FOREIGN KEY(id_pedido) REFERENCES pedidos(id),
  FOREIGN KEY(id_produto) REFERENCES produtos(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;