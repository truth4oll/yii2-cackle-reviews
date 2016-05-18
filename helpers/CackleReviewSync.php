<?php
namespace evgenybukharev\yii2_cackle_reviews\helpers;

use evgenybukharev\yii2_cackle_reviews\models\Reviews;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class CackleReviewSync
{
    private   $_module;
    protected $ch;

    function __construct()
    {
        if ($this->_module == null) {
            $this->_module = Yii::$app->getModule('cackle_reviews');
        }

        if (!$this->_module) {
            throw new InvalidConfigException("Не определен модуль в конфигурации проекта");
        }
    }

    /**
     * Функция синхронизации отзывов
     */
    function sync()
    {
        try {
            $since = strtotime(Reviews::find()->max('modified'));
            $this->curlInit();
            $url = $this->getUrl($since);
            $result = $this->curlExec($url);
            $reviews = Json::decode($result);

            $reviews['reviews'] = !empty($reviews['reviews']) ? $reviews['reviews'] : [];

            $this->saveReviews($reviews['reviews']);
            if ($reviews['reviews']['totalPages'] > 1) {
                for ($i = 1; $i < $reviews['reviews']['totalPages']; $i++) {
                    $url = $this->getUrl($since, $i);
                    $result = $this->curlExec($url);
                    $reviews = Json::decode($result);
                    $this->saveComments($reviews['reviews']);
                }
            }
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        } finally {
            $this->curlClose();
        }
    }


    /**
     * Сохранение отзывов в базу данных.
     * Если комментарий с таким cackle-id уже имеется, то выполняется обновление
     * @param array $reviews
     * @throws \Exception
     */
    protected function saveReviews($reviews)
    {
        $data = $this->prepareItems($reviews);

        $sql = '';
        $set_str_params = [];
        for ($i = 0; $i < count($data); $i++) {
            $set_str = $this->prepareData($data[$i]);
            $set_str_params = ArrayHelper::merge($set_str_params, $this->prepareData($data[$i], true));
            $sql .= "INSERT INTO " . Reviews::tableName() . " SET $set_str ON DUPLICATE KEY UPDATE $set_str;";
        }
        return Yii::$app->db->createCommand($sql)->bindValues($set_str_params)->execute();
    }

    /**
     * Функция получения url для запроса к Cakcle Api
     * @param string $modified Время последнего изменения
     * @param integer $page Необязательный параметр - возвращает отзывы с определенной (page) страницы. Это необходимо если кол-во отзывов превышает size.
     * @param integer $size Необязательный параметр - возвращает опр.кол-во отзывов
     * @return string
     */
    public function getUrl($modified = null, $page = null, $size = null)
    {
        $modified=is_null($modified)?$modified:$modified*1000;

        $url = "http://cackle.me/api/3.0/review/list.json?";
        $url .= "id={$this->_module->site_id}&";
        $url .= "siteApiKey={$this->_module->site_api_key}&";
        $url .= "accountApiKey={$this->_module->account_api_key}&";
        $url .= "modified={$modified}&";
        $url .= "page={$page}&";
        $url .= "size={$size}";

        return $url;
    }

    /**
     * Функция инициализации curl соединения
     */
    protected function curlInit()
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_USERAGENT,
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_ENCODING, "gzip, deflate");
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ['Content-type: application/x-www-form-urlencoded; charset=utf-8']);
    }

    /**
     * Функция выполнения curl запроса
     */
    protected function curlExec($url)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);

        return curl_exec($this->ch);
    }

    /**
     * Функция закрытия curl соединения
     */
    protected function curlClose()
    {
        curl_close($this->ch);
    }

    /**
     * Функция подготовки данных для импорта в базу
     * @param array $reviews Массив полученный из запроса с Cackle Api
     * @return array
     */
    function prepareItems($reviews)
    {
        $z = 0;
        $data = [];
        for ($i = 0; $i < count($reviews); $i++) {
            foreach ($reviews[$i] as $key => $item) {
                if (is_array($item)) {
                    foreach ($item as $item_key => $item_item) {
                        $data[$z][$key . '_' . $item_key] = $item_item;
                    }
                } else {
                    switch ($key) {
                        case 'id':
                            $data[$z]['cackle_id'] = $item;
                            break;
                        case 'created':
                        case 'modified':
                            $data[$z][$key] = strftime("%Y-%m-%d %H:%M:%S", $item / 1000);
                            break;
                        default:
                            $data[$z][$key] = $item;
                            break;
                    }
                }
            }
            $z++;
        }

        return $data;
    }

    /**
     * Функция формирования массива параметров или строки запроса
     * @param array $data Массив с данными
     * @param boolean $params Вывод массива параметров или строки запроса
     * @return mixed Строка запроса или массив параметров
     */
    function prepareData($data, $params = false)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $key_uniq = $key . '_' . substr(base64_encode(md5($value . $key)), 0, 6);
            if ($params) {
                $result[":" . $key_uniq] = $value;
            } else {
                $result[] = $key . "=:" . $key_uniq;
            }
        }

        return $params ? $result : implode(',', $result);
    }

    /**
     * Функция получения локальных отзывов
     * @param string $channel Канал сообщений
     * @return array
     */
    function getLocalReviews($channel = false)
    {
        $where['status']='approved';
        if($channel)
            $where['chan_channel']=$channel;

        return Reviews::find()->where($where)->asArray()->all();
    }
}
