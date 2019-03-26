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
namespace Yesf\Database\Builder\Common;

/**
 *
 * Base builder for all query objects.
 *
 */
abstract class AbstractBuilder
{
    /**
     *
     * Builds the flags as a space-separated string.
     *
     * @param array $flags The flags to build.
     *
     * @return string
     *
     */
    public function buildFlags(array $flags)
    {
        if (empty($flags)) {
            return ''; // not applicable
        }

        return ' ' . implode(' ', array_keys($flags));
    }

    /**
     *
     * Builds the `WHERE` clause of the statement.
     *
     * @param array $where The WHERE elements.
     *
     * @return string
     *
     */
    public function buildWhere(array $where)
    {
        if (empty($where)) {
            return ''; // not applicable
        }

        return ' WHERE' . $this->indent($where);
    }

    /**
     *
     * Builds the `ORDER BY ...` clause of the statement.
     *
     * @param array $order_by The ORDER BY elements.
     *
     * @return string
     *
     */
    public function buildOrderBy(array $order_by)
    {
        if (empty($order_by)) {
            return ''; // not applicable
        }

        return ' ORDER BY' . $this->indentCsv($order_by);
    }

    /**
     *
     * Builds the `LIMIT` clause of the statement.
     *
     * @param int $limit The LIMIT element.
     *
     * @return string
     *
     */
    public function buildLimit($limit)
    {
        if (empty($limit)) {
            return '';
        }
        return " LIMIT {$limit}";
    }

    /**
     *
     * Builds the `LIMIT ... OFFSET` clause of the statement.
     *
     * @param int $limit The LIMIT element.
     *
     * @param int $offset The OFFSET element.
     *
     * @return string
     *
     */
    public function buildLimitOffset($limit, $offset)
    {
        $clause = '';

        if (!empty($limit)) {
            $clause .= "LIMIT {$limit}";
        }

        if (!empty($offset)) {
            $clause .= " OFFSET {$offset}";
        }

        if (!empty($clause)) {
            $clause = ' ' . trim($clause);
        }

        return $clause;
    }

    /**
     *
     * Returns an array as an indented comma-separated values string.
     *
     * @param array $list The values to convert.
     *
     * @return string
     *
     */
    public function indentCsv(array $list)
    {
        return ' ' . implode(',', $list) . ' ';
    }

    /**
     *
     * Returns an array as an indented string.
     *
     * @param array $list The values to convert.
     *
     * @return string
     *
     */
    public function indent(array $list)
    {
        return ' ' . implode(' ', $list) . ' ';
    }
}
