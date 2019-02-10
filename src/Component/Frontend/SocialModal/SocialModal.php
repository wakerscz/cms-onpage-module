<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\OnPageModule\Component\Frontend\SocialModal;


use Nette\Application\UI\Form;
use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\BaseModule\Util\AjaxValidate;
use Wakers\BaseModule\Database\DatabaseException;
use Wakers\BaseModule\Util\Exception\ProtectedFileException;
use Wakers\BaseModule\Util\SetDisabledForm;
use Wakers\OnPageModule\Manager\OnPageManager;
use Wakers\OnPageModule\Security\OnPageAuthorizator;
use Wakers\PageModule\Database\Page;
use Wakers\PageModule\Repository\PageRepository;


class SocialModal extends BaseControl
{
    use AjaxValidate;
    use SetDisabledForm;


    /**
     * @var OnPageManager
     */
    protected $onPageManager;


    /**
     * @var Page
     */
    protected $activePage;


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
     */
    public function __construct(
        PageRepository $pageRepository,
        OnPageManager $onPageManager
    ) {
        $this->onPageManager = $onPageManager;
        $this->activePage = $pageRepository->getActivePage();
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
            ->setRequired('Titulek pro sociální sítě je povinný.')
            ->addRule(Form::MIN_LENGTH, 'Minimální délka titulku jsou %d znaky.', 3)
            ->addRule(Form::MAX_LENGTH, 'Maximální délka titulku je %d znaků.', 50);

        $form->addText('description')
            ->setRequired(FALSE)
            ->addRule(Form::MIN_LENGTH, 'Minimální délka popisu jsou %d znaky.', 3)
            ->addRule(Form::MAX_LENGTH, 'Maximální délka popisu je %d znaků.', 50);

        $form->addUpload('image')
            ->setRequired(FALSE)
            ->addRule(Form::IMAGE, 'Obrázek může být pouze JPG or PNG.')
            ->addRule(Form::MIME_TYPE, 'Obrázek musí mít správný mime-type.', ['image/jpeg', 'image/png'])
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost obrázku je 8 MB.', 1024 * 1024 * 8)
            ->addRule(Form::PATTERN, 'Obrázek musí mít koncovku .jpg, .png.', '.*\.(jpg|png)');

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

        if (!$this->presenter->user->isAllowed(OnPageAuthorizator::RES_SOCIAL))
        {
            $this->setDisabledForm($form);
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
                    'On-Page faktory',
                    'Informace pro sociální sítě byly úspěšně uloženy.',
                    'success',
                    FALSE
                );

                $this->onSave();
            }
            catch (DatabaseException|ProtectedFileException $exception)
            {
                $this->onPageManager->getConnection()->rollBack();

                $this->presenter->notificationAjax(
                    'Chyba',
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
                    'On-Page faktory',
                    'Obrázek pro sociální sítě byl odstaněn.',
                    'info',
                    FALSE
                );

                $this->onRemoveImage();
            }
        }
    }
}