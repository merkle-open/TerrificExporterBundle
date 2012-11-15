<?php
/**
 * User: blorenz
 * Date: 05.11.12
 * Time: 08:05
 * To change this template use File | Settings | File Templates.
 */


namespace Terrific\ExporterBundle\Factory {
    use Symfony\Bundle\AsseticBundle\Factory\AssetFactory;
    use Symfony\Bundle\SecurityBundle\Tests\Functional\AppKernel;

    /**
     * Extends default LazyAssetManager to ignore default TerrificAssets.
     *
     */
    class LazyAssetManager extends \Assetic\Factory\LazyAssetManager
    {

        /**
         * Filters the asset list against the given configuration options.
         *
         * @param $formula Assetic Formular
         * @return boolean
         */
        protected function filterNames($formula)
        {
            $ret = false;
            $exportList = $this->service->getContainer()->getParameter("terrific_exporter.assetic_export_list");

            if (count($exportList) == 0) {
                $ret = true;
            } else {
                foreach ($exportList as $e) {
                    if (isset($formula[2])) {
                        $data = $formula["2"];

                        if (strpos($data["output"], $e)) {
                            $ret = true;
                        }
                    }
                }
            }

            return $ret;
        }

        /**
         * @return array
         */
        public function getNames()
        {
            $ret = parent::getNames();

            $nRet = array();
            foreach ($ret as $asset) {
                $formula = $this->getFormula($asset);

                if ($this->filterNames($formula)) {
                    $nRet[] = $asset;
                }
            }
            return $nRet;
        }


    }
}
