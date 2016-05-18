<?php

namespace evgenybukharev\yii2_cackle_reviews\commands;

use evgenybukharev\yii2_cackle_reviews\helpers\CackleReviewSync;
use yii\console\Controller;

class SyncController extends Controller
{

    public function actionIndex()
    {
        $sync = new CackleReviewSync();
        $sync->sync();
    }
}