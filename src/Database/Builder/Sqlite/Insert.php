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
namespace Yesf\Database\Builder\Sqlite;

use Yesf\Database\Builder\Common;

/**
 *
 * An object for Sqlite INSERT queries.
 *
 */
class Insert extends Common\Insert
{
    /**
     *
     * Adds or removes OR ABORT flag.
     *
     * @param bool $enable Set or unset flag (default true).
     *
     * @return $this
     *
     */
    public function orAbort($enable = true)
    {
        $this->setFlag('OR ABORT', $enable);
        return $this;
    }

    /**
     *
     * Adds or removes OR FAIL flag.
     *
     * @param bool $enable Set or unset flag (default true).
     *
     * @return $this
     *
     */
    public function orFail($enable = true)
    {
        $this->setFlag('OR FAIL', $enable);
        return $this;
    }

    /**
     *
     * Adds or removes OR IGNORE flag.
     *
     * @param bool $enable Set or unset flag (default true).
     *
     * @return $this
     *
     */
    public function orIgnore($enable = true)
    {
        $this->setFlag('OR IGNORE', $enable);
        return $this;
    }

    /**
     *
     * Adds or removes OR REPLACE flag.
     *
     * @param bool $enable Set or unset flag (default true).
     *
     * @return $this
     *
     */
    public function orReplace($enable = true)
    {
        $this->setFlag('OR REPLACE', $enable);
        return $this;
    }

    /**
     *
     * Adds or removes OR ROLLBACK flag.
     *
     * @param bool $enable Set or unset flag (default true).
     *
     * @return $this
     *
     */
    public function orRollback($enable = true)
    {
        $this->setFlag('OR ROLLBACK', $enable);
        return $this;
    }
}