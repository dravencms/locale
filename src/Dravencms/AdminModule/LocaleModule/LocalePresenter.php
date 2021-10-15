<?php declare(strict_types = 1);

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dravencms\AdminModule\LocaleModule;


use Dravencms\AdminModule\Components\Locale\LocaleForm\LocaleForm;
use Dravencms\AdminModule\Components\Locale\LocaleForm\LocaleFormFactory;
use Dravencms\AdminModule\Components\Locale\LocaleGrid\LocaleGrid;
use Dravencms\AdminModule\Components\Locale\LocaleGrid\LocaleGridFactory;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\Flash;
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


    public function renderDefault(): void
    {
        $this->template->h1 = 'Locale overview';
    }

    /**
     * @param int|null $id
     * @throws \Nette\Application\BadRequestException
     */
    public function actionEdit(int $id = null): void
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
     * @return \Dravencms\AdminModule\Components\Locale\LocaleGrid\LocaleGrid
     */
    public function createComponentGridLocale(): LocaleGrid
    {
        $control = $this->localeGridFactory->create();
        $control->onDelete[] = function()
        {
            if ($success) {
                $this->flashMessage('Locale has been successfully deleted', Flash::SUCCESS);
            } else {
                $this->flashMessage('Failed to delete locale, maybe it is used somewhere?', Flash::DANGER);
            }
            $this->redirect('Locale:');
        };
        return $control;
    }

    /**
     * @return \Dravencms\AdminModule\Components\Locale\LocaleForm\LocaleForm
     */
    public function createComponentFormLocale(): LocaleForm
    {
        $control = $this->localeFormFactory->create($this->localeEntity);
        $control->onSuccess[] = function()
        {
            $this->flashMessage('Locale has been successfully saved', Flash::SUCCESS);
            $this->redirect('Locale:');
        };
        return $control;
    }

}
