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
        return 'Cria tabela products (Product entity)';
    }

    public function up(Schema $schema): void
    {
        // cria tabela products
        $table = $schema->createTable('products');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->setPrimaryKey(['id']);

        $table->addColumn('name', 'string', ['length' => 150]);
        $table->addColumn('description', 'text', ['notnull' => false]);

        $table->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2]);

        $table->addColumn('url_img', 'string', ['length' => 255]);

        $table->addColumn('customizable', 'boolean', ['default' => false]);
        $table->addColumn('available', 'boolean', ['default' => true]);

        $table->addColumn('category_id', 'integer', ['notnull' => false]);

        $table->addColumn('created_at', 'datetime_immutable');
        $table->addColumn('updated_at', 'datetime');

        $table->addForeignKeyConstraint('categories', ['category_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('products');
    }
}
