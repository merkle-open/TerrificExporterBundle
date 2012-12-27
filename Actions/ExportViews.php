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
                $targetPath = $params["exportPath"] . "/" . ltrim($pathResolver->resolve($route->getExportName()), "/");
                $resp = $pageManager->dumpRoute($route);


                if (!empty($params["build_local_paths"]) && $params["build_local_paths"] === true) {
                    $content = $resp->getContent();
                    foreach ($route->getAllAssets() as $asset) {
                        $nAssetPath = $params["exportPath"] . "/" . ltrim($pathResolver->resolve($asset), "/");
                        $retPath = $this->fs->makePathRelative(dirname($nAssetPath), dirname($targetPath));
                        $nAsset = $retPath . basename($nAssetPath);


                        // TODO: Find a better solutation than this
                        foreach (array("../${asset}?1", "/${asset}?1") as $f) {
                            $content = str_replace($f, $nAsset, $content);
                        }
                    }
                    $resp->setContent($content);
                }


                $file = $tmpFileMgr->putContent($resp->getContent());
                $this->saveToPath($file, $targetPath);
            }

            return new ActionResult(ActionResult::OK);

        }
    }
}

