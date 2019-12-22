<?php
declare(strict_types=1);

namespace Hipper;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): array
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
        ];
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->setParameter('container.autowiring.strict_mode', true);
        $container->setParameter('container.dumper.inline_class_loader', true);
        $configDir = $this->getProjectDir() . '/config';

        $loader->load($configDir.'/packages/*.yml', 'glob');
        $loader->load($configDir.'/packages/' . $this->environment . '/*.yml', 'glob');
        $loader->load($configDir . '/services.yml');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $configDir = $this->getProjectDir() . '/config';

        $routes->import($configDir . '/routes/api/sign_up_flow/routes.yml')->setHost('%domain%');
        $routes->import($configDir . '/routes/api/app/routes.yml')->setHost('{subdomain}.%domain%');
        $routes->import($configDir . '/routes/front_end/sign_up_flow/routes.yml')->setHost('%domain%');
        $routes->import($configDir . '/routes/front_end/app/routes.yml')->setHost('{subdomain}.%domain%');
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log';
    }
}
