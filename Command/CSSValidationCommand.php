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
use DOMDocument;
use DOMXPath;

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
		//$this->cURL = curl_init();

		require_once($this->rootPath . "/../vendor/cssmin/cssmin.php");

		$assets = $this->getContainer()->get('assetic.asset_manager');

		$output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Validating CSS"));

		foreach ($assets->getNames() as $name) {
			foreach ($assets->get($name) as $leaf) {
				$leaf->load();
				$this->validate($leaf, $output);
			}
		}

		//curl_close($this->cURL);
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

	protected function validateCssContent($content, OutputInterface $output)
	{

		$dom = $this->sendToW3Validator('CSS', $content);

		$xpath = new DOMXpath($dom);
		$xpath->registerNamespace("m", "http://www.w3.org/2005/07/css-validator");

		$errorCount = (int)$xpath->query("//m:errorcount")->item(0)->nodeValue;
		$output->writeln($this->getMessage(($errorCount == 0 ? AbstractCommand::MSG_LEVEL_INFO : AbstractCommand::MSG_LEVEL_ERROR), sprintf("Found %d Errors", $errorCount)));
		$errors = $xpath->query("//m:error");
		//$this->outputW3Messages($errors, $output);


		$warnCount = (int)$xpath->query("//m:warningcount")->item(0)->nodeValue;
		$warnCount -= 1;
		$output->writeln($this->getMessage(($warnCount == 0 ? AbstractCommand::MSG_LEVEL_INFO : AbstractCommand::MSG_LEVEL_WARN), sprintf("Found %d Warnings", $warnCount)));
		$warnings = $xpath->query("//m:warning");
		//$this->outputW3Messages($warnings, $output);
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

		$output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Validating : " . basename($asset->getSourcePath())));
		//$this->validateCssContent($content, $output);

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
			$output->writeln($msg);
		}
	}
}
