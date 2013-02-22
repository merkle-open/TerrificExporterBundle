<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 10.11.12
 * Time: 23:47
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\Console\Output\OutputInterface;
    use Terrific\ExporterBundle\Object\ActionResult;
    use Symfony\Component\Filesystem\Exception\IOException;
    use Terrific\ExporterBundle\Service\Log;

    /**
     *
     */
    class ClearAction extends AbstractAction implements IAction {
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
         * @param \Symfony\Component\Console\Output\OutputInterface $output
         * @param array $params
         * @return ActionResult|void
         */
        public function run(OutputInterface $output, $params = array()) {
            /** @var $fs Filesystem */
            $fs = $this->container->get("filesystem");

            try {
                // Check if export folder exists and delete it (build.ini)
                if ($fs->exists($params["exportPath"])) {
                    $fs->remove($params["exportPath"]);
                    Log::info("Deleted old export path: %s.", $params["exportPath"]);
                }

                // Build zip file name (build.ini)
                $target = $params["exportPath"] . ".zip";
                Log::info("Zip file name created: %s.", array($target));

                // Check if zip exists and delete it
                if ($fs->exists($target)) {
                    $fs->remove($target);
                    Log::info("Deleted old zip file: %s.", array($target));
                }
                return new ActionResult(ActionResult::OK);
            } catch (IOException $ex) {
                return new ActionResult(ActionResult::STOP);
            }
        }
    }
}





