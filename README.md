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
            'cackle-reviews' => [
                'class' => evgenybukharev\yii2-cackle-reviews\Module::className(),
                'siteId' => YOUR_ID,
                'accountAPIKey' => 'YOUR_KEY',
                'siteAPIKey' => 'YOUR_KEY',
            ],
        ],
    ```
С более подробной информацией, как получить `accountAPIKey` и `siteAPIKey` Вы можете ознакомиться на странице [http://cackle.me/help/review-import]

Профит!
