<?php

/*
 * This file is part of the Terrific Core Bundle.
 *
 * (c) Remo Brunschwiler <remo@terrifically.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Terrific\ExporterBundle\Filter {
    use Assetic\Filter\BaseCssFilter;
    use Assetic\Asset\AssetInterface;
    use Assetic\Filter\FilterInterface;
    use Terrific\ExporterBundle\Helper\AsseticHelper;
    use Terrific\ExporterBundle\Service\PathResolver;
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\DependencyInjection\ContainerAwareInterface;
    use Symfony\Component\Filesystem\Filesystem;

    /**
     * Fixes CSS urls for Terrific Resources.
     *
     * @author Remo Brunschwiler <remo@terrifically.org>
     */
    class CSSPathRewriteFilter extends BaseCssFilter implements ContainerAwareInterface {

        /** @var PathResolver */
        private $pathResolver;

        /** @var ContainerInterface */
        private $container;

        /**
         * @param \Terrific\ExporterBundle\Service\PathResolver $pathResolver
         */
        public function setPathResolver($pathResolver) {
            $this->pathResolver = $pathResolver;
        }

        /**
         * Sets the Container.
         *
         * @param ContainerInterface $container A ContainerInterface instance
         *
         * @api
         */
        public function setContainer(ContainerInterface $container = null) {
            $this->container = $container;
        }

        /**
         * @param \Assetic\Asset\AssetInterface $asset
         */
        public function filterLoad(AssetInterface $asset) {
        }

        /**
         * @param \Assetic\Asset\AssetInterface $asset
         */
        public function filterDump(AssetInterface $asset) {
            $params = $this->container->getParameter('terrific_exporter');

            if (!empty($params["build_local_paths"]) && $params["build_local_paths"] === true) {
                $fs = new Filesystem();

                $sourcePath = $asset->getSourcePath();
                $targetPath = $asset->getTargetPath();

                if (null === $sourcePath || null === $targetPath || $sourcePath == $targetPath) {
                    return;
                }

                $exportPath = $this->pathResolver->resolve($targetPath);

                $content = $asset->getContent();

                foreach (AsseticHelper::retrieveImages($content) as $img) {
                    $nPath = $this->pathResolver->resolve($img);

                    $tPath = $fs->makePathRelative(dirname($nPath), dirname($exportPath));
                    $content = str_replace($img, $tPath . basename($img), $content);
                }

                $asset->setContent($content);
            }
        }

    }
}
