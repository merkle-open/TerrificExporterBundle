<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 10.12.12
 * Time: 07:59
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Helper {
    use Terrific\ExporterBundle\Object\ValidationResult;
    use Terrific\ExporterBundle\Object\ValidationResultItem;

    /**
     *
     */
    abstract class XmlLintHelper {

        /**
         * Returns all issues detected by JSHint.
         *
         * @param $data String
         * @return ValidationResult
         */
        public static function parseXML($data) {
            $ret = new ValidationResult();

            try {
                $xml = new \DOMDocument();
                $xml->loadXML($data);


                $xpath = new \DOMXPath($xml);
                foreach ($xpath->query('//issue') as $issue) {
                    $item = new ValidationResultItem($issue->getAttribute("reason"));
                    $item->setChar($issue->getAttribute("char"));
                    $item->setLine($issue->getAttribute("line"));

                    $ret->addResult($item);
                }
            } catch (\Exception $ex) {
                return $ex;
            }

            return $ret;
        }
    }
}
