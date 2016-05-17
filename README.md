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
Более подробную информацию, как получить `accountAPIKey` и `siteAPIKey` Вы можете получить на странице [http://cackle.ru/help/comment-sync]
Выполните миграцию:
```
php yii mgrate/up --migrationPath="@vendor/evgenybukharev/yii2-cackle-reviews/migrations"
```
