<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 12.11.12
 * Time: 14:13
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Symfony\Component\Console\Output\OutputInterface;
    use Terrific\ExporterBundle\Service\PageManager;
    use Terrific\ExporterBundle\Service\TempFileManager;
    use Terrific\ExporterBundle\Object\ActionResult;
    use Terrific\ExporterBundle\Service\PathResolver;
    use Terrific\ExporterBundle\Object\Route;
    use Terrific\ExporterBundle\Helper\FileHelper;
    use Terrific\ExporterBundle\Service\Log;
    use Terrific\ExporterBundle\Object\RouteLocale;

    /**
     *
     */
    class ExportViews extends AbstractExportAction implements IAction {
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
            return (isset($params["export_views"]) && $params["export_views"]);
        }


        /**
         * @param \Terrific\ExporterBundle\Object\Route $route
         */
        protected function buildLocalPaths(Route $route, PathResolver $pathResolver, $content, $targetPath, $params) {
            if (!empty($params["build_local_paths"]) && $params["build_local_paths"] === true) {
                foreach ($route->getAllAssets() as $asset) {

                    $asset = ltrim($pathResolver->buildWebPath($asset), "/");

                    $nAssetPath = $params["exportPath"] . "/" . ltrim($pathResolver->resolve($asset), "/");
                    $retPath = $this->fs->makePathRelative(dirname($nAssetPath), dirname($targetPath));
                    $nAsset = $retPath . basename($nAssetPath);

                    // TODO: Find a better solutation than this
                    foreach (array("../${asset}?1", "/${asset}?1") as $f) {
                        $content = str_replace($f, $nAsset, $content);
                    }
                }
            }
            return $content;
        }

        protected function export($exportName, Route $route, TempFileManager $tmpFileMgr, PathResolver $pathResolver, $content, $params) {
            $targetPath = $params["exportPath"] . "/" . ltrim($pathResolver->resolve($exportName), "/");
            $content = $this->buildLocalPaths($route, $pathResolver, $content, $targetPath, $params);

            $file = $tmpFileMgr->putContent($content);
            $this->saveToPath($file, $targetPath);
        }

        /**
         * @param $params
         * @return ActionResult
         */
        public function run(OutputInterface $output, $params = array()) {
            /** @var $tmpFileMgr TempFileManager */
            $tmpFileMgr = $this->container->get("terrific.exporter.tempfilemanager");

            /** @var $pageManager PageManager */
            $pageManager = $this->container->get("terrific.exporter.pagemanager");

            /** @var $pathResolver PathResolver */
            $pathResolver = $this->container->get("terrific.exporter.pathresolver");


            /** @var $route Route */
            foreach ($pageManager->findRoutes(true) as $route) {
                Log::info("Exporting View: " . $route->getExportName());

                if (!$route->isLocalized()) {
                    $resp = $pageManager->dumpRoute($route);
                    $this->export($route->getExportName(), $route, $tmpFileMgr, $pathResolver, $resp->getContent(), $params);

                } else {
                    Log::blkstart();
                    /** @var $locale RouteLocale */
                    foreach ($route->getLocales() as $locale) {
                        Log::info("Export Locale: " . $locale->getLocale());
                        $resp = $pageManager->dumpRoute($route, $locale->getLocale());

                        $this->export($locale->getExportName(), $route, $tmpFileMgr, $pathResolver, $resp->getContent(), $params);
                    }
                    Log::blkend();
                }


            }

            return new ActionResult(ActionResult::OK);

        }
    }
}

