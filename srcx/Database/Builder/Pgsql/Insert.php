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
namespace Yesf\Database\Builder\Pgsql;

use Yesf\Database\Builder\Common;

/**
 *
 * An object for PgSQL INSERT queries.
 *
 */
class Insert extends Common\Insert implements ReturningInterface
{
    use ReturningTrait;

    /**
     *
     * Builds the statement.
     *
     * @return string
     *
     */
    protected function build()
    {
        return parent::build()
            . $this->builder->buildReturning($this->returning);
    }

    /**
     *
     * Returns the proper name for passing to `PDO::lastInsertId()`.
     *
     * @param string $col The last insert ID column.
     *
     * @return string The sequence name "{$into_table}_{$col}_seq", or the
     * value from `$last_insert_id_names`.
     *
     */
    public function getLastInsertIdName($col)
    {
        $name = parent::getLastInsertIdName($col);
        if (! $name) {
            $name = "{$this->into_raw}_{$col}_seq";
        }
        return $name;
    }
}
