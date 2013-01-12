<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 10.11.12
 * Time: 23:47
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Assetic\Asset\FileAsset;
    use Assetic\Factory\LazyAssetManager;
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Filesystem\Exception\IOException;
    use Symfony\Component\Finder\Finder;
    use Symfony\Component\Finder\SplFileInfo;
    use Terrific\ExporterBundle\Service\TempFileManager;
    use Terrific\ExporterBundle\Service\PageManager;
    use Terrific\ExporterBundle\Service\PathResolver;
    use Terrific\ExporterBundle\Service\TimerService;
    use Terrific\ExporterBundle\Object\ActionResult;
    use Terrific\ExporterBundle\Helper\FileHelper;
    use Terrific\ExporterBundle\Object\Route;
    use Symfony\Component\Config\FileLocator;
    use Terrific\ExporterBundle\Object\RouteModule;
    use Terrific\ExporterBundle\Helper\AsseticHelper;
    use Terrific\ExporterBundle\Service\Log;

    /**
     *
     */
    class ExportImages extends AbstractExportAction implements IAction {
        /**
         * Returns requirements for running this Action.
         *
         * @return array
         */
        public static function getRequirements() {
            return array();
        }

        /**
         * Return true if the action should be runned false if not.
         *
         * @param array $params
         * @return bool
         */
        public function isRunnable(array $params) {
            return true;
        }


        protected function saveImage($image, PathResolver $pathResolver, array $params) {

            if (!is_array($image)) {
                $image = array($image);
            }

            foreach ($image as $img) {
                $targetPath = $pathResolver->resolve($img);
                $sourcePath = $pathResolver->locate(basename($img), $img);

                $targetPath = $params["exportPath"] . "/" . $targetPath;
                $this->saveToPath($sourcePath, $targetPath);
            }

            return (count($image));
        }


        /**
         * @param $pathResolver
         * @param $params
         */
        protected function exportViewImages(PageManager $pageManager, PathResolver $pathResolver, $params) {
            $count = 0;

            /** @var $route Route */
            foreach ($pageManager->findRoutes(true) as $route) {
                foreach ($route->getAssets(array('IMG')) as $img) {
                    $this->saveImage($img, $pathResolver, $params);
                    ++$count;
                }
            }

            return $count;
        }

        /**
         * @param \Terrific\ExporterBundle\Service\PathResolver $pathResolver
         * @param $params
         */
        protected function exportModuleImage(PageManager $pageManager, PathResolver $pathResolver, $params) {
            $files = array();

            /** @var $route Route */
            foreach ($pageManager->findRoutes(true) as $route) {

                /** @var $module RouteModule */
                foreach ($route->getModules() as $module) {

                    foreach ($module->getAssets(array('IMG')) as $img) {
                        $this->saveImage($img, $pathResolver, $params);
                        $files[] = $img;
                    }
                }
            }

            return count(array_unique($files));
        }

        /**
         * @param \Terrific\ExporterBundle\Service\PageManager $pageManager
         * @param \Terrific\ExporterBundle\Service\PathResolver $pathResolver
         * @param $params
         */
        protected function exportCSSImages(PageManager $pageManager, PathResolver $pathResolver, $params) {
            /** @var $assetManager LazyAssetManager */
            $assetManager = $this->container->get("assetic.asset_manager");

            $styles = array();
            $count = 0;

            /** @var $route Route */
            foreach ($pageManager->findRoutes(true) as $route) {
                $styles = array_merge($styles, $route->getAssets(array('CSS')));
            }
            $styles = array_unique($styles);


            foreach ($assetManager->getNames() as $name) {
                /** @var $asset FileAsset */
                $asset = $assetManager->get($name);

                if (in_array($asset->getTargetPath(), $styles)) {
                    foreach ($asset as $leaf) {
                        $leaf = AsseticHelper::removeMinFilters($leaf);

                        $content = $leaf->dump();
                        $imageList = AsseticHelper::retrieveImages($content);
                        $count += $this->saveImage($imageList, $pathResolver, $params);
                    }
                }
            }

            return $count;
        }


        /**
         * @param \Symfony\Component\Console\Output\OutputInterface $output
         * @param array $params
         * @return ActionResult|void
         */
        public function run(OutputInterface $output, $params = array()) {
            /** @var $pageManager PageManager */
            $pageManager = $this->container->get("terrific.exporter.pagemanager");

            /** @var $pathResolver PathResolver */
            $pathResolver = $this->container->get("terrific.exporter.pathresolver");

            /** @var $timer TimerService */
            $timer = $this->container->get("terrific.exporter.timerservice");

            $timer->lap("START-ViewImageExport");
            $count = $this->exportViewImages($pageManager, $pathResolver, $params);
            Log::info("Exported %d Images from Views", array($count));
            $timer->lap("STOP-ViewImageExport");

            if ($this->logger) {
                $this->logger->debug(sprintf("ViewImageExport took %s seconds", $timer->getTime("START-ViewImageExport", "STOP-ViewImageExport")));
            }

            $timer->lap("START-ModuleImageExport");
            $count = $this->exportModuleImage($pageManager, $pathResolver, $params);
            Log::info("Exported %d Images from Modules", array($count));
            $timer->lap("STOP-ModuleImageExport");

            if ($this->logger) {
                $this->logger->debug(sprintf("ModuleImageExport took %s seconds", $timer->getTime("START-ModuleImageExport", "STOP-ModuleImageExport")));
            }

            $timer->lap("START-CSSImageExport");
            $count = $this->exportCSSImages($pageManager, $pathResolver, $params);
            Log::info("Exported %d Images from CSS", array($count));
            $timer->lap("STOP-CSSImageExport");

            if ($this->logger) {
                $this->logger->debug(sprintf("CSSImageExport took %s seconds", $timer->getTime("START-CSSImageExport", "STOP-CSSImageExport")));
            }


            return new ActionResult(ActionResult::OK);
        }
    }
}





