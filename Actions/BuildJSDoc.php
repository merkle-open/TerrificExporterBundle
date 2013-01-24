<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 17.12.12
 * Time: 08:30
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Symfony\Component\Console\Output\OutputInterface;
    use Terrific\ExporterBundle\Object\ActionResult;
    use Terrific\ExporterBundle\Service\ConfigFinder;
    use Terrific\ExporterBundle\Helper\ProcessHelper;
    use Terrific\ExporterBundle\Object\ActionRequirement;
    use Terrific\ExporterBundle\Service\Log;
    use Terrific\ExporterBundle\Service\BuildOptions;

    /**
     *
     */
    class BuildJSDoc extends AbstractAction implements IAction {
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

            $ret[] = new ActionRequirement("yuidoc", ActionRequirement::TYPE_PROCESS, 'Terrific\ExporterBundle\Actions\BuildJSDoc');

            return $ret;
        }

        /**
         * @param $file
         */
        protected function updateYUIDocConfig($file, $params) {
            /** @var $buildOptions BuildOptions */
            $buildOptions = $this->container->get("terrific.exporter.build_options");

            $content = file_get_contents($file);
            $obj = json_decode($content);

            $obj->version = sprintf("%d.%d.%d", $buildOptions["version.major"], $buildOptions["version.minor"], $buildOptions["version.build"]);

            $content = json_encode($obj);
            file_put_contents($file, $content);
        }

        /**
         * Return true if the action should be runned false if not.
         *
         * @param array $params
         * @return bool
         */
        public function isRunnable(array $params) {
            return $params["build_js_doc"];
        }

        /**
         * @param $params
         * @return ActionResult
         */
        public function run(OutputInterface $output, $params = array()) {
            /** @var $configFinder ConfigFinder */
            $configFinder = $this->container->get("terrific.exporter.config_finder");

            $kernelRootDir = realpath($this->container->getParameter("kernel.root_dir") . "/../");

            $configFile = $configFinder->find("yuidoc.json");
            $this->updateYUIDocConfig($configFile, $params);

            $targetPath = $params["exportPath"] . "/apidoc";
            \Terrific\ExporterBundle\Helper\FileHelper::createPathRecursive($targetPath);

            $process = ProcessHelper::startCommand("yuidoc", array("-c", $configFile, "-o", $targetPath), $kernelRootDir);

            if (!$process->isSuccessful()) {
                Log::err("Cannot build javascript documentation");
                $this->logger->err($process->getErrorOutput());
                return new ActionResult(ActionResult::STOP);
            } else {
                $this->logger->warn($process->getErrorOutput());
            }

            return new ActionResult(ActionResult::OK);
        }

    }
}
