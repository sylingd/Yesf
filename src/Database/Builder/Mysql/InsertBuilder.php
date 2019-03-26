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
namespace Yesf\Database\Builder\Mysql;

use Yesf\Database\Builder\Common;

/**
 *
 * INSERT builder for MySQL.
 *
 */
class InsertBuilder extends Common\InsertBuilder
{
    /**
     *
     * Builds the UPDATE ON DUPLICATE KEY part of the statement.
     *
     * @param array $col_on_update_values Columns and values to use for
     * ON DUPLICATE KEY UPDATE.
     *
     * @return string
     *
     */
    public function buildValuesForUpdateOnDuplicateKey($col_on_update_values)
    {
        if (empty($col_on_update_values)) {
            return ''; // not applicable
        }

        $values = array();
        foreach ($col_on_update_values as $key => $row) {
            $values[] = $this->indent(array($key . ' = ' . $row));
        }

        return ' ON DUPLICATE KEY UPDATE'
            . implode (',', $values);
    }
}
