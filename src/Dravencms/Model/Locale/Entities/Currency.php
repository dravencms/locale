<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dravencms\Model\Locale\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Nette;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * Class Currency
 * @package App\Model\Entities
 * @ORM\Entity
 * @ORM\Table(name="localeCurrency")
 */
class Currency extends Nette\Object implements ICurrency
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
    public function __construct($name, $code, $sign, $isDefault = false, $isActive = true)
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
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @param string $sign
     */
    public function setSign($sign)
    {
        $this->sign = $sign;
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
    public function getSign()
    {
        return $this->sign;
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
     * @return Locale[]|ArrayCollection
     */
    public function getLocales()
    {
        return $this->locales;
    }


}