<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Component\Frontend\RemoveRedirectModal;


use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\OnPageModule\Database\Redirect;
use Wakers\OnPageModule\Manager\OnPageManager;
use Wakers\OnPageModule\Repository\OnPageRepository;
use Wakers\OnPageModule\Security\OnPageAuthorizator;


class RemoveRedirectModal extends BaseControl
{
    /**
     * @var OnPageRepository
     */
    protected $onPageRepository;


    /**
     * @var OnPageManager
     */
    protected $onPageManager;


    /**
     * @var Redirect
     */
    protected $redirectEntity;


    /**
     * @var callable
     */
    public $onRemove = [];


    /**
     * @var callable
     */
    public $onOpen = [];


    /**
     * RemoveRedirectModal constructor.
     * @param OnPageRepository $onPageRepository
     * @param OnPageManager $onPageManager
     */
    public function __construct(
        OnPageRepository $onPageRepository,
        OnPageManager $onPageManager
    ) {
        $this->onPageRepository = $onPageRepository;
        $this->onPageManager = $onPageManager;
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->redirectEntity = $this->redirectEntity;
        $this->template->setFile(__DIR__ . '/templates/removeRedirectModal.latte');
        $this->template->render();
    }


    /**
     * Handler pro odstranění
     * @param int $redirectId
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function handleRemove(int $redirectId) : void
    {
        if ($this->presenter->isAjax() && $this->presenter->user->isAllowed(OnPageAuthorizator::RES_REDIRECT_REMOVE))
        {
            $redirect = $this->onPageRepository->findOneRedirectById($redirectId);

            if ($redirect)
            {
                $this->onPageManager->removeRedirect($redirect);

                $this->presenter->notificationAjax(
                    'URL odstraněna',
                    'URL pro přesměrování byla úspěšně odstraněna.',
                    'info',
                    FALSE
                );

                $this->presenter->handleModalToggle('hide', '#wakers_onpage_redirect_remove_modal', FALSE);

                $this->onRemove();
            }
        }
    }


    /**
     * Otevře modální ono a nastaví entitu
     * @param int $redirectId
     */
    public function handleOpen(int $redirectId) : void
    {
        if ($this->presenter->isAjax())
        {
            $this->redirectEntity = $this->onPageRepository->findOneRedirectById($redirectId);

            $this->presenter->handleModalToggle('show', '#wakers_onpage_redirect_remove_modal', FALSE);

            $this->onOpen();
        }
    }

}