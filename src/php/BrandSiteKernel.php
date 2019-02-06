<?php
declare(strict_types=1);

namespace Lithos;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class BrandSiteKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): array
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
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
        $loader->load($configDir . '/services.yml');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $configDir = $this->getProjectDir() . '/config';

        $routes->import($configDir . '/routes/brand-site/routes.yml');
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/brand-site/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log/brand-site';
    }
}