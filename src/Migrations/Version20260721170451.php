<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Agrega estado "pendiente" (con nivel bajo/medio/alto) a estado_diario.
 */
final class Version20260721170451 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Agrega columnas pendiente, nivel_pendiente, fecha_pendiente y usuario_pendiente_id a estado_diario';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE estado_diario ADD pendiente TINYINT(1) NOT NULL DEFAULT 0, ADD nivel_pendiente VARCHAR(20) DEFAULT NULL, ADD fecha_pendiente DATETIME DEFAULT NULL, ADD usuario_pendiente_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE estado_diario ADD CONSTRAINT FK_estado_diario_usuario_pendiente FOREIGN KEY (usuario_pendiente_id) REFERENCES usuario (id)');
        $this->addSql('CREATE INDEX IDX_estado_diario_usuario_pendiente ON estado_diario (usuario_pendiente_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE estado_diario DROP FOREIGN KEY FK_estado_diario_usuario_pendiente');
        $this->addSql('DROP INDEX IDX_estado_diario_usuario_pendiente ON estado_diario');
        $this->addSql('ALTER TABLE estado_diario DROP pendiente, DROP nivel_pendiente, DROP fecha_pendiente, DROP usuario_pendiente_id');
    }
}
