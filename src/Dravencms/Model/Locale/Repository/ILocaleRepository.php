<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Locale\Repository;

use Dravencms\Model\Locale\Entities\ILocale;

interface ILocaleRepository
{
    /**
     * @return ILocale
     */
    public function getCurrentLocale();
    /**
     * @return ILocale
     */
    public function getDefault();
    /**
     * @return ILocale[]
     */
    public function getActive();
}