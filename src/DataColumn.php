<?php


namespace vivretech\rest\grid;


use yii\base\BaseObject;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\ActiveQueryInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;


class DataColumn extends BaseObject
{

    /**
     * @var Grid the grid view object that owns this column.
     */
    public $grid;

    /**
     * @var string the attribute name associated with this column. When [[value]]
     * is specified, the value of the specified attribute will be retrieved from each data model and displayed.
     *
     * Also, if [[label]] is not specified, the label associated with the attribute will be displayed.
     */
    public $attribute;

    /**
     * @var string label to be displayed in the [[header|header cell]].
     * If it is not set and the models provided by the Grid data provider are instances
     * of [[\yii\db\ActiveRecord]], the label will be determined using [[\yii\db\ActiveRecord::getAttributeLabel()]].
     * Otherwise [[\yii\helpers\Inflector::camel2words()]] will be used to get a label.
     */
    public $label;

    /**
     * @var string description to be displayed in the [[header|header cell]].
     */
    public $description;

    /**
     * @var string|array|Closure an anonymous function or a string that is used to determine the value to display in the current column.
     *
     * If this is an anonymous function, it will be called for each row and the return value will be used as the value to
     * display for every data model. The signature of this function should be: `function ($model, $key, $index, $column)`.
     * Where `$model`, `$key`, and `$index` refer to the model, key and index of the row currently being rendered
     * and `$column` is a reference to the [[DataColumn]] object.
     *
     * You may also set this property to a string representing the attribute name to be displayed in this column.
     * This can be used when the attribute to be displayed is different from the [[attribute]].
     *
     * If this is not set, `$model[$attribute]` will be used to obtain the value, where `$attribute` is the value of [[attribute]].
     */
    public $value;

    /**
     * @var string|array|Closure in which format should the value of each data model be displayed as (e.g. `"raw"`, `"text"`, `"html"`,
     * `['date', 'php:Y-m-d']`). Supported formats are determined by the [[Grid::formatter|formatter]] used by
     * the [[Grid]]. Default format is "raw".
     * @see \yii\i18n\Formatter::format()
     */
    public $format = 'raw';

    /**
     * @var array the header cell content.
     */
    public $header;

    /**
     * @var array the footer cell content.
     */
    public $footer;

    /**
     * @var bool whether this column is visible. Defaults to true.
     */
    public $visible = true;

    /**
     * @var array the attributes for the row group tag.
     */
    public $rowOptions = [];

    /**
     * @var array the attributes for the header cell tag.
     */
    public $headerOptions = [];

    /**
     * @var array the attributes for the footer cell tag.
     */
    public $footerOptions = [];

    /**
     * @var bool whether to allow sorting by this column.
     */
    public $enableSorting = true;

    /**
     * @var bool whether to allow filtering by this column.
     */
    public $enableFiltering = false;

    /**
     * @var array the array code representing a filter input (e.g. a dropdown list)
     * that is used for this data column.
     * - If this property is an array, a dropdown list will be generated that uses this property value as
     *   the list options.
     * - If you don't want a filter for this data column, set this value to be false or set enableFiltering to false.
     */
    public $filter;


    /**
     * @return string
     */
    public function renderLabel()
    {
        $label = $this->getHeaderCellLabel();

        return $label;
    }


    /**
     * @return string|array
     */
    public function renderDescription()
    {
        return $this->description;
    }


    /**
     * Renders a data cell.
     * @param mixed $model the data model being rendered
     * @param mixed $key the key associated with the data model
     * @param int $index the zero-based index of the data item among the item array returned by [[Grid::dataProvider]].
     * @return array the rendering result
     */
    public function renderDataCell($model, $key, $index)
    {
        return $this->renderDataCellContent($model, $key, $index);
    }


    /**
     * Renders the header cell.
     * @return array
     */
    public function renderHeaderCell()
    {
        $value = [
            'value' => empty($this->header) ? [] : $this->header,
            'options' => empty($this->headerOptions) ? [] : $this->headerOptions
        ];
        return $value;
    }


    /**
     * Renders the filter cell.
     * @return array
     */
    public function renderFilterCell()
    {
        $selectedValue = null;

        if (empty($this->grid->filterModel) === false)
        {
            if (in_array($this->attribute, $this->grid->filterModel->attributes()))
            {
                $selectedValue = $this->grid->filterModel->{$this->attribute};
            }
        }

        $return = [
            'selected' => $selectedValue,
            'items' => empty($this->filter) ? [] : $this->filter,
            'options' => []
        ];
        return $return;
    }


    /**
     * Renders the footer cell.
     * @return array
     */
    public function renderFooterCell()
    {
        $value = [
            'value' => empty($this->footer) ? [] : $this->footer,
            'options' => empty($this->footerOptions) ? [] : $this->footerOptions
        ];
        return $value;
    }


    /**
     * @inheritdoc
     */
    protected function getHeaderCellLabel()
    {
        $provider = $this->grid->dataProvider;
        $label = $this->label;

        if ($this->label === null)
        {

            if ($provider instanceof ActiveDataProvider && $provider->query instanceof ActiveQueryInterface) {
                /* @var $modelClass Model */
                $modelClass = $provider->query->modelClass;
                $model = $modelClass::instance();
                $label = $model->getAttributeLabel($this->attribute);

            } elseif ($provider instanceof ArrayDataProvider && $provider->modelClass !== null) {
                /* @var $modelClass Model */
                $modelClass = $provider->modelClass;
                $model = $modelClass::instance();
                $label = $model->getAttributeLabel($this->attribute);

            } elseif ($this->grid->filterModel !== null && $this->grid->filterModel instanceof Model) {
                $label = $this->grid->filterModel->getAttributeLabel($this->attribute);

            } else {
                $models = $provider->getModels();

                if (($model = reset($models)) instanceof Model) {
                    /* @var $model Model */
                    $label = $model->getAttributeLabel($this->attribute);

                } else {
                    $label = Inflector::camel2words($this->attribute);

                }
            }

        }

        return $label;
    }


    /**
     * Renders the data cell content.
     * @param mixed $model the data model
     * @param mixed $key the key associated with the data model
     * @param int $index the zero-based index of the data model among the models array returned by [[GridView::dataProvider]].
     * @return array the rendering result
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $value = $this->getDataCellValue($model, $key, $index);
        $value = isset($value) ? ($this->grid->formatter->format($value, $this->format)) : $this->grid->emptyCell;

        return [$this->attribute => $value];
    }


    /**
     * Returns the data cell value.
     * @param mixed $model the data model
     * @param mixed $key the key associated with the data model
     * @param int $index the zero-based index of the data model among the models array returned by [[GridView::dataProvider]].
     * @return string|array the data cell value
     */
    public function getDataCellValue($model, $key, $index)
    {
        if ($this->value !== null)
        {
            if (is_string($this->value))
            {
                return ArrayHelper::getValue($model, $this->value);
            }

            return call_user_func($this->value, $model, $key, $index, $this);

        } elseif ($this->attribute !== null) {
            return ArrayHelper::getValue($model, $this->attribute);
        }

        return null;
    }


}
