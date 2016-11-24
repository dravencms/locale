<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dravencms\AdminModule\LocaleModule;


use Dravencms\AdminModule\Components\Locale\LocaleForm\LocaleFormFactory;
use Dravencms\AdminModule\Components\Locale\LocaleGrid\LocaleGridFactory;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\Model\Locale\Entities\Locale;
use Dravencms\Model\Locale\Repository\CurrencyRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;

/**
 * Description of LocalePresenter
 *
 * @author Adam Schubert
 */
class LocalePresenter extends SecuredPresenter
{
    /** @var LocaleRepository @inject */
    public $localeLocaleRepository;

    /** @var CurrencyRepository @inject */
    public $localeCurrencyRepository;

    /** @var LocaleGridFactory @inject */
    public $localeGridFactory;
    
    /** @var LocaleFormFactory @inject */
    public $localeFormFactory;

    /** @var Locale */
    private $localeEntity = null;

    public function renderDefault()
    {
        $this->template->h1 = 'Locale overview';
    }

    public function actionEdit($id)
    {
        if ($id) {
            $locale = $this->localeLocaleRepository->getOneById($id);
            if (!$locale) {
                $this->error();
            }
            $this->template->h1 = sprintf('Locale „%s“', $locale->getName());

            $this->localeEntity = $locale;
        } else {
            $this->template->h1 = 'New locale';
        }
    }

    /**
     * @return \AdminModule\Components\User\AclOperationGrid
     */
    public function createComponentGridLocale()
    {
        $control = $this->localeGridFactory->create();
        $control->onDelete[] = function()
        {
            $this->flashMessage('Locale has been successfully deleted', 'alert-success');
            $this->redirect('Locale:');
        };
        return $control;
    }

    /**
     * @return \AdminModule\Components\Locale\LocaleForm
     */
    public function createComponentFormLocale()
    {
        $control = $this->localeFormFactory->create($this->localeEntity);
        $control->onSuccess[] = function()
        {
            $this->flashMessage('Locale has been successfully saved', 'alert-success');
            $this->redirect('Locale:');
        };
        return $control;
    }

}
