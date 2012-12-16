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

        public function validateFile($file) {

            if ($this->logger) {
                $this->logger->debug("Sending file to W3C Validator: " . basename($file));
            }

            $file = realpath($file);
            $postFields = array("uploaded_file" => "@/" . $file . ";type=text/html");

            return $this->sendValidationRequest($postFields);
        }

        /**
         *
         */
        public function validate($content) {
            $postFields = array("fragment" => $content);

            return $this->sendValidationRequest($postFields);
        }

        /**
         * @param $postFields
         */
        protected function sendValidationRequest($postFields = array(), $retry = false) {
            // if its default w3c validator wait a seconds for each request
            if ($this->url == "http://validator.w3.org/check") {
                sleep(1);
            }

            $postFields["output"] = "soap12";

            curl_setopt_array($this->cURL, array(CURLOPT_URL => $this->url, CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_POSTFIELDS => $postFields));

            if (isset($postFields["uploaded_file"])) {
                curl_setopt_array($this->cURL, array(CURLOPT_HTTPHEADER => array("Content-type: multipart/form-data")));
            }

            $ret = curl_exec($this->cURL);

            try {
                $dom = new DOMDocument();
                $dom->loadXML($ret);

                $xpath = new DOMXpath($dom);
                $xpath->registerNamespace("m", "http://www.w3.org/2005/10/markup-validator");

                $ret = new ValidationResult();
                $errors = $xpath->query("//m:error");
                $this->buildResultList($errors, $ret, "error");

                $warnings = $xpath->query("//m:warning");
                $this->buildResultList($warnings, $ret, "warning");
            } catch (\Exception $ex) {
                // try one more
                if (!$retry) {
                    return $this->sendValidationRequest($postFields, true);
                }

                if ($this->logger) {
                    $this->logger->err($ex->getMessage());
                    $this->logger->err($ex->getTraceAsString());
                }
            }
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

