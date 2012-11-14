<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 12.11.12
 * Time: 14:15
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Symfony\Component\Console\Output\OutputInterface;
    use Terrific\ExporterBundle\Object\ActionResult;
    use Symfony\Component\Process\ProcessBuilder;

    /**
     *
    $builder = new ProcessBuilder(array('ls', '-lsa'));
    $builder->getProcess()->run();
     *     */

    /**
     *
     */
    class ValidateJS extends AbstractAction implements IAction
    {
        /**
         *
         * @param $params
         * @return ActionResult
         */
        public function run(OutputInterface $output, $params = array())
        {
            \Terrific\ExporterBundle\Helper\ProcessHelper::checkCommand(array('ls', '-la'));


            return new ActionResult(ActionResult::OK);
        }
    }
}
