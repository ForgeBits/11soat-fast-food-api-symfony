<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251231154800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create orders and orders_products tables';
    }

    public function up(Schema $schema): void
    {
        $orders = $schema->createTable('orders');
        $orders->addColumn('id', 'integer', ['autoincrement' => true]);
        $orders->setPrimaryKey(['id']);

        $orders->addColumn('client_id', 'integer', ['notnull' => false]);
        $orders->addColumn('status', 'string', ['length' => 50]);
        $orders->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2]);
        $orders->addColumn('transaction_id', 'string', ['length' => 191, 'notnull' => false]);
        $orders->addColumn('is_random_client', 'boolean', ['default' => false]);
        $orders->addColumn('code_client_random', 'integer', ['notnull' => false]);
        $orders->addColumn('observation', 'text', ['notnull' => false]);
        $orders->addColumn('created_at', 'datetime_immutable');
        $orders->addColumn('updated_at', 'datetime');

        $orders->addUniqueIndex(['transaction_id'], 'uniq_orders_transaction_id');
        $orders->addIndex(['client_id'], 'idx_orders_client_id');
        $orders->addIndex(['status'], 'idx_orders_status');

        $pivot = $schema->createTable('orders_products');
        $pivot->addColumn('order_id', 'integer');
        $pivot->addColumn('product_id', 'integer');
        $pivot->setPrimaryKey(['order_id', 'product_id']);

        $pivot->addIndex(['product_id'], 'idx_orders_products_product');

        $pivot->addForeignKeyConstraint('orders', ['order_id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_orders_products_order');
        $pivot->addForeignKeyConstraint('products', ['product_id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_orders_products_product');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('orders_products');
        $schema->dropTable('orders');
    }
}
