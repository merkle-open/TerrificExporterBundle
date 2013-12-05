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
                ->booleanNode('export_views')->defaultTrue()->end()
                ->booleanNode('export_modules')->defaultTrue()->end()
                ->booleanNode('export_with_version')->defaultTrue()->end()
                ->booleanNode('autoincrement_build')->defaultTrue()->end()


                ->arrayNode('build_actions')
                    ->prototype('scalar')
                    ->end()
                ->end()

                ->scalarNode('changelog_path')->defaultValue('build/changelogs')->end()
                ->scalarNode('build_settings')->defaultValue('build/build.ini')->end()
                ->scalarNode('build_path')->defaultValue('build')->end()

            /* Sprite Settings */
                ->arrayNode('sprites')
                ->requiresAtLeastOneElement()
                ->prototype('array')
                    ->children()
                        ->scalarNode('directory')->end()
                        ->scalarNode('target')->end()
                        ->scalarNode('type')->defaultValue('vertical')->validate()->IfNotInArray(array('vertical', 'horizontal', 'clustered'))->thenInvalid("Invalid value given. Valid values are 'vertical', 'horizontal' and 'clustered")->end()->end()
                        ->arrayNode('item')
                            ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('height')->defaultValue(50)->end()
                                    ->scalarNode('width')->defaultValue(50)->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode("pathtemplates")->children()
                    ->scalarNode("image")->defaultValue("/img/common")->end()
                    ->scalarNode("font")->defaultValue("/fonts")->end()
                    ->scalarNode("css")->defaultValue("/css")->end()
                    ->scalarNode("js")->defaultValue("/js")->end()
                    ->scalarNode("json")->defaultValue("/json")->end()
                    ->scalarNode("view")->defaultValue("/views")->end()
                    ->scalarNode("flash")->defaultValue("/flash")->end()
                    ->scalarNode("silverlight")->defaultValue("/silverlight")->end()
                    ->scalarNode("icon")->defaultValue("/")->end()
                    ->scalarNode("video")->defaultValue("/media/video")->end()
                    ->scalarNode("audio")->defaultValue("/media/audio")->end()

                    ->scalarNode("changelog")->defaultValue("/changelogs")->end()
                    ->scalarNode("diff")->defaultValue("/changelogs/diff")->end()

                    ->scalarNode("module_image")->defaultValue("/img/%module%")->end()
                    ->scalarNode("module_font")->defaultValue("/fonts/%module%")->end()
                    ->scalarNode("module_css")->defaultValue("/css/%module%")->end()
                    ->scalarNode("module_js")->defaultValue("/js/%module%")->end()
                    ->scalarNode("module_json")->defaultValue("/json/%module%")->end()
                    ->scalarNode("module_view")->defaultValue("/views/%module%")->end()
                    ->scalarNode("module_flash")->defaultValue("/flash/%module%")->end()
                    ->scalarNode("module_silverlight")->defaultValue("/silverlight/%module%")->end()
                    ->scalarNode("module_video")->defaultValue("/media/video/%module%")->end()
                    ->scalarNode("module_audio")->defaultValue("/media/audio/%module%")->end()

                ->end()->end()

            /* Export type */
                ->scalarNode('export_type')->defaultValue('zip')->validate()
                    ->IfNotInArray(array('zip', 'folder'))->thenInvalid("Invalid value given. Valid values are 'zip' and 'folder'.")->end()
                ->end();



            return $treeBuilder;
        }
    }
}
