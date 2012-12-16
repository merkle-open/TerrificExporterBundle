<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 10.12.12
 * Time: 10:01
 * To change this template use File | Settings | File Templates.
 */

namespace Terrific\ExporterBundle\Helper {
    use Assetic\Asset\AssetInterface;
    use Assetic\Filter\FilterInterface;

    /**
     *
     */
    abstract class AsseticHelper {

        /**
         * Removes minification filter for validation.
         *
         * @param \Assetic\Asset\AssetInterface $origAsset
         * @return AssetInterface
         */
        public static function removeMinFilters(AssetInterface $origAsset) {
            $asset = clone $origAsset;

            $filters = $asset->getFilters();
            $asset->clearFilters();

            $removeFilter = array('Assetic\Filter\CssMinFilter', 'Assetic\Filter\JsMinFilter');

            /** @var $filter FilterInterface */
            foreach ($filters as $filter) {
                $removed = false;
                foreach ($removeFilter as $rFilter) {
                    if ($filter instanceof $rFilter) {
                        $removed = true;
                        break;
                    }
                }
                if (!$removed) {
                    $asset->ensureFilter($filter);
                }
            }

            return $asset;
        }

        /**
         *
         *
         * @param String $content
         * @return array
         */
        public static function retrieveImages($content) {
            $css = new \CssMin();
            $tokenList = $css->parse($content);

            $images = array();

            foreach ($tokenList as $token) {
                if (isset($token->Property)) {
                    switch ($token->Property) {
                        case "background-image":
                            $matches = array();

                            if (preg_match('/url\([\'"]([^\'"]+)/', $token->Value, $matches)) {
                                $images[] = $matches[1];
                            }

                            break;
                    }
                }
            }

            return array_unique($images);
        }


    }
}
