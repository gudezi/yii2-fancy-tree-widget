yii2-fancytree-widget
=====================
The yii2-fancytree-widget is a Yii 2 wrapper for the [Fancytree](http://wwwendt.de/tech/fancytree/demo/). A JavaScript dynamic tree view plugin for jQuery with support for persistence, keyboard, checkboxes, tables, drag'n'drop, and lazy loading.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist gudezi/yii2-fancytree-widget "*"
```

or add

```
"gudezi/yii2-fancytree-widget": "*"
```

to the require section of your `composer.json` file.


How to use
----------
IMORTANT: Widget is fit to use with Nested Sets behavoiur. You need to install this behaviour before use this Fancy Tree widget.

On your view file.

```php

<?php
// Example of data.
$data = [
	['title' => 'Node 1', 'key' => 1],
	['title' => 'Folder 2', 'key' => '2', 'folder' => true, 'children' => [
		['title' => 'Node 2.1', 'key' => '3'],
		['title' => 'Node 2.2', 'key' => '4']
	]]
];

 <?= $form->field($model, 'attribute')->widget(FancytreeWidget::classname(), [
            'name' => 'fancytree',
            'source' => $data,
            'parent' =>$id // parent category id (if exist)
            'options' => [
            ],
        ]); ?>

```
