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
 * An interface for LIMIT...OFFSET clauses.
 *
 */
trait LimitOffsetTrait
{
    use LimitTrait;

    /**
     *
     * The OFFSET value.
     *
     * @var int
     *
     */
    protected $offset = 0;

    /**
     *
     * Sets a limit offset on the query.
     *
     * @param int $offset Start returning after this many rows.
     *
     * @return $this
     *
     */
    public function offset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }

    /**
     *
     * Returns the OFFSET value.
     *
     * @return int
     *
     */
    public function getOffset()
    {
        return $this->offset;
    }
}
