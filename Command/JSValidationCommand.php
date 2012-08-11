<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 24.06.12
 * Time: 08:04
 * To change this template use File | Settings | File Templates.
 */


namespace Terrific\ExporterBundle\Command {

    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    use Assetic\Asset\AssetInterface;
    use Symfony\Component\Finder\Finder;

    /**
     *
     */
    class JSValidationCommand extends AbstractCommand
    {

        protected $config;

        /**
         *
         */
        protected function configure()
        {
            $this
                ->setName('build:validatejs')
                ->setDescription('Validates Javascript');
        }

        /**
         * @param \Assetic\Asset\AssetInterface $leaf
         * @param \Symfony\Component\Console\Output\OutputInterface $output
         */
        protected function validate(AssetInterface $leaf, OutputInterface $output)
        {
            $filename = basename($leaf->getSourcePath());
            $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Validating " . $filename));


            $finder = new Finder();
            $finder->files()->in(__DIR__ . "/../../../../../src/Terrific/Module")->name($filename)->depth("<= 99");

            foreach ($finder as $f) {
                $ret = array();
                $retval = 0;
                exec(sprintf('jshint --jslint-reporter --config=%s %s', $this->config, $f->getPathname()), $ret, $retval);

                $xml = new \DOMDocument();
                $xml->loadXML(implode($ret));

                $xpath = new \DOMXPath($xml);

                $nodeList = $xpath->query("//file/issue");

                if ($nodeList->length > 0) {
                    $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_WARN, sprintf("%d Issues found", $nodeList->length)));
                }
// <issue line="31" char="24" reason="&apos;params&apos; is already defined." evidence="            var params = (params === undefined ? $({}) : $(params));" />
                foreach ($nodeList as $node) {
                    $output->writeln("    " . $this->getMessage(AbstractCommand::MSG_LEVEL_WARN, sprintf('Line %d Col %d', $node->getAttribute("line"), $node->getAttribute("char"))));
                    $output->writeln(str_repeat(" ", 11) . trim($node->getAttribute("reason")));
                    $output->writeln(str_repeat(" ", 11) . trim($node->getAttribute("evidence")));
                    $output->writeln("");
                }

            }
        }


        protected function execute(InputInterface $input, OutputInterface $output)
        {
            parent::execute($input, $output);

            $this->config = realpath(__DIR__ . "/../Resources/config/jshint.json");

            $assets = $this->getContainer()->get('assetic.asset_manager');

            $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, 'Checking jshint installation'));
            $retval = -1;
            $ret = array();
            exec('jshint --help 2>&1', $ret, $retval);
            if ($retval == 0) {
                $ret = array();
                $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Validating JS"));

                foreach ($assets->getNames() as $name) {
                    foreach ($assets->get($name) as $leaf) {
                        if (strtolower(substr($leaf->getTargetPath(), -2)) === 'js' && strpos($leaf->getSourcePath(), "/Terrific/Module") !== false) {
                            $this->validate($leaf, $output);
                        }
                    }
                }
            }
        }
    }

}
