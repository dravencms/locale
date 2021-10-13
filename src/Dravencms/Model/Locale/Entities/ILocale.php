<?php declare(strict_types = 1);
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
    public function getId(): int;

    /**
     * @return ICurrency
     */
    public function getCurrency(): ICurrency;

    /**
     * @return string
     */
    public function getLanguageCode(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @return string
     */
    public function getDecPoint(): string;

    /**
     * @return string
     */
    public function getThousandsSep(): string;

    /**
     * @return string
     */
    public function getDateFormat(): string;

    /**
     * @return string
     */
    public function getTimeFormat(): string;

    /**
     * @return string
     */
    public function getDateTimeFormat(): string;
}