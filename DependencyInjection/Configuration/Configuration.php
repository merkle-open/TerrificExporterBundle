<?php

/*
 * This file is part of the Terrific Core Bundle.
 *
 * (c) Remo Brunschwiler <remo@terrifically.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Terrific\ExporterBundle\DependencyInjection\Configuration {
    use Symfony\Component\Config\Definition\Builder\TreeBuilder;
    use Symfony\Component\Config\Definition\ConfigurationInterface;

    /**
     * This is the class that validates and merges configuration from your app/config files
     *
     * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
     */
    class Configuration implements ConfigurationInterface
    {
        /**
         * {@inheritDoc}
         */
        public function getConfigTreeBuilder()
        {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('terrific_exporter');

            $rootNode
                ->children()
                ->booleanNode('build_local_paths')->defaultTrue()->end()
                ->booleanNode('build_js_doc')->defaultTrue()->end()
                ->booleanNode('validate_js')->defaultTrue()->end()
                ->booleanNode('validate_html')->defaultTrue()->end()
                ->booleanNode('validate_css')->defaultTrue()->end()
                ->booleanNode('optimize_images')->defaultTrue()->end()
                ->booleanNode('export_rewrite_routes')->defaultFalse()->end()
                ->booleanNode('export_layouts')->defaultTrue()->end()
                ->booleanNode('export_modules')->defaultTrue()->end()
                ->booleanNode('export_fonts')->defaultTrue()->end()
                ->booleanNode('base_files_workaround')->defaultTrue()->end()
                ->booleanNode('append_changelogs')->defaultTrue()->end()
                ->booleanNode('build_sprites')->defaultTrue()->end()

                ->scalarNode('build_settings')->defaultValue('build/build.ini')->end()
                ->scalarNode('build_path')->defaultValue('build')->end()

                ->arrayNode('module_export_list')
                ->requiresAtLeastOneElement()
                ->prototype('array')
                ->children()
                ->scalarNode('name')->end()
                ->end()
                ->end()
                ->end()

                ->arrayNode('layout_export_list')
                ->requiresAtLeastOneElement()
                ->prototype('array')
                ->children()
                ->scalarNode('url')->end()

                ->arrayNode('locations')
                ->prototype('scalar')
                ->end()->end()

                ->end()->end()->end()

                ->arrayNode("assetic_export_list")
                ->requiresAtLeastOneElement()
                ->prototype('scalar')
                ->end()->end()

            /* Sprite Settings */
                ->arrayNode('sprites')
                ->requiresAtLeastOneElement()
                ->prototype('array')
                ->children()
                ->scalarNode('directory')->end()

                #->arrayNode('files')
                #->prototype('array')->children()
                #->scalarNode('name')->end()
                #->end()->end()->end()

                ->scalarNode('target')->end()
                ->scalarNode('type')->defaultValue('vertical')->validate()->IfNotInArray(array('vertical', 'horizontal', 'clustered'))->thenInvalid("Invalid value given. Valid values are 'vertical', 'horizontal' and 'clustered")->end()->end()
                ->arrayNode('item')
                ->addDefaultsIfNotSet()
                ->children()
                ->scalarNode('height')->defaultValue(50)->end()
                ->scalarNode('width')->defaultValue(50)->end()
                ->end()->end()

                ->end()->end()->end()


            /* Export type */
                ->scalarNode('export_type')->defaultValue('zip')->validate()
                    ->IfNotInArray(array('zip', 'folder'))->thenInvalid("Invalid value given. Valid values are 'zip' and 'folder'.")->end()
                ->end();



            return $treeBuilder;
        }
    }
}
