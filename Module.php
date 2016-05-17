<?php

namespace evgenybukharev\yii2_cackle_reviews;

use Yii;

class Module extends \yii\base\Module
{

    public  $site_id;
    public  $account_api_key;
    public  $site_api_key;

    public function init()
    {
        parent::init();

        if (Yii::$app instanceof \yii\console\Application)
            $this->controllerNamespace = 'evgenybukharev\yii2_cackle_reviews\commands';
    }
}