<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251231203100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create order_items and order_item_customizations tables';
    }

    public function up(Schema $schema): void
    {
        // order_items
        $orderItems = $schema->createTable('order_items');
        $orderItems->addColumn('id', 'integer', ['autoincrement' => true]);
        $orderItems->setPrimaryKey(['id']);
        $orderItems->addColumn('order_id', 'integer');
        $orderItems->addColumn('product_id', 'integer');
        $orderItems->addColumn('title', 'string', ['length' => 255]);
        $orderItems->addColumn('quantity', 'integer');
        $orderItems->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2]);
        $orderItems->addColumn('observation', 'text', ['notnull' => false]);
        $orderItems->addColumn('created_at', 'datetime_immutable');
        $orderItems->addColumn('updated_at', 'datetime');

        $orderItems->addIndex(['order_id'], 'idx_order_items_order');
        $orderItems->addIndex(['product_id'], 'idx_order_items_product');
        $orderItems->addForeignKeyConstraint('orders', ['order_id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_order_items_order');
        $orderItems->addForeignKeyConstraint('products', ['product_id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_order_items_product');

        // order_item_customizations
        $customs = $schema->createTable('order_item_customizations');
        $customs->addColumn('id', 'integer', ['autoincrement' => true]);
        $customs->setPrimaryKey(['id']);
        $customs->addColumn('order_item_id', 'integer');
        $customs->addColumn('item_id', 'integer');
        $customs->addColumn('title', 'string', ['length' => 255]);
        $customs->addColumn('quantity', 'integer');
        $customs->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2]);
        $customs->addColumn('observation', 'text', ['notnull' => false]);
        $customs->addColumn('created_at', 'datetime_immutable');
        $customs->addColumn('updated_at', 'datetime');

        $customs->addIndex(['order_item_id'], 'idx_order_item_custom_order_item');
        $customs->addIndex(['item_id'], 'idx_order_item_custom_item');
        $customs->addForeignKeyConstraint('order_items', ['order_item_id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_customizations_order_item');
        $customs->addForeignKeyConstraint('items', ['item_id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_customizations_item');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('order_item_customizations');
        $schema->dropTable('order_items');
    }
}
