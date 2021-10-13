<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: sadam
 * Date: 25.2.17
 * Time: 19:30
 */

namespace Dravencms\Locale;


use Contributte\Translation\Translator;

trait TLocalizedPresenter
{
    /** @persistent */
    public $locale;

    /** @var Translator @inject */
    public $translator;

    public function startup()
    {
        parent::startup();

        $this->template->lang = $this->locale;
    }
}