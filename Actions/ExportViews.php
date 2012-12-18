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
                $resp = $pageManager->dumpRoute($route);
                $file = $tmpFileMgr->putContent($resp->getContent());

                $targetPath = $params["exportPath"] . "/" . $pathResolver->resolve($route->getExportName());
                $this->saveToPath($file, $targetPath);
            }

            return new ActionResult(ActionResult::OK);

        }
    }
}

