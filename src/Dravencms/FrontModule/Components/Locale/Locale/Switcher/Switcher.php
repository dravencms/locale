<?php

namespace Dravencms\FrontModule\Components\Locale\Locale\Switcher;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentCurrency;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\Locale\Repository\CurrencyRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;

class Switcher extends BaseControl
{
    /** @var LocaleRepository */
    private $localeRepository;

    /** @var CurrencyRepository */
    private $currencyRepository;

    /** @var CurrentLocale */
    private $currentLocale;

    /** @var CurrentCurrency */
    private $currentCurrency;

    public function __construct(
        LocaleRepository $localeRepository,
        CurrencyRepository $currencyRepository,
        CurrentLocale $currentLocale,
        CurrentCurrency $currentCurrency
    )
    {
        parent::__construct();
        $this->localeRepository = $localeRepository;
        $this->currencyRepository = $currencyRepository;
        $this->currentLocale = $currentLocale;
        $this->currentCurrency = $currentCurrency;
    }

    public function render()
    {
        $template = $this->template;
        $template->locales = $this->localeRepository->getActive();
        $template->currencies = $this->currencyRepository->getActive();
        $template->currentLocale = $this->currentLocale;
        $template->currentCurrency = $this->currentCurrency;
        $template->setFile(__DIR__ . '/switcher.latte');
        $template->render();
    }
}
