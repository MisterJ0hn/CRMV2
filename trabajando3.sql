CREATE TABLE importancia (id INT AUTO_INCREMENT NOT NULL, urgencia VARCHAR(255) NOT NULL, categorizacion VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE ticket ADD importancia_id INT DEFAULT NULL;
ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3C56AE4E9 FOREIGN KEY (importancia_id) REFERENCES importancia (id);
CREATE INDEX IDX_97A0ADA3C56AE4E9 ON ticket (importancia_id);

insert into importancia (Urgencia,categorizacion) value ('URGENTE','NOTIFICACION');
insert into importancia (Urgencia,categorizacion) value ('URGENTE','REDACCION ESCRITURA');
insert into importancia (Urgencia,categorizacion) value ('URGENTE','CLIENTE RECLAMA X GESTION ABOGADO');
insert into importancia (Urgencia,categorizacion) value ('URGENTE','EMBARGO');
insert into importancia (Urgencia,categorizacion) value ('URGENTE','CLIENTE SOLICITA AVANCE CAUSA Y CONDICIONA PAGO');
insert into importancia (Urgencia,categorizacion) value ('URGENTE','CLIENTE SOLICITA NUEVO SERVICIO');
insert into importancia (Urgencia,categorizacion) value ('NORMAL','CLIENTE AVISA ENTREGA O ENVIO DE DOCUMENTOS');
insert into importancia (Urgencia,categorizacion) value ('NORMAL','CLIENTES SOLICITA AVANCE CAUSA');
insert into importancia (Urgencia,categorizacion) value ('NORMAL','DESCONOCE CONTRATO');
insert into importancia (Urgencia,categorizacion) value ('NORMAL','DESISTE CONTRATO');

INSERT INTO `modulo` (`id`, `nombre`, `ruta`, `nombre_alt`, `descripcion`) VALUES (NULL, 'ticket_reasignar', 'ticket_reasignar', 'Resignar Ticket', 'Reasigna el usuario encargado del ticket');
