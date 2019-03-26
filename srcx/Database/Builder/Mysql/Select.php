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
 * An object for MySQL SELECT queries.
 *
 */
class Select extends Common\Select
{
    /**
     *
     * Adds or removes SQL_CALC_FOUND_ROWS flag.
     *
     * @param bool $enable Set or unset flag (default true).
     *
     * @return $this
     *
     */
    public function calcFoundRows($enable = true)
    {
        $this->setFlag('SQL_CALC_FOUND_ROWS', $enable);
        return $this;
    }

    /**
     *
     * Adds or removes SQL_CACHE flag.
     *
     * @param bool $enable Set or unset flag (default true).
     *
     * @return $this
     *
     */
    public function cache($enable = true)
    {
        $this->setFlag('SQL_CACHE', $enable);
        return $this;
    }

    /**
     *
     * Adds or removes SQL_NO_CACHE flag.
     *
     * @param bool $enable Set or unset flag (default true).
     *
     * @return $this
     *
     */
    public function noCache($enable = true)
    {
        $this->setFlag('SQL_NO_CACHE', $enable);
        return $this;
    }

    /**
     *
     * Adds or removes STRAIGHT_JOIN flag.
     *
     * @param bool $enable Set or unset flag (default true).
     *
     * @return $this
     *
     */
    public function straightJoin($enable = true)
    {
        $this->setFlag('STRAIGHT_JOIN', $enable);
        return $this;
    }

    /**
     *
     * Adds or removes HIGH_PRIORITY flag.
     *
     * @param bool $enable Set or unset flag (default true).
     *
     * @return $this
     *
     */
    public function highPriority($enable = true)
    {
        $this->setFlag('HIGH_PRIORITY', $enable);
        return $this;
    }

    /**
     *
     * Adds or removes SQL_SMALL_RESULT flag.
     *
     * @param bool $enable Set or unset flag (default true).
     *
     * @return $this
     *
     */
    public function smallResult($enable = true)
    {
        $this->setFlag('SQL_SMALL_RESULT', $enable);
        return $this;
    }

    /**
     *
     * Adds or removes SQL_BIG_RESULT flag.
     *
     * @param bool $enable Set or unset flag (default true).
     *
     * @return $this
     *
     */
    public function bigResult($enable = true)
    {
        $this->setFlag('SQL_BIG_RESULT', $enable);
        return $this;
    }

    /**
     *
     * Adds or removes SQL_BUFFER_RESULT flag.
     *
     * @param bool $enable Set or unset flag (default true).
     *
     * @return $this
     *
     */
    public function bufferResult($enable = true)
    {
        $this->setFlag('SQL_BUFFER_RESULT', $enable);
        return $this;
    }
}
