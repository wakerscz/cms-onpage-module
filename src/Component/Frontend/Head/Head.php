<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Component\Frontend\Head;


use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\PageModule\Database\Page;
use Wakers\PageModule\Repository\PageRepository;


class Head extends BaseControl
{
    /**
     * @var Page
     */
    protected $activePage;


    /**
     * Head constructor.
     * @param PageRepository $pageRepository
     */
    public function __construct(PageRepository $pageRepository)
    {
        $this->activePage = $pageRepository->getActivePage();
    }


    /**
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function render() : void
    {
        $this->template->onPagePrimary = $this->activePage->getPrimary();
        $this->template->onPageSocial = $this->activePage->getSocial();

        $this->template->setFile(__DIR__ . '/templates/head.latte');
        $this->template->render();
    }
}