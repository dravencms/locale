<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: sadam
 * Date: 25.2.17
 * Time: 22:32
 */

namespace Dravencms\Model\Locale\Entities;

/**
 * Interface ICurrency
 * @package Dravencms\Model\Locale\Entities
 */
interface ICurrency
{
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
    public function getSign(): string;
}