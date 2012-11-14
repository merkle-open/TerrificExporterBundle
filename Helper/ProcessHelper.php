<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 14.11.12
 * Time: 13:18
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Helper {
    use Symfony\Component\Process\ProcessBuilder;

    /**
     *
     */
    abstract class ProcessHelper
    {

        /**
         * @param $command
         * @param $args
         */
        public static function checkCommand($args)
        {
            $builder = new ProcessBuilder($args);

            var_dump($builder->getProcess());
        }
    }
}
