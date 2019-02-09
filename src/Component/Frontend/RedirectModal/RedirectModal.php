<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */

namespace Wakers\OnPageModule\Component\Frontend\RedirectModal;


use Nette\Application\UI\Form;
use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\BaseModule\Util\AjaxValidate;
use Wakers\BaseModule\Database\DatabaseException;
use Wakers\LangModule\Database\Lang;
use Wakers\LangModule\Repository\LangRepository;
use Wakers\OnPageModule\Manager\OnPageManager;
use Wakers\OnPageModule\Repository\OnPageRepository;
use Wakers\PageModule\Database\Page;
use Wakers\PageModule\Repository\PageRepository;


class RedirectModal extends BaseControl
{
    use AjaxValidate;


    /**
     * @var PageRepository
     */
    protected $pageRepository;


    /**
     * @var OnPageRepository
     */
    protected $onPageRepository;


    /**
     * @var OnPageManager
     */
    protected $onPageManager;


    /**
     * @var Page
     */
    protected $activePage;


    /**
     * @var Lang
     */
    protected $activeLang;


    /**
     * @var callable
     */
    public $onSave = [];


    /**
     * @var callable
     */
    public $onEdit = [];


    /**
     * @persistent
     * @var int
     */
    public $redirectId;


    /**
     * RedirectModal constructor.
     * @param PageRepository $pageRepository
     * @param LangRepository $langRepository
     * @param OnPageRepository $onPageRepository
     * @param OnPageManager $onPageManager
     */
    public function __construct(
        PageRepository $pageRepository,
        LangRepository $langRepository,
        OnPageRepository $onPageRepository,
        OnPageManager $onPageManager
    )
    {
        $this->pageRepository = $pageRepository;
        $this->onPageRepository = $onPageRepository;
        $this->onPageManager = $onPageManager;

        $this->activePage = $pageRepository->getActivePage();
        $this->activeLang = $langRepository->getActiveLang();
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->redirects = $this->onPageRepository->findAllRedirectJoinPageJoinPageUrl();
        $this->template->setFile(__DIR__ . '/templates/redirectModal.latte');
        $this->template->render();
    }


    /**
     * Form
     * @return Form
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function createComponentRedirectForm() : Form
    {
        $pages = [];
        $tree = $this->pageRepository->findAllByLevelNameByLangAsTree($this->activeLang);
        $collection = $this->pageRepository->findAllByLevelNameByLang($tree);

        foreach ($collection as $page)
        {
            $pages[$page->getId()] = $page->getName();
        }


        $form = new Form;

        $form->addText('oldUrl')
            ->setRequired('Původní URL je povinná.');

        $form->addSelect('pageId', NULL, $pages)
            ->setRequired('Cílová stránka je povinná.');

        $form->addSubmit('save');


        $form->onValidate[] = function (Form $form) { $this->validate($form); };
        $form->onSuccess[] = function (Form $form) { $this->success($form); };


        if ($this->redirectId)
        {
            $redirect = $this->onPageRepository->findOneRedirectById($this->redirectId);

            $form->setDefaults([
                'oldUrl' => $redirect->getOldUrl(),
                'pageId' => $redirect->getPageId()
            ]);
        }

        return $form;
    }


    /**
     * Success
     * @param Form $form
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function success(Form $form) : void
    {
        if ($this->presenter->isAjax())
        {
            $values = $form->getValues();

            try
            {
                $redirect = NULL;
                $page = $this->pageRepository->findOneById($values->pageId);

                if ($this->redirectId)
                {
                    $redirect = $this->onPageRepository->findOneRedirectById($this->redirectId);
                }

                $this->onPageManager->saveRedirect($redirect, $page, $values->oldUrl);

                $form->reset();

                $this->presenter->notificationAjax(
                    'URL přesměrována',
                    'URL byla úspěšně přesměrována.',
                    'success',
                    FALSE
                );

                $this->onSave();
            }
            catch (DatabaseException $exception)
            {
                $this->presenter->notificationAjax(
                    'Chyba',
                    $exception->getMessage(),
                    'error'
                );
            }
        }
    }


    /**
     * Nastaví id záznamu
     * @param int $redirectId
     */
    public function handleEdit(int $redirectId) : void
    {
        if ($this->presenter->isAjax())
        {
            $this->redirectId = $redirectId;
            $this->onEdit();
        }
    }
}