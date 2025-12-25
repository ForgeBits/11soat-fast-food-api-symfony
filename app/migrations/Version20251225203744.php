<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251225203744 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cria tabela categories (Category entity)';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('categories');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->setPrimaryKey(['id']);

        $table->addColumn('name', 'string', ['length' => 150]);
        $table->addColumn('description', 'text', ['notnull' => false]);

        $table->addColumn('created_at', 'datetime_immutable');
        $table->addColumn('updated_at', 'datetime');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('categories');
    }
}
