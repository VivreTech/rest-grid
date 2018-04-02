<?php

namespace vivretech\rest\grid;

/**
 * SerialColumn displays a column of row numbers (1-based).
 *
 * To add a SerialColumn to the [[Grid]], add it to the [[Grid::columns|columns]] configuration as follows:
 *
 * ```php
 * 'columns' => [
 *     // ...
 *     [
 *         'class' => 'vivretech\rest\grid\SerialColumn',
 *         // you may configure additional properties here
 *     ],
 * ]
 * ```
 */
class SerialColumn extends DataColumn
{

    /**
     * @inheritdoc
     */
    public $header = '';

    /**
     * @inheritdoc
     */
    public $attribute = '#';

    /**
     * @inheritdoc
     */
    public $enableSorting = false;

    /**
     * @inheritdoc
     */
    public $enableFiltering = false;

    /**
     * @inheritdoc
     */

    public $filter = null;


    /**
     * @inheritdoc
     */
    public function getDataCellValue($model, $key, $index)
    {
        $pagination = $this->grid->dataProvider->getPagination();

        if ($pagination !== false) {
            return $pagination->getOffset() + $index + 1;
        }

        return $index + 1;
    }


}
