<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251231032638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cria tabela product_items para relação entre products e items';
    }

    public function up(Schema $schema): void
    {
        // cria tabela product_items
        $table = $schema->createTable('product_items');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->setPrimaryKey(['id']);

        $table->addColumn('product_id', 'integer');
        $table->addColumn('item_id', 'integer');

        $table->addColumn('essential', 'boolean', ['default' => false]);
        $table->addColumn('quantity', 'integer', ['default' => 1]);
        $table->addColumn('customizable', 'boolean', ['default' => false]);

        $table->addColumn('created_at', 'datetime_immutable');
        $table->addColumn('updated_at', 'datetime');

        $table->addIndex(['product_id'], 'idx_product_items_product_id');
        $table->addIndex(['item_id'], 'idx_product_items_item_id');

        // foreign keys
        $table->addForeignKeyConstraint('products', ['product_id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_product_items_product');
        $table->addForeignKeyConstraint('items', ['item_id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_product_items_item');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('product_items');
    }
}
