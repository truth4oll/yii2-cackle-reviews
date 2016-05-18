yii2-cackle-reviews
===========

Установка
-------------
1. Добавьте "yii2-cackle-reviews" в секцию "require" файла composer.json вашего проекта:
    <pre>
       {
            "require": {
                "evgenybukharev/yii2-cackle-reviews": "dev-master"
            }
       }
    </pre>
2. Запустите обновление менеджера пакетов 
    <pre>
      php composer.phar update
    </pre>

3. Выполните миграции
    <pre>
    php yii migrate/up --migrationPath=@vendor/evgenybukharev/yii2-cackle-reviews/migrations
    </pre>

4. Настройте модуль в конфигурационном файле
    ```php
    'modules' => [
            'cackle_reviews' => [
                'class' => 'evgenybukharev\yii2_cackle_reviews\Module',
                'site_id' => 'YOUR_ID',
                'account_api_key' => 'YOUR_KEY',
                'site_api_key' => 'YOUR_KEY',
            ],
        ],
    ```
    С более подробной информацией, как получить `account_api_key` и `site_api_key` Вы можете ознакомиться на странице [http://cackle.me/help/review-import]

5. Вызов виджета
   ```php
    echo CackleReviewWidget::widget([
        'sync' => true,
        'params' => [
            'channel' => 'your_product_id',
        ]
    ]);
    ```
6. Синхронизация отзывов с сервером cackle.

   Реализовано 2 типа синхронизации, непосредственно через виджет, указав параметр sync=>true, либо используя консольную команду:
     ```
    yii cackle_reviews/sync
     ```
Профит!
