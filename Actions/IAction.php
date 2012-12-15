<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 10.11.12
 * Time: 23:48
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\DependencyInjection\ContainerAwareInterface;

    /**
     *
     */
    interface IAction extends ContainerAwareInterface {
        /**
         * @param $directory
         * @return void
         */
        public function setWorkingDir($directory);

        /**
         * @param $params
         * @return ActionResult
         */
        public function run(OutputInterface $output, $params = array());


        /**
         * Return true if the action should be runned false if not.
         *
         * @param array $params
         * @return bool
         */
        public function isRunnable(array $params);
    }
}
