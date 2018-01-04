<?php
/**
 * Created by PhpStorm.
 * User: sadam
 * Date: 25.2.17
 * Time: 22:29
 */

namespace Dravencms\Locale;


use Dravencms\Model\Locale\Entities\Currency;
use Dravencms\Model\Locale\Repository\CurrencyRepository;
use Nette\Http\Request;
use Nette\SmartObject;

class CurrentCurrencyResolver
{
    use SmartObject;

    /** @var CurrencyRepository */
    private $currencyRepository;

    /** @var CurrentLocaleResolver */
    private $currentLocaleResolver;

    /** @var Request */
    private $request;

    /** @var null|Currency */
    private $currentCurrency = null;
    
    public function __construct(
        CurrencyRepository $currencyRepository,
        CurrentLocaleResolver $currentLocaleResolver,
        Request $request
    )
    {
        $this->request = $request;
        $this->currencyRepository = $currencyRepository;
        $this->currentLocaleResolver = $currentLocaleResolver;
    }
    
    /**
     * @return \Dravencms\Model\Locale\Entities\Currency|mixed|null
     * @throws \Exception
     */
    private function findCurrentCurrency()
    {
        if ($currency = $this->request->getQuery('currency'))
        {
            if ($found = $found = $this->currencyRepository->getByCode($currency))
            {
                return $found;
            }
        }

        if ($found = $this->currentLocaleResolver->getCurrentLocale()->getCurrency()) {
            return $found;
        } else {
            throw new \Exception('No default currency selected');
        }
    }

    /**
     * @return Currency
     * @throws \Exception
     */
    public function getCurrentCurrency()
    {
        if (!$this->currentCurrency)
        {
            $this->currentCurrency = $this->findCurrentCurrency();
        }

        return $this->currentCurrency;
    }
}