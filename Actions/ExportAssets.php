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

    /**
     *
     */
    class ExportAssets extends AbstractAction implements IAction {

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
         *
         */
        protected function addDependencies($param) {
            /** @var $pathResolver PathResolver */
            $pathResolver = $this->container->get("terrific.exporter.pathresolver");

            $f = new Finder();

            $jsPath = $this->container->getParameter("kernel.root_dir") . "/../web/js/dependencies";
            $cssPath = $this->container->getParameter("kernel.root_dir") . "/../web/css/dependencies";

            /** @var $file SplFileInfo */
            foreach ($f->in($cssPath)->in($jsPath)->name("*.js")->name("*.css") as $file) {
                $resolve = $pathResolver->resolve($file->getRelativePathname());
                $targetPath = dirname($resolve) . "/dependencies/" . $file->getRelativePathname();
                $this->saveToPath($file->getPathname(), $param["exportPath"] . "/" . $targetPath);
            }
        }


        /**
         * @param \Symfony\Component\Console\Output\OutputInterface $output
         * @param array $params
         * @return ActionResult|void
         */
        public function run(OutputInterface $output, $params = array()) {
            /** @var $assetManager LazyAssetManager */
            $assetManager = $this->container->get("assetic.asset_manager");

            /** @var $tmpFileMgr TempFileManager */
            $tmpFileMgr = $this->container->get("terrific.exporter.tempfilemanager");

            /** @var $pageManager PageManager */
            $pageManager = $this->container->get("terrific.exporter.pagemanager");

            /** @var $pathResolver PathResolver */
            $pathResolver = $this->container->get("terrific.exporter.pathresolver");

            /** @var $timer TimerService */
            $timer = $this->container->get("terrific.exporter.timerservice");

            // retrieve only assets matching the current export
            $assetList = $pageManager->retrieveAllAssets(true);

            $results = true;
            foreach ($assetManager->getNames() as $name) {
                /** @var $asset FileAsset */
                $asset = $assetManager->get($name);

                if (in_array($asset->getTargetPath(), $assetList)) {
                    $this->logger->info("Exporting asset " . basename($asset->getTargetPath()));

                    $nPath = $params["exportPath"] . "/" . $pathResolver->resolve($asset->getTargetPath());

                    $sPoint = $timer->lap();
                    $content = $asset->dump();
                    $file = $tmpFileMgr->putContent($content);
                    $results &= $this->saveToPath($file, $nPath);
                    $ePoint = $timer->lap();

                    $this->logger->info(sprintf("Exporting took %s seconds", $timer->getTime($sPoint, $ePoint)));
                }
            }

            $this->addDependencies($params);

            if (!$results) {
                return new ActionResult(ActionResult::STOP);
            }

            return new ActionResult(ActionResult::OK);
        }
    }
}





