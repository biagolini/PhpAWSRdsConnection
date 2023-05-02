-- Drop the existing CustomerData database if it exists
DROP DATABASE IF EXISTS CustomerData;
-- Create the CustomerData database
CREATE DATABASE CustomerData;
-- Create the tblTypeState table
CREATE TABLE CustomerData.tblTypeState (
 idTypeState INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
 dsAbbreviation VARCHAR(2) NOT NULL,
 dsType VARCHAR(100) NOT NULL,
 stActive BOOLEAN NOT NULL DEFAULT 1
);
-- Insert data into tblTypeState table
INSERT INTO CustomerData.tblTypeState(dsAbbreviation,dsType) VALUES
('AC','Acre'),
('AL','Alagoas'),
('AP','Amapa'),
('AM','Amazonas'),
('BA','Bahia'),
('CE','Ceara'),
('DF','Distrito Federal'),
('ES','Esporito Santo'),
('GO','Goias'),
('MA','Maranhao'),
('MT','Mato Grosso'),
('MS','Mato Grosso do Sul'),
('MG','Minas Gerais'),
('PA','Para'),
('PB','Paraiba'),
('PR','Parana'),
('PE','Pernambuco'),
('PI','Piaui'),
('RJ','Rio de Janeiro'),
('RN','Rio Grande do Norte'),
('RS','Rio Grande do Sul'),
('RO','Rondonia'),
('RR','Roraima'),
('SC','Santa Catarina'),
('SP','Sao Paulo'),
('SE','Sergipe'),
('TO','Tocantins');