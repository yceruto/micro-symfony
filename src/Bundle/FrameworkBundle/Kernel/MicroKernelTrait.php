<?php

namespace MicroSymfony\Bundle\FrameworkBundle\Kernel;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RouteCollection;

/**
 * Backporting features from https://github.com/symfony/symfony/pull/57408
 *
 * @note To be removed in Symfony 7.2
 */
trait MicroKernelTrait
{
    use \Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait {
        loadRoutes as private loadRoutesFromTrait;
    }

    /**
     * Configures the container.
     *
     * You can register extensions:
     *
     *     $container->extension('framework', [
     *         'secret' => '%secret%'
     *     ]);
     *
     * Or services:
     *
     *     $container->services()->set('halloween', 'FooBundle\HalloweenProvider');
     *
     * Or parameters:
     *
     *     $container->parameters()->set('halloween', 'lot of fun');
     */
    private function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $configDir = preg_replace('{/config$}', '/{config}', $this->getConfigDir());

        $container->import($configDir.'/{packages}/*.{php,yaml}');
        $container->import($configDir.'/{packages}/'.$this->environment.'/*.{php,yaml}');

        if (is_file($configDir.'/services.yaml')) {
            $container->import($configDir.'/services.yaml');
            $container->import($configDir.'/{services}_'.$this->environment.'.yaml');
        } else {
            $container->import($configDir.'/{services}.php');
            $container->import($configDir.'/{services}_'.$this->environment.'.php');
        }
    }

    /**
     * Adds or imports routes into your application.
     *
     *     $routes->import($this->getConfigDir().'/*.{yaml,php}');
     *     $routes
     *         ->add('admin_dashboard', '/admin')
     *         ->controller('App\Controller\AdminController::dashboard')
     *     ;
     */
    private function configureRoutes(RoutingConfigurator $routes): void
    {
        $configDir = preg_replace('{/config$}', '/{config}', $this->getConfigDir());

        $routes->import($configDir.'/{routes}/'.$this->environment.'/*.{php,yaml}');
        $routes->import($configDir.'/{routes}/*.{php,yaml}');

        if (is_file($configDir.'/routes.yaml')) {
            $routes->import($configDir.'/routes.yaml');
        } else {
            $routes->import($configDir.'/{routes}.php');
        }

        if ($fileName = (new \ReflectionObject($this))->getFileName()) {
            $routes->import($fileName, 'attribute');
        }
    }

    public function registerBundles(): iterable
    {
        if (!is_file($bundlesPath = $this->getBundlesPath())) {
            yield new FrameworkBundle();

            return;
        }

        $contents = require $bundlesPath;
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    /**
     * @internal
     */
    public function loadRoutes(LoaderInterface $loader): RouteCollection
    {
        $collection = $this->loadRoutesFromTrait($loader);

        foreach ($collection as $route) {
            $controller = $route->getDefault('_controller');

            if ($this::class === $controller && method_exists($this, '__invoke')) {
                $route->setDefault('_controller', 'kernel');
            }
        }

        return $collection;
    }
}
