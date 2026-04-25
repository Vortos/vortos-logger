<?php

declare(strict_types=1);

namespace Vortos\Logger\DependencyInjection;

use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Wires Monolog as the PSR-3 logger.
 *
 * Replaces: packages/monolog.php
 *
 * Dev:  LineFormatter → var/log/dev.log at DEBUG level
 * Prod: JsonFormatter → stderr at ERROR level
 */
final class LoggerExtension extends Extension
{
    public function getAlias(): string
    {
        return 'vortos_logger';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $env = $container->hasParameter('kernel.env')
            ? $container->getParameter('kernel.env')
            : 'prod';

        $logPath = $container->hasParameter('kernel.log_path')
            ? $container->getParameter('kernel.log_path')
            : sys_get_temp_dir();

        $container->register('monolog.formatter.json', JsonFormatter::class)
            ->setShared(true)
            ->setPublic(false);

        $container->register('monolog.formatter.line', LineFormatter::class)
            ->setShared(true)
            ->setPublic(false);

        $container->register('monolog.processor.introspection', IntrospectionProcessor::class)
            ->setShared(true)
            ->setPublic(false);

        $streamHandler = $container->register('monolog.handler.main', StreamHandler::class)
            ->setShared(true)
            ->setPublic(false);

        if ($env === 'dev') {
            $streamHandler
                ->addArgument($logPath . '/dev.log')
                ->addArgument(Level::Debug)
                ->addMethodCall('setFormatter', [new Reference('monolog.formatter.line')]);
        } else {
            $streamHandler
                ->addArgument('php://stderr')
                ->addArgument(Level::Error)
                ->addMethodCall('setFormatter', [new Reference('monolog.formatter.json')]);
        }

        $container->register('monolog.logger', Logger::class)
            ->setArguments(['app'])
            ->addMethodCall('pushHandler', [new Reference('monolog.handler.main')])
            ->addMethodCall('pushProcessor', [new Reference('monolog.processor.introspection')])
            ->setShared(true)
            ->setPublic(true);

        $container->setAlias(LoggerInterface::class, 'monolog.logger')
            ->setPublic(true);
    }
}
