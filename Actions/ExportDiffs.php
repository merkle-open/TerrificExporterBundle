<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 03.01.13
 * Time: 20:06
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Terrific\ExporterBundle\Object\ActionRequirement;
    use Terrific\ExporterBundle\Object\ActionResult;
    use Symfony\Component\Console\Output\OutputInterface;
    use Terrific\ExporterBundle\Helper\ProcessHelper;
    use Terrific\ExporterBundle\Exception\ExporterException;
    use Terrific\ExporterBundle\Service\TempFileManager;
    use Terrific\ExporterBundle\Service\PageManager;
    use Terrific\ExporterBundle\Object\Route;
    use Terrific\ExporterBundle\Object\RouteLocale;
    use Symfony\Component\Finder\Finder;
    use Symfony\Component\Finder\SplFileInfo;
    use Terrific\ExporterBundle\Service\PathResolver;

    /**
     *
     */
    class ExportDiffs extends AbstractExportAction {

        /**
         * Returns requirements for running this Action.
         *
         * @param \Symfony\Component\Console\Output\OutputInterface $output
         * @param array $params
         * @param array $runnedActions
         * @return array
         */
        public static function getRequirements() {
            $ret = array();

            $ret[] = new ActionRequirement("diff", ActionRequirement::TYPE_PROCESS, 'Terrific\ExporterBundle\Actions\ExportDiffs');

            return $ret;
        }

        /**
         * Return true if the action should be runned false if not.
         *
         * @param array $params
         * @return bool
         */
        public function isRunnable(array $params) {
            return (isset($params["input"]["last-export"]) && !empty($params["input"]["last-export"]));
        }


        /**
         * @param $params
         * @return ActionResult
         */
        public function run(OutputInterface $output, $params = array()) {
            /** @var $pageManager PageManager */
            $pageManager = $this->container->get("terrific.exporter.page_manager");

            /** @var $pathResolver PathResolver */
            $pathResolver = $this->container->get("terrific.exporter.pathresolver");

            /** @var $tmpFileMgr TempFileManager */
            $tmpFileMgr = $this->container->get("terrific.exporter.temp_file_manager");

            $lastExportPath = $params["input"]["last-export"];
            $changeLogPath = $params["exportPath"] . "/changelogs/diffs";


            $finder = new Finder();
            $finder->in($lastExportPath);

            /** @var $route Route */
            foreach ($pageManager->findRoutes(true) as $route) {
                if ($route->isLocalized()) {
                    /** @var $locale RouteLocale */
                    foreach ($route->getLocales() as $locale) {
                        $finder->name($locale->getExportName());
                    }
                } else {
                    $finder->name($route->getExportName());
                }
            }

            /** @var $file SplFileInfo */
            foreach ($finder as $file) {
                $exportTo = $params["exportPath"] . "/" . $pathResolver->resolve($file->getFilename());

                $process = ProcessHelper::startCommand("diff", array($file->getPathname(), $exportTo));
                if ($process->getExitCode() == 1) {
                    $diff = trim($process->getOutput());

                    $tmpFile = $tmpFileMgr->putContent($diff);
                    $this->saveToPath($tmpFile, "${changeLogPath}/" . basename($file->getFilename()) . ".diff");
                }
            }

            return new ActionResult(ActionResult::OK);
        }
    }
}
