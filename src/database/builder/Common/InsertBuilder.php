<?php
/**
 * SQL Builder类
 * 
 * 这是一个Aura.SqlQuery(https://github.com/auraphp/Aura.SqlQuery)的修改版本
 * 原项目开源协议为MIT License(http://opensource.org/licenses/mit-license.php)
 * 
 * This is a modified copy of Aura.SqlQuery(https://github.com/auraphp/Aura.SqlQuery)
 * The original project open source under MIT License(http://opensource.org/licenses/mit-license.php)
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Library
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 */
namespace yesf\database\builder\Common;

/**
 *
 * Common INSERT builder.
 *
 */
class InsertBuilder extends AbstractBuilder
{
    /**
     *
     * Builds the INTO clause.
     *
     * @param string $into The INTO element.
     *
     * @return string
     *
     */
    public function buildInto($into)
    {
        return " INTO {$into}";
    }


    /**
     *
     * Builds the inserted columns and values of the statement.
     *
     * @param array $col_values The column names and values.
     *
     * @return string
     *
     */
    public function buildValuesForInsert(array $col_values)
    {
        return ' ('
            . $this->indentCsv(array_keys($col_values))
            . ' ) VALUES ('
            . $this->indentCsv(array_values($col_values))
            . ' )';
    }

    /**
     *
     * Builds the bulk-inserted columns and values of the statement.
     *
     * @param array $col_order The column names to insert, in order.
     *
     * @param array $col_values_bulk The bulk-insert values, in the same order
     * the column names.
     *
     * @return string
     *
     */
    public function buildValuesForBulkInsert(array $col_order, array $col_values_bulk)
    {
        $cols = "    (" . implode(', ', $col_order) . ")";
        $vals = array();
        foreach ($col_values_bulk as $row_values) {
            $vals[] = "    (" . implode(', ', $row_values) . ")";
        }
        return ' ' . $cols . ' '
            . "VALUES" . ' '
            . implode(",", $vals);
    }
}
