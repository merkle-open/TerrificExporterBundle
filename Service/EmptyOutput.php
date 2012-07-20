<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 12.07.12
 * Time: 11:41
 * To change this template use File | Settings | File Templates.
 */

namespace Terrific\ExporterBundle\Service {
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Formatter\OutputFormatterInterface;


    class EmptyOutput implements OutputInterface
    {

        /**
         * Writes a message to the output.
         *
         * @param string|array $messages The message as an array of lines of a single string
         * @param Boolean      $newline  Whether to add a newline or not
         * @param integer      $type     The type of output
         *
         * @throws \InvalidArgumentException When unknown output type is given
         *
         * @api
         */
        function write($messages, $newline = false, $type = 0)
        {
            // TODO: Implement write() method.
        }

        /**
         * Writes a message to the output and adds a newline at the end.
         *
         * @param string|array $messages The message as an array of lines of a single string
         * @param integer      $type     The type of output
         *
         * @api
         */
        function writeln($messages, $type = 0)
        {
            // TODO: Implement writeln() method.
        }

        /**
         * Sets the verbosity of the output.
         *
         * @param integer $level The level of verbosity
         *
         * @api
         */
        function setVerbosity($level)
        {
            // TODO: Implement setVerbosity() method.
        }

        /**
         * Gets the current verbosity of the output.
         *
         * @return integer The current level of verbosity
         *
         * @api
         */
        function getVerbosity()
        {
            // TODO: Implement getVerbosity() method.
        }

        /**
         * Sets the decorated flag.
         *
         * @param Boolean $decorated Whether to decorate the messages or not
         *
         * @api
         */
        function setDecorated($decorated)
        {
            // TODO: Implement setDecorated() method.
        }

        /**
         * Gets the decorated flag.
         *
         * @return Boolean true if the output will decorate messages, false otherwise
         *
         * @api
         */
        function isDecorated()
        {
            // TODO: Implement isDecorated() method.
        }

        /**
         * Sets output formatter.
         *
         * @param OutputFormatterInterface $formatter
         *
         * @api
         */
        function setFormatter(OutputFormatterInterface $formatter)
        {
            // TODO: Implement setFormatter() method.
        }

        /**
         * Returns current output formatter instance.
         *
         * @return  OutputFormatterInterface
         *
         * @api
         */
        function getFormatter()
        {
            // TODO: Implement getFormatter() method.
        }
    }
}
