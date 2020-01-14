<?php

use rcms\core\migrations\Migration;

/**
 * Handles the creation of table `{{%content_file_storage}}`.
 */
class m190914_212300_create_content_file_storage_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%content_file_storage}}', [
            'file_id' => $this->primaryKey()->unique(),
            'file_hash' => $this->string(256)->notNull()->unique(),
            'name' => $this->string(256)->notNull(),
            'ext' => $this->string(10)->notNull(),
            'type' => $this->string(256)->notNull(),
            'size' => $this->bigInteger()->notNull(),
            'path' => $this->text()->notNull(),
            'is_available' => $this->boolean()->notNull()->defaultValue(0),
            'created_at' => $this->integer(),
            'created_by' => $this->integer()
        ]);

        $this->createIndex('{{%content_file_storage_unique_idx}}', '{{%content_file_storage}}', [
            'file_id'
        ], true);

        $this->createIndex('{{%content_file_storage_file_hash}}', '{{%content_file_storage}}', [
            'file_hash'
        ], true);

        $this->createIndex('{{%content_file_storage_name_idx}}', '{{%content_file_storage}}', [
            'name'
        ], false);

        $this->createIndex('{{%content_file_storage_ext_idx}}', '{{%content_file_storage}}', [
            'ext'
        ], false);

        $this->createIndex('{{%content_file_storage_type_idx}}', '{{%content_file_storage}}', [
            'type'
        ], false);

        $this->createIndex('{{%content_file_storage_available_idx}}', '{{%content_file_storage}}', [
            'is_available'
        ], false);

        Yii::$app->db->schema->refreshTableSchema('{{%content_file_storage}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%content_file_storage}}');
    }
}
