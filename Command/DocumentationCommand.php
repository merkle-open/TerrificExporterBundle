<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 24.06.12
 * Time: 08:04
 * To change this template use File | Settings | File Templates.
 */


namespace Terrific\ExporterBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Assetic\Asset\AssetInterface;
use Symfony\Component\Finder\Finder;
use DOMDocument;
use DOMXPath;

class DocumentationCommand extends AbstractCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('doc:build')
            ->setDescription('Builds a documentation');
    }

    /**
     *
     */
    protected function findCommonFunctions($file, $ret)
    {
        $data = file_get_contents($file);

        $matches = array();
        if (preg_match_all('/(Tc[^\(]*)\(/', $data, $matches) !== false) {
            if (isset($matches[1])) {
                array_walk($matches[1], function(&$i, $idx)
                {
                    switch (true) {
                        case (strpos($i, "Tc.Module.extend") !== false):
                        case (strpos($i, "Tc.Config") === 0):
                            $i = "";
                            break;
                    }
                });
            }

            $t = array_unique($matches[1]);

            $ret[$file] = array();
            foreach ($t as $val) {
                if (trim($val) != "") {
                    $ret[$file][] = $val;
                }
            }
        }

        return $ret;
    }

    /**
     * @param $layout
     */
    protected function findConnectors($layout)
    {

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $finder = new Finder();
        $finder->files()->in(__DIR__ . "/../../../../../src/Terrific/Module")->name("*.js")->depth("<= 99");


        $ret = array();
        foreach ($finder as $file) {
//            $ret = $this->findCommonFunctions(realpath($file->getPathName()), $ret);
            $this->parseJavascript($file->getPathName());

        }

        return;

        foreach ($ret as $js => $funcList) {

            $readme = dirname($js) . "/../../../";
            if (strpos(dirname($js), 'skin') !== false) {
                $readme .= "/../";
            }

            $readme .= "README.md";

            $data = file_get_contents($readme);
            $data .= "\n\n";
            $data .= "# Common used Functions\n";

            $count = 0;
            sort($funcList);
            foreach ($funcList as $func) {
                if (trim($func) != "") {
                    $data .= "- " . $func . "\n";
                    $count++;
                }
            }

            if ($count > 0) {
                file_put_contents($readme, $data);
            }

            //
        }

    }
}
