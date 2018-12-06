<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Database;


use Wakers\BaseModule\Util\ProtectedFile;
use Wakers\BaseModule\Util\Validator;
use Wakers\OnPageModule\Database\Base\Social as BaseSocial;
use Wakers\OnPageModule\Repository\OnPageRepository;


class Social extends BaseSocial
{
    public function setDescription($v)
    {
        $v = Validator::isStringEmpty($v) ? NULL : $v;

        return parent::setDescription($v);
    }


    /**
     * @return ProtectedFile
     */
    public function getImage() : ProtectedFile
    {
        return new ProtectedFile(OnPageRepository::DESTINATION_SOCIAL_IMAGE, $this->getImageName());
    }
}
