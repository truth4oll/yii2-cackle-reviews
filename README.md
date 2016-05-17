yii2-cackle-reviews
===========


Установка
------------
Добавьте в секцию require вашего файла `composer.json`
```
"evgenybukharev/yii2-cackle-reviews": "*"
```
Добавьте в конфигурационный файл вашего приложения:
```
modules => [
    'cackle-reviews' => [
                'class' => evgenybukharev\yii2-cackle-reviews\Module::className(),
                'siteId' => YOUR_ID,
                'accountAPIKey' => 'YOUR_KEY',
                'siteAPIKey' => 'YOUR_KEY',
            ],
]
```
С более подробной информацией, как получить `accountAPIKey` и `siteAPIKey` Вы можете ознакомиться на странице [http://cackle.me/help/review-import]

Миграция
```
php yii mgrate/up --migrationPath="@vendor/evgenybukharev/yii2-cackle-reviews/migrations"
```
