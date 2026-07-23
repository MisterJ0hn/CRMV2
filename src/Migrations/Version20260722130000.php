<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260722130000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Crea tabla usuario_fcm_token para registrar tokens de notificaciones push (Firebase) por usuario';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE usuario_fcm_token (
            id INT AUTO_INCREMENT NOT NULL,
            usuario_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            plataforma VARCHAR(20) DEFAULT NULL,
            activo TINYINT(1) NOT NULL,
            fecha_registro DATETIME NOT NULL,
            fecha_actualizacion DATETIME DEFAULT NULL,
            UNIQUE INDEX UNIQ_usuario_fcm_token_token (token),
            INDEX IDX_usuario_fcm_token_usuario (usuario_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE usuario_fcm_token ADD CONSTRAINT FK_usuario_fcm_token_usuario FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE usuario_fcm_token');
    }
}
