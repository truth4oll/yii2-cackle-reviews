<?php
namespace evgenybukharev\yii2_cackle_reviews\helpers;
//require_once(dirname(__FILE__) . '/cackle_api.php');

class CackleReviewSync
{
    function __construct()
    {
        $cackle_api = new CackleReviewAPI();
        $this->siteId = $cackle_api->cackle_get_param("site_id");
        $this->accountApiKey = $cackle_api->cackle_get_param("account_api");
        $this->siteApiKey = $cackle_api->cackle_get_param("site_api");
    }

    function init($a = "")
    {
        $apix = new CackleReviewAPI();
        $review_last_modified = $apix->cackle_get_param("review_last_modified");

        if ($a == "all_reviews") {
            $response1 = $this->get_reviews(0);
        } else {
            $response1 = $this->get_reviews($review_last_modified);
        }

        //get reviews from CackleReview Api for sync
        if ($response1 == null) {
            return false;
        }
        $response_size = $this->push_reviews($response1); // get review from array and insert it to wp db
        $totalPages = $this->cackle_json_decodes($response1);

        $totalPages = $totalPages['reviews']['totalPages'];
        if ($totalPages > 1) {

            for ($i = 1; $i < $totalPages; $i++) {

                if ($a == "all_reviews") {
                    $response2 = $this->get_reviews(0, $i);
                } else {

                    $response2 = $this->get_reviews($review_last_modified, $i);
                }
                //$response2 = $apix->get_reviews(($a=="all_reviews") ? 0 : cackle_get_param("cackle_review_last_modified",0),$i);
                //get reviews from CackleReview Api for sync
                $response_size = $this->push_reviews($response2); // get review from array and insert it to wp db
            }
        }

        return "success";
    }

    function get_reviews($review_last_modified, $cackle_page = 0)
    {
        $this->get_url = "http://cackle.me/api/2.0/review/list.json?id=$this->siteId&accountApiKey=$this->accountApiKey&siteApiKey=$this->siteApiKey";
        $host = $this->get_url . "&modified=" . $review_last_modified . "&page=" . $cackle_page . "&size=100";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host);

        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
        //curl_setopt($ch,CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-type: application/x-www-form-urlencoded; charset=utf-8',
            ]
        );
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;

    }

    function to_i($number_to_format)
    {
        return number_format($number_to_format, 0, '', '');
    }


    function cackle_json_decodes($response)
    {

        $obj = json_decode($response, true);

        return $obj;
    }

    function filter_cp1251($string1)
    {
        $cackle_api = new CackleReviewAPI();
        if ($cackle_api->cackle_get_param("cackle_encoding") == "1") {
            $string2 = iconv("utf-8", "CP1251", $string1);
            //print "###33";
        }

        return $string2;
    }


    function insert_review($review, $status)
    {
        $data = [];

        if (is_array($review)) {
            foreach ($review as $key => $item) {
                if (is_array($item)) {
                    foreach ($item as $item_key => $item_item)
                        $data[$key . '_' . $item_key] = $item_item;
                } else {
                    switch ($key) {
                        case 'id':
                            $data['cackle_id'] = $item;
                            break;
                        case 'created':
                        case 'modified':
                            $data[$key] = strftime("%Y-%m-%d %H:%M:%S", $item / 1000);
                            break;
                        default:
                            $data[$key] = $item;
                            break;
                    }
                }
            }
        }


        $cackle_api = new CackleReviewAPI();
        $conn = $cackle_api->conn();

        $fields = array_keys($data);
        $values = array_values($data);
        $fieldlist = implode(',', $fields);
        $qs = str_repeat("?,", count($fields) - 1);
        $sql = "insert into " . PREFIX . "_reviews ($fieldlist) values(${qs}?)";
        $q = $conn->prepare($sql);
        $q->execute($values);
        $q = null;

        $cackle_api->cackle_set_param("last_review", $data['channel']);
        $get_last_modified = $cackle_api->cackle_get_param("review_last_modified");
        $get_last_modified = (int)$get_last_modified;
        if ($review['modified'] > $get_last_modified) {
            $cackle_api->cackle_set_param("review_last_modified", (string)$data['modified']);
        }

    }

    function review_status_decoder($review)
    {
        if (strtolower($review['status']) == "approved") {
            $status = 1;
        } elseif (strtolower($review['status'] == "pending") || strtolower($review['status']) == "rejected") {
            $status = 2;
        } elseif (strtolower($review['status']) == "spam") {
            $status = 3;
        } elseif (strtolower($review['status']) == "deleted") {
            $status = 4;
        }

        return $status;
    }

    function update_review_status($review_id, $status, $modified, $review_content, $review_rating)
    {
        $apix = new CackleReviewAPI();
        $cackle_api = new CackleReviewAPI();
        $sql = "update " . PREFIX . "_reviews set approve = ? , comment = ? , rating = ? where user_agent = ?";
        $conn = $cackle_api->conn();
        if ($cackle_api->cackle_get_param("cackle_encoding") == 1) {

            $conn->exec('SET NAMES cp1251');
        } else {
            $conn->exec('SET NAMES utf8');
        }
        $q = $conn->prepare($sql);
        $q->execute([$status, $review_content, $review_rating, "CackleReview:$review_id"]);
        $q = null;
        if ($modified > $apix->cackle_get_param('review_last_modified', 0)) {
            $cackle_api->cackle_set_param("review_last_modified", $modified);
        }

    }

    function push_reviews($response)
    {
        $apix = new CackleReviewAPI();
        $obj = $this->cackle_json_decodes($response, true);
        $obj = $obj['reviews']['content'];
        if ($obj) {
            $reviews_size = count($obj);
            if ($reviews_size != 0) {
                foreach ($obj as $review) {
                    if ($review['id'] > $apix->cackle_get_param('last_review')) {
                        $this->insert_review($review, $this->review_status_decoder($review));
                    } else {
                        // if ($review['modified'] > $apix->cackle_get_param('cackle_review_last_modified', 0)) {
                        $this->update_review_status($review['id'], $this->review_status_decoder($review), $review['modified'], $review['comment'], $review['rating']);
                        // }
                    }
                }
            }
        }

        return $reviews_size;

    }

}
