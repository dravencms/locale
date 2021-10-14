<?php declare(strict_types = 1);

namespace Dravencms\Locale\DI;

use Dravencms\Latte\Locale\Filters\Locale;
use Dravencms\Locale\CurrentCurrencyResolver;
use Dravencms\Locale\CurrentLocaleResolver;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\DI\CompilerExtension;

/**
 * Class LocaleExtension
 * @package Dravencms\Locale\DI
 */
class LocaleExtension extends CompilerExtension
{
    public static $prefix = 'locale';

    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();
        $builder->addDefinition($this->prefix(self::$prefix.'.currentLocaleResolver'))
            ->setFactory(CurrentLocaleResolver::class);

        $builder->addDefinition($this->prefix(self::$prefix.'.currentCurrencyResolver'))
            ->setFactory(CurrentCurrencyResolver::class);

        $builder->addDefinition($this->prefix(self::$prefix.'.filters'))
            ->setFactory(Locale::class)
            ->setAutowired(false);

        $this->loadComponents();
        $this->loadModels();
        $this->loadConsole();
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();


        $latteFactoryService = $builder->getDefinitionByType(LatteFactory::class);
        $latteFactoryService->addSetup('addFilter', ['formatNumber', [$this->prefix('@'.self::$prefix.'filters'), 'formatNumber']]);
        $latteFactoryService->addSetup('addFilter', ['formatPrice', [$this->prefix('@'.self::$prefix.'filters'), 'formatPrice']]);
        $latteFactoryService->addSetup('addFilter', ['formatDate', [$this->prefix('@'.self::$prefix.'filters'), 'formatDate']]);
        $latteFactoryService->addSetup('addFilter', ['formatDateRange', [$this->prefix('@'.self::$prefix.'filters'), 'formatDateRange']]);
        $latteFactoryService->addSetup('addFilter', ['dateStringToDateTime', [$this->prefix('@'.self::$prefix.'filters'), 'dateStringToDateTime']]);
        $latteFactoryService->addSetup('addFilter', ['dateTimeToDateString', [$this->prefix('@'.self::$prefix.'filters'), 'dateTimeToDateString']]);
        $latteFactoryService->addSetup('addFilter', ['localeFormatToJsFormat', [$this->prefix('@'.self::$prefix.'filters'), 'localeFormatToJsFormat']]);
        $latteFactoryService->addSetup('addFilter', ['inflection', [$this->prefix('@'.self::$prefix.'filters'), 'inflection']]);
    }


    protected function loadComponents(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/components.neon') as $i => $command) {
            $cli = $builder->addFactoryDefinition($this->prefix('components.' . $i))
                ->setAutowired(false);
            if (is_string($command)) {
                $cli->setImplement($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadModels(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/models.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('models.' . $i))
                ->setAutowired(false);
            if (is_string($command)) {
                $cli->setFactory($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadConsole(): void
    {
        $builder = $this->getContainerBuilder();

        foreach ($this->loadFromFile(__DIR__ . '/console.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('cli.' . $i))
                ->setAutowired(false);

            if (is_string($command)) {
                $cli->setFactory($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

}
