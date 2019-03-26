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
namespace yesf\database\builder\Common;

/**
 *
 * A quoting mechanism for identifier names (not values).
 *
 */
interface QuoterInterface
{
    /**
     *
     * Returns the prefix to use when quoting identifier names.
     *
     * @return string
     *
     */
    public function getQuoteNamePrefix();

    /**
     *
     * Returns the suffix to use when quoting identifier names.
     *
     * @return string
     *
     */
    public function getQuoteNameSuffix();

    /**
     *
     * Quotes a single identifier name (table, table alias, table column,
     * index, sequence).
     *
     * If the name contains `' AS '`, this method will separately quote the
     * parts before and after the `' AS '`.
     *
     * If the name contains a space, this method will separately quote the
     * parts before and after the space.
     *
     * If the name contains a dot, this method will separately quote the
     * parts before and after the dot.
     *
     * @param string $spec The identifier name to quote.
     *
     * @return string The quoted identifier name.
     *
     */
    public function quoteName($spec);

    /**
     *
     * Quotes all fully-qualified identifier names ("table.col") in a string,
     * typically an SQL snippet for a SELECT clause.
     *
     * Does not quote identifier names that are string literals (i.e., inside
     * single or double quotes).
     *
     * Looks for a trailing ' AS alias' and quotes the alias as well.
     *
     * @param string $text The string in which to quote fully-qualified
     * identifier names to quote.
     *
     * @return string|array The string with names quoted in it.
     *
     */
    public function quoteNamesIn($text);
}
