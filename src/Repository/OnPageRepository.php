<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Repository;


use Nette\Http\Request;
use Propel\Runtime\Collection\ObjectCollection;
use Wakers\OnPageModule\Database\Primary;
use Wakers\OnPageModule\Database\PrimaryQuery;
use Wakers\OnPageModule\Database\Redirect;
use Wakers\OnPageModule\Database\RedirectQuery;
use Wakers\OnPageModule\Database\Social;
use Wakers\OnPageModule\Database\SocialQuery;


class OnPageRepository
{
    const DESTINATION_SOCIAL_IMAGE = 'on-page/social/';


    /**
     * @var string
     */
    protected $baseDomain;


    /**
     * @var Request
     */
    protected $request;


    /**
     * OnPageRepository constructor.
     * @param string $baseDomain
     * @param Request $request
     */
    public function __construct(string $baseDomain, Request $request)
    {
        $this->baseDomain = $baseDomain;
        $this->request = $request;
    }


    /**
     * @param string $title
     * @return Primary|NULL
     */
    public function findOnePrimaryByTitle(string $title) : ?Primary
    {
        return PrimaryQuery::create()
            ->findOneByTitle($title);
    }


    /**
     * @param string $title
     * @return Social|NULL
     */
    public function findOneSocialByTitle(string $title) : ?Social
    {
        return SocialQuery::create()
            ->findOneByTitle($title);
    }


    /**
     * @param int $id
     * @return Redirect|NULL
     */
    public function findOneRedirectById(int $id) : ?Redirect
    {
        return RedirectQuery::create()
            ->findOneById($id);
    }


    /**
     * @return ObjectCollection|Redirect[]
     */
    public function findAllRedirectJoinPageJoinPageUrl() : ObjectCollection
    {
        return RedirectQuery::create()
            ->joinWithPage()
            ->usePageQuery()
                ->joinWithPageUrl()
            ->endUse()
            ->orderByOldUrl()
            ->find();
    }


    /**
     * @param string $oldUrl
     * @return Redirect|NULL
     */
    public function findOneRedirectByOldUrl(string $oldUrl) : ?Redirect
    {
        return  RedirectQuery::create()
            ->findOneByOldUrl($oldUrl);
    }


    /**
     * @return Redirect|NULL
     */
    public function findOneRedirectByHttpRequest() : ?Redirect
    {
        $url = $this->request->getUrl();

        if (strpos($this->baseDomain, $url->getHostUrl()) !== FALSE)
        {
            return NULL;
        }

        return RedirectQuery::create()
            ->joinWithPage()
            ->usePageQuery()
                ->joinWithPageUrl()
            ->endUse()
            ->findOneByOldUrl($url->getAbsoluteUrl());
    }
}