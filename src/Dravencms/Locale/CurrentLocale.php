<?php

namespace Dravencms\Locale;
use Dravencms\Model\Locale\Entities\Currency;
use Dravencms\Model\Locale\Entities\ILocale;
use Dravencms\Model\Locale\Entities\Locale;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Translation\Translator;
use Nette\Security\User;


/**
 * Class Locale
 * @package Dravencms\Locale
 */
class CurrentLocale extends \Nette\Object implements ILocale
{
    private $localeRepository;

    private $translator;

    private $user;

    /**
     * @var null|Locale
     */
    private $currentLocale = null;

    public function __construct(
        LocaleRepository $localeRepository,
        Translator $translator,
        User $user
    )
    {
        $this->localeRepository = $localeRepository;
        $this->translator = $translator;
        $this->user = $user;
    }


    /**
     * @return Locale|mixed|null
     * @throws \Exception
     */
    private function findCurrentLocale()
    {
        /*$user = $this->user->getIdentity();
        if ($user)
        {
            $userLocale = $user->getLocale();
            if ($userLocale)
            {
                $this->translator->setLocale($userLocale->getLanguageCode());
            }
        }*/

        // Set current locale model
        if ($found = $this->localeRepository->getOneByLanguageCode($this->translator->getLocale())) {
            return $found;
        } else {
            //Not found
            if ($found = $this->localeRepository->getDefault()) {
                return $found;
            } else {
                throw new \Exception('No default locale selected');
            }
        }
    }

    /**
     * @return Locale|mixed|null
     * @throws \Exception
     */
    private function getCurrentLocale()
    {
        if (is_null($this->currentLocale))
        {
            $this->currentLocale = $this->findCurrentLocale();
        }

        return $this->currentLocale;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->getCurrentLocale()->getId();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getDateFormat()
    {
        return $this->getCurrentLocale()->getDateFormat();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getTimeFormat()
    {
        return $this->getCurrentLocale()->getTimeFormat();
    }

    /**
     * @return string
     */
    public function getDateTimeFormat()
    {
        return $this->getDateFormat().' '.$this->getTimeFormat();
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->getCurrentLocale()->getCurrency();
    }

    /**
     * @return string
     */
    public function getLanguageCode()
    {
        return $this->getCurrentLocale()->getLanguageCode();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getCurrentLocale()->getName();
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getCurrentLocale()->getCode();
    }

    /**
     * @return string
     */
    public function getDecPoint()
    {
        return $this->getCurrentLocale()->getDecPoint();
    }

    /**
     * @return string
     */
    public function getThousandsSep()
    {
        return $this->getCurrentLocale()->getThousandsSep();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getCurrentLocale()->getId();
    }
}
