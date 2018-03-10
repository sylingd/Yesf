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
namespace yesf\library\database\builder\Common;

use yesf\library\database\builder\AbstractDmlQuery;

/**
 *
 * An object for DELETE queries.
 *
 */
class Delete extends AbstractDmlQuery implements DeleteInterface
{
    use WhereTrait;

    /**
     *
     * The table to delete from.
     *
     * @var string
     *
     */
    protected $from;

    /**
     *
     * Sets the table to delete from.
     *
     * @param string $table The table to delete from.
     *
     * @return $this
     *
     */
    public function from($table)
    {
        $this->from = $this->quoter->quoteName($table);
        return $this;
    }

    /**
     *
     * Builds this query object into a string.
     *
     * @return string
     *
     */
    protected function build()
    {
        return 'DELETE'
            . $this->builder->buildFlags($this->flags)
            . $this->builder->buildFrom($this->from)
            . $this->builder->buildWhere($this->where)
            . $this->builder->buildOrderBy($this->order_by);
    }
}
