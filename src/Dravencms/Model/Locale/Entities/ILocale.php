<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Locale\Entities;


interface ILocale
{
    /**
     * @return string
     */
    public function getLanguageCode();
}