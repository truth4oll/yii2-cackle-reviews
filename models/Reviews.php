<?php

namespace evgenybukharev\yii2_cackle_reviews\models;

use Yii;

/**
 * This is the model class for table "{{%reviews}}".
 *
 * @property integer $id
 * @property integer $cackle_id
 * @property integer $siteId
 * @property integer $star
 * @property string $pros
 * @property string $cons
 * @property string $comment
 * @property string $media
 * @property integer $up
 * @property integer $down
 * @property string $created
 * @property integer $status
 * @property string $details
 * @property integer $author_id
 * @property string $author_name
 * @property string $author_email
 * @property string $author_hash
 * @property string $author_avatar
 * @property string $author_www
 * @property string $author_provider
 * @property string $author_openId
 * @property string $author_verify
 * @property string $author_notify
 * @property integer $chan_id
 * @property string $chan_channel
 * @property string $chan_url
 * @property string $chan_title
 * @property string $ip
 * @property string $modified
 * @property integer $rating
 * @property string $lack
 * @property string $dignity
 * @property integer $stars
 */
class Reviews extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%reviews}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cackle_id', 'siteId', 'star', 'up', 'down', 'author_id', 'chan_id', 'rating', 'stars'], 'integer'],
            [['pros', 'cons', 'comment', 'media', 'created', 'details', 'author_name', 'author_email', 'author_hash', 'author_avatar', 'author_www', 'author_provider', 'author_openId', 'author_verify', 'author_notify', 'chan_channel', 'chan_url', 'chan_title', 'ip', 'lack', 'dignity'], 'required'],
            [['pros', 'cons', 'status', 'comment', 'media', 'details', 'author_name', 'author_email', 'author_hash', 'author_avatar', 'author_www', 'author_provider', 'author_openId', 'author_verify', 'author_notify', 'chan_channel', 'chan_url', 'chan_title', 'lack', 'dignity'], 'string'],
            [['created', 'modified'], 'safe'],
            [['ip'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cackle_id' => 'Cackle ID',
            'siteId' => 'Site ID',
            'star' => 'Star',
            'pros' => 'Pros',
            'cons' => 'Cons',
            'comment' => 'Comment',
            'media' => 'Media',
            'up' => 'Up',
            'down' => 'Down',
            'created' => 'Created',
            'status' => 'Status',
            'details' => 'Details',
            'author_id' => 'Author ID',
            'author_name' => 'Author Name',
            'author_email' => 'Author Email',
            'author_hash' => 'Author Hash',
            'author_avatar' => 'Author Avatar',
            'author_www' => 'Author Www',
            'author_provider' => 'Author Provider',
            'author_openId' => 'Author Open ID',
            'author_verify' => 'Author Verify',
            'author_notify' => 'Author Notify',
            'chan_id' => 'Chan ID',
            'chan_channel' => 'Chan Channel',
            'chan_url' => 'Chan Url',
            'chan_title' => 'Chan Title',
            'ip' => 'Ip',
            'modified' => 'Modified',
            'rating' => 'Rating',
            'lack' => 'Lack',
            'dignity' => 'Dignity',
            'stars' => 'Stars',
        ];
    }
}
