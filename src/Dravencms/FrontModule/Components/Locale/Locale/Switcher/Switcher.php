<?php declare(strict_types = 1);

namespace Dravencms\FrontModule\Components\Locale\Locale\Switcher;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentCurrencyResolver;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Locale\Entities\ICurrency;
use Dravencms\Model\Locale\Entities\ILocale;
use Dravencms\Model\Locale\Repository\CurrencyRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;

class Switcher extends BaseControl
{
    /** @var LocaleRepository */
    private $localeRepository;

    /** @var CurrencyRepository */
    private $currencyRepository;

    /** @var ILocale */
    private $currentLocale;

    /** @var ICurrency */
    private $currentCurrency;

    /**
     * Switcher constructor.
     * @param LocaleRepository $localeRepository
     * @param CurrencyRepository $currencyRepository
     * @param CurrentLocaleResolver $currentLocaleResolver
     * @param CurrentCurrencyResolver $currentCurrencyResolver
     * @throws \Exception
     */
    public function __construct(
        LocaleRepository $localeRepository,
        CurrencyRepository $currencyRepository,
        CurrentLocaleResolver $currentLocaleResolver,
        CurrentCurrencyResolver $currentCurrencyResolver
    )
    {
        parent::__construct();
        $this->localeRepository = $localeRepository;
        $this->currencyRepository = $currencyRepository;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
        $this->currentCurrency = $currentCurrencyResolver->getCurrentCurrency();
    }

    public function render(): void
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
