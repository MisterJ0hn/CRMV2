<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * "cliente_historial" pasa de guardar una foto completa del cliente en cada
 * modificación a guardar solo el dato que cambió (con su valor anterior); los
 * campos que no cambiaron quedan en NULL, así que nombre, rut, correo, telefono,
 * sexo y telefono_recado dejan de ser NOT NULL.
 *
 * Solo cambia el esquema: las filas históricas ya existentes se mantienen tal cual
 * (con todos sus campos poblados).
 */
final class Version20260731120000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Hace nullable las columnas de datos de cliente_historial para registrar solo el dato que cambia';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cliente_historial
            MODIFY nombre VARCHAR(255) DEFAULT NULL,
            MODIFY rut VARCHAR(512) DEFAULT NULL,
            MODIFY correo VARCHAR(512) DEFAULT NULL,
            MODIFY telefono VARCHAR(512) DEFAULT NULL,
            MODIFY sexo VARCHAR(20) DEFAULT NULL,
            MODIFY telefono_recado VARCHAR(512) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // Las filas creadas después de esta migración pueden tener NULL en estas
        // columnas, así que se normalizan a '' antes de volver a NOT NULL.
        $this->addSql("UPDATE cliente_historial
            SET nombre = COALESCE(nombre, ''),
                rut = COALESCE(rut, ''),
                correo = COALESCE(correo, ''),
                telefono = COALESCE(telefono, ''),
                sexo = COALESCE(sexo, ''),
                telefono_recado = COALESCE(telefono_recado, '')");

        $this->addSql('ALTER TABLE cliente_historial
            MODIFY nombre VARCHAR(255) NOT NULL,
            MODIFY rut VARCHAR(512) NOT NULL,
            MODIFY correo VARCHAR(512) NOT NULL,
            MODIFY telefono VARCHAR(512) NOT NULL,
            MODIFY sexo VARCHAR(20) NOT NULL,
            MODIFY telefono_recado VARCHAR(512) NOT NULL');
    }
}
