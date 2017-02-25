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

    const TAG_LOADER = 'translation.loader';
    const TAG_DUMPER = 'translation.dumper';
    const TAG_EXTRACTOR = 'translation.extractor';

    const RESOLVER_REQUEST = 'request';
    const RESOLVER_HEADER = 'header';
    const RESOLVER_SESSION = 'session';


    /**
     * @var array
     */
    public $defaults = [
        'whitelist' => NULL, // array('cs', 'en'),
        'default' => 'en',
        'logging' => NULL, //  TRUE for psr/log, or string for kdyby/monolog channel
        // 'fallback' => array('en_US', 'en'), // using custom merge strategy becase Nette's config merger appends lists of values
        'dirs' => [],
        'cache' => 'Kdyby\Translation\Caching\PhpFileStorage',
        'debugger' => '%debugMode%',
        'resolvers' => [
            self::RESOLVER_SESSION => FALSE,
            self::RESOLVER_REQUEST => TRUE,
            self::RESOLVER_HEADER => TRUE,
        ],
        'loaders' => []
    ];

    /**
     * @var array
     */
    private $loaders;

    public function __construct()
    {
        $this->defaults['cache'] = new Nette\DI\Statement($this->defaults['cache'], ['%tempDir%/cache']);
    }

    public function loadConfiguration()
    {
        $this->loaders = [];

        $config = $this->getConfig();
        $builder = $this->getContainerBuilder();


        $builder->addDefinition($this->prefix('locale'))
            ->setClass('Dravencms\Locale\Locale', []);

        $builder->addDefinition($this->prefix('filters'))
            ->setClass('Dravencms\Latte\Locale\Filters\Locale')
            ->setInject(FALSE);


        $translator = $builder->addDefinition($this->prefix('default'))
            ->setClass('Kdyby\Translation\Translator', [$this->prefix('@userLocaleResolver')])
            ->addSetup('?->setTranslator(?)', [$this->prefix('@userLocaleResolver.param'), '@self'])
            ->addSetup('setDefaultLocale', [$config['default']])
            ->addSetup('setLocaleWhitelist', [$config['whitelist']])
            ->setInject(FALSE);

        Nette\Utils\Validators::assertField($config, 'fallback', 'list');
        $translator->addSetup('setFallbackLocales', [$config['fallback']]);

        $catalogueCompiler = $builder->addDefinition($this->prefix('catalogueCompiler'))
            ->setClass('Kdyby\Translation\CatalogueCompiler', self::filterArgs($config['cache']))
            ->setInject(FALSE);

        if ($config['debugger'] && interface_exists('Tracy\IBarPanel')) {
            $builder->addDefinition($this->prefix('panel'))
                ->setClass('Kdyby\Translation\Diagnostics\Panel', [dirname($builder->expand('%appDir%'))])
                ->addSetup('setLocaleWhitelist', [$config['whitelist']]);

            $translator->addSetup('?->register(?)', [$this->prefix('@panel'), '@self']);
            $catalogueCompiler->addSetup('enableDebugMode');
        }

        $this->loadLocaleResolver($config);

        $builder->addDefinition($this->prefix('helpers'))
            ->setClass('Kdyby\Translation\TemplateHelpers')
            ->setFactory($this->prefix('@default') . '::createTemplateHelpers')
            ->setInject(FALSE);

        $builder->addDefinition($this->prefix('fallbackResolver'))
            ->setClass('Kdyby\Translation\FallbackResolver')
            ->setInject(FALSE);

        $builder->addDefinition($this->prefix('catalogueFactory'))
            ->setClass('Kdyby\Translation\CatalogueFactory')
            ->setInject(FALSE);

        $builder->addDefinition($this->prefix('selector'))
            ->setClass('Symfony\Component\Translation\MessageSelector')
            ->setInject(FALSE);

        $builder->addDefinition($this->prefix('extractor'))
            ->setClass('Symfony\Component\Translation\Extractor\ChainExtractor')
            ->setInject(FALSE);

        $this->loadExtractors();

        $builder->addDefinition($this->prefix('writer'))
            ->setClass('Symfony\Component\Translation\Writer\TranslationWriter')
            ->setInject(FALSE);

        $this->loadDumpers();

        $builder->addDefinition($this->prefix('loader'))
            ->setClass('Kdyby\Translation\TranslationLoader')
            ->setInject(FALSE);

        $loaders = $this->loadFromFile(__DIR__ . '/config/loaders.neon');
        $this->loadLoaders($loaders, $config['loaders'] ? : array_keys($loaders));

        if ($this->isRegisteredConsoleExtension()) {
            $this->loadConsole($config);
        }

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

        $config = $this->getConfig();

        $this->beforeCompileLogging($config);

        $registerToLatte = function (Nette\DI\ServiceDefinition $def) {

            $def->addSetup('addFilter', ['formatNumber', [$this->prefix('@filters'), 'formatNumber']]);
            $def->addSetup('addFilter', ['formatPrice', [$this->prefix('@filters'), 'formatPrice']]);
            $def->addSetup('addFilter', ['formatDateRange', [$this->prefix('@filters'), 'formatDateRange']]);
            $def->addSetup('addFilter', ['dateStringToDateTime', [$this->prefix('@filters'), 'dateStringToDateTime']]);
            $def->addSetup('addFilter', ['dateTimeToDateString', [$this->prefix('@filters'), 'dateTimeToDateString']]);
            $def->addSetup('addFilter', ['localeFormatToJsFormat', [$this->prefix('@filters'), 'localeFormatToJsFormat']]);
            $def->addSetup('addFilter', ['inflection', [$this->prefix('@filters'), 'inflection']]);


            $def->addSetup('?->onCompile[] = function($engine) { Kdyby\Translation\Latte\TranslateMacros::install($engine->getCompiler()); }', ['@self']);

            if (method_exists('Latte\Engine', 'addProvider')) { // Nette 2.4
                $def->addSetup('addProvider', ['translator', $this->prefix('@default')])
                    ->addSetup('addFilter', ['translate', [$this->prefix('@helpers'), 'translateFilterAware']]);
            } else {
                $def->addSetup('addFilter', ['getTranslator', [$this->prefix('@helpers'), 'getTranslator']])
                    ->addSetup('addFilter', ['translate', [$this->prefix('@helpers'), 'translate']]);
            }
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

        $applicationService = $builder->getByType('Nette\Application\Application') ?: 'application';
        if ($builder->hasDefinition($applicationService)) {
            $builder->getDefinition($applicationService)
                ->addSetup('$service->onRequest[] = ?', [[$this->prefix('@userLocaleResolver.param'), 'onRequest']]);

            if ($config['debugger'] && interface_exists('Tracy\IBarPanel')) {
                $builder->getDefinition($applicationService)
                    ->addSetup('$self = $this; $service->onStartup[] = function () use ($self) { $self->getService(?); }', [$this->prefix('default')])
                    ->addSetup('$service->onRequest[] = ?', [[$this->prefix('@panel'), 'onRequest']]);
            }
        }

        if (class_exists('Tracy\Debugger')) {
            Panel::registerBluescreen();
        }

        $extractor = $builder->getDefinition($this->prefix('extractor'));
        foreach ($builder->findByTag(self::TAG_EXTRACTOR) as $extractorId => $meta) {
            Nette\Utils\Validators::assert($meta, 'string:2..');

            $extractor->addSetup('addExtractor', [$meta, '@' . $extractorId]);

            $builder->getDefinition($extractorId)->setAutowired(FALSE)->setInject(FALSE);
        }

        $writer = $builder->getDefinition($this->prefix('writer'));
        foreach ($builder->findByTag(self::TAG_DUMPER) as $dumperId => $meta) {
            Nette\Utils\Validators::assert($meta, 'string:2..');

            $writer->addSetup('addDumper', [$meta, '@' . $dumperId]);

            $builder->getDefinition($dumperId)->setAutowired(FALSE)->setInject(FALSE);
        }

        $this->loaders = [];
        foreach ($builder->findByTag(self::TAG_LOADER) as $loaderId => $meta) {
            Nette\Utils\Validators::assert($meta, 'string:2..');
            $builder->getDefinition($loaderId)->setAutowired(FALSE)->setInject(FALSE);
            $this->loaders[$meta] = $loaderId;
        }

        $builder->getDefinition($this->prefix('loader'))
            ->addSetup('injectServiceIds', [$this->loaders])
            ->setInject(FALSE);

        foreach ($this->compiler->getExtensions() as $extension) {
            if (!$extension instanceof ITranslationProvider) {
                continue;
            }

            $config['dirs'] = array_merge($config['dirs'], array_values($extension->getTranslationResources()));
        }

        if ($dirs = array_values(array_filter($config['dirs'], Nette\Utils\Callback::closure('is_dir')))) {
            foreach ($dirs as $dir) {
                $builder->addDependency($dir);
            }

            $this->loadResourcesFromDirs($dirs);
        }
    }


    /**
     * {@inheritdoc}
     */
    public function getConfig(array $defaults = NULL, $expand = TRUE)
    {
        return parent::getConfig($this->defaults) + ['fallback' => ['en_US']];
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

    public function afterCompile(Nette\PhpGenerator\ClassType $class)
    {
        $initialize = $class->getMethod('initialize');
        if (class_exists('Tracy\Debugger')) {
            $initialize->addBody('Kdyby\Translation\Diagnostics\Panel::registerBluescreen();');
        }
    }


    protected function beforeCompileLogging(array $config)
    {
        $builder = $this->getContainerBuilder();
        $translator = $builder->getDefinition($this->prefix('default'));

        if ($config['logging'] === TRUE) {
            $translator->addSetup('injectPsrLogger');

        } elseif (is_string($config['logging'])) { // channel for kdyby/monolog
            $translator->addSetup('injectPsrLogger', [
                new Nette\DI\Statement('@Kdyby\Monolog\Logger::channel', [$config['logging']]),
            ]);

        } elseif ($config['logging'] !== NULL) {
            throw new InvalidArgumentException(sprintf(
                "Invalid config option for logger. Valid are TRUE for general psr/log or string for kdyby/monolog channel, but %s was given",
                $config['logging']
            ));
        }
    }

    protected function loadResourcesFromDirs($dirs)
    {
        $builder = $this->getContainerBuilder();
        $config = $this->getConfig();

        $whitelistRegexp = Translator::buildWhitelistRegexp($config['whitelist']);
        $translator = $builder->getDefinition($this->prefix('default'));

        $mask = array_map(function ($value) {
            return '*.*.' . $value;
        }, array_keys($this->loaders));

        foreach (Nette\Utils\Finder::findFiles($mask)->from($dirs) as $file) {
            /** @var \SplFileInfo $file */
            if (!$m = Nette\Utils\Strings::match($file->getFilename(), '~^(?P<domain>.*?)\.(?P<locale>[^\.]+)\.(?P<format>[^\.]+)$~')) {
                continue;
            }

            if ($whitelistRegexp && !preg_match($whitelistRegexp, $m['locale']) && $builder->parameters['productionMode']) {
                continue; // ignore in production mode, there is no need to pass the ignored resources
            }

            $this->validateResource($m['format'], $file->getPathname(), $m['locale'], $m['domain']);
            $translator->addSetup('addResource', [$m['format'], $file->getPathname(), $m['locale'], $m['domain']]);
            $builder->addDependency($file->getPathname());
        }
    }

    /**
     * @param string $format
     * @param string $file
     * @param string $locale
     * @param string $domain
     */
    protected function validateResource($format, $file, $locale, $domain)
    {
        $builder = $this->getContainerBuilder();

        if (!isset($this->loaders[$format])) {
            return;
        }

        try {
            $def = $builder->getDefinition($this->loaders[$format]);
            $refl = Nette\Reflection\ClassType::from($def->getEntity() ?: $def->getClass());
            if (($method = $refl->getConstructor()) && $method->getNumberOfRequiredParameters() > 1) {
                return;
            }

            $loader = $refl->newInstance();
            if (!$loader instanceof LoaderInterface) {
                return;
            }

        } catch (\ReflectionException $e) {
            return;
        }

        try {
            $loader->load($file, $locale, $domain);

        } catch (\Exception $e) {
            throw new InvalidResourceException("Resource $file is not valid and cannot be loaded.", 0, $e);
        }
    }


    protected function loadLocaleResolver(array $config)
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('userLocaleResolver.param'))
            ->setClass('Kdyby\Translation\LocaleResolver\LocaleParamResolver')
            ->setAutowired(FALSE)
            ->setInject(FALSE);

        $builder->addDefinition($this->prefix('userLocaleResolver.acceptHeader'))
            ->setClass('Kdyby\Translation\LocaleResolver\AcceptHeaderResolver')
            ->setInject(FALSE);

        $builder->addDefinition($this->prefix('userLocaleResolver.session'))
            ->setClass('Kdyby\Translation\LocaleResolver\SessionResolver')
            ->setInject(FALSE);

        $chain = $builder->addDefinition($this->prefix('userLocaleResolver'))
            ->setClass('Kdyby\Translation\IUserLocaleResolver')
            ->setFactory('Kdyby\Translation\LocaleResolver\ChainResolver')
            ->setInject(FALSE);

        $resolvers = [];
        if ($config['resolvers'][self::RESOLVER_HEADER]) {
            $resolvers[] = $this->prefix('@userLocaleResolver.acceptHeader');
            $chain->addSetup('addResolver', [$this->prefix('@userLocaleResolver.acceptHeader')]);
        }

        if ($config['resolvers'][self::RESOLVER_REQUEST]) {
            $resolvers[] = $this->prefix('@userLocaleResolver.param');
            $chain->addSetup('addResolver', [$this->prefix('@userLocaleResolver.param')]);
        }

        if ($config['resolvers'][self::RESOLVER_SESSION]) {
            $resolvers[] = $this->prefix('@userLocaleResolver.session');
            $chain->addSetup('addResolver', [$this->prefix('@userLocaleResolver.session')]);
        }

        if ($config['debugger'] && interface_exists('Tracy\IBarPanel')) {
            $builder->getDefinition($this->prefix('panel'))
                ->addSetup('setLocaleResolvers', [array_reverse($resolvers)]);
        }
    }

    protected function loadDumpers()
    {
        $builder = $this->getContainerBuilder();

        foreach ($this->loadFromFile(__DIR__ . '/config/dumpers.neon') as $format => $class) {
            $builder->addDefinition($this->prefix('dumper.' . $format))
                ->setClass($class)
                ->addTag(self::TAG_DUMPER, $format);
        }
    }



    protected function loadLoaders(array $loaders, array $allowed)
    {
        $builder = $this->getContainerBuilder();

        foreach ($loaders as $format => $class) {
            if (array_search($format, $allowed) === FALSE) {
                continue;
            }
            $builder->addDefinition($this->prefix('loader.' . $format))
                ->setClass($class)
                ->addTag(self::TAG_LOADER, $format);
        }
    }

    protected function loadExtractors()
    {
        $builder = $this->getContainerBuilder();

        foreach ($this->loadFromFile(__DIR__ . '/config/extractors.neon') as $format => $class) {
            $builder->addDefinition($this->prefix('extractor.' . $format))
                ->setClass($class)
                ->addTag(self::TAG_EXTRACTOR, $format);
        }
    }

    private function isRegisteredConsoleExtension()
    {
        foreach ($this->compiler->getExtensions() as $extension) {
            if ($extension instanceof ConsoleExtension) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * @param string|\stdClass $statement
     * @return Nette\DI\Statement[]
     */
    public static function filterArgs($statement)
    {
        return Nette\DI\Compiler::filterArguments([is_string($statement) ? new Nette\DI\Statement($statement) : $statement]);
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
