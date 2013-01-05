<?php

/*
 * This file is part of the Terrific Core Bundle.
 *
 * (c) Bruno Lorenz <bruno.lorenz@namics.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Terrific\ExporterBundle {
    use Symfony\Component\HttpKernel\Bundle\Bundle;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
    use Symfony\Component\Config\FileLocator;


    /**
     *
     */
    class TerrificExporterBundle extends Bundle
    {

        public function build(ContainerBuilder $container)
        {
            parent::build($container);

            ini_set('memory_limit', '512M');

            $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/Resources/config'));
            $loader->load("services.xml");
        }
    }
}
