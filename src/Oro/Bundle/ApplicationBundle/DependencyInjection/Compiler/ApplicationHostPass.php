<?php

namespace Oro\Bundle\ApplicationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ApplicationHostPass implements CompilerPassInterface
{
    const PARAMETER_NAME = 'application_hosts';
    const PARAMETER_PREFIX = 'application_host.';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $hosts = [];

        foreach ($container->getParameterBag()->all() as $name => $value) {
            if (strpos($name, self::PARAMETER_PREFIX) === 0) {
                $applicationName = substr($name, strlen(self::PARAMETER_PREFIX));
                $hosts[$applicationName] = $value;
            }
        }

        $container->setParameter(self::PARAMETER_NAME, $hosts);
    }
}