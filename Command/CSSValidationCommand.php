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

class CSSValidationCommand extends AbstractCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('build:validatecss')
            ->setDescription('Validates Stylesheets');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        require_once($this->rootPath . "/../vendor/cssmin/cssmin.php");

        $assets = $this->getContainer()->get('assetic.asset_manager');

        $output->writeln('<info>[INFO]</info> Validating CSS');

        foreach ($assets->getNames() as $name) {
            foreach ($assets->get($name) as $leaf) {
                $leaf->load();
                $this->validate($leaf, $output);
            }
        }
    }

    /**
     * @param $selector
     * @param $moduleName
     */
    protected function checkCssSelectors($selectors, $moduleName, $skin, OutputInterface $output)
    {
        $ret = array();

        foreach ($selectors as $selector) {
            $valid = false;

            foreach (array($moduleName, $skin) as $css) {
                if ($css == "") {
                    continue;
                }

                if (strpos($selector, "mod" . $css) !== false) {
                    $valid = true;
                }

                if (!$valid) {
                    $modName = strtolower(preg_replace('/([A-Z])/', '-$1', $css));
                    if (strpos($selector, "mod" . $modName) !== false) {
                        $valid = true;
                    }
                }
            }

            if (!$valid) {
                $ret[] = "  " . $this->getMessage(AbstractCommand::MSG_LEVEL_WARN, "Incomplete CSS Rule, no valid module identifier found -> " . $selector);
            }
        }

        return $ret;
    }

    /**
     *
     */
    protected function validate(AssetInterface $asset, OutputInterface $output)
    {
        if (strtolower(substr($asset->getTargetPath(), -3)) !== 'css') {
            return;
        }

        $msg = array();
        $matches = array();

        preg_match('/Terrific\/Module\/([\w\d]+)\//', $asset->getSourcePath(), $matches);
        $moduleName = (isset($matches[1]) ? $matches[1] : null);

        $skin = "";

        $content = $asset->getContent();

        /*
        // TODO: Fix line break !
        $i = 1;
        foreach (explode('\n', str_replace('\r', '\n', $content)) as $line) {
            if (strpos($line, 'a') !== false) {
                $msg[] = $this->getMessage(AbstractCommand::MSG_LEVEL_WARN, "Found tab in line " . $i);
            }
            $i++;
        }
        */

        $cssParser = new \CssParser($content);
        foreach ($cssParser->getTokens() as $token) {
            if ($token instanceof \CssRulesetStartToken && $moduleName != null) {
                $msg = array_merge($msg, $this->checkCssSelectors($token->Selectors, $moduleName, $skin, $output));
            }
        }


        if (count($msg) > 0) {
            $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Checking file : " . $asset->getSourcePath()));
            $output->writeln($msg);
        }
    }
}
