<?php
/**
 * Created by PhpStorm.
 * User: sadam
 * Date: 25.2.17
 * Time: 22:29
 */

namespace Dravencms\Locale;


use Dravencms\Model\Locale\Entities\Currency;
use Dravencms\Model\Locale\Entities\ICurrency;
use Dravencms\Model\Locale\Repository\CurrencyRepository;
use Nette\Http\Request;

class CurrentCurrency implements ICurrency
{
    /** @var CurrencyRepository */
    private $currencyRepository;

    /** @var CurrentLocale */
    private $currentLocale;

    /** @var Request */
    private $request;

    /** @var null|Currency */
    private $currentCurrency = null;
    
    public function __construct(
        CurrencyRepository $currencyRepository,
        CurrentLocale $currentLocale,
        Request $request
    )
    {
        $this->request = $request;
        $this->currencyRepository = $currencyRepository;
        $this->currentLocale = $currentLocale;
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

        if ($found = $this->currentLocale->getCurrency()) {
            return $found;
        } else {
            throw new \Exception('No default currency selected');
        }
    }

    /**
     * @return Currency
     * @throws \Exception
     */
    private function getCurrentCurrency()
    {
        if (!$this->currentCurrency)
        {
            $this->currentCurrency = $this->findCurrentCurrency();
        }

        return $this->currentCurrency;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getCurrentCurrency()->getName();
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getCurrentCurrency()->getCode();
    }

    /**
     * @return string
     */
    public function getSign()
    {
        return $this->getCurrentCurrency()->getSign();
    }
}