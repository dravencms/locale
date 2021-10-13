<?php declare(strict_types = 1);

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dravencms\Model\Locale\Entities;

use Dravencms\Model\Location\Entities\Country;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Nette;

/**
 * Class Locale
 * @package App\Model\Entities
 * @ORM\Entity
 * @ORM\Table(name="localeLocale")
 */
class Locale implements ILocale
{
    use Nette\SmartObject;
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
     * @var Country
     * @ORM\ManyToOne(targetEntity="Dravencms\Model\Location\Entities\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    private $country;

    /**
     * Locale constructor.
     * @param Currency $currency
     * @param Country $country
     * @param string $name
     * @param string $code
     * @param string $decPoint
     * @param string $thousandsSep
     * @param string $dateFormat
     * @param string $timeFormat
     * @param bool $isDefault
     * @param bool $isActive
     * @throws \Exception
     */
    public function __construct(
        Currency $currency,
        Country $country,
        string $name,
        string $code,
        string $decPoint,
        string $thousandsSep,
        string $dateFormat = 'Y-m-d',
        string $timeFormat = 'H:i:s',
        bool $isDefault = false,
        bool $isActive = true
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
    public function setCode(string $code): void
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
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $languageCode
     */
    public function setLanguageCode(string $languageCode): void
    {
        $this->languageCode = $languageCode;
    }

    /**
     * @param string $decPoint
     */
    public function setDecPoint(string $decPoint): void
    {
        $this->decPoint = $decPoint;
    }

    /**
     * @param string $thousandsSep
     */
    public function setThousandsSep(string $thousandsSep): void
    {
        $this->thousandsSep = $thousandsSep;
    }

    /**
     * @param string $dateFormat
     */
    public function setDateFormat(string $dateFormat): void
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * @param string $timeFormat
     */
    public function setTimeFormat(string $timeFormat): void
    {
        $this->timeFormat = $timeFormat;
    }

    /**
     * @param boolean $isDefault
     */
    public function setIsDefault(bool $isDefault): void
    {
        $this->isDefault = $isDefault;
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @param Currency $currency
     */
    public function setCurrency(Currency $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @param Country $country
     */
    public function setCountry(Country $country): void
    {
        $this->country = $country;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): ICurrency
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getDecPoint(): string
    {
        return $this->decPoint;
    }

    /**
     * @return string
     */
    public function getThousandsSep(): string
    {
        return $this->thousandsSep;
    }

    /**
     * @return string
     */
    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }

    /**
     * @return string
     */
    public function getTimeFormat(): string
    {
        return $this->timeFormat;
    }

    /**
     * @return boolean
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return Country
     */
    public function getCountry(): Country
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getDateTimeFormat(): string
    {
        return $this->getDateFormat().' '.$this->getTimeFormat();
    }
}