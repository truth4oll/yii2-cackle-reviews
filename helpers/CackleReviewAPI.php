<?php
namespace evgenybukharev\yii2_cackle_reviews\helpers;

use PDO;


define('PREFIX', 'vr');


class CackleReviewAPI
{

    public $site_id              = '44504';
    public $site_api             = 'vvd5dw6iiwhyIKqxmPGMdzHMO1dKtzyteJTXPXI9OrfY21zTyD3tLdxBssYvLXnJ';
    public $account_api          = 'gcHjfYxeYxqBNkfGcXHVopbzqyyjtLfS1tPm9D1zfGySMZGwjXY5dwFakqmImKH0';
    public $review_last_modified = '';

    function __construct()
    {
        $this->last_error = null;
    }

    function db_connect($query)
    {
        try {
            $DBH = \Yii::$app->db->getMasterPdo();

            if ($this->cackle_get_param("cackle_encoding") == 1) {

                $DBH->exec('SET NAMES cp1251');
            } else {
                $DBH->exec('SET NAMES utf8');
            }

            $DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $STH = $DBH->query($query);

            #  устанавливаем режим выборки
            $STH->setFetchMode(PDO::FETCH_ASSOC);
            $x = 0;
            $row = [];
            while ($res = $STH->fetch()) {
                $row[$x] = $res;
                $x++;
            }
            $DBH = null;

            return $row;
        } catch (PDOException $e) {
            // echo "invalid sql - $query - ";
            file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
        }
    }

    function conn()
    {
        try {
            $DBH = \Yii::$app->db->getMasterPdo();
            $DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $DBH;
        } catch (PDOException $e) {
            echo "invalid sql - $query - ";
            file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
        }
    }

    function db_table_exist($table)
    {
        $table_exists = \Yii::$app->db->schema->getTableSchema($table) == null ? false : true;

        return $table_exists;
    }

    function db_column_exist($table, $column)
    {
        if ($this->db_table_exist($table)) {
            $DBH = \Yii::$app->db->getMasterPdo();
            $quer = "SHOW COLUMNS FROM $table LIKE '$column'";
            $column_exist = $DBH->query($quer)->fetch();
            $column_exist = $column_exist['Field'];

            //$column_exist = (gettype($DBH->query("SHOW COLUMNS FROM $table LIKE '$column''")) == "integer")?true:false;
            return $column_exist;
            //return $quer;
        } else {
            return false;
        }
    }

    function cackle_set_param($param, $value)
    {
        $this->$param = $value;
    }

    function cackle_get_param($param, $default = 0)
    {
        return !empty($this->$param) ? $this->$param : $default;
    }


    function get_last_error()
    {
        if (empty($this->last_error)) return;
        if (!is_string($this->last_error)) {
            return var_export($this->last_error);
        }

        return $this->last_error;
    }

    function curl($url)
    {
        $ch = curl_init();
        $php_version = phpversion();
        $useragent = "Drupal";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["referer" => "localhost"]);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

}