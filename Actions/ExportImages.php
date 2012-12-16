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
    use Terrific\ExporterBundle\Helper\TimerService;
    use Terrific\ExporterBundle\Object\ActionResult;
    use Terrific\ExporterBundle\Helper\FileHelper;
    use Terrific\ExporterBundle\Object\Route;
    use Symfony\Component\Config\FileLocator;
    use Terrific\ExporterBundle\Object\RouteModule;


    /**
     *
     */
    class ExportImages extends AbstractExportAction implements IAction {

        /**
         * Return true if the action should be runned false if not.
         *
         * @param array $params
         * @return bool
         */
        public function isRunnable(array $params) {
            return true;
        }


        /**
         * @param $pathResolver
         * @param $params
         */
        protected function exportViewImages(PageManager $pageManager, PathResolver $pathResolver, $params) {
            /** @var $route Route */
            foreach ($pageManager->findRoutes(true) as $route) {
                foreach ($route->getAssets(array('IMG')) as $img) {
                    $targetPath = $pathResolver->resolve($img);
                    $sourcePath = $pathResolver->locate(basename($img), $img);

                    $targetPath = $params["exportPath"] . "/" . $targetPath;
                    $this->saveToPath($sourcePath, $targetPath);
                }
            }
        }

        /**
         * @param \Terrific\ExporterBundle\Service\PathResolver $pathResolver
         * @param $params
         */
        protected function exportModuleImage(PageManager $pageManager, PathResolver $pathResolver, $params) {
            /** @var $route Route */
            foreach ($pageManager->findRoutes(true) as $route) {

                /** @var $module RouteModule */
                foreach ($route->getModules() as $module) {

                    foreach ($module->getAssets(array('IMG')) as $img) {
                        $targetPath = $pathResolver->resolve($img);
                        $sourcePath = $pathResolver->locate(basename($img), $img);

                        $targetPath = $params["exportPath"] . "/" . $targetPath;
                        $this->saveToPath($sourcePath, $targetPath);
                    }
                }
            }
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
            $this->exportViewImages($pageManager, $pathResolver, $params);
            $timer->lap("STOP-ViewImageExport");

            if ($this->logger) {
                $this->logger->debug(sprintf("ViewImageExport took %s seconds", $timer->getTime("START-ViewImageExport", "STOP-ViewImageExport")));
            }

            $timer->lap("START-ModuleImageExport");
            $this->exportModuleImage($pageManager, $pathResolver, $params);
            $timer->lap("STOP-ModuleImageExport");

            if ($this->logger) {
                $this->logger->debug(sprintf("ModuleImageExport took %s seconds", $timer->getTime("START-ModuleImageExport", "STOP-ModuleImageExport")));
            }


            return new ActionResult(ActionResult::OK);
        }
    }
}





