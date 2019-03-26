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
namespace yesf\database\builder\Pgsql;

/**
 *
 * Common code for RETURNING clauses.
 *
 */
trait BuildReturningTrait
{
    /**
     *
     * Builds the `RETURNING` clause of the statement.
     *
     * @param array $returning Return these columns.
     *
     * @return string
     *
     */
    public function buildReturning(array $returning)
    {
        if (empty($returning)) {
            return ''; // not applicable
        }

        return ' RETURNING' . $this->indentCsv($returning);
    }
}
