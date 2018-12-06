<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Database;


use Nette\InvalidArgumentException;
use Wakers\BaseModule\Util\Validator;
use Wakers\OnPageModule\Database\Base\Primary as BasePrimary;


class Primary extends BasePrimary
{
    /**
     * Typy indexování (pro vyhledávače)
     */
    const
        INDEXING_TYPE_INDEX_FOLLOW = 1,
        INDEXING_TYPE_INDEX_NOFOLLOW = 2,
        INDEXING_TYPE_NOINDEX_FOLLOW = 3,
        INDEXING_TYPE_NOINDEX_NOFOLLOW = 4;


    /**
     * Všechny typy indexování
     */
    const ALL_INDEXING_TYPES = [
        self::INDEXING_TYPE_INDEX_FOLLOW,
        self::INDEXING_TYPE_INDEX_NOFOLLOW,
        self::INDEXING_TYPE_NOINDEX_FOLLOW,
        self::INDEXING_TYPE_NOINDEX_NOFOLLOW
    ];


    /**
     * @param int $v
     * @return $this|Primary
     */
    public function setIndexingType($v)
    {
        if (!in_array($v, self::ALL_INDEXING_TYPES))
        {
            $arguments = implode(', ', self::ALL_INDEXING_TYPES);
            throw new InvalidArgumentException("Value '{$v}' is not allowed. You can use only '{$arguments}'.");
        }

        return parent::setIndexingType($v);
    }


    /**
     * @param string $v
     * @return $this|Primary
     */
    public function setDescription($v)
    {
        $v = Validator::isStringEmpty($v) ? NULL : $v;

        return parent::setDescription($v);
    }
}
