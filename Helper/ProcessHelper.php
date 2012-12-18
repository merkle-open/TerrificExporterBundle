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
    abstract class ProcessHelper {

        /**
         * Returns if the command is available.
         *
         * @param $command string
         * @return bool
         */
        public static function checkCommand($command) {
            switch ($command) {
                case "trimage":
                    $buildOp = array($command, "--version");
                    break;

                default:
                    $buildOp = array($command);
                    break;
            }


            $builder = new ProcessBuilder($buildOp);

            try {
                return ($builder->getProcess()->run() === 0);
            } catch (\RuntimeException $ex) {
                return false;
            }
        }

        /**
         * Starts the command with the given arguments.
         *
         * @param $command string
         * @param $args array
         * @return \Symfony\Component\Process\Process
         */
        public static function startCommand($command, $args = array(), $cwd = null) {
            $args = array_merge(array($command), $args);
            $builder = new ProcessBuilder($args);

            $process = $builder->getProcess();

            if ($cwd != null) {

                $process->setWorkingDirectory($cwd);
            }

            try {
                $process->setTimeout(7200);
                $process->run();
                return $process;
            } catch (\RuntimeException $ex) {
            }

            return null;
        }
    }
}
