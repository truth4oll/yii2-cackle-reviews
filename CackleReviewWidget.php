<?php

namespace evgenybukharev\yii2_cackle_reviews;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

class CackleReviewWidget extends \yii\base\Widget
{
    private $_module;
    private $_uid;
    public $params=[];
    public $sync=false;

    public function init()
    {

        if ($this->_module == null)
            $this->_module = Yii::$app->getModule('cackle_reviews');

        if(!$this->_module)
            throw new InvalidConfigException("Не определен модуль в конфигурации проекта");

        /*Устанавливаем по умолчанию канал равный адресу страницы*/
        if (!$this->params['channel'])
            $this->params['channel'] = Yii::$app->request->url;

        /*Синхронизация комментариев*/
        if ($this->sync) {
            $sync = new helpers\CackleReviewSync();
            $sync->sync();
        }

        $this->_uid=uniqid(time());

        $this->params['widget']='Review';
        $this->params['id']=$this->_module->site_id;
        $this->params['chanWithoutParams']=true;
        $this->params['container']='mc-review-'.$this->_uid;

        $js= 'cackle_widget.push('.Json::encode($this->params).');';
        $cackle_pos_end  = 'document.getElementById("mc-review-'.$this->_uid.'").innerHTML = "";';
        $cackle_pos_end .= 'cackle_widget = window.cackle_widget || [];';
        $cackle_pos_end .= "
        (function() {
            var mc = document.createElement('script');
            mc.type = 'text/javascript';
            mc.async = true;
            mc.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://cackle.me/widget.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(mc, s.nextSibling);
        })();";

        $this->getView()->registerJs($js, View::POS_READY, 'cackle_init_'.$this->_uid);
        $this->getView()->registerJs($cackle_pos_end, View::POS_END, 'cackle_load');

        parent::init();
    }


    public function run()
    {
        parent::run();

        echo Html::beginTag('div', ['id' => 'mc-review-'.$this->_uid]);
        echo Html::beginTag('ul', ['id' => 'cackle-reviews-'.$this->_uid]);
        echo $this->list_reviews();
        echo Html::endTag('ul');
        echo Html::endTag('div');
    }

    /**
     * Функция получения списка отзывов
     * @return string
     */
    function list_reviews()
    {
        $sync = new helpers\CackleReviewSync();
        $channel = $this->params['channel'];
        $reviews = $sync->getLocalReviews($channel);

        for ($i = 0; $i < count($reviews); $i++)
            $this->render_review($reviews[$i]);
    }

    /**
     * Функция ренедеринга отзыва
     * @param array $review Отзыв
     * @return string
     */
    function render_review($review)
    {
        echo Html::beginTag('li', ['id' => 'cackle-review-' . $review['id']]);

        echo Html::beginTag('div', ['id' => 'cackle-review-header-' . $review['id'], 'class' => 'cackle-review-header']);
        echo Html::beginTag('cite', ['id' => 'cackle-cite-' . $review['id']]);

        echo Html::a(Html::img($review['author_avatar']), $review['author_www'], ['id' => 'cackle-author-user-' . $review['id'], 'target' => '_blank', 'rel' => 'nofollow']);

        echo Html::endTag('cite');
        echo Html::endTag('div');

        echo Html::beginTag('div', ['id' => 'cackle-review-body-' . $review['id'], 'class' => 'cackle-review-body']);
        echo Html::tag('div', $review['pros'], ['id' => 'cackle-review-message-' . $review['id'], 'class' => 'cackle-review-message']);
        echo Html::tag('div', $review['cons'], ['id' => 'cackle-review-message-' . $review['id'], 'class' => 'cackle-review-message']);
        echo Html::tag('div', $review['comment'], ['id' => 'cackle-review-message-' . $review['id'], 'class' => 'cackle-review-message']);
        echo Html::endTag('div');

        echo Html::endTag('li');
    }
}