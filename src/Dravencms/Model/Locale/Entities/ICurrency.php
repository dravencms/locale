<?php
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
    public function getName();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getSign();
}