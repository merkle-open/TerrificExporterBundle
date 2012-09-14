<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 24.06.12
 * Time: 14:19
 * To change this template use File | Settings | File Templates.
 */

namespace Terrific\ExporterBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use DOMDocument;


abstract class AbstractCommand extends ContainerAwareCommand
{
    const MSG_LEVEL_ERROR = 1;
    const MSG_LEVEL_INFO = 2;
    const MSG_LEVEL_WARN = 4;

    /**
     * @var
     */
    protected $rootPath;
    protected $modulePath;
    protected $buildPath;
    protected $buildOptions;
    protected $cURL;


    /**
     *
     */
    protected function retrieveExportPath($toEnd = true, $lang = "")
    {
        $buildPath = $this->getContainer()->getParameter('terrific_exporter.build_path');

        $ret = $this->rootPath . "/../";
        $ret .= $buildPath . "/";
        if ($toEnd) {
            if ($lang != "") {
                $ret .= strtoupper($lang) . "_";
            }
            $ret .= $this->buildExportName(($this->getContainer()->getParameter('terrific_exporter.export_type') == "zip"));
        }

        return $ret;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('yellow', 'black', array()));
        $output->getFormatter()->setStyle('info', new OutputFormatterStyle('green', 'black', array()));

        $this->rootPath = $this->getContainer()->getParameter('kernel.root_dir');
        $this->modulePath = realpath($this->rootPath . "/../src/Terrific/Module");
        $this->buildPath = realpath($this->retrieveExportPath(false));

        if ($this->modulePath === false) {
            throw new \Exception("Couldn't find module path : " . $this->modulePath);
        }

        $this->buildOptions = $this->getContainer()->get("terrific.exporter.build_options");
        $this->buildOptions->setFile($this->rootPath . "/../" . $this->getContainer()->getParameter("terrific_exporter.build_settings"));
    }

    /**
     * @param $lvl
     * @param $msg
     * @return string
     */
    protected function getMessage($lvl, $msg)
    {
        $tpl = "";

        switch ($lvl) {
            case AbstractCommand::MSG_LEVEL_ERROR:
                $tpl = "<error>[ERROR]</error> %s";
                break;

            case AbstractCommand::MSG_LEVEL_INFO:
                $tpl = "<info>[INFO]</info> %s";
                break;

            case AbstractCommand::MSG_LEVEL_WARN:
                $tpl = "<warning>[WARN]</warning> %s";
                break;
        }

        return sprintf($tpl, $msg);
    }

    /**
     * @param $assetPath
     * @param $path
     * @return string
     */
    protected function buildAssetPath($assetPath, $path, $stripCompiled = false)
    {
        if (strpos($assetPath, "_controller") > -1) {
            $ret = str_replace("_controller", $path, $assetPath);
        } else {
            $ret = $path . "/" . $assetPath;
        }

        if ($stripCompiled) {
            $ret = str_replace("/compiled", "", $ret);
        }

        return $ret;
    }


    /**
     * @param \DOMNodeList $dNodeList
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function outputW3Messages(\DOMNodeList $dNodeList, OutputInterface $output)
    {
        foreach ($dNodeList as $e) {
            try {

                switch (strtoupper($e->tagName)) {
                    case "M:ERROR":
                        $msgLvl = AbstractCommand::MSG_LEVEL_ERROR;
                        break;

                    case "M:WARNING":
                        $msgLvl = AbstractCommand::MSG_LEVEL_WARN;
                        break;

                    default:
                        $msgLvl = AbstractCommand::MSG_LEVEL_INFO;
                        break;
                }


                $msg = $e->getElementsByTagName("message")->item(0)->nodeValue;
                if ($e->getElementsByTagName("line")->length > 0) {
                    $line = $e->getElementsByTagName("line")->item(0)->nodeValue;
                    $col = $e->getElementsByTagName("col")->item(0)->nodeValue;
                    $pos = $e->getElementsByTagName("source")->item(0)->nodeValue;
                    $pos = str_replace(array('&#34;', '&#62;', '&#60;'), array('"', '>', '<'), $pos);

                    $output->writeln("    " . $this->getMessage($msgLvl, sprintf("Line %d Col %d", $line, $col)));
                    $output->writeln("           " . trim($msg));
                    $output->writeln("           " . trim(urldecode($pos)));
                    $output->writeln("");
                } else {
                    $output->writeln("    " . $this->getMessage($msgLvl, trim($msg)));
                }


            } catch (\Exception $ex) {

            }
        }
    }


    /**
     *
     * @param $content
     * @return DOMDocument
     */
    protected function sendToW3Validator($mode, $content)
    {
        sleep(2);

        $postFields = null;
        switch ($mode) {
            case "HTML":
                $url = "http://validator.w3.org/check";
                $postFields = array(
                    "fragment" => $content,
                    "output" => "soap12"
                );
                break;

            case "CSS":
                $url = "http://jigsaw.w3.org/css-validator/validator";
                $postFields = array(
                    'uri' => '',
                    "text" => urlencode($content),
                    "output" => "soap12"
                );
                break;
        }

        curl_setopt_array($this->cURL, array(
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $postFields
        ));

        $ret = curl_exec($this->cURL);

        $dom = new DOMDocument();
        $dom->loadXML($ret);

        return $dom;
    }


    /**
     * @param $command
     * @param $expectedResult
     */
    protected function checkCommand($command, $expectedResult)
    {
        $ret = array();
        $retval = -1;
        exec($command, $ret, $retval);
        return ($retval == $expectedResult);
    }
}
