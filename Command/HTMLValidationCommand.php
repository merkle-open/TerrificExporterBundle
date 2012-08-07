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


	/**
	 *
	 */
	class HTMLValidationCommand extends AbstractCommand
	{

		/**
		 *
		 */
		protected function configure()
		{
			$this
				->setName('build:validatehtml')
				->setDescription('Validates HTML');
		}

		protected function validatePageContent($content, OutputInterface $output) {

			$dom = $this->sendToW3Validator('HTML', $content);

			$xpath = new DOMXpath($dom);
			$xpath->registerNamespace("m", "http://www.w3.org/2005/10/markup-validator");

			$errorCount = (int)$xpath->query("//m:errorcount")->item(0)->nodeValue;
			$output->writeln($this->getMessage(($errorCount == 0 ? AbstractCommand::MSG_LEVEL_INFO : AbstractCommand::MSG_LEVEL_ERROR), sprintf("Found %d Errors", $errorCount)));
			$errors = $xpath->query("//m:error");
			$this->outputW3Messages($errors, $output);


			$warnCount = (int)$xpath->query("//m:warningcount")->item(0)->nodeValue;
			$warnCount -= 1;
			$output->writeln($this->getMessage(($warnCount == 0 ? AbstractCommand::MSG_LEVEL_INFO : AbstractCommand::MSG_LEVEL_WARN), sprintf("Found %d Warnings", $warnCount)));
			$warnings = $xpath->query("//m:warning");
			$this->outputW3Messages($warnings, $output);
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

			$output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Validating HTML"));

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
