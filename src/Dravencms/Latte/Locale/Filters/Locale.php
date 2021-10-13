<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Latte\Locale\Filters;

use Dravencms\Locale\CurrentCurrencyResolver;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Locale\Inflection\Czech;
use Dravencms\Model\Locale\Entities\ILocale;

/**
 * Class Locale
 * @package Latte\Filters
 */
class Locale
{
    /** @var ILocale */
    private $currentLocale;

    /** @var ILocale */
    private $currentCurrency;

    public function __construct(
        CurrentLocaleResolver $currentLocaleResolver,
        CurrentCurrencyResolver $currentCurrencyResolver
    )
    {
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
        $this->currentCurrency = $currentCurrencyResolver->getCurrentCurrency();
    }

    /**
     * @param integer $price
     * @return string string
     */
    public function formatPrice(int $price): string
    {
        return $this->formatNumber($price) . ' ' . $this->currentCurrency->getCode();
    }

    /**
     * @param integer $number
     * @return string mixed
     */
    public function formatNumber(int $number): string
    {
        return str_replace(' ', '&nbsp;', number_format($number, 0, $this->currentLocale->getDecPoint(), $this->currentLocale->getThousandsSep()));
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @return string
     * @throws \Exception
     */
    public function formatDate(\DateTimeInterface $dateTime): string
    {
        $now = new \DateTime;
        $tomorrow = new \DateTime('+1 day');
        $yesterday = new \DateTime('-1 day');
        if ($dateTime->format('Y-m-d') == $now->format('Y-m-d')) {
            return 'Dnes v ' . $dateTime->format(str_replace(':s', '', $this->currentLocale->getTimeFormat()));
        } else {
            if ($dateTime->format('Y-m-d') == $tomorrow->format('Y-m-d')) {
                return 'Zítra v ' . $dateTime->format(str_replace(':s', '', $this->currentLocale->getTimeFormat()));
            } else {
                if ($dateTime->format('Y-m-d') == $yesterday->format('Y-m-d')) {
                    return 'Včera v ' . $dateTime->format(str_replace(':s', '', $this->currentLocale->getTimeFormat()));
                } else {
                    if ($dateTime->format('Y') == date('Y')) {
                        return $dateTime->format(str_replace('Y', '', $this->currentLocale->getDateFormat()) . ' ' . str_replace(':s', '', $this->currentLocale->getTimeFormat()));
                    } else {
                        return $dateTime->format($this->currentLocale->getDateFormat() . ' ' . str_replace(':s', '', $this->currentLocale->getTimeFormat()));
                    }
                }
            }
        }
    }

    /**
     * @param \DateTimeInterface $dateTimeStart
     * @param \DateTimeInterface $dateTimeEnd
     * @return string
     * @throws \Exception
     */
    public function formatDateRange(\DateTimeInterface $dateTimeStart, \DateTimeInterface $dateTimeEnd): string
    {
        $now = new \DateTime;
        $tomorrow = new \DateTime('+1 day');
        $yesterday = new \DateTime('-1 day');


        if ($dateTimeStart->format('Y-m-d') == $now->format('Y-m-d') && $dateTimeEnd->format('Y-m-d') == $now->format('Y-m-d')) {
            return 'Dnes ' . $dateTimeStart->format(str_replace(':s', '', $this->currentLocale->getTimeFormat())) . ' - ' . $dateTimeEnd->format(str_replace(':s', '', $this->currentLocale->getTimeFormat()));
        } else {
            if ($dateTimeStart->format('Y-m-d') == $tomorrow->format('Y-m-d') && $dateTimeEnd->format('Y-m-d') == $tomorrow->format('Y-m-d')) {
                return 'Zítra ' . $dateTimeStart->format(str_replace(':s', '', $this->currentLocale->getTimeFormat())) . ' - ' . $dateTimeEnd->format(str_replace(':s', '',
                    $this->currentLocale->getTimeFormat()));
            } else {
                if ($dateTimeStart->format('Y-m-d') == $yesterday->format('Y-m-d') && $dateTimeEnd->format('Y-m-d') == $yesterday->format('Y-m-d')) {
                    return 'Včera ' . $dateTimeStart->format(str_replace(':s', '', $this->currentLocale->getTimeFormat())) . ' - ' . $dateTimeEnd->format(str_replace(':s', '',
                        $this->currentLocale->getTimeFormat()));
                } else {
                    if ($dateTimeStart->format('Y') == date('Y') && $dateTimeEnd->format('Y') == date('Y')) {
                        if ($dateTimeStart->format('m-d') == $dateTimeEnd->format('m-d')) {
                            return $dateTimeStart->format(str_replace('Y', '', $this->currentLocale->getDateFormat()) . ' ' . str_replace(':s', '',
                                    $this->currentLocale->getTimeFormat())) . ' - ' . $dateTimeEnd->format(str_replace(':s', '', $this->currentLocale->getTimeFormat()));
                        } else {
                            return $dateTimeStart->format(str_replace('Y', '', $this->currentLocale->getDateFormat()) . ' ' . str_replace(':s', '',
                                    $this->currentLocale->getTimeFormat())) . ' - ' . $dateTimeEnd->format('d.m ' . str_replace(':s', '', $this->currentLocale->getTimeFormat()));
                        }

                    } else {
                        return $dateTimeStart->format($this->currentLocale->getDateFormat() . str_replace(':s', '',
                                $this->currentLocale->getTimeFormat())) . ' - ' . $dateTimeEnd->format('d.m.Y ' . str_replace(':s', '', $this->currentLocale->getTimeFormat()));
                    }
                }
            }
        }
    }

    /**
     * @param string $dateString
     * @param bool $time
     * @return \DateTimeInterface
     */
    public function dateStringToDateTime(string $dateString, bool $time = true): \DateTimeInterface
    {
        $format = array();
        $format[] = $this->currentLocale->getDateFormat();
        if ($time) {
            $format[] = $this->currentLocale->getTimeFormat();
        }

        return \DateTime::createFromFormat(implode(' ', $format), $dateString);
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @param bool $time
     * @return string
     */
    public function dateTimeToDateString(\DateTimeInterface $dateTime, bool $time = true): string
    {
        $format = [];
        $format[] = $this->currentLocale->getDateFormat();
        if ($time) {
            $format[] = $this->currentLocale->getTimeFormat();
        }

        return $dateTime->format(implode(' ', $format));
    }

    /**
     * @param string $localeFormat
     * @return string
     */
    public function localeFormatToJsFormat(string $localeFormat): string
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
     * @return string
     */
    public function inflection(string $string, $type = null): string
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