# Backported Symfony features

Implement new Symfony features in older versions!

[![Latest Stable Version](http://poser.pugx.org/yceruto/micro-symfony/v)](https://packagist.org/packages/yceruto/micro-symfony) 
[![Total Downloads](http://poser.pugx.org/yceruto/micro-symfony/downloads)](https://packagist.org/packages/yceruto/micro-symfony) 
[![Latest Unstable Version](http://poser.pugx.org/yceruto/micro-symfony/v/unstable)](https://packagist.org/packages/yceruto/micro-symfony) 
[![License](http://poser.pugx.org/yceruto/micro-symfony/license)](https://packagist.org/packages/yceruto/micro-symfony) 
[![PHP Version Require](http://poser.pugx.org/yceruto/micro-symfony/require/php)](https://packagist.org/packages/yceruto/micro-symfony)
![ci](https://github.com/yceruto/micro-symfony/actions/workflows/ci.yml/badge.svg)

## Installation

```
composer require yceruto/micro-symfony
```

## Prepending Extension Config with `$container->import()`

Since Symfony 6.1, the `AbstractBundle` class helps you to create a bundle quickly, and one of useful feature is
prepending config for other bundles or extensions:

```php
namespace Acme\FooBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
// ...

class AcmeFooBundle extends AbstractBundle
{
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // prepend config from a config file
        $container->import('../config/packages/cache.yaml');
    }
}
```

> [!NOTE]
> The `$container->import()` method support in `prependExtension` was implemented in Symfony 7.1,
> so you can remove this package from your dependencies after upgrading accordingly.

## MicroKernelTrait

This class is an implementation of the base `Kernel` + `MicroKernelTrait` that allows you to create a 
single "one-file" application for your cloud worker, microservice, or any other small application.

```php
// index.php

use MicroSymfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\Kernel;

class StripeWebhookEventSubscriber extends Kernel
{
    use MicroKernelTrait;

    #[Route('/', methods: 'GET')]
    public function __invoke(Request $request, NotifierInterface $notifier): Response
    {
        // parse the webhook event and notify the user...
    
        return new Response('OK');
    }
}

return static function (array $context) {
    $kernel = new StripeWebhookEventSubscriber($context['APP_ENV'], (bool) $context['APP_DEBUG']);

    return \PHP_SAPI === 'cli' ? new Application($kernel) : $kernel;
};
```

You can use the same `index.php` as console application to perform the common cache clear operations or any other
command you need to run.

```bash
$ php index.php cache:clear
```

> [!NOTE]
> The `MicroKernelTrait` optional capabilities were implemented in Symfony 7.2, so you can remove this package
> from your dependencies after upgrading accordingly.

## Server-Sent Event (SSE) Improvements

This package provides utilities to enhance working with server-sent events (SSE) in Symfony applications.

- **EventStreamResponse:** A response object designed specifically for streaming server events.
- **ServerEvent:** Used to construct and emit individual server events in the response.

**Example Usage:**

```php
return new EventStreamResponse(function () {
    yield new ServerEvent(time(), type: 'ping');

    sleep(1);
    
    yield new ServerEvent(time(), type: 'ping');
});
```

> [!NOTE]
> The `EvenStreamResponse` and `ServerEvent` are natively supported since Symfony 7.3, so you can remove this package
> from your dependencies after upgrading accordingly.

### Upgrade Notes

All classes included in this package are registered under the `MicroSymfony` namespace,
however, they follow the same organization that Symfony. Thus, to upgrade just remove 
the `Micro` prefix from all imported classes and everything should keep working as before.

```diff
-use MicroSymfony\Component\DependencyInjection\Extension\AbstractExtension;
+use Symfony\Component\DependencyInjection\Extension\AbstractExtension;
```

## License

This software is published under the [MIT License](LICENSE)
