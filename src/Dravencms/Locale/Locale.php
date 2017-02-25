<?php

namespace Dravencms\Locale;
use Dravencms\Model\Locale\Repository\LocaleRepository;


/**
 * Class Locale
 * @package Dravencms\Locale
 */
class Locale extends \Nette\Object
{
    private $localeRepository;
    
    
    public function __construct(
        LocaleRepository $localeRepository
    )
    {
        $this->localeRepository = $localeRepository;
    }
    
    public function getCurrentLocale()
    {
        
    }
}
