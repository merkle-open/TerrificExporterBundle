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
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\Console\Formatter\OutputFormatterStyle;
	use Assetic\Asset\AssetInterface;
	use DOMDocument;
	use DOMXpath;


	class HTMLValidationCommand extends AbstractCommand
	{
		private $cURL;

		/**
		 *
		 */
		protected function configure()
		{
			$this
				->setName('build:validatehtml')
				->setDescription('Validates HTML');
		}

		protected function outputMessages(\DOMNodeList $dNodeList, OutputInterface $output)
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

						$output->writeln("    ".$this->getMessage($msgLvl, sprintf("Line %d Col %d", $line, $col)));
						$output->writeln("           " . trim($msg));
						$output->writeln("           " . trim(urldecode($pos)));
						$output->writeln("");
					} else {
						$output->writeln("    ".$this->getMessage($msgLvl, trim($msg)));
					}


				} catch (\Exception $ex) {

				}
			}

		}

		/**
		 *
		 * @param $content
		 */
		protected function validatePageContent($content, OutputInterface $output)
		{
			curl_setopt_array($this->cURL, array(
				CURLOPT_URL => "http://validator.w3.org/check",
				CURLOPT_POST => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => array(
					"fragment" => $content,
					"output" => "soap12"
				)
			));

			$ret = curl_exec($this->cURL);

			$dom = new DOMDocument();
			$dom->loadXML($ret);
			$xpath = new DOMXpath($dom);
			$xpath->registerNamespace("m", "http://www.w3.org/2005/10/markup-validator");

			$errorCount = (int)$xpath->query("//m:errorcount")->item(0)->nodeValue;
			$output->writeln($this->getMessage(($errorCount == 0 ? AbstractCommand::MSG_LEVEL_INFO : AbstractCommand::MSG_LEVEL_ERROR), sprintf("Found %d Errors", $errorCount)));
			$errors = $xpath->query("//m:error");
			$this->outputMessages($errors, $output);


			$warnCount = (int)$xpath->query("//m:warningcount")->item(0)->nodeValue;
			$warnCount -= 1;
			$output->writeln($this->getMessage(($warnCount == 0 ? AbstractCommand::MSG_LEVEL_INFO : AbstractCommand::MSG_LEVEL_WARN), sprintf("Found %d Warnings", $warnCount)));
			$warnings = $xpath->query("//m:warning");
			$this->outputMessages($warnings, $output);

		}


		/**
		 * @param InputInterface $input
		 * @param OutputInterface $output
		 * @return int|void
		 */
		protected function execute(InputInterface $input, OutputInterface $output)
		{
			parent::execute($input, $output);
			$this->cURL = curl_init();


			$output->writeln('<info>[INFO]</info> Validating HTML');

			$pageManager = $this->getContainer()->get("terrific.composer.page.manager");
			$http = $this->getContainer()->get("http_kernel");

			foreach ($pageManager->getPages() as $page) {
				$request = Request::create($page->getUrl());
				$resp = $http->handle($request);
				$ret = $resp->getContent();

				$output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Validate " . $page->getUrl()));
				$this->validatePageContent($ret, $output);
			}

			curl_close($this->cURL);
		}

	}
}