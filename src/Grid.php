<?php


namespace vivretech\rest\grid;


use Yii;
use vivretech\rest\grid\renderer\GridViewRenderer;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataFilter;
use yii\i18n\Formatter;
use yii\web\Request;
use vivretech\rest\renderer\DataRenderer;


class Grid extends BaseObject
{


    /* @var string Grid Id. */
    protected $_id;

    /**
     * @var int a counter used to generate [[id]] for grid.
     * @internal
     */
    public static $counter = 0;

    /**
     * @var array parameters (name => value) that should be used to obtain the current layout rendering zones.
     * If not set, `$_GET` will be used instead.
     *
     * If the element does not exist, the [[defaultLayout]] will be used.
     *
     * @see layoutParam
     * @see defaultLayout
     */
    public $params;

    /**
     * @var \yii\data\DataProviderInterface the data provider for the view. This property is required.
     */
    public $dataProvider;

    /**
     * @var DataRenderer the data renderer for the view. This property is required.
     */
    protected $dataRenderer;

    /**
     * @var string the caption of the grid table
     */
    public $caption;

    /**
     * @var string the description of the grid table
     */
    public $description;

    /**
     * @var \yii\db\QueryInterface the data source query.
     * Note: this field will be ignored in case [[dataProvider]] is set.
     */
    public $query;

    /**
     * @var int the number of records to be fetched in each batch.
     * This property takes effect only in case of [[query]] usage.
     */
    public $batchSize = 100;

    /**
     * @var array|Formatter the formatter used to format model attribute values into displayable texts.
     * This can be either an instance of [[Formatter]] or an configuration array for creating the [[Formatter]]
     * instance. If this property is not set, the "formatter" application component will be used.
     */
    public $formatter;

    /**
     * @var array|DataColumn[] grid column configuration. Each array element represents the configuration
     * for one particular grid column. For example:
     *
     * ```php
     * [
     *     [
     *         'class' => DataColumn::className(), // this line is optional
     *         'attribute' => 'name',
     *         'label' => 'Name'
     *     ],
     * ]
     * ```
     *
     * If a column is of class [[DataColumn]], the "class" element can be omitted.
     */
    public $columns = [];

    /**
     * @var bool whether to show the header section of the grid table -> columns.
     */
    public $showHeader = true;

    /**
     * @var bool whether to show the filters section of the grid table.
     */
    public $showFilters = true;

    /**
     * @var bool whether to show the footer section of the grid table -> columns.
     */
    public $showFooter = false;

    /* @var array */
    public $options = [];

    /**
     * @var array the data display when the content of a cell is empty.
     * This property is used to render cells that have no defined content,
     * e.g. empty footer or filter cells.
     *
     * Note that this is not used by the [[DataColumn]] if a data item is `null`. In that case
     * the [[\yii\i18n\Formatter::nullDisplay|nullDisplay]] property of the [[formatter]] will
     * be used to indicate an empty data value.
     */
    public $emptyCell = null;

    /**
     * @var \yii\base\Model the model that keeps the user-entered filter data. When this property is set,
     * the grid view will enable column-based filtering.
     *
     * Note that in order to show an input field for filtering, a column must have its [[DataColumn::attribute]]
     * property set and the attribute should be active in the current scenario of $filterModel or have
     * [[DataColumn::filter]] set as the HTML code for the input field.
     *
     * When this property is not set (null) the filtering feature is disabled.
     */
    public $filterModel;

    /**
     * @var array define a list of allowed layout items.
     * The following tokens will be replaced with the corresponding section contents:
     *
     * - `metadata`: the grid metadata.
     * - `pager`: the pager.
     * - `columns`: the list columns.
     * - `items`: the list items.
     */
    private $allowedLayout = ['metadata', 'pager', 'columns', 'items'];

    /**
     * @var string the character used to separate different attributes that need to be used by layout.
     */
    public $separator = ',';

    /**
     * @var string the name of the parameter that specifies which tokens will be used for rendering grid.
     * Defaults to `layout`.
     */
    public $layoutParam = 'layout';

    /**
     * @var array the default layout that determines how different sections of the grid view should be organized.
     * The following tokens will be replaced with the corresponding section contents:
     *
     * - `metadata`: the grid metadata.
     * - `pager`: the pager.
     * - `columns`: the list columns.
     * - `items`: the list items.
     */
    public $defaultLayout = ['metadata', 'pager', 'columns', 'items'];

    /**
     * @var callable a PHP callable that will be called to return the new row cell content
     * The signature of the callable should be:
     *
     * ```php
     * function ($grid, $cellContent) {
     *     // $grid is the Grid object currently running
     *     // $cellContent is the main row cell data that the grid is currently rendered.
     * }
     * ```
     *
     * The callable should return an array with the new cell data content.
     */
    public $rowDataCellRender;

    /**
     * @var array the currently requested layout order as computed by [[getLayoutSections]].
     */
    private $_layout = null;


