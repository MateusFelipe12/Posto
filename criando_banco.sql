DROP DATABASE IF EXISTS posto;
CREATE DATABASE posto;
use posto;

-- PESSOA
CREATE TABLE person(
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL
);

-- PESSOA JURIDICA
CREATE TABLE legal_person(
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    -- cnpj
    cnpj NUMERIC(14,0) NOT NULL UNIQUE,
    -- nome fantasia
    name_fantasy VARCHAR(100) NOT NULL,
    -- razão social
    corporate_name VARCHAR(100) NOT NULL,
    -- inscrição estadual
    state_registration NUMERIC(12,0) UNIQUE NOT NULL,
    -- id da pessoa
    id_person INTEGER NOT NULL,
    CONSTRAINT fk_person_to_legal_person  FOREIGN KEY (id_person) REFERENCES person(id)
);

-- FORNECEDOR
CREATE TABLE provider (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    agency NUMERIC(6,0) NOT NULL,
    account NUMERIC(10,0) NOT NULL UNIQUE,
    -- ID DA PESSOA JURIDICA
    id_legal_person INTEGER NOT NULL UNIQUE,
    CONSTRAINT fk_legal_person_to_provider FOREIGN KEY (id_legal_person) REFERENCES legal_person(id)
);

-- FORNECIMENTO
CREATE TABLE supply (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total NUMERIC(10,2) NOT NULL,
    id_provider INTEGER NOT NULL,
    CONSTRAINT fk_provider_to_supply FOREIGN KEY (id_provider) REFERENCES provider(id)
);

-- CONTATO
CREATE TABLE contact (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(20) NOT NULL,
    value VARCHAR(100) NOT NULL UNIQUE,
    id_person INTEGER NOT NULL,
    CONSTRAINT fk_person_to_contact FOREIGN KEY (id_person) REFERENCES person(id) 
);

-- ENDEREÇOS
CREATE TABLE address (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    country VARCHAR(50) NOT NULL,
    uf VARCHAR(2) NOT NULL, 
    city VARCHAR(50) NOT NULL,
    neighborhood VARCHAR(50) NOT NULL,
    street VARCHAR(50) NOT NULL,
    cep NUMERIC(8,0) NOT NULL,
    id_person INTEGER NOT NULL,
    CONSTRAINT fk_person_to_address FOREIGN KEY (id_person) REFERENCES person(id)
);

-- UNIDADE DE MEDIDA
CREATE TABLE unit_measure (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    description varchar(20) NOT NULL,
    symbol VARCHAR(10) NOT NULL, 
    fractions BOOLEAN NOT NULL DEFAULT 1
);

-- PRODUTO
CREATE TABLE product (
    code INTEGER PRIMARY KEY AUTO_INCREMENT,
    description VARCHAR(100) NOT NULL,
    stock NUMERIC(8,2) NOT NULL DEFAULT 0,
    value_sale NUMERIC(8,2) NOT NULL DEFAULT 0,
    id_unit_measure INTEGER NOT NULL,
    CONSTRAINT fk_unit_measure_to_product FOREIGN KEY (id_unit_measure) REFERENCES unit_measure(id)
);

CREATE TABLE sale (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    value_total NUMERIC(8,2) NOT NULL DEFAULT 0,
    quantity_parcels NUMERIC(2,0) NOT NULL DEFAULT 1,
    person_id INTEGER,
    CONSTRAINT fk_person_to_sale  FOREIGN KEY (person_id) REFERENCES person(id)
);

-- PARCELA
CREATE TABLE parcel (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    value NUMERIC(8,2) NOT NULL DEFAULT 0,
    date_provider DATE NOT NULL,
    date_effective TIMESTAMP
);


-- PRODUTOS VENDA
CREATE TABLE product_sale (
    code_product INTEGER NOT NULL,
    id_sale INTEGER NOT NULL,
    quantity  NUMERIC(8,2) NOT NULL,
    value_single NUMERIC(8,2) NOT NULL,
    CONSTRAINT fk_product_to_product_sale FOREIGN KEY (code_product) REFERENCES product(code),
    CONSTRAINT fk_sale_to_product_sale FOREIGN KEY (id_sale) REFERENCES sale(id)
);

-- PRODUTOS FORNECIMENTO
CREATE TABLE product_supply (
    code_product INTEGER NOT NULL,
    id_supply INTEGER NOT NULL,
    quantity  NUMERIC(8,2) NOT NULL,
    value_single NUMERIC(8,2) NOT NULL,
    CONSTRAINT fk_product_to_product_supply FOREIGN KEY (code_product) REFERENCES product(code),
    CONSTRAINT fk_supply_to_product_supply FOREIGN KEY (id_supply) REFERENCES supply(id)
);

-- USER
CREATE TABLE user (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    token VARCHAR(100),
    name VARCHAR(100) NOT NULL,
    permission VARCHAR(10) NOT NULL DEFAULT 'read'
);





-- criando usuarios
-- user principal, pode fazer o crud completo
 CREATE USER 'mateusrauber3@gmail.com'@'localhost' IDENTIFIED BY '123';
 GRANT SELECT, INSERT, UPDATE, DELETE ON posto.* TO 'mateusrauber3@gmail.com'@'localhost';

-- user basico, pode fazer apenas login e criar usuarios
 CREATE USER 'server'@'localhost' IDENTIFIED BY 'postodegasolina123';
 GRANT SELECT, INSERT ON posto.user TO 'server'@'localhost';

 
--  user apenas de leitura
 CREATE USER 'read'@'localhost' IDENTIFIED BY '123';
 GRANT SELECT ON posto.* TO 'read'@'localhost';


--  CREATE USER 'read'@'localhost' IDENTIFIED BY '123';
--  GRANT SELECT ON posto.* TO 'read'@'localhost';