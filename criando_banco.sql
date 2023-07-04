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
    id_sale INTEGER NOT NULL,
    value NUMERIC(8,2) NOT NULL DEFAULT 0,
    date_provider DATE NOT NULL,
    date_effective TIMESTAMP,
    CONSTRAINT fk_sale_to_parcel FOREIGN KEY (id_sale) REFERENCES sale(id)
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
 GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON posto.* TO 'mateusrauber3@gmail.com'@'localhost';

-- user basico, pode fazer apenas login e criar usuarios
 CREATE USER 'server'@'localhost' IDENTIFIED BY 'postodegasolina123';
 GRANT SELECT, INSERT ON posto.user TO 'server'@'localhost';

 
--  user apenas de leitura
 CREATE USER 'read'@'localhost' IDENTIFIED BY '123';
 GRANT SELECT ON posto.* TO 'read'@'localhost';




use posto;
-- precedures
-- vai atualizar o valor total dos fornecimentos apos a inserção de todos os produtos do fornecimento
DELIMITER //
DROP PROCEDURE IF EXISTS update_supply//
CREATE PROCEDURE update_supply(id_supply INTEGER)
BEGIN
	UPDATE supply SET total = (
    	SELECT SUM(ps.value_single) FROM product_supply AS ps WHERE ps.id_supply = id_supply
    ) WHERE id = id_supply;
END //
DELIMITER ;


-- METHODS
-- ao criar um novo produto de um fornecimento atualiza o saldo
DELIMITER //
DROP TRIGGER IF EXISTS update_stock_products_supply//
CREATE TRIGGER update_stock_products_supply
before INSERT ON product_supply FOR EACH ROW
BEGIN
    UPDATE product AS p SET p.stock = p.stock + NEW.quantity WHERE code = NEW.code_product;
END//
DELIMITER ;

-- ao deletar um produto de um fornecimento atualiza o saldo
DELIMITER //
DROP TRIGGER IF EXISTS update_stock_products_supply_delete//
CREATE TRIGGER update_stock_products_supply_delete
before DELETE ON product_supply FOR EACH ROW
BEGIN
    UPDATE product AS p SET p.stock = p.stock - old.quantity WHERE code = old.code_product;
END//
DELIMITER ;

-- ao criar um novo produto de uma venda atualiza o saldo
-- e ajusta o valor da venda
DELIMITER //
DROP TRIGGER IF EXISTS update_stock_products_sale_insert//
CREATE TRIGGER update_stock_products_sale_insert
before INSERT ON product_sale FOR EACH ROW
BEGIN
    UPDATE product AS p SET p.stock = p.stock - NEW.quantity WHERE code = NEW.code_product;
    SET new.value_single = (SELECT value_sale from product WHERE code = NEW.code_product);
END//
DELIMITER ;

-- ao deletar um produto de uma venda atualiza o saldo
DELIMITER //
DROP TRIGGER IF EXISTS update_stock_products_sale_delete//
CREATE TRIGGER update_stock_products_sale_delete
before DELETE ON product_sale FOR EACH ROW
BEGIN
    UPDATE product AS p SET p.stock = p.stock + OLD.quantity WHERE code = OLD.code_product;
END//
DELIMITER ;

DELIMITER //
DROP TRIGGER IF EXISTS update_total_products_sale//
CREATE TRIGGER update_total_products_sale
after INSERT ON product_sale FOR EACH ROW
BEGIN
    UPDATE sale AS s SET s.value_total = (SELECT calculate_order_total(NEW.id_sale)) WHERE id = NEW.id_sale;
END//
DELIMITER ;

-- function
-- usada para calcular o valor total de uma venda
DELIMITER //
DROP FUNCTION IF EXISTS calculate_order_total//
CREATE FUNCTION calculate_order_total(order_id INT)
RETURNS DECIMAL(10, 2)
BEGIN
    DECLARE total DECIMAL(10, 2);
    
    SELECT SUM(p.value_sale * ps.quantity) INTO total
    FROM sale as s
    JOIN product_sale as ps ON s.id = ps.id_sale
    JOIN product as p ON ps.code_product = p.code
    WHERE s.id = order_id;
    
    RETURN total;
END//
DELIMITER ;
