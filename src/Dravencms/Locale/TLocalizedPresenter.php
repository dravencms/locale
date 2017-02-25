<?php
/**
 * Created by PhpStorm.
 * User: sadam
 * Date: 25.2.17
 * Time: 19:30
 */

namespace Dravencms\Locale;


trait TLocalizedPresenter
{
    /** @persistent */
    public $locale;


    public function startup()
    {
        parent::startup();

        $this->template->lang = $this->locale;
    }
}