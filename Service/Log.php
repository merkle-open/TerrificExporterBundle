<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 18.12.12
 * Time: 12:31
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Service {
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Formatter\OutputFormatterStyle;

    /**
     *
     */
    class Log {
        const INDENT_SPACE = 2;

        /** @var Log */
        private static $instance = null;

        /** @var int */
        private static $currentIndent = 0;

        /** @var OutputInterface */
        private $output;

        /**
         * @param $params
         */
        public static function init(OutputInterface $output) {
            self::$instance = new self($output);
        }


        /**
         * @param $msg
         * @param $indent
         */
        private static function createMessage($msg, $indent) {
            if ($indent == -1) {
                $indent = self::$currentIndent;
            }

            if ($indent > 0) {
                return str_pad("", $indent * self::INDENT_SPACE, "-") . " " . $msg;
            }

            return $msg;
        }

        /**
         * @param $msg
         * @param array $params
         */
        public static function info($msg, array $params = array(), $indent = -1) {
            if (self::$instance != null) {
                self::$instance->output->writeln(self::createMessage('<info>[INFO]</info> ' . vsprintf($msg, $params), $indent));
            }
        }

        /**
         * @param $msg
         * @param array $params
         */
        public static function err($msg, array $params = array(), $indent = -1) {
            if (self::$instance != null) {
                self::$instance->output->writeln(self::createMessage('<error>[ERROR]</error> ' . vsprintf($msg, $params), $indent));
            }
        }

        /**
         * @param $msg
         * @param array $paramss
         */
        public static function warn($msg, array $params = array(), $indent = -1) {
            if (self::$instance != null) {
                self::$instance->output->writeln(self::createMessage('<warning>[WARNING]</warning> ' . vsprintf($msg, $params), $indent));
            }
        }

        /**
         *
         */
        public static function blkstart() {
            ++self::$currentIndent;
        }

        /**
         *
         */
        public static function blkend() {
            --self::$currentIndent;

            if (self::$currentIndent < 0) {
                self::$currentIndent == 0;
            }

            if (self::$currentIndent == 0 && self::$instance != null) {
                self::$instance->output->writeln("\n");
            }
        }

        /**
         *
         */
        private function __construct(OutputInterface $output) {
            $this->output = $output;

            $this->output->getFormatter()->setStyle('error', new OutputFormatterStyle('red', 'black', array()));
            $this->output->getFormatter()->setStyle('warning', new OutputFormatterStyle('yellow', 'black', array()));
            $this->output->getFormatter()->setStyle('info', new OutputFormatterStyle('green', 'black', array()));
        }
    }
}
