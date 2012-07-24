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
		$this->buildPath = realpath($this->rootPath . "/../build");

		if ($this->modulePath === false) {
			throw new \Exception("Couldn't find module path : " . $this->modulePath);
		}

		$this->buildOptions = $this->getContainer()->get("terrific.exporter.build_options");
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


}
