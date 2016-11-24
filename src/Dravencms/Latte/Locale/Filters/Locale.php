<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Latte\Locale\Filters;

use Dravencms\Locale\Inflection\Czech;
use Dravencms\Model\Locale\Repository\CurrencyRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Translation\Translator;

/**
 * Class Locale
 * @package Latte\Filters
 */
class Locale
{
    /** @var \Dravencms\Model\Locale\Entities\Currency|mixed|null */
    private $currentCurrency;

    /** @var \Dravencms\Model\Locale\Entities\Locale|mixed|null */
    private $currentLocale;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var CurrencyRepository */
    private $currencyRepository;

    public function __construct(Translator $translator, LocaleRepository $localeRepository, CurrencyRepository $currencyRepository)
    {
        $this->localeRepository = $localeRepository;
        $this->currencyRepository = $currencyRepository;

        $this->currentLocale = $localeRepository->getCurrentLocale();
        $this->currentCurrency = $currencyRepository->getCurrentCurrency();
    }

    /**
     * @param integer $price
     * @return string string
     */
    public function formatPrice($price)
    {
        return $this->formatNumber($price) . ' ' . $this->currentCurrency->short;
    }

    /**
     * @param integer $number
     * @return string mixed
     */
    public function formatNumber($number)
    {
        return str_replace(' ', '&nbsp;', number_format($number, 0, $this->currentLocale->decPoint, $this->currentLocale->thousandsSep));
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @return string
     */
    public function formatDate(\DateTimeInterface $dateTime)
    {
        $now = new \DateTime;
        $tomorrow = new \DateTime('+1 day');
        $yesterday = new \DateTime('-1 day');
        if ($dateTime->format('Y-m-d') == $now->format('Y-m-d')) {
            return 'Dnes ' . $dateTime->format(str_replace(':s', '', $this->currentLocale->timeFormat));
        } else {
            if ($dateTime->format('Y-m-d') == $tomorrow->format('Y-m-d')) {
                return 'Zítra ' . $dateTime->format(str_replace(':s', '', $this->currentLocale->timeFormat));
            } else {
                if ($dateTime->format('Y-m-d') == $yesterday->format('Y-m-d')) {
                    return 'Včera ' . $dateTime->format(str_replace(':s', '', $this->currentLocale->timeFormat));
                } else {
                    if ($dateTime->format('Y') == date('Y')) {
                        return $dateTime->format(str_replace('Y', '', $this->currentLocale->dateFormat) . ' ' . str_replace(':s', '', $this->currentLocale->timeFormat));
                    } else {
                        return $dateTime->format($this->currentLocale->dateFormat . ' ' . str_replace(':s', '', $this->currentLocale->timeFormat));
                    }
                }
            }
        }
    }

    /**
     * @param \DateTimeInterface $dateTimeStart
     * @param \DateTimeInterface $dateTimeEnd
     * @return string
     */
    public function formatDateRange(\DateTimeInterface $dateTimeStart, \DateTimeInterface $dateTimeEnd)
    {
        $now = new \DateTime;
        $tomorrow = new \DateTime('+1 day');
        $yesterday = new \DateTime('-1 day');


        if ($dateTimeStart->format('Y-m-d') == $now->format('Y-m-d') && $dateTimeEnd->format('Y-m-d') == $now->format('Y-m-d')) {
            return 'Dnes ' . $dateTimeStart->format(str_replace(':s', '', $this->currentLocale->timeFormat)) . ' - ' . $dateTimeEnd->format(str_replace(':s', '', $this->currentLocale->timeFormat));
        } else {
            if ($dateTimeStart->format('Y-m-d') == $tomorrow->format('Y-m-d') && $dateTimeEnd->format('Y-m-d') == $tomorrow->format('Y-m-d')) {
                return 'Zítra ' . $dateTimeStart->format(str_replace(':s', '', $this->currentLocale->timeFormat)) . ' - ' . $dateTimeEnd->format(str_replace(':s', '',
                    $this->currentLocale->timeFormat));
            } else {
                if ($dateTimeStart->format('Y-m-d') == $yesterday->format('Y-m-d') && $dateTimeEnd->format('Y-m-d') == $yesterday->format('Y-m-d')) {
                    return 'Včera ' . $dateTimeStart->format(str_replace(':s', '', $this->currentLocale->timeFormat)) . ' - ' . $dateTimeEnd->format(str_replace(':s', '',
                        $this->currentLocale->timeFormat));
                } else {
                    if ($dateTimeStart->format('Y') == date('Y') && $dateTimeEnd->format('Y') == date('Y')) {
                        if ($dateTimeStart->format('m-d') == $dateTimeEnd->format('m-d')) {
                            return $dateTimeStart->format(str_replace('Y', '', $this->currentLocale->dateFormat) . ' ' . str_replace(':s', '',
                                    $this->currentLocale->timeFormat)) . ' - ' . $dateTimeEnd->format(str_replace(':s', '', $this->currentLocale->timeFormat));
                        } else {
                            return $dateTimeStart->format(str_replace('Y', '', $this->currentLocale->dateFormat) . ' ' . str_replace(':s', '',
                                    $this->currentLocale->timeFormat)) . ' - ' . $dateTimeEnd->format('d.m ' . str_replace(':s', '', $this->currentLocale->timeFormat));
                        }

                    } else {
                        return $dateTimeStart->format($this->currentLocale->dateFormat . str_replace(':s', '',
                                $this->currentLocale->timeFormat)) . ' - ' . $dateTimeEnd->format('d.m.Y ' . str_replace(':s', '', $this->currentLocale->timeFormat));
                    }
                }
            }
        }
    }

    /**
     * @param string $dateString
     * @param bool $time
     * @return \DateTime
     */
    public function dateStringToDateTime($dateString, $time = true)
    {
        $format = array();
        $format[] = $this->currentLocale->dateFormat;
        if ($time) {
            $format[] = $this->currentLocale->timeFormat;
        }

        return \DateTime::createFromFormat(implode(' ', $format), $dateString);
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @param bool $time
     * @return string
     */
    public function dateTimeToDateString(\DateTimeInterface $dateTime, $time = true)
    {
        $format = [];
        $format[] = $this->currentLocale->dateFormat;
        if ($time) {
            $format[] = $this->currentLocale->timeFormat;
        }

        return $dateTime->format(implode(' ', $format));
    }

    /**
     * @param $localeFormat
     * @return mixed
     */
    public function localeFormatToJsFormat($localeFormat)
    {
        $replaceArray = [];
        $replaceArray['Y'] = 'yyyy';
        $replaceArray['m'] = 'mm';
        $replaceArray['d'] = 'dd';
        return strtr($localeFormat, $replaceArray);
    }

    /**
     * @param $string
     * @param $type
     * @return array
     */
    public function inflection($string, $type = null)
    {
        if (!is_null($type))
        {
            user_error('IMPLEMENT', E_USER_NOTICE);
        }
        
        switch ($this->currentLocale->getLanguageCode())
        {
            case 'cs':
                $cs = new Czech();
                return $cs->Sklonuj($string)[5];
                break;

            default:
                return $string;
                break;
        }

    }
}