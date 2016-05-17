<?php
namespace evgenybukharev\yii2_cackle_reviews\helpers;

use yii\base\Widget;
use yii\helpers\Html;
use yii\web\View;

class CackleReview extends Widget
{
    private $channel;
    const TIMER = 300;

    function __construct($init = true, $channel)
    {
        $this->channel = $channel;

        if ($init) {
            $sync = new CackleReviewSync();
            if ($this->time_is_over(self::TIMER)) {
                $sync->init();
            }
            $this->cackle_display_reviews();
        }
    }

    function time_is_over($cron_time)
    {
        $cackle_api = new CackleReviewAPI();
        $get_last_time = $cackle_api->cackle_get_param("last_time");
        $now = time();
        if ($get_last_time == "") {
            $set_time = $cackle_api->cackle_set_param("last_time", $now);

            return $now;
        } else {
            if ($get_last_time + $cron_time > $now) {
                return false;
            }
            if ($get_last_time + $cron_time < $now) {
                $set_time = $cackle_api->cackle_set_param("last_time", $now);

                return $cron_time;
            }
        }
    }

    function cackle_review($review)
    {
        echo Html::beginTag('li', ['id' => 'cackle-review-' . $review['id']]);

        echo Html::beginTag('div', ['id' => 'cackle-review-header-' . $review['id'], 'class' => 'cackle-review-header']);
        echo Html::beginTag('cite', ['id' => 'cackle-cite-' . $review['id']]);
        if ($review['autor']) :
            echo Html::a($review['autor'], '#', ['id' => 'cackle-author-user-' . $review['id'], 'target' => '_blank', 'rel' => 'nofollow']);
        else :
            echo Html::tag('span', $review['name'], ['id' => 'cackle-author-user-' . $review['id']]);
        endif;
        echo Html::endTag('cite');
        echo Html::endTag('div');

        echo Html::beginTag('div', ['id' => 'cackle-review-body-' . $review['id'], 'class' => 'cackle-review-body']);
        echo Html::tag('div', $review['comment'], ['id' => 'cackle-review-message-' . $review['id'], 'class' => 'cackle-review-message']);
        echo Html::tag('div', $review['dignity'], ['id' => 'cackle-review-message-' . $review['id'], 'class' => 'cackle-review-message']);
        echo Html::tag('div', $review['lack'], ['id' => 'cackle-review-message-' . $review['id'], 'class' => 'cackle-review-message']);
        echo Html::endTag('div');

        echo Html::endTag('li');
    }


    function cackle_display_reviews()
    {
        $cackle_api = new CackleReviewAPI();
        echo Html::beginTag('div', ['id' => 'mc-review']);
        echo Html::tag('ul', $this->list_reviews(), ['id' => 'cackle-reviews']);
        echo Html::endTag('div');

        $site_id = $cackle_api->cackle_get_param("site_id");
        $cackle_js
            = <<<JS
            document.getElementById('mc-review').innerHTML = '';
            cackle_widget = window.cackle_widget || [];

            cackle_widget.push({
                widget: 'Review',
                chanWithoutParams: true,
                id: $site_id
            })
            ;
            (function () {
                var mc = document.createElement('script');
                mc.type = 'text/javascript';
                mc.async = true;
                mc.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://cackle.me/widget.js';
                var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(mc, s.nextSibling);
            })();
JS;

        $this->getView()->registerJs($cackle_js, View::POS_READY);
    }

    function get_local_reviews()
    {
        //getting all reviews for special post_id from database.
        $cackle_api = new CackleReviewAPI();
        $channel = $this->channel;
        $get_all_reviews = $cackle_api->db_connect("select * from " . PREFIX . "_reviews where channel = $channel and status = 1;");

        return $get_all_reviews;
    }

    function list_reviews()
    {
        $obj = $this->get_local_reviews();
        if ($obj) {
            foreach ($obj as $review) {
                $this->cackle_review($review);
            }
        }
    }
}

?>
