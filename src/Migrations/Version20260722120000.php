<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260722120000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Crea tabla api_llamado_estado_diario para log de request/response de los endpoints de estado diario';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE api_llamado_estado_diario (
            id INT AUTO_INCREMENT NOT NULL,
            estado_diario_id INT DEFAULT NULL,
            endpoint VARCHAR(50) DEFAULT NULL,
            json_request LONGTEXT DEFAULT NULL,
            json_response LONGTEXT DEFAULT NULL,
            fecha_registro DATETIME DEFAULT NULL,
            exito TINYINT(1) DEFAULT NULL,
            mensaje_error LONGTEXT DEFAULT NULL,
            INDEX IDX_api_llamado_estado_diario_estado_diario (estado_diario_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE api_llamado_estado_diario ADD CONSTRAINT FK_api_llamado_estado_diario_estado_diario FOREIGN KEY (estado_diario_id) REFERENCES estado_diario (id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE api_llamado_estado_diario');
    }
}
