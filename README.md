# dbmodel
A class which makes easy database actions

## Database connection

Initiaze class with an array which contains database information.

```php
require_once __DIR__ . '/Model.php';


$dbConfig = [
    'dbHost' => 'localhost',
    'dbName' => 'db',
    'dbUser' => 'root',
    'dbPassword' => 'root',
    'charset' => 'utf8'
];

$model = new Model($dbConfig);
```


## Select Examples

Get one row

```php

$model->setTable('users')->select()->where('user_id','1');
$result = $model->get();
```

Get All Rows

```php

$model->setTable('users');
$model->select();
$model->where('username','john', 'LIKE');
$result = $model->getAll();
```
## Insert Examples

```php
$model->setTable('users');
$model->insert([
    'username' => 'User',
    'user_email' => 'mail@example.com',
]);

$userId = $model->insertId();
```
