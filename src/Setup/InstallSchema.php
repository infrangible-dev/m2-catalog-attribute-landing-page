<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class InstallSchema
    implements InstallSchemaInterface
{
    /**
     * @throws \Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        $connection = $setup->getConnection();

        $pageTableName = $setup->getTable('catalog_attribute_landing_page');
        $pageStoreTableName = $setup->getTable('catalog_attribute_landing_page_store');
        $groupTableName = $setup->getTable('catalog_attribute_landing_group');
        $groupStoreTableName = $setup->getTable('catalog_attribute_landing_group_store');
        $eavAttributeTableName = $setup->getTable('eav_attribute');
        $storeTableName = $setup->getTable('store');

        if (!$setup->tableExists($pageTableName)) {
            $pageTable = $connection->newTable($pageTableName);

            $pageTable->addColumn('page_id', Table::TYPE_INTEGER, 10, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true]);
            $pageTable->addColumn('attribute_id1', Table::TYPE_SMALLINT, 5, ['unsigned' => true, 'nullable' => false]);
            $pageTable->addColumn('value1', Table::TYPE_TEXT, 255, ['nullable' => false]);
            $pageTable->addColumn('attribute_id2', Table::TYPE_SMALLINT, 5, ['unsigned' => true, 'nullable' => true]);
            $pageTable->addColumn('value2', Table::TYPE_TEXT, 255, ['nullable' => true]);
            $pageTable->addColumn('attribute_id3', Table::TYPE_SMALLINT, 5, ['unsigned' => true, 'nullable' => true]);
            $pageTable->addColumn('value3', Table::TYPE_TEXT, 255, ['nullable' => true]);
            $pageTable->addColumn('attribute_id4', Table::TYPE_SMALLINT, 5, ['unsigned' => true, 'nullable' => true]);
            $pageTable->addColumn('value4', Table::TYPE_TEXT, 255, ['nullable' => true]);
            $pageTable->addColumn('attribute_id5', Table::TYPE_SMALLINT, 5, ['unsigned' => true, 'nullable' => true]);
            $pageTable->addColumn('value5', Table::TYPE_TEXT, 255, ['nullable' => true]);
            $pageTable->addColumn(
                'attribute_set_id', Table::TYPE_SMALLINT, 5, ['unsigned' => true, 'nullable' => true]);
            $pageTable->addColumn('attribute_orders', Table::TYPE_TEXT, 1024, ['nullable' => true]);
            $pageTable->addColumn('attribute_order', Table::TYPE_TEXT, 255, ['nullable' => true]);
            $pageTable->addColumn('attribute_order_direction', Table::TYPE_TEXT, 4, ['nullable' => true]);
            $pageTable->addColumn('url_key', Table::TYPE_TEXT, 255, ['nullable' => false]);
            $pageTable->addColumn('headline', Table::TYPE_TEXT, 255, ['nullable' => false]);
            $pageTable->addColumn('description', Table::TYPE_TEXT, 10000, ['nullable' => false]);
            $pageTable->addColumn('cms_block_id', Table::TYPE_INTEGER, 10, ['nullable' => true, 'unsigned' => true]);
            $pageTable->addColumn(
                'additional_cms_block_id', Table::TYPE_INTEGER, 10, ['nullable' => true, 'unsigned' => true]);
            $pageTable->addColumn(
                'seo_cms_block_id', Table::TYPE_INTEGER, 10, ['nullable' => true, 'unsigned' => true]);
            $pageTable->addColumn('image', Table::TYPE_TEXT, 255, ['nullable' => true]);
            $pageTable->addColumn('logo', Table::TYPE_TEXT, 255, ['nullable' => true]);
            $pageTable->addColumn('thumbnail', Table::TYPE_TEXT, 255, ['nullable' => true]);
            $pageTable->addColumn('page_title', Table::TYPE_TEXT, 255, ['nullable' => false]);
            $pageTable->addColumn('meta_description', Table::TYPE_TEXT, 255, ['nullable' => false]);
            $pageTable->addColumn('meta_keywords', Table::TYPE_TEXT, 255, ['nullable' => false]);
            $pageTable->addColumn('check_active', Table::TYPE_SMALLINT, 1, ['nullable' => true, 'default' => '0']);
            $pageTable->addColumn(
                'active', Table::TYPE_SMALLINT, 1, ['nullable' => false, 'unsigned' => true, 'default' => 0]);
            $pageTable->addColumn('created_at', Table::TYPE_DATETIME, null, [
                'nullable' => false,
                'default' => '0000-00-00 00:00:00']);
            $pageTable->addColumn('updated_at', Table::TYPE_DATETIME, null, [
                'nullable' => false,
                'default' => '0000-00-00 00:00:00']);

            $pageTable->addForeignKey(
                $setup->getFkName(
                    $pageTableName, 'attribute_id1', $eavAttributeTableName, 'attribute_id'), 'attribute_id1',
                $eavAttributeTableName, 'attribute_id', Table::ACTION_CASCADE);

            $pageTable->addForeignKey(
                $setup->getFkName(
                    $pageTableName, 'attribute_id2', $eavAttributeTableName, 'attribute_id'), 'attribute_id2',
                $eavAttributeTableName, 'attribute_id', Table::ACTION_CASCADE);

            $pageTable->addForeignKey(
                $setup->getFkName(
                    $pageTableName, 'attribute_id3', $eavAttributeTableName, 'attribute_id'), 'attribute_id3',
                $eavAttributeTableName, 'attribute_id', Table::ACTION_CASCADE);

            $pageTable->addForeignKey(
                $setup->getFkName(
                    $pageTableName, 'attribute_id4', $eavAttributeTableName, 'attribute_id'), 'attribute_id4',
                $eavAttributeTableName, 'attribute_id', Table::ACTION_CASCADE);

            $pageTable->addForeignKey(
                $setup->getFkName(
                    $pageTableName, 'attribute_id5', $eavAttributeTableName, 'attribute_id'), 'attribute_id5',
                $eavAttributeTableName, 'attribute_id', Table::ACTION_CASCADE);

            $connection->createTable($pageTable);
        }

        if (!$setup->tableExists($pageStoreTableName)) {
            $pageStoreTable = $connection->newTable($pageStoreTableName);

            $pageStoreTable->addColumn('page_id', Table::TYPE_INTEGER, 10, [
                'unsigned' => true,
                'nullable' => false,
                'primary' => true]);
            $pageStoreTable->addColumn('store_id', Table::TYPE_SMALLINT, 5, [
                'unsigned' => true,
                'nullable' => false,
                'primary' => true]);

            $pageStoreTable->addForeignKey(
                $setup->getFkName($pageStoreTableName, 'page_id', $pageTableName, 'page_id'), 'page_id', $pageTableName,
                'page_id', Table::ACTION_CASCADE);
            $pageStoreTable->addForeignKey(
                $setup->getFkName(
                    $pageStoreTableName, 'store_id', $storeTableName, 'store_id'), 'store_id', $storeTableName,
                'store_id', Table::ACTION_CASCADE);

            $connection->createTable($pageStoreTable);
        }

        if (!$setup->tableExists($groupTableName)) {
            $groupTable = $connection->newTable($groupTableName);

            $groupTable->addColumn('group_id', Table::TYPE_INTEGER, 10, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true]);
            $groupTable->addColumn('attribute_id1', Table::TYPE_SMALLINT, 5, ['unsigned' => true, 'nullable' => false]);
            $groupTable->addColumn('attribute_id2', Table::TYPE_SMALLINT, 5, ['unsigned' => true, 'nullable' => true]);
            $groupTable->addColumn('attribute_id3', Table::TYPE_SMALLINT, 5, ['unsigned' => true, 'nullable' => true]);
            $groupTable->addColumn('attribute_id4', Table::TYPE_SMALLINT, 5, ['unsigned' => true, 'nullable' => true]);
            $groupTable->addColumn('attribute_id5', Table::TYPE_SMALLINT, 5, ['unsigned' => true, 'nullable' => true]);
            $groupTable->addColumn('url_key', Table::TYPE_TEXT, 255, ['nullable' => false]);
            $groupTable->addColumn('headline', Table::TYPE_TEXT, 255, ['nullable' => false]);
            $groupTable->addColumn('description', Table::TYPE_TEXT, 10000, ['nullable' => false]);
            $groupTable->addColumn('cms_block_id', Table::TYPE_INTEGER, 10, ['nullable' => false, 'unsigned' => true]);
            $groupTable->addColumn('page_title', Table::TYPE_TEXT, 255, ['nullable' => false]);
            $groupTable->addColumn('meta_description', Table::TYPE_TEXT, 255, ['nullable' => false]);
            $groupTable->addColumn('meta_keywords', Table::TYPE_TEXT, 255, ['nullable' => false]);
            $groupTable->addColumn(
                'active', Table::TYPE_SMALLINT, 1, ['nullable' => false, 'unsigned' => true, 'default' => 0]);
            $groupTable->addColumn('created_at', Table::TYPE_DATETIME, null, [
                'nullable' => false,
                'default' => '0000-00-00 00:00:00']);
            $groupTable->addColumn('updated_at', Table::TYPE_DATETIME, null, [
                'nullable' => false,
                'default' => '0000-00-00 00:00:00']);

            $groupTable->addForeignKey(
                $setup->getFkName($groupTableName, 'attribute_id1', $eavAttributeTableName, 'attribute_id'),
                'attribute_id1', $eavAttributeTableName, 'attribute_id', Table::ACTION_CASCADE);

            $groupTable->addForeignKey(
                $setup->getFkName($groupTableName, 'attribute_id2', $eavAttributeTableName, 'attribute_id'),
                'attribute_id2', $eavAttributeTableName, 'attribute_id', Table::ACTION_CASCADE);

            $groupTable->addForeignKey(
                $setup->getFkName($groupTableName, 'attribute_id3', $eavAttributeTableName, 'attribute_id'),
                'attribute_id3', $eavAttributeTableName, 'attribute_id', Table::ACTION_CASCADE);

            $groupTable->addForeignKey(
                $setup->getFkName($groupTableName, 'attribute_id4', $eavAttributeTableName, 'attribute_id'),
                'attribute_id4', $eavAttributeTableName, 'attribute_id', Table::ACTION_CASCADE);

            $groupTable->addForeignKey(
                $setup->getFkName($groupTableName, 'attribute_id5', $eavAttributeTableName, 'attribute_id'),
                'attribute_id5', $eavAttributeTableName, 'attribute_id', Table::ACTION_CASCADE);

            $connection->createTable($groupTable);
        }

        if (!$setup->tableExists($groupStoreTableName)) {
            $groupStoreTable = $connection->newTable($groupStoreTableName);

            $groupStoreTable->addColumn('group_id', Table::TYPE_INTEGER, 10, [
                'unsigned' => true,
                'nullable' => false,
                'primary' => true]);
            $groupStoreTable->addColumn('store_id', Table::TYPE_SMALLINT, 5, [
                'unsigned' => true,
                'nullable' => false,
                'primary' => true]);

            $groupStoreTable->addForeignKey(
                $setup->getFkName($groupStoreTableName, 'group_id', $groupTableName, 'group_id'), 'group_id',
                $groupTableName, 'group_id', Table::ACTION_CASCADE);
            $groupStoreTable->addForeignKey(
                $setup->getFkName($groupStoreTableName, 'store_id', $storeTableName, 'store_id'), 'store_id',
                $storeTableName, 'store_id', Table::ACTION_CASCADE);

            $connection->createTable($groupStoreTable);
        }

        $setup->endSetup();
    }
}
