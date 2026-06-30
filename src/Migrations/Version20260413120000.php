<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260413120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Agrega caducidad de contraseña y tabla de historial de contraseñas';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        // Columna de expiración en la tabla usuario
        $this->addSql('ALTER TABLE usuario ADD password_expiracion DATETIME DEFAULT NULL');

        // Tabla de historial de contraseñas
        $this->addSql('
            CREATE TABLE password_historial (
                id INT AUTO_INCREMENT NOT NULL,
                usuario_id INT NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                fecha_creacion DATETIME NOT NULL,
                INDEX IDX_PWDHIST_USUARIO (usuario_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('
            ALTER TABLE password_historial
                ADD CONSTRAINT FK_PWDHIST_USUARIO
                FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE password_historial DROP FOREIGN KEY FK_PWDHIST_USUARIO');
        $this->addSql('DROP TABLE password_historial');
        $this->addSql('ALTER TABLE usuario DROP COLUMN password_expiracion');
    }
}
