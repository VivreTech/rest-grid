<?php


namespace vivretech\rest\grid\renderer;


use yii\data\Sort;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use vivretech\rest\grid\Grid;
use vivretech\rest\grid\DataColumn;
use vivretech\rest\renderer\DataRenderer;


class GridViewRenderer extends DataRenderer
{

    /**
     * @var Grid the grid view object that owns this column.
     */
    public $grid;


    /**
     * @param array $params
     * @return array
     */
    public function renderMain($params = [])
    {
        if (empty($params['grid']) || ($params['grid'] instanceof Grid) === false)
        {
            throw new \InvalidArgumentException(
                'This render action ' .
                $this->getUniqueId() . '::' . __FUNCTION__ . ' is requiring to provide a grid param of type ' .
                Grid::className()
            );
        }

        $this->grid = $params['grid'];

        $content = [];

        foreach ($this->grid->getLayoutSections() as $section)
        {
            $sectionData = $this->renderSection($section);

            if ($sectionData !== false)
            {
                $content = ArrayHelper::merge($content, $sectionData);
            }
        }

        return $content;
    }


    /**
     * Renders a section of the specified name.
     * If the named section is not supported, false will be returned.
     * @param string $name the section name, e.g., `metadata`, `pager`, `columns`, `items`.
     * @return array|bool the rendering result of the section, or false if the named section is not supported.
     */
    public function renderSection($name)
    {
        switch ($name)
        {
            case 'metadata':
                return $this->renderMetaData();

            case 'pager':
                return $this->renderPager();

            case 'columns':
                return $this->renderColumnGroup();

            case 'items':
                return $this->renderItems();

            default:
                return false;
        }
    }


    /**
     * @return array
     */
    public function renderPager()
    {
        $pagination 		= $this->grid->dataProvider->getPagination();
        $totalResults 		= $this->grid->dataProvider->getTotalCount();
        $resultsPerPage 	= $this->grid->dataProvider->getCount();
        $totalPages 		= 1;
        $currentPage 		= 1;

        if (isset($pagination) && is_object($pagination) == true)
        {
            $totalPages 	= $pagination->pageCount;
            $currentPage 	= $pagination->page + 1;
        }

        return [
            'pager' => [
                'results' => [
                    'total' => (int) $totalResults,
                    'per_page' => (int) $resultsPerPage,
                ],
                'pages' => [
                    'total' => (int) $totalPages,
                    'current' => (int) $currentPage
                ],
            ]
        ];
    }


    /**
     * @return array
     */
    public function renderMetaData()
    {
        $value = [
            'id' => $this->grid->getId(),
            'caption' => $this->grid->caption,
            'description' => $this->grid->description,
            'options' => $this->grid->options,

            'header' => [
                'show' => $this->grid->showHeader,
            ],

            'filters' => [
                'show' => $this->grid->showFilters,
            ],

            'footer' => [
                'show' => $this->grid->showFooter,
            ],

            'request_params' => []
        ];

        $pagination = $this->grid->dataProvider->getPagination();
        $sort = $this->grid->dataProvider->getSort();


        if (isset($pagination) && ($pagination instanceof Pagination) == true)
        {
            $value['request_params']['pager'] = [
                'param' => $pagination->pageParam,
                'size' => $pagination->pageSizeParam,
            ];
        }

        if (isset($sort) && ($sort instanceof Sort) == true)
        {
            $value['request_params']['sorter'] = [
                'param' => $sort->sortParam,
                'separator' => $sort->separator,
                'multi_sort' => $sort->enableMultiSort,
            ];
        }

        return ['metadata' => $value];
    }


    /**
     * @return array
     */
    public function renderColumnGroup()
    {
        $cols = [];

        foreach ($this->grid->columns as $column)
        {
            $cols[] = [
                'label'         => $column->renderLabel(),
                'attribute'     => $column->attribute,
                'description'   => $column->renderDescription(),

                'sortable'      => $column->enableSorting,
                'filterable'    => $column->enableFiltering,

                'header'        => $column->renderHeaderCell(),
                'filter'        => $column->renderFilterCell(),
                'row' => [
                    'options' => $column->rowOptions,
                ],
                'footer' => $column->renderFooterCell(),
            ];
        }

        return ['columns' => $cols];
    }


    /**
     * Renders the data models for the grid view.
     * @return array
     */
    public function renderItems()
    {
        $models = array_values($this->grid->dataProvider->getModels());
        $keys = $this->grid->dataProvider->getKeys();
        $rows = [];

        foreach ($models as $index => $model)
        {
            $key = $keys[$index];
            $cells = [];

            /* @var $column DataColumn */
            foreach ($this->grid->columns as $column)
            {
                $dataCellContent = $column->renderDataCell($model, $key, $index);

                if (is_callable($this->grid->rowDataCellRender))
                {
                    $dataCellContent = call_user_func($this->grid->rowDataCellRender, $this->grid, $dataCellContent);
                }

                $cells = array_merge($cells, $dataCellContent);
            }

            $rows[] = $cells;
        }

        return ['items' => $rows];
    }



}
