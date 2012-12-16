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

    /**
     *
     */
    class ClearAction extends AbstractAction implements IAction {

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
                $fs->remove($params["exportPath"]);
                return new ActionResult(ActionResult::OK);
            } catch (IOException $ex) {
                return new ActionResult(ActionResult::STOP);
            }
        }
    }
}





