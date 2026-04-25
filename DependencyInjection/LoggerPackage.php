<?php

declare(strict_types=1);

namespace Vortos\Logger\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Vortos\Foundation\Contract\PackageInterface;

final class LoggerPackage implements PackageInterface
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new LoggerExtension();
    }

    public function build(ContainerBuilder $container): void {}
}
