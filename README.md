This extension provides the ability like GridView but for REST.


For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/vivre-tech/rest-grid/v/stable.png)](https://packagist.org/packages/vivre-tech/rest-grid)
[![Total Downloads](https://poser.pugx.org/vivre-tech/rest-grid/downloads.png)](https://packagist.org/packages/vivre-tech/rest-grid)
[![Build Status](https://travis-ci.org/vivre-tech/rest-grid.svg?branch=master)](https://travis-ci.org/vivre-tech/rest-grid)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist vivre-tech/rest-grid
```

or add

```json
"vivre-tech/rest-grid": "*"
```

to the require section of your composer.json.


Usage
------------

Example:
```php
$arrayRows = [];
foreach(range(1, 100) as $item)
{
    $arrayRows[] = [
        'id' => $item,
        'name' => 'Product ' . $item,
        'price' => 100 + $item,
        'created_at' => date('Y-m-d H:i:s')
    ];
}

$dataProvider = new \yii\data\ArrayDataProvider([
    'allModels' => $arrayRows,
    'pagination' => [
        'pageSize' => 5,
    ],
]);


$grid = new \vivretech\rest\grid\Grid([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'id'
        ],
        [
            'attribute' => 'name'
        ],
        [
            'attribute' => 'price'
        ],
        [
            'attribute' => 'created_at'
        ]
    ]
]);

return $grid->run();
```


Response
------------
```json
{
    "metadata": {
        "id": "aa899d9e6e420821e20f04ea857e0c2d_0",
        "caption": null,
        "description": null,
        "options": [],
        "header": {
            "show": true
        },
        "filters": {
            "show": true
        },
        "footer": {
            "show": false
        },
        "request_params": {
            "pager": {
                "param": "page",
                "size": "per-page"
            },
            "sorter": {
                "param": "sort",
                "separator": ",",
                "multi_sort": false
            }
        }
    },
    "pager": {
        "results": {
            "total": 100,
            "per_page": 5
        },
        "pages": {
            "total": 20,
            "current": 1
        }
    },
    "columns": [
        {
            "label": "Id",
            "attribute": "id",
            "description": null,
            "sortable": true,
            "filterable": false,
            "header": {
                "value": [],
                "options": []
            },
            "filter": {
                "selected": null,
                "items": [],
                "options": []
            },
            "row": {
                "options": []
            },
            "footer": {
                "value": [],
                "options": []
            }
        },
        {
            "label": "Name",
            "attribute": "name",
            "description": null,
            "sortable": true,
            "filterable": false,
            "header": {
                "value": [],
                "options": []
            },
            "filter": {
                "selected": null,
                "items": [],
                "options": []
            },
            "row": {
                "options": []
            },
            "footer": {
                "value": [],
                "options": []
            }
        },
        {
            "label": "Price",
            "attribute": "price",
            "description": null,
            "sortable": true,
            "filterable": false,
            "header": {
                "value": [],
                "options": []
            },
            "filter": {
                "selected": null,
                "items": [],
                "options": []
            },
            "row": {
                "options": []
            },
            "footer": {
                "value": [],
                "options": []
            }
        },
        {
            "label": "Created At",
            "attribute": "created_at",
            "description": null,
            "sortable": true,
            "filterable": false,
            "header": {
                "value": [],
                "options": []
            },
            "filter": {
                "selected": null,
                "items": [],
                "options": []
            },
            "row": {
                "options": []
            },
            "footer": {
                "value": [],
                "options": []
            }
        }
    ],
    "items": [
        [
            {
                "id": 1
            },
            {
                "name": "Product 1"
            },
            {
                "price": 101
            },
            {
                "created_at": "2017-12-14 11:13:57"
            }
        ],
        [
            {
                "id": 2
            },
            {
                "name": "Product 2"
            },
            {
                "price": 102
            },
            {
                "created_at": "2017-12-14 11:13:57"
            }
        ],
        [
            {
                "id": 3
            },
            {
                "name": "Product 3"
            },
            {
                "price": 103
            },
            {
                "created_at": "2017-12-14 11:13:57"
            }
        ],
        [
            {
                "id": 4
            },
            {
                "name": "Product 4"
            },
            {
                "price": 104
            },
            {
                "created_at": "2017-12-14 11:13:57"
            }
        ],
        [
            {
                "id": 5
            },
            {
                "name": "Product 5"
            },
            {
                "price": 105
            },
            {
                "created_at": "2017-12-14 11:13:57"
            }
        ]
    ]
}
```


Unit Testing
------------
If you run the following command: `composer install` in a dev environment then you will find `phpunit` in `/vendor/bin/phpunit`.

In case `phpunit` in not installed via command `composer install`, just fallow next steps:
1. run in console/terminal `brew install phpunit`

To test, in the `root` of the project, base on how `phpunit` is installed you will have two choices to run:
1. installed via command `composer install` you will have to execute in console/terminal: `vendor/bin/phpunit`
2. installed via `brew` you will have to execute in console/terminal: `phpunit`
