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

use Yesf\Exception\DBException;

/**
 *
 * Common SELECT builder.
 *
 */
class SelectBuilder extends AbstractBuilder
{
    /**
     *
     * Builds the columns portion of the SELECT.
     *
     * @param array $cols The columns.
     *
     * @return string
     *
     * @throws DBException when there are no columns in the SELECT.
     *
     */
    public function buildCols(array $cols)
    {
        if (empty($cols)) {
            throw new DBException('No columns in the SELECT.');
        }
        return $this->indentCsv($cols);
    }

    /**
     *
     * Builds the FROM clause.
     *
     * @param array $from The FROM elements.
     *
     * @param array $join The JOIN elements.
     *
     * @return string
     *
     */
    public function buildFrom(array $from, array $join)
    {
        if (empty($from)) {
            return ''; // not applicable
        }

        $refs = array();
        foreach ($from as $from_key => $from) {
            if (isset($join[$from_key])) {
                $from = array_merge($from, $join[$from_key]);
            }
            $refs[] = implode(' ', $from);
        }
        return ' FROM' . $this->indentCsv($refs);
    }

    /**
     *
     * Builds the GROUP BY clause.
     *
     * @param array $group_by The GROUP BY elements.
     *
     * @return string
     *
     */
    public function buildGroupBy(array $group_by)
    {
        if (empty($group_by)) {
            return ''; // not applicable
        }

        return ' GROUP BY' . $this->indentCsv($group_by);
    }

    /**
     *
     * Builds the HAVING clause.
     *
     * @param array $having The HAVING elements.
     *
     * @return string
     *
     */
    public function buildHaving(array $having)
    {
        if (empty($having)) {
            return ''; // not applicable
        }

        return ' HAVING' . $this->indent($having);
    }

    /**
     *
     * Builds the FOR UPDATE portion of the SELECT.
     *
     * @param bool $for_update True if FOR UPDATE, false if not.
     *
     * @return string
     *
     */
    public function buildForUpdate($for_update)
    {
        if (! $for_update) {
            return ''; // not applicable
        }

        return ' FOR UPDATE';
    }
}
