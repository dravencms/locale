<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dravencms\Model\Locale\Entities;

use App\Model\User\Entities\Country;
use App\Model\User\Entities\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;
use Salamek\Cms\Models\ILocale;

/**
 * Class Locale
 * @package App\Model\Entities
 * @ORM\Entity
 * @ORM\Table(name="localeLocale")
 */
class Locale extends Nette\Object implements ILocale
{
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,unique=true,nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,unique=true,nullable=false)
     */
    private $code;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,unique=true,nullable=false)
     */
    private $languageCode;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $decPoint;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $thousandsSep;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $dateFormat;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $timeFormat;

    /**
     * @var bool
     * @ORM\Column(type="boolean",nullable=false)
     */
    private $isDefault;

    /**
     * @var bool
     * @ORM\Column(type="boolean",nullable=false)
     */
    private $isActive;

    /**
     * @var Currency
     * @ORM\ManyToOne(targetEntity="Currency", inversedBy="locales")
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="id")
     */
    private $currency;

    /**
     * @var ArrayCollection|User[]
     * @ORM\OneToMany(targetEntity="\App\Model\User\Entities\User", mappedBy="locale",cascade={"persist"})
     */
    private $users;

    /**
     * @var Country
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entities\Country", inversedBy="locales")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    private $country;

    /**
     * Locale constructor.
     * @param Currency $currency
     * @param Country $country
     * @param $name
     * @param $code
     * @param $decPoint
     * @param $thousandsSep
     * @param string $dateFormat
     * @param string $timeFormat
     * @param bool $isDefault
     * @param bool $isActive
     */
    public function __construct(
        Currency $currency,
        Country $country,
        $name,
        $code,
        $decPoint,
        $thousandsSep,
        $dateFormat = 'Y-m-d',
        $timeFormat = 'H:i:s',
        $isDefault = false,
        $isActive = true
    ) {
        $this->setCode($code);

        $this->name = $name;
        $this->decPoint = $decPoint;
        $this->thousandsSep = $thousandsSep;
        $this->dateFormat = $dateFormat;
        $this->timeFormat = $timeFormat;
        $this->isDefault = $isDefault;
        $this->isActive = $isActive;
        $this->currency = $currency;
        $this->country = $country;
    }

    /**
     * @param string $code
     * @throws \Exception
     */
    public function setCode($code)
    {
        $exploded = explode('_', $code);
        if (count($exploded) != 2)
        {
            throw new \Exception('$code have wrong format');
        }

        $this->code = $code;

        $this->languageCode = strtolower($exploded[0]);
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $languageCode
     */
    public function setLanguageCode($languageCode)
    {
        $this->languageCode = $languageCode;
    }

    /**
     * @param string $decPoint
     */
    public function setDecPoint($decPoint)
    {
        $this->decPoint = $decPoint;
    }

    /**
     * @param string $thousandsSep
     */
    public function setThousandsSep($thousandsSep)
    {
        $this->thousandsSep = $thousandsSep;
    }

    /**
     * @param string $dateFormat
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * @param string $timeFormat
     */
    public function setTimeFormat($timeFormat)
    {
        $this->timeFormat = $timeFormat;
    }

    /**
     * @param boolean $isDefault
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @param Currency $currency
     */
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * @param Country $country
     */
    public function setCountry(Country $country)
    {
        $this->country = $country;
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getDecPoint()
    {
        return $this->decPoint;
    }

    /**
     * @return string
     */
    public function getThousandsSep()
    {
        return $this->thousandsSep;
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * @return string
     */
    public function getTimeFormat()
    {
        return $this->timeFormat;
    }

    /**
     * @return boolean
     */
    public function isDefault()
    {
        return $this->isDefault;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @return \App\Model\User\Entities\User[]|ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }
}