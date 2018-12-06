<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Component\Frontend\RemoveRedirectModal;


use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\LangModule\Translator\Translate;
use Wakers\OnPageModule\Database\Redirect;
use Wakers\OnPageModule\Manager\OnPageManager;
use Wakers\OnPageModule\Repository\OnPageRepository;


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
     * @var Translate
     */
    protected $translate;


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
     * @param Translate $translate
     */
    public function __construct(
        OnPageRepository $onPageRepository,
        OnPageManager $onPageManager,
        Translate $translate
    ) {
        $this->onPageRepository = $onPageRepository;
        $this->onPageManager = $onPageManager;
        $this->translate = $translate;
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
        if ($this->presenter->isAjax())
        {
            $redirect = $this->onPageRepository->findOneRedirectById($redirectId);

            if ($redirect)
            {
                $this->onPageManager->removeRedirect($redirect);

                $this->presenter->notificationAjax(
                    $this->translate->translate('URL removed'),
                    $this->translate->translate('Redirect URL has been successfully removed.'),
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