    /**
     * Returns the ID of the grid.
     * @return string ID of the grid.
     */
    public function getId()
    {
        if (empty($this->_id) )
        {
            $this->_id = md5(self::className()) . '_'. static::$counter++;
        }

        return $this->_id;
    }


    /**
     * Initializes the grid view.
     * This method will initialize required property values and instantiate [[columns]] objects.
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        /* Init DataProvider if there is nothing set. */
        if (empty($this->dataProvider))
        {
            if ($this->query !== null)
            {
                $this->dataProvider = Yii::createObject([
                    'class' => ActiveDataFilter::className(),
                    'query' => $this->query,
                    'pagination' => [
                        'pageSize' => $this->batchSize
                    ]
                ]);
            }
        }

        /* Init DataRenderer if there is nothing set. */
        if (empty($this->dataRenderer))
        {
            $this->dataRenderer = Yii::createObject([
                'class' => GridViewRenderer::className(),
            ]);
        }

        /* Init Formatter if there is nothing set. */
        if ($this->formatter === null)
        {
            $this->formatter = Yii::$app->getFormatter();

        } elseif (is_array($this->formatter)) {
            $this->formatter = Yii::createObject($this->formatter);

        }

        if (!$this->formatter instanceof Formatter) {
            throw new InvalidConfigException('The "formatter" property must be either a Format object or a configuration array.');
        }

        /* Init columns. */
        $this->initColumns();
    }


    /**
     * Run.
     */
    public function run()
    {
        return $this->dataRenderer->run('main', [[
            'grid' => $this
        ]]);
    }


    /**
     * Returns the currently requested layout information.
     * @param bool $refresh whether to refresh the layout
     * @return array layout attributes.
     */
    public function getLayoutSections($refresh = false)
    {
        if ($this->_layout === null || $refresh)
        {
            $this->_layout = [];

            if (($params = $this->params) === null)
            {
                $request = Yii::$app->getRequest();
                $params = $request instanceof Request ? $request->getQueryParams() : [];
            }

            if (isset($params[$this->layoutParam]))
            {
                $attributes = $this->parseLayoutParam($params[$this->layoutParam]);

                foreach ($attributes as $attribute)
                {
                    if (in_array($attribute, $this->allowedLayout))
                    {
                        $this->_layout[] = $attribute;
                    }
                }
            }

            if (empty($this->_layout) && is_array($this->defaultLayout)) {
                $this->_layout = $this->defaultLayout;
            }
        }

        return $this->_layout;
    }


    /**
     * Parses the value of [[layoutParam]] into an array of layout attributes.
     *
     * The format must be the attribute name only.
     *
     * @param string $param the value of the [[layoutParam]].
     * @return array the valid layout attributes.
     * @see $separator for the attribute name separator.
     * @see $layoutParam
     */
    private function parseLayoutParam($param)
    {
        return is_scalar($param) ? explode($this->separator, $param) : [];
    }


    /**
     * Creates column objects and initializes them.
     * @return void
     * @throws \yii\base\InvalidConfigException
     */
    protected function initColumns()
    {
        if (empty($this->columns))
        {
            $this->guessColumns();
        }

        foreach ($this->columns as $i => $column)
        {
            if (is_string($column)) {
                $column = $this->createDataColumn($column);

            } else {

                $column = Yii::createObject(array_merge([
                    'class' => DataColumn::className(),
                    'grid'  => $this,
                ], $column));

            }

            if (($column instanceof DataColumn) === false)
            {
                throw new InvalidConfigException('The column must be class type of ' . DataColumn::className());
            }

            if (!$column->visible)
            {
                unset($this->columns[$i]);
                continue;
            }

            $this->columns[$i] = $column;
        }
    }


    /**
     * Creates a [[DataColumn]] object based on a string in the format of "attribute:format:label".
     * @param string $text the column specification string
     * @return DataColumn the column instance
     * @throws InvalidConfigException if the column specification is invalid
     */
    protected function createDataColumn($text)
    {
        if (!preg_match('/^([^:]+)(:(\w*))?(:(.*))?$/', $text, $matches))
        {
            throw new InvalidConfigException('The column must be specified in the format of "attribute", "attribute:format" or "attribute:format:label"');
        }

        return Yii::createObject([
            'class' => DataColumn::className(),
            'grid' => $this,
            'attribute' => $matches[1],
            'format' => isset($matches[3]) ? $matches[3] : 'raw',
            'label' => isset($matches[5]) ? $matches[5] : null,
        ]);
    }


    /**
     * This function tries to guess the columns to show from the given data
     * if [[columns]] are not explicitly specified.
     */
    protected function guessColumns()
    {
        $models = $this->dataProvider->getModels();
        $model = reset($models);

        if (is_array($model) || is_object($model))
        {
            foreach ($model as $name => $value)
            {
                if ($value === null || is_scalar($value) || is_callable([$value, '__toString']))
                {
                    $this->columns[] = (string) $name;
                }
            }
        }
    }


}
