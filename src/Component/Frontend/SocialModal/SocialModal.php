<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Component\Frontend\SocialModal;


use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\BaseModule\Util\AjaxValidate;
use Wakers\BaseModule\Database\DatabaseException;
use Wakers\BaseModule\Util\Exception\ProtectedFileException;
use Wakers\LangModule\Translator\Translate;
use Wakers\OnPageModule\Manager\OnPageManager;
use Wakers\PageModule\Database\Page;
use Wakers\PageModule\Repository\PageRepository;


class SocialModal extends BaseControl
{
    use AjaxValidate;


    /**
     * @var OnPageManager
     */
    protected $onPageManager;


    /**
     * @var Page
     */
    protected $activePage;


    /**
     * @var Translate
     */
    protected $translate;


    /**
     * @var callable
     */
    public $onSave = [];


    /**
     * @var callable
     */
    public $onRemoveImage = [];


    /**
     * SocialModal constructor.
     * @param PageRepository $pageRepository
     * @param OnPageManager $onPageManager
     * @param Translate $translate
     */
    public function __construct(
        PageRepository $pageRepository,
        OnPageManager $onPageManager,
        Translate $translate
    ) {
        $this->onPageManager = $onPageManager;
        $this->activePage = $pageRepository->getActivePage();
        $this->translate = $translate;
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->setFile(__DIR__ . '/templates/socialModal.latte');
        $this->template->render();
    }


    /**
     * Social Form
     * @return Form
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function createComponentSocialForm() : Form
    {
        $form = new Form;

        $form->addText('title')
            ->setRequired('Social title is required')
            ->addRule(Form::MIN_LENGTH, 'Minimal length of social title is %d chars.', 3)
            ->addRule(Form::MAX_LENGTH, 'Maximal length of social title is %d chars.', 50);

        $form->addText('description')
            ->setRequired(FALSE)
            ->addRule(Form::MIN_LENGTH, 'Minimal length of social description is %d chars.', 3)
            ->addRule(Form::MAX_LENGTH, 'Maximal length of social description is %d chars.', 100);

        $form->addUpload('image')
            ->setRequired(FALSE)
            ->addRule(Form::IMAGE, 'Image must be only JPG or PNG.')
            ->addRule(Form::MIME_TYPE, 'Image must be image type.', ['image/jpeg', 'image/png'])
            ->addRule(Form::MAX_FILE_SIZE, 'Maximal image size is 16 MB.', 1024 * 1024 * 16)
            ->addRule(Form::PATTERN, 'Image must have extension (.jpg or .png).', '.*\.(jpg|png)');

        $form->addSubmit('save');


        $onPageSocial = $this->activePage->getSocial();

        if ($onPageSocial)
        {
            $form->setDefaults([
                'title' => $onPageSocial->getTitle(),
                'description' => $onPageSocial->getDescription()
            ]);
        }

        $form->onValidate[] = function (Form $form) { $this->validate($form); };
        $form->onSuccess[] = function (Form $form) { $this->success($form); };

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

            $this->onPageManager->getConnection()->beginTransaction();

            try
            {
                $this->onPageManager->saveSocial($this->activePage, $values->title, $values->description);

                if ($values->image->isOk())
                {
                    $this->onPageManager->saveSocialImage($this->activePage, $values->image);
                }

                $this->onPageManager->getConnection()->commit();

                $this->presenter->notificationAjax(
                    $this->translate->translate('Page updated'),
                    $this->translate->translate('Social info has been successfully updated.'),
                    'success',
                    FALSE
                );

                $this->onSave();
            }
            catch (DatabaseException|ProtectedFileException $exception)
            {
                $this->onPageManager->getConnection()->rollBack();

                $this->presenter->notificationAjax(
                    $this->translate->translate('Error'),
                    $exception->getMessage(),
                    'error'
                );
            }
        }
    }


    /**
     * Odstranění obrázku
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function handleRemoveImage()
    {
        if ($this->presenter->isAjax())
        {
            $removed = $this->onPageManager->removeSocialImage($this->activePage);

            if ($removed)
            {
                $this->presenter->notificationAjax(
                    $this->translate->translate('Page updated'),
                    $this->translate->translate('Image for social sites has been removed.'),
                    'info',
                    FALSE
                );

                $this->onRemoveImage();
            }
        }
    }
}