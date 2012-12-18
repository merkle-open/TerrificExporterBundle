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

            $process = ProcessHelper::startCommand("yuidoc", array("-c", $configFile), $kernelRootDir);

            var_dump($process->getOutput());
            var_dump($process->getErrorOutput());





            return new ActionResult(ActionResult::OK);
        }

    }
}
