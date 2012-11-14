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

    /**
     *
     */
    class ValidateCSS extends AbstractAction implements IAction
    {
        /**
         * @param $params
         * @return ActionResult
         */
        public function run(OutputInterface $output, $params = array())
        {
            return new ActionResult(ActionResult::OK);
        }
    }
}
