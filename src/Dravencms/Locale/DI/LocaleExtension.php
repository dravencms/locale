<?php

namespace Dravencms\Locale\DI;

use Kdyby\Console\DI\ConsoleExtension;
use Kdyby\Translation\DI\ITranslationProvider;
use Kdyby\Translation\Diagnostics\Panel;
use Kdyby\Translation\InvalidArgumentException;
use Kdyby\Translation\InvalidResourceException;
use Kdyby\Translation\Translator;
use Nette;
use Nette\DI\Compiler;
use Symfony\Component\Translation\Loader\LoaderInterface;

/**
 * Class LocaleExtension
 * @package Dravencms\Locale\DI
 */
class LocaleExtension extends Nette\DI\CompilerExtension
{
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('currentLocale'))
            ->setClass('Dravencms\Locale\CurrentLocale', []);

        $builder->addDefinition($this->prefix('currentCurrency'))
            ->setClass('Dravencms\Locale\CurrentCurrency', []);

        $builder->addDefinition($this->prefix('filters'))
            ->setClass('Dravencms\Latte\Locale\Filters\Locale')
            ->setInject(FALSE);

        $this->loadComponents();
        $this->loadModels();
        $this->loadConsole();
    }


    /**
     * @param \Nette\Configurator $configurator
     */
    public static function register(Nette\Configurator $configurator)
    {
        $configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
            $compiler->addExtension('localeExtension', new LocaleExtension());
        };
    }


    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();

        $registerToLatte = function (Nette\DI\ServiceDefinition $def) {
            $def->addSetup('addFilter', ['formatNumber', [$this->prefix('@filters'), 'formatNumber']]);
            $def->addSetup('addFilter', ['formatPrice', [$this->prefix('@filters'), 'formatPrice']]);
            $def->addSetup('addFilter', ['formatDate', [$this->prefix('@filters'), 'formatDate']]);
            $def->addSetup('addFilter', ['formatDateRange', [$this->prefix('@filters'), 'formatDateRange']]);
            $def->addSetup('addFilter', ['dateStringToDateTime', [$this->prefix('@filters'), 'dateStringToDateTime']]);
            $def->addSetup('addFilter', ['dateTimeToDateString', [$this->prefix('@filters'), 'dateTimeToDateString']]);
            $def->addSetup('addFilter', ['localeFormatToJsFormat', [$this->prefix('@filters'), 'localeFormatToJsFormat']]);
            $def->addSetup('addFilter', ['inflection', [$this->prefix('@filters'), 'inflection']]);
        };

        $latteFactoryService = $builder->getByType('Nette\Bridges\ApplicationLatte\ILatteFactory');
        if (!$latteFactoryService || !self::isOfType($builder->getDefinition($latteFactoryService)->getClass(), 'Latte\engine')) {
            $latteFactoryService = 'nette.latteFactory';
        }

        if ($builder->hasDefinition($latteFactoryService) && self::isOfType($builder->getDefinition($latteFactoryService)->getClass(), 'Latte\Engine')) {
            $registerToLatte($builder->getDefinition($latteFactoryService));
        }

        if ($builder->hasDefinition('nette.latte')) {
            $registerToLatte($builder->getDefinition('nette.latte'));
        }
    }

    protected function loadComponents()
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/components.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('components.' . $i))
                ->setInject(FALSE); // lazy injects
            if (is_string($command)) {
                $cli->setImplement($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadModels()
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/models.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('models.' . $i))
                ->setInject(FALSE); // lazy injects
            if (is_string($command)) {
                $cli->setClass($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadConsole()
    {
        $builder = $this->getContainerBuilder();

        foreach ($this->loadFromFile(__DIR__ . '/console.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('cli.' . $i))
                ->addTag(ConsoleExtension::TAG_COMMAND)
                ->setInject(FALSE); // lazy injects

            if (is_string($command)) {
                $cli->setClass($command);

            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    /**
     * @param string $class
     * @param string $type
     * @return bool
     */
    private static function isOfType($class, $type)
    {
        return $class === $type || is_subclass_of($class, $type);
    }
}
