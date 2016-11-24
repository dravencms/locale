<?php

namespace Dravencms\Locale\DI;

use Kdyby\Console\DI\ConsoleExtension;
use Nette;
use Nette\DI\Compiler;
use Nette\DI\Configurator;
use Salamek\Cms\DI\CmsExtension;
/**
 * Class LocaleExtension
 * @package Dravencms\Locale\DI
 */
class LocaleExtension extends Nette\DI\CompilerExtension
{

    public function loadConfiguration()
    {
        $config = $this->getConfig();
        $builder = $this->getContainerBuilder();


        $builder->addDefinition($this->prefix('locale'))
            ->setClass('Dravencms\Locale\Locale', []);

        $builder->addDefinition($this->prefix('filters'))
            ->setClass('Dravencms\Latte\Locale\Filters\Locale')
            ->setInject(FALSE);

        $this->loadCmsComponents();
        $this->loadComponents();
        $this->loadModels();
        $this->loadConsole();
    }


    /**
     * @param Configurator $config
     * @param string $extensionName
     */
    public static function register(Configurator $config, $extensionName = 'localeExtension')
    {
        $config->onCompile[] = function (Configurator $config, Compiler $compiler) use ($extensionName) {
            $compiler->addExtension($extensionName, new LocaleExtension());
        };
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();
        $registerToLatte = function (Nette\DI\ServiceDefinition $def) {
            //$def->addSetup('?->onCompile[] = function($engine) { Salamek\Cms\Macros\Latte::install($engine->getCompiler()); }', ['@self']);

            $def->addSetup('addFilter', ['formatNumber', [$this->prefix('@filters'), 'formatNumber']]);
            $def->addSetup('addFilter', ['formatPrice', [$this->prefix('@filters'), 'formatPrice']]);
            $def->addSetup('addFilter', ['formatDateRange', [$this->prefix('@filters'), 'formatDateRange']]);
            $def->addSetup('addFilter', ['dateStringToDateTime', [$this->prefix('@filters'), 'dateStringToDateTime']]);
            $def->addSetup('addFilter', ['dateTimeToDateString', [$this->prefix('@filters'), 'dateTimeToDateString']]);
            $def->addSetup('addFilter', ['localeFormatToJsFormat', [$this->prefix('@filters'), 'localeFormatToJsFormat']]);
            $def->addSetup('addFilter', ['inflection', [$this->prefix('@filters'), 'inflection']]);

            /*if (method_exists('Latte\Engine', 'addProvider')) { // Nette 2.4
                $def->addSetup('addProvider', ['cms', $this->prefix('@cms')])
                    ->addSetup('addFilter', ['cmsLink', [$this->prefix('@helpers'), 'cmsLinkFilterAware']]);
            } else {
                $def->addSetup('addFilter', ['getCms', [$this->prefix('@helpers'), 'getCms']])
                    ->addSetup('addFilter', ['cmsLink', [$this->prefix('@helpers'), 'cmsLink']]);
            }*/
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


    /**
     * {@inheritdoc}
     */
    public function getConfig(array $defaults = [], $expand = true)
    {
        $defaults = [
        ];

        return parent::getConfig($defaults, $expand);
    }

    protected function loadCmsComponents()
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/cmsComponents.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('cmsComponent.' . $i))
                ->addTag(CmsExtension::TAG_COMPONENT)
                ->setInject(FALSE); // lazy injects
            if (is_string($command)) {
                $cli->setImplement($command);
            } else {
                throw new \InvalidArgumentException;
            }
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
