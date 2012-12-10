<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 10.12.12
 * Time: 13:04
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Service {
    use Symfony\Component\HttpKernel\Log\LoggerInterface;
    use Terrific\ExporterBundle\Object\ValidationResult;
    use Terrific\ExporterBundle\Object\ValidationResultItem;
    use DOMDocument;
    use DOMXPath;
    use DOMNodeList;


    /**
     *
     */
    class W3CValidator {
        /**
         * @var string
         */
        private $url = "http://validator.w3.org/check";

        /**
         * @var Resource
         */
        private $cURL = null;

        /**
         * @var LoggerInterface
         */
        private $logger;

        /**
         * @param LoggerInterface $logger
         */
        public function setLogger(LoggerInterface $logger) {
            $this->logger = $logger;
        }

        /**
         * @return LoggerInterface
         */
        public function getLogger() {
            return $this->logger;
        }


        /**
         * @param string $url
         */
        public function setUrl($url) {
            $this->url = $url;
        }

        /**
         * @return string
         */
        public function getUrl() {
            return $this->url;
        }


        /**
         * @param \DOMNodeList $nodeList
         * @return void
         */
        protected function buildResultList(DOMNodeList $nodeList, ValidationResult $results, $type = "error") {
            foreach ($nodeList as $node) {
                $desc = $node->getElementsByTagName("message")->item(0)->nodeValue;
                $line = 0;
                $col = 0;

                if ($node->getElementsByTagName("line")->length > 0) {
                    $line = $node->getElementsByTagName("line")->item(0)->nodeValue;
                    $col = $node->getElementsByTagName("col")->item(0)->nodeValue;
                    $pos = $node->getElementsByTagName("source")->item(0)->nodeValue;
                    $pos = str_replace(array('&#34;', '&#62;', '&#60;'), array('"', '>', '<'), $pos);
                }

                $item = new ValidationResultItem($desc, $line, $col);

                $item->setError(($type == "error"));
                $item->setWarning(($type == "warning"));

                $results->addResult($item);
            }
        }

        /**
         *
         */
        public function validate($file) {

            if ($this->logger) {
                $this->logger->debug("Sending file to W3C Validator: " . basename($file));
            }

            $content = file_get_contents($file);
            $postFields = array("fragment" => $content, "output" => "soap12");

            curl_setopt_array($this->cURL, array(CURLOPT_URL => $this->url, CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_POSTFIELDS => $postFields));

            $ret = curl_exec($this->cURL);

            $dom = new DOMDocument();
            $dom->loadXML($ret);

            $xpath = new DOMXpath($dom);
            $xpath->registerNamespace("m", "http://www.w3.org/2005/10/markup-validator");

            $ret = new ValidationResult();
            $errors = $xpath->query("//m:error");
            $this->buildResultList($errors, $ret, "error");

            $warnings = $xpath->query("//m:warning");
            $this->buildResultList($warnings, $ret, "warning");

            return $ret;
        }

        /**
         *
         */
        public function __destruct() {
            curl_close($this->cURL);
        }

        /**
         * Constructor
         */
        public function __construct() {
            $this->cURL = curl_init();
        }
    }
}






/*
 * 		protected function validatePageContent($content, OutputInterface $output) {

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
 */
