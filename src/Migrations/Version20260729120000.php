<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Suma "correo" al cifrado de datos sensibles iniciado en Version20260723120000
 * (quedó fuera en esa primera pasada):
 *  - Ensancha cliente.correo y cliente_historial.correo a VARCHAR(512), porque el
 *    valor cifrado+base64 ocupa más que el texto plano original.
 *  - Agrega cliente.correo_hash (HMAC-SHA256 del correo en minúsculas) para poder
 *    seguir filtrando por correo, ya que la columna cifrada no es comparable.
 *
 * Solo cambia el esquema. El backfill de los datos existentes se hace con el
 * comando app:cifrar-datos-cliente, que debe correrse después de desplegar esto.
 */
final class Version20260729120000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Ensancha cliente/cliente_historial.correo para cifrado y agrega cliente.correo_hash';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cliente
            MODIFY correo VARCHAR(512) NOT NULL,
            ADD correo_hash CHAR(64) DEFAULT NULL');

        $this->addSql('CREATE INDEX IDX_cliente_correo_hash ON cliente (correo_hash)');

        $this->addSql('ALTER TABLE cliente_historial
            MODIFY correo VARCHAR(512) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX IDX_cliente_correo_hash ON cliente');

        $this->addSql('ALTER TABLE cliente
            DROP correo_hash,
            MODIFY correo VARCHAR(255) NOT NULL');

        $this->addSql('ALTER TABLE cliente_historial
            MODIFY correo VARCHAR(255) NOT NULL');
    }
}
