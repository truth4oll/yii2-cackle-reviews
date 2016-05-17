<?php
namespace evgenybukharev\yii2_cackle_reviews\helpers;

use Yii;
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

    function sync()
    {
        try {
            $this->curlInit();
            $url = $this->getUrl();
            $result = $this->curlExec($url);
            $reviews = Json::decode($result);

            $reviews['reviews'] = !empty($reviews['reviews']) ? $reviews['reviews'] : [];

            $this->saveReviews($reviews['reviews']);

//            $since = strtotime(Reviews::find()->max('modified'));

        } catch (Exception $e) {

        } finally {
            $this->curlClose();
        }

////        $apix = new CackleReviewAPI();
////        $review_last_modified = $apix->cackle_get_param("review_last_modified");
//        $review_last_modified=0;
//
//        if ($a == "all_reviews") {
//            $response1 = $this->get_reviews(0);
//        } else {
//            $response1 = $this->get_reviews($review_last_modified);
//        }
//        //get reviews from CackleReview Api for sync
//        if ($response1 == null) {
//            return false;
//        }
//        $response_size = $this->push_reviews($response1); // get review from array and insert it to wp db
//        $totalPages = $this->cackle_json_decodes($response1);
//
//
//        $totalPages = $totalPages['reviews']['totalPages'];
//        if ($totalPages > 1) {
//
//            for ($i = 1; $i < $totalPages; $i++) {
//
//                if ($a == "all_reviews") {
//                    $response2 = $this->get_reviews(0, $i);
//                } else {
//
//                    $response2 = $this->get_reviews($review_last_modified, $i);
//                }
//                //$response2 = $apix->get_reviews(($a=="all_reviews") ? 0 : cackle_get_param("cackle_review_last_modified",0),$i);
//                //get reviews from CackleReview Api for sync
//                $response_size = $this->push_reviews($response2); // get review from array and insert it to wp db
//            }
//        }
//
//        return "success";
    }

    function array2string($data){
        $str = [];
        foreach ($data as $key => $value) {
            if(is_array($value)){
                $str[]= $key."=". array2string($value);
            } else{
                $str[]= $bind?$key."=:".$key:":".$key."=".$value;
            }
        }
        return implode(',',$str);
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

        $sql='';

        for ($i = 0; $i < count($data); $i++) {
            $set_str=$this->array2string($data[$i]);
//            $sql="INSERT INTO ".Reviews::tableName()." SET $set_str ON DUPLICATE KEY UPDATE $set_str;";
        }
\FB::send($set_str);

//        return Yii::$app->db->createCommand($sql)->execute();

//        foreach ($comments as $comment) {
//            $q = \Yii::$app->db->createCommand();
//            try {
//                $q->insert(Comment::tableName(), [
//                    'id' => $comment['id'],
//                    'pubStatus' => (strtolower($comment['status']) == 'approved') ? 1 : 0,
//                    'pubStatus' => function ($comment) {
//                        switch (strtolower($comment['status'])) {
//                            case 'approved' :
//                                return Comment::STATUS_APPROVED;
//                                break;
//                            case 'pending' :
//                                return Comment::STATUS_PENDING;
//                                break;
//                            case 'spam' :
//                                return Comment::STATUS_SPAM;
//                                break;
//                            case 'deleted' :
//                                return Comment::STATUS_DELETED;
//                                break;
//                            default :
//                                return Comment::STATUS_PENDING;
//                        }
//                    },
//                    'channel' => $comment['channel'],
//                    'message' => $comment['message'],
//                    'dateCreate' => strftime("%Y-%m-%d %H:%M:%S", $comment['created'] / 1000),
//                    'dateModify' => strftime("%Y-%m-%d %H:%M:%S", $comment['modified'] / 1000),
//                    'autor' => $comment['author']['name'] ?: '',
//                    'email' => $comment['author']['email'] ?: '',
//                ])->execute();
//            } catch (IntegrityException $e) {
//                $q->update(Comment::tableName(), [
//                    'id' => $comment['id'],
//                    'pubStatus' => (strtolower($comment['status']) == 'approved') ? 1 : 0,
//                    'pubStatus' => function ($comment) {
//                        switch (strtolower($comment['status'])) {
//                            case 'approved' :
//                                return Comment::STATUS_APPROVED;
//                                break;
//                            case 'pending' :
//                                return Comment::STATUS_PENDING;
//                                break;
//                            case 'spam' :
//                                return Comment::STATUS_SPAM;
//                                break;
//                            case 'deleted' :
//                                return Comment::STATUS_DELETED;
//                                break;
//                            default :
//                                return Comment::STATUS_PENDING;
//                        }
//                    },
//                    'channel' => $comment['channel'],
//                    'message' => $comment['message'],
//                    'dateCreate' => strftime("%Y-%m-%d %H:%M:%S", $comment['created'] / 1000),
//                    'dateModify' => strftime("%Y-%m-%d %H:%M:%S", $comment['modified'] / 1000),
//                    'autor' => $comment['author']['name'] ?: '',
//                    'email' => $comment['author']['email'] ?: '',
//                ],
//                    [
//                        'id' => $comment['id'],
//                    ]);
//            }
//        }
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
     * Обертка над соединениями в curl.
     * Т.к. может быть несколько страниц с комментариями, то 1 раз инициализируем соединение,
     * а закроем его в самом конце.
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

    protected function curlExec($url)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);

        return curl_exec($this->ch);
    }

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
//
//    function get_reviews($review_last_modified, $cackle_page = 0)
//    {
//        $this->get_url = "http://cackle.me/api/2.0/review/list.json?id={$this->_module->site_id}&accountApiKey={$this->_module->account_api_key}&siteApiKey={$this->_module->site_api_key}";
//        $host = $this->get_url . "&modified=" . $review_last_modified . "&page=" . $cackle_page . "&size=100";
//
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $host);
//
//        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
//        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
//        //curl_setopt($ch,CURLOPT_ENCODING, '');
//        curl_setopt($ch, CURLOPT_HTTPHEADER, [
//                'Content-type: application/x-www-form-urlencoded; charset=utf-8',
//            ]
//        );
//        $result = curl_exec($ch);
//        curl_close($ch);
//
//        return $result;
//
//    }
//
//    function to_i($number_to_format)
//    {
//        return number_format($number_to_format, 0, '', '');
//    }
//
//
//    function cackle_json_decodes($response)
//    {
//
//        $obj = json_decode($response, true);
//
//        return $obj;
//    }
//
//    function filter_cp1251($string1)
//    {
//        $cackle_api = new CackleReviewAPI();
//        if ($cackle_api->cackle_get_param("cackle_encoding") == "1") {
//            $string2 = iconv("utf-8", "CP1251", $string1);
//            //print "###33";
//        }
//
//        return $string2;
//    }
//
//
//    function insert_review($review, $status)
//    {
//        $data = [];
//
//        if (is_array($review)) {
//            foreach ($review as $key => $item) {
//                if (is_array($item)) {
//                    foreach ($item as $item_key => $item_item)
//                        $data[$key . '_' . $item_key] = $item_item;
//                } else {
//                    switch ($key) {
//                        case 'id':
//                            $data['cackle_id'] = $item;
//                            break;
//                        case 'created':
//                        case 'modified':
//                            $data[$key] = strftime("%Y-%m-%d %H:%M:%S", $item / 1000);
//                            break;
//                        default:
//                            $data[$key] = $item;
//                            break;
//                    }
//                }
//            }
//        }
//
//
//        $cackle_api = new CackleReviewAPI();
//        $conn = $cackle_api->conn();
//
//        $fields = array_keys($data);
//        $values = array_values($data);
//        $fieldlist = implode(',', $fields);
//        $qs = str_repeat("?,", count($fields) - 1);
//        $sql = "insert into " . PREFIX . "_reviews ($fieldlist) values(${qs}?)";
//        $q = $conn->prepare($sql);
//        $q->execute($values);
//        $q = null;
//
//        $cackle_api->cackle_set_param("last_review", $data['channel']);
//        $get_last_modified = $cackle_api->cackle_get_param("review_last_modified");
//        $get_last_modified = (int)$get_last_modified;
//        if ($review['modified'] > $get_last_modified) {
//            $cackle_api->cackle_set_param("review_last_modified", (string)$data['modified']);
//        }
//
//    }
//
//    function review_status_decoder($review)
//    {
//        if (strtolower($review['status']) == "approved") {
//            $status = 1;
//        } elseif (strtolower($review['status'] == "pending") || strtolower($review['status']) == "rejected") {
//            $status = 2;
//        } elseif (strtolower($review['status']) == "spam") {
//            $status = 3;
//        } elseif (strtolower($review['status']) == "deleted") {
//            $status = 4;
//        }
//
//        return $status;
//    }
//
//    function update_review_status($review_id, $status, $modified, $review_content, $review_rating)
//    {
//        $apix = new CackleReviewAPI();
//        $cackle_api = new CackleReviewAPI();
//        $sql = "update " . PREFIX . "_reviews set approve = ? , comment = ? , rating = ? where user_agent = ?";
//        $conn = $cackle_api->conn();
//        if ($cackle_api->cackle_get_param("cackle_encoding") == 1) {
//
//            $conn->exec('SET NAMES cp1251');
//        } else {
//            $conn->exec('SET NAMES utf8');
//        }
//        $q = $conn->prepare($sql);
//        $q->execute([$status, $review_content, $review_rating, "CackleReview:$review_id"]);
//        $q = null;
//        if ($modified > $apix->cackle_get_param('review_last_modified', 0)) {
//            $cackle_api->cackle_set_param("review_last_modified", $modified);
//        }
//
//    }
//
//    function push_reviews($response)
//    {
//        $apix = new CackleReviewAPI();
//        $obj = $this->cackle_json_decodes($response, true);
//        $obj = $obj['reviews']['content'];
//        if ($obj) {
//            $reviews_size = count($obj);
//            if ($reviews_size != 0) {
//                foreach ($obj as $review) {
//                    if ($review['id'] > $apix->cackle_get_param('last_review')) {
//                        $this->insert_review($review, $this->review_status_decoder($review));
//                    } else {
//                        // if ($review['modified'] > $apix->cackle_get_param('cackle_review_last_modified', 0)) {
//                        $this->update_review_status($review['id'], $this->review_status_decoder($review), $review['modified'], $review['comment'], $review['rating']);
//                        // }
//                    }
//                }
//            }
//        }
//
//        return $reviews_size;
//
//    }

}
