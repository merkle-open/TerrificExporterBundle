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
    use Terrific\ExporterBundle\Filter\CSSPathRewriteFilter;
    use Terrific\ExporterBundle\Service\Log;

    /**
     *
     */
    class ExportAssets extends AbstractExportAction implements IAction {
        /**
         * Returns requirements for running this Action.
         *
         * @param \Symfony\Component\Console\Output\OutputInterface $output
         * @param array $params
         * @param array $runnedActions
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

        /**
         *
         */
        protected function addDependencies($param) {
            /** @var $pathResolver PathResolver */
            $pathResolver = $this->container->get("terrific.exporter.pathresolver");

            $f = new Finder();
            $jsPath = $this->container->getParameter("kernel.root_dir") . "/../web/js/dependencies";
            if (file_exists($jsPath)) {
                $f->in($jsPath);
            }

            $cssPath = $this->container->getParameter("kernel.root_dir") . "/../web/css/dependencies";
            if (file_exists($cssPath)) {
                $f->in($cssPath);
            }

            /** @var $file SplFileInfo */
            foreach ($f->name("*.js")->name("*.css") as $file) {
                $resolve = $pathResolver->resolve($file->getPathname());
                $this->saveToPath($file->getPathname(), $param["exportPath"] . "/" . $resolve);
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

            /** @var $cssPathFilter CSSPathRewriteFilter */
            $cssPathFilter = $this->container->get("terrific.exporter.filter.exportpathfilter");

            if ($this->logger) {
                $this->logger->info("Using asset list: " . implode(", ", $assetList));
            }

            $results = true;
            foreach ($assetManager->getNames() as $name) {
                /** @var $asset FileAsset */
                $asset = $assetManager->get($name);

                if (in_array($asset->getTargetPath(), $assetList)) {
                    $this->logger->info("Exporting asset " . basename($asset->getTargetPath()));

                    $nPath = $params["exportPath"] . "/" . $pathResolver->resolve($asset->getTargetPath());

                    $sPoint = $timer->lap();
                    $content = $asset->dump($cssPathFilter);
                    $file = $tmpFileMgr->putContent($content);
                    $results &= $this->saveToPath($file, $nPath);
                    $ePoint = $timer->lap();

                    Log::info("Exported asset [%s]", array(basename($asset->getTargetPath())));
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





