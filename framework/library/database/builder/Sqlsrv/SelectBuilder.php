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
namespace yesf\library\database\builder\Sqlsrv;

use yesf\library\database\builder\Common;

/**
 *
 * An object for Sqlsrv SELECT queries.
 *
 */
class SelectBuilder extends Common\SelectBuilder
{
    /**
     *
     * Override so that LIMIT equivalent will be applied by applyLimit().
     *
     * @param int $limit Ignored.
     *
     * @param int $offset Ignored.
     *
     * @see build()
     *
     * @see applyLimit()
     *
     */
    public function buildLimitOffset($limit, $offset)
    {
        return '';
    }

    /**
     *
     * Modify the statement applying limit/offset equivalent portions to it.
     *
     * @param string $stm The SQL statement.
     *
     * @param int $limit The LIMIT value.
     *
     * @param int $offset The OFFSET value.
     *
     * @return string
     *
     */
    public function applyLimit($stm, $limit, $offset)
    {
        if (! $limit && ! $offset) {
            return $stm; // no limit or offset
        }

        // limit but no offset?
        if ($limit && ! $offset) {
            // use TOP in place
            return preg_replace(
                '/^(SELECT( DISTINCT)?)/',
                "$1 TOP {$limit}",
                $stm
            );
        }

        // both limit and offset. must have an ORDER clause to work; OFFSET is
        // a sub-clause of the ORDER clause. cannot use FETCH without OFFSET.
        return $stm . " OFFSET {$offset} ROWS "
                    . "FETCH NEXT {$limit} ROWS ONLY";
    }
}
