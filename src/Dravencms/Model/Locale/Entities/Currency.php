<?php declare(strict_types = 1);

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dravencms\Model\Locale\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Nette;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;

/**
 * Class Currency
 * @package App\Model\Entities
 * @ORM\Entity
 * @ORM\Table(name="localeCurrency")
 */
class Currency implements ICurrency
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
    private $sign;

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
     * @var ArrayCollection|Locale[]
     * @ORM\OneToMany(targetEntity="Locale", mappedBy="currency",cascade={"persist"})
     */
    private $locales;

    /**
     * Currency constructor.
     * @param string $name
     * @param string $code
     * @param string $sign
     * @param bool $isDefault
     * @param bool $isActive
     */
    public function __construct(string $name, string $code, string $sign, bool $isDefault = false, bool $isActive = true)
    {
        $this->name = $name;
        $this->code = $code;
        $this->sign = $sign;
        $this->isDefault = $isDefault;
        $this->isActive = $isActive;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @param string $sign
     */
    public function setSign(string $sign): void
    {
        $this->sign = $sign;
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
    public function getSign(): string
    {
        return $this->sign;
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
     * @return Locale[]|ArrayCollection
     */
    public function getLocales(): ArrayCollection
    {
        return $this->locales;
    }


}