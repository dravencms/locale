<?php declare(strict_types = 1);

namespace Dravencms\Locale\DI;

use Dravencms\Latte\Locale\Filters\Locale;
use Dravencms\Locale\CurrentCurrencyResolver;
use Dravencms\Locale\CurrentLocaleResolver;
use Nette;

/**
 * Class LocaleExtension
 * @package Dravencms\Locale\DI
 */
class LocaleExtension extends Nette\DI\CompilerExtension
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


    protected function loadComponents(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/components.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('components.' . $i))
                ->setAutowired(false);
            if (is_string($command)) {
                $cli->setFactory($command);
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
