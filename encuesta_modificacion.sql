CREATE TABLE funcion_encuesta (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE funcion_respuesta (id INT AUTO_INCREMENT NOT NULL, funcion_encuesta_id INT NOT NULL, nombre VARCHAR(255) NOT NULL, INDEX IDX_F48A1AA45A7EBB88 (funcion_encuesta_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE funcion_respuesta ADD CONSTRAINT FK_F48A1AA45A7EBB88 FOREIGN KEY (funcion_encuesta_id) REFERENCES funcion_encuesta (id);
ALTER TABLE contrato ADD qty_gestion_encuesta INT DEFAULT NULL;
ALTER TABLE encuesta ADD funcion_encuesta_id INT DEFAULT NULL, ADD funcion_respuesta_id INT DEFAULT NULL, CHANGE fecha_creacion fecha_creacion DATETIME DEFAULT NULL;
ALTER TABLE encuesta ADD CONSTRAINT FK_B25B68415A7EBB88 FOREIGN KEY (funcion_encuesta_id) REFERENCES funcion_encuesta (id);
ALTER TABLE encuesta ADD CONSTRAINT FK_B25B6841570A09D FOREIGN KEY (funcion_respuesta_id) REFERENCES funcion_respuesta (id);
CREATE INDEX IDX_B25B68415A7EBB88 ON encuesta (funcion_encuesta_id);
CREATE INDEX IDX_B25B6841570A09D ON encuesta (funcion_respuesta_id);


INSERT INTO `funcion_encuesta` (`id`, `nombre`) VALUES
(1, 'Contacto SI'),
(2, 'Contacto NO');

INSERT INTO `funcion_respuesta` (`id`, `funcion_encuesta_id`, `nombre`) VALUES
(1, 1, 'Responde encuesta'),
(2, 1, 'No responde encuesta'),
(3, 1, 'Solicita encuesta por email'),
(4, 2, 'No responde'),
(5, 2, 'Número equivocado'),
(6, 2, 'No existe celular');


ALTER TABLE encuesta ADD estado_id INT NOT NULL;
ALTER TABLE encuesta ADD CONSTRAINT FK_B25B68419F5A440B FOREIGN KEY (estado_id) REFERENCES estado_encuesta (id);
CREATE INDEX IDX_B25B68419F5A440B ON encuesta (estado_id);

update `contrato` set qty_gestion_encuesta=0 WHERE 1;
INSERT INTO `codi_ejamtest`.`modulo` (`id`, `nombre`, `ruta`, `nombre_alt`, `descripcion`) VALUES (NULL, 'encuesta_grupo', 'encuesta_grupo', 'encuesta_grupo', 'encuesta_grupo');
ALTER TABLE encuesta ADD fecha_pendiente DATETIME DEFAULT NULL, ADD fecha_cierre DATETIME DEFAULT NULL;
update contrato set estado_encuesta_id = 3 where estado_encuesta_id is null;

        