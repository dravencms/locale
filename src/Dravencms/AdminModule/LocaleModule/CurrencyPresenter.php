<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dravencms\AdminModule\LocaleModule;


use Dravencms\AdminModule\Components\Locale\CurrencyFormFactory;
use Dravencms\AdminModule\Components\Locale\CurrencyGridFactory;
use Dravencms\AdminModule\SecuredPresenter;
use App\Model\Locale\Entities\Currency;
use App\Model\Locale\Repository\CurrencyRepository;

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

    public function renderDefault()
    {
        $this->template->h1 = 'Currency overview';
    }

    /**
     * @param $id
     * @throws \Nette\Application\BadRequestException
     */
    public function actionEdit($id)
    {
        if ($id) {
            $currency = $this->localeCurrencyRepository->getOneById($id);
            if (!$currency) {
                $this->error();
            }

            $this->currencyEntity = $currency;
            $this->template->h1 = sprintf('Currency „%s“', $currency->getName());
        } else {
            $this->template->h1 = 'New currency';
        }
    }

    /**
     * @return \AdminModule\Components\Locale\CurrencyGrid
     */
    public function createComponentGridCurrency()
    {
        $control = $this->currencyGridFactory->create();
        $control->onDelete[] = function()
        {
            $this->flashMessage('Currency has been successfully deleted', 'alert-success');
            $this->redirect('Currency:');
        };
        return $control;
    }

    /**
     * @return \AdminModule\Components\Locale\CurrencyForm
     */
    public function createComponentFormCurrency()
    {
        $control = $this->currencyFormFactory->create($this->currencyEntity);
        $control->onSuccess[] = function()
        {
            $this->flashMessage('Currency has been successfully saved', 'alert-success');
            $this->redirect('Currency:');
        };
        return $control;
    }

}
