<?php

use yii\db\Migration;

class m160516_191906_cackle_reviews extends Migration
{

    public function safeUp()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%reviews}}', [
            'id' => $this->primaryKey(),
            'cackle_id' => $this->integer(11)->unique(),
            'siteId' => $this->integer(11),
            'star' => $this->integer(11),
            'pros' => $this->text()->notNull(),
            'cons' => $this->text()->notNull(),
            'comment' => $this->text()->notNull(),
            'media' => $this->text()->notNull(),
            'up' => $this->integer(11),
            'down' => $this->integer(11),
            'created' => $this->dateTime()->notNull(),
            'status' => $this->text()->notNull(),
            'details' => $this->text()->notNull(),
            'author_id' => $this->integer(11),
            'author_name' => $this->text()->notNull(),
            'author_email' => $this->text()->notNull(),
            'author_hash' => $this->text()->notNull(),
            'author_avatar' => $this->text()->notNull(),
            'author_www' => $this->text()->notNull(),
            'author_provider' => $this->text()->notNull(),
            'author_openId' => $this->text()->notNull(),
            'author_verify' => $this->text()->notNull(),
            'author_notify' => $this->text()->notNull(),
            'chan_id' => $this->integer(11),
            'chan_channel' => $this->text()->notNull(),
            'chan_url' => $this->text()->notNull(),
            'chan_title' => $this->text()->notNull(),
            'ip' => $this->string(500)->notNull(),
            'modified' => $this->dateTime(),
            'rating' => $this->integer(11),
            'lack' => $this->text()->notNull(),
            'dignity' => $this->text()->notNull(),
            'stars' => $this->integer(11),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%reviews}}');
    }
}
