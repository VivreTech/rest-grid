<?php


namespace vivretech\tests\unit\rest\grid;


use vivretech\rest\grid\Grid;
use yii\data\SqlDataProvider;

final class GridTest extends TestCase
{

    /* @var string In memory table name. */
    private $tableName = 'product';

    /* @var array In memory table columns. */
    private $tableColumns = [
        'id' => 'pk',
        'name' => 'string',
        'price' => 'decimal',
        'created_at' => 'datetime'
    ];

    /* @var integer */
    private $rowsToGenerate = 100;


    /**
     * Setup tables for test ActiveRecord
     */
    protected function setupTestDbData()
    {
        $db = \Yii::$app->getDb();

        /* Create table with columns. */
        $db->createCommand()->createTable($this->tableName, $this->tableColumns)->execute();

        $rows = [];
        foreach (range(1, $this->rowsToGenerate) as $item)
        {
            $rows[] = [
                'Product ' . $item,
                100 + $item,
                '2017-12-12 00:00:00'
            ];
        }

        /* Populate table with rows. */
        $db->createCommand()->batchInsert(
            $this->tableName,
            array_diff(array_keys($this->tableColumns), ['id']),
            $rows
        )->execute();
    }


    /**
     * @return SqlDataProvider
     * @throws \yii\db\Exception
     */
    protected function getSqlDataProvider()
    {
        $count = \Yii::$app->db->createCommand("SELECT COUNT(*) FROM `{$this->tableName}`")->queryScalar();
        $sqlCommandText = "SELECT * FROM `{$this->tableName}`";

        $provider = new SqlDataProvider([
            'sql' => $sqlCommandText,
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $provider;
    }


    /* --------------------------------- */
    /*           Running Tests.          */
    /* --------------------------------- */
    public function testGridWithSqlDataProvider()
    {
        $this->setupTestDbData();
        $provider = $this->getSqlDataProvider();
        $grid = new Grid([
            'dataProvider' => $provider,
            'defaultLayout' => ['metadata', 'pager', 'columns', 'items'],
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
                ],
            ]
        ]);

        $result = $grid->run();


        /* test response. */
        $this->assertArrayHasKey('metadata', $result);
        $this->assertArrayHasKey('pager', $result);
        $this->assertArrayHasKey('columns', $result);
        $this->assertArrayHasKey('items', $result);


        /* Test Pager. */
        $this->assertTrue($result['pager']['results']['total'] == $this->rowsToGenerate);


        /* Test columns. */
        $collectColumnsFromResult = [];
        $columnsFromDb = array_keys($this->tableColumns);

        foreach($result['columns'] as $column)
        {
            $collectColumnsFromResult[] = $column['attribute'];
        }

        $this->assertEquals($columnsFromDb, $collectColumnsFromResult);


        /* Test First Item. */
        $item = reset($result['items']);
        $itemExpectations = [
            [
                'id' => 1,
            ],

            [
                'name' => 'Product 1'
            ],

            [
                'price' => 101
            ],

            [
                'created_at' => '2017-12-12 00:00:00'
            ]
        ];
        $this->assertEquals($item, $itemExpectations);
    }


}
