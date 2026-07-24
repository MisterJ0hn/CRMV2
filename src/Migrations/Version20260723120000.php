<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Prepara "cliente" y "cliente_historial" para cifrar rut, telefono, telefono_recado,
 * direccion y clave_unica (AES-256-GCM, ver App\Security\Cifrado):
 *  - Ensancha esas columnas a VARCHAR(512), porque el valor cifrado+base64 ocupa
 *    más espacio que el texto plano original (podía llegar a superar 255 caracteres).
 *  - Agrega rut_hash / telefono_hash / telefono_recado_hash en "cliente" (HMAC-SHA256),
 *    usadas para búsquedas exactas ya que la columna cifrada no es comparable directamente.
 *  - "cliente_historial" es solo un log de auditoría (no se busca por estos campos),
 *    así que no lleva columnas hash.
 *
 * Esta migración solo cambia el esquema. El backfill de los datos ya existentes
 * (cifrar lo que hoy está en texto plano y calcular los hashes) se hace con el
 * comando app:cifrar-datos-cliente, que debe correrse después de desplegar esto.
 */
final class Version20260723120000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Ensancha columnas sensibles de cliente/cliente_historial para cifrado y agrega columnas hash de búsqueda en cliente';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cliente
            MODIFY rut VARCHAR(512) NOT NULL,
            MODIFY telefono VARCHAR(512) NOT NULL,
            MODIFY telefono_recado VARCHAR(512) NOT NULL,
            MODIFY direccion VARCHAR(512) DEFAULT NULL,
            MODIFY clave_unica VARCHAR(512) DEFAULT NULL,
            ADD rut_hash CHAR(64) DEFAULT NULL,
            ADD telefono_hash CHAR(64) DEFAULT NULL,
            ADD telefono_recado_hash CHAR(64) DEFAULT NULL');

        $this->addSql('CREATE INDEX IDX_cliente_rut_hash ON cliente (rut_hash)');
        $this->addSql('CREATE INDEX IDX_cliente_telefono_hash ON cliente (telefono_hash)');
        $this->addSql('CREATE INDEX IDX_cliente_telefono_recado_hash ON cliente (telefono_recado_hash)');

        $this->addSql('ALTER TABLE cliente_historial
            MODIFY rut VARCHAR(512) NOT NULL,
            MODIFY telefono VARCHAR(512) NOT NULL,
            MODIFY telefono_recado VARCHAR(512) NOT NULL,
            MODIFY direccion VARCHAR(512) DEFAULT NULL,
            MODIFY clave_unica VARCHAR(512) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX IDX_cliente_rut_hash ON cliente');
        $this->addSql('DROP INDEX IDX_cliente_telefono_hash ON cliente');
        $this->addSql('DROP INDEX IDX_cliente_telefono_recado_hash ON cliente');

        $this->addSql('ALTER TABLE cliente
            DROP rut_hash,
            DROP telefono_hash,
            DROP telefono_recado_hash,
            MODIFY rut VARCHAR(255) NOT NULL,
            MODIFY telefono VARCHAR(255) NOT NULL,
            MODIFY telefono_recado VARCHAR(255) NOT NULL,
            MODIFY direccion VARCHAR(255) DEFAULT NULL,
            MODIFY clave_unica VARCHAR(255) DEFAULT NULL');

        $this->addSql('ALTER TABLE cliente_historial
            MODIFY rut VARCHAR(255) NOT NULL,
            MODIFY telefono VARCHAR(255) NOT NULL,
            MODIFY telefono_recado VARCHAR(255) NOT NULL,
            MODIFY direccion VARCHAR(255) DEFAULT NULL,
            MODIFY clave_unica VARCHAR(255) DEFAULT NULL');
    }
}
