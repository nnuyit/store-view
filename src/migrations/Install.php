<?php

namespace nelsonnguyen\craftstoreview\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Table;
use nelsonnguyen\craftstoreview\records\StoreViewRecord;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Place installation code here...

        if (!$this->db->tableExists(StoreViewRecord::tableName())) {

            $this->createTable(StoreViewRecord::tableName(), [
                'id' => $this->primaryKey(),
                'elementId' => $this->integer()->null(),
                'siteId' => $this->integer()->notNull(),
                'uri' => $this->string()->null(),
                'total' => $this->integer()->defaultValue(0),
                'day' => $this->integer()->defaultValue(0),
                'week' => $this->integer()->defaultValue(0),
                'month' => $this->integer()->defaultValue(0),
                'lastUpdated' => $this->dateTime()->null(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            $this->createIndex(null, StoreViewRecord::tableName(), 'elementId');
            $this->createIndex(null, StoreViewRecord::tableName(), 'uri');
            $this->createIndex(null, StoreViewRecord::tableName(), 'lastUpdated');

            $this->addForeignKey(null, StoreViewRecord::tableName(), 'elementId', Table::ELEMENTS, 'id', 'CASCADE');
            $this->addForeignKey(null, StoreViewRecord::tableName(), 'siteId', Table::SITES, 'id', 'CASCADE');
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists(StoreViewRecord::tableName());

        return true;
    }
}
