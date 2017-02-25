<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Locale\Entities;

/**
 * Interface ILocale
 * @package Dravencms\Model\Locale\Entities
 */
interface ILocale
{
    /**
     * @return integer
     */
    public function getId();

    /**
     * @return Currency
     */
    public function getCurrency();

    /**
     * @return string
     */
    public function getLanguageCode();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getDecPoint();

    /**
     * @return string
     */
    public function getThousandsSep();

    /**
     * @return string
     */
    public function getDateFormat();

    /**
     * @return string
     */
    public function getTimeFormat();

    /**
     * @return string
     */
    public function getDateTimeFormat();
}