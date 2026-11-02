<?php declare(strict_types = 1);

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dravencms\AdminModule\LocaleModule;


use Dravencms\AdminModule\Components\Locale\CurrencyForm\CurrencyForm;
use Dravencms\AdminModule\Components\Locale\CurrencyForm\CurrencyFormFactory;
use Dravencms\AdminModule\Components\Locale\CurrencyGrid\CurrencyGrid;
use Dravencms\AdminModule\Components\Locale\CurrencyGrid\CurrencyGridFactory;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\Flash;
use Dravencms\Model\Locale\Entities\Currency;
use Dravencms\Model\Locale\Repository\CurrencyRepository;

/**
 * Description of CurrencyPresenter
 *
 * @author Adam Schubert
 */
class CurrencyPresenter extends SecuredPresenter
{
    /** @var CurrencyRepository @inject */
    public $localeCurrencyRepository;

    /** @var CurrencyFormFactory @inject */
    public $currencyFormFactory;

    /** @var CurrencyGridFactory @inject */
    public $currencyGridFactory;

    /** @var Currency */
    private $currencyEntity = null;

    public function renderDefault(): void
    {
        $this->template->h1 = $this->translator->translate('locale.admin.title.currencies');
    }

    /**
     * @param int|null $id
     * @throws \Nette\Application\BadRequestException
     */
    public function actionEdit(?int $id = null): void
    {
        if ($id) {
            $currency = $this->localeCurrencyRepository->getOneById($id);
            if (!$currency) {
                $this->error();
            }

            $this->currencyEntity = $currency;
            $this->template->h1 = $this->translator->translate('locale.admin.title.editCurrency', ['name' => $currency->getName()]);
        } else {
            $this->template->h1 = $this->translator->translate('locale.admin.title.newCurrency');
        }
    }

    /**
     * @return \Dravencms\AdminModule\Components\Locale\CurrencyGrid\CurrencyGrid
     */
    public function createComponentGridCurrency(): CurrencyGrid
    {
        $control = $this->currencyGridFactory->create();
        $control->onDelete[] = function($success)
        {
            if ($success) {
                $this->flashMessage($this->translator->translate('locale.admin.flash.currencyDeleted'), Flash::SUCCESS);
            } else {
                $this->flashMessage($this->translator->translate('locale.admin.flash.currencyDeleteFailed'), Flash::DANGER);
            }

            $this->redirect('Currency:');
        };
        return $control;
    }

    /**
     * @return \Dravencms\AdminModule\Components\Locale\CurrencyForm\CurrencyForm
     */
    public function createComponentFormCurrency(): CurrencyForm
    {
        $control = $this->currencyFormFactory->create($this->currencyEntity);
        $control->onSuccess[] = function()
        {
            $this->flashMessage($this->translator->translate('locale.admin.flash.currencySaved'), Flash::SUCCESS);
            $this->redirect('Currency:');
        };
        return $control;
    }

}
