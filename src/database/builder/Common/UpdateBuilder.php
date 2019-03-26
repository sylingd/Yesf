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
 * Common UPDATE builder.
 *
 */
class UpdateBuilder extends AbstractBuilder
{
    /**
     *
     * Builds the table portion of the UPDATE.
     *
     * @param string $table The table name.
     *
     * @return string
     *
     */
    public function buildTable($table)
    {
        return " {$table}";
    }

    /**
     *
     * Builds the columns and values for the statement.
     *
     * @param array $col_values The columns and values.
     *
     * @return string
     *
     */
    public function buildValuesForUpdate(array $col_values)
    {
        $values = array();
        foreach ($col_values as $col => $value) {
            $values[] = "{$col} = {$value}";
        }
        return ' SET' . $this->indentCsv($values);
    }
}
