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


    /**
     *
     */
    class ExportImages extends AbstractAction implements IAction {

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
         * @param $tmpFile String
         * @param $targetFile String
         */
        protected function saveToPath($tmpFile, $targetFile) {
            /** @var $fs Filesystem */
            $fs = $this->container->get("filesystem");

            $targetPath = dirname($targetFile);

            try {
                FileHelper::createPathRecursive(dirname($targetFile));
                $fs->copy($tmpFile, $targetFile);

                return true;
            } catch (IOException $ex) {
                $this->logger->err($ex->getMessage());
                $this->logger->err($ex->getTraceAsString());
            }

            return false;
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

            /** @var $route Route */
            foreach ($pageManager->findRoutes() as $route) {
                foreach ($route->getAssets(array('IMG')) as $img) {
                    $targetPath = $pathResolver->resolve($img);
                    $sourcePath = $pathResolver->locate(basename($img), $img);

                    $targetPath = $params["exportPath"] . "/" . $targetPath;
                    var_dump($sourcePath, $targetPath);
                }
            }


            return new ActionResult(ActionResult::OK);
        }
    }
}





