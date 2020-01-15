<?php

use rcms\core\migrations\Migration;

/**
 * Handles the creation of table `{{%content_page}}`.
 * @author Andrii Borodin
 * @since 0.1
 */
class m190814_083036_create_content_page_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addContentPage();
        $this->addContentRevision();
        $this->addContentLayout();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%content_revision}}');
        $this->dropTable('{{%content_page}}');
        $this->dropTable('{{%content_layout}}');
    }

    private function addContentPage()
    {
        $this->createTable('{{%content_page}}', [
            'content_page_id' => $this->primaryKey()->unique(),
            'language' => $this->string(10)->notNull(),
            'type' => $this->string(16)->notNull(),
            'title' => $this->string()->notNull(),
            'content' => $this->text(),
            'plain_content' => $this->text(),
            'url' => $this->string()->notNull(),
            'css_style' => $this->text(),
            'js_script' => $this->text(),
            'content_layout_id' => $this->integer()->notNull()->defaultValue(-1),
            'metadata' => $this->text(),

            'is_main_page' => $this->boolean()->notNull()->defaultValue(0),
            'is_published' => $this->boolean()->notNull()->defaultValue(0),
            'start_publish_date' => $this->bigInteger(),
            'end_publish_date' => $this->bigInteger(),

            'for_guests' => $this->boolean()->notNull()->defaultValue(0),
            'for_auth' => $this->boolean()->notNull()->defaultValue(0),
            'only_with_post' => $this->boolean()->notNull()->defaultValue(0),

            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),

            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        $this->createIndex('{{%content_page_unique_idx}}', '{{%content_page}}', [
            'content_page_id'
        ], true);

        $this->createIndex('{{%content_page_unique}}', '{{%content_page}}', [
            'url', 'language'
        ], true);

        $this->createIndex('{{%content_page_language}}', '{{%content_page}}', [
            'language'
        ]);

        $this->createIndex('{{%content_page_url}}', '{{%content_page}}', [
            'url'
        ]);

        Yii::$app->db->schema->refreshTableSchema('{{%content_page}}');
    }

    private function addContentRevision()
    {
        $this->createTable('{{%content_revision}}', [
            'content_revision_id' => $this->primaryKey()->unique(),
            'content_page_id' => $this->integer()->notNull(),
            'revision_number' => $this->integer()->notNull(),
            'title' => $this->string()->notNull(),
            'content' => $this->text(),
            'css_style' => $this->text(),
            'js_script' => $this->text(),
            'metadata' => $this->text(),
            'tags' => $this->text(),

            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),

            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        $this->createIndex('{{%content_revision_unique_idx}}', '{{%content_revision}}', [
            'content_revision_id'
        ], true);

        $this->createIndex('{{%content_page_revision_unique_idx}}', '{{%content_revision}}', [
            'content_page_id', 'revision_number'
        ], true);

        $this->createIndex('{{%content_page_idx}}', '{{%content_revision}}', [
            'content_page_id'
        ]);

        $this->addForeignKey('{{%fk_content_page_revision_content_id}}',
            '{{%content_revision}}', 'content_page_id',
            '{{%content_page}}', 'content_page_id', 'CASCADE');

        Yii::$app->db->schema->refreshTableSchema('{{%content_revision}}');
    }

    private function addContentLayout()
    {
        $this->createTable('{{%content_layout}}', [
            'content_layout_id' => $this->primaryKey()->unique(),
            'layout_name' => $this->string()->notNull(),
            'content' => $this->text()->notNull(),
            'css_style' => $this->text(),
            'js_script' => $this->text(),
            'metadata' => $this->text(),

            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),

            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        Yii::$app->db->schema->refreshTableSchema('{{%content_header}}');

    }
}
