<?php

namespace Dravencms\FrontModule\Components\Locale\Locale\Switcher;

use Dravencms\Components\BaseControl;
use Dravencms\Model\Locale\Repository\CurrencyRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;

class Switcher extends BaseControl
{
    /** @var LocaleRepository */
    private $localeRepository;

    /** @var CurrencyRepository */
    private $currencyRepository;

    public function __construct(LocaleRepository $localeRepository, CurrencyRepository $currencyRepository)
    {
        parent::__construct();
        $this->localeRepository = $localeRepository;
        $this->currencyRepository = $currencyRepository;
    }

    public function render()
    {
        $template = $this->template;
        $template->locales = $this->localeRepository->getActive();
        $template->currencies = $this->currencyRepository->getActive();
        $template->currentLocale = $this->localeRepository->getCurrentLocale();
        $template->currentCurrency = $this->currencyRepository->getCurrentCurrency();
        $template->setFile(__DIR__ . '/switcher.latte');
        $template->render();
    }
}
