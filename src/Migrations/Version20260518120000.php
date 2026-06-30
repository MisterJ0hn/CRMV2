<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260518120000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Crea tabla api_llamado_adereso para log de consultas Adereso';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE api_llamado_adereso (
            id INT AUTO_INCREMENT NOT NULL,
            telefono VARCHAR(50) DEFAULT NULL,
            json_request LONGTEXT DEFAULT NULL,
            json_response LONGTEXT DEFAULT NULL,
            fecha_registro DATETIME DEFAULT NULL,
            exito TINYINT(1) DEFAULT NULL,
            mensaje_error LONGTEXT DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE api_llamado_adereso');
    }
}
