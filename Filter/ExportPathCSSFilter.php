<?php

/*
 * This file is part of the Terrific Core Bundle.
 *
 * (c) Remo Brunschwiler <remo@terrifically.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Terrific\ExporterBundle\Filter {
    use Assetic\Filter\BaseCssFilter;
    use Assetic\Asset\AssetInterface;
    use Assetic\Filter\FilterInterface;

    /**
     * Fixes CSS urls for Terrific Resources.
     *
     * @author Remo Brunschwiler <remo@terrifically.org>
     */
    class ExportPathCSSFilter extends BaseCssFilter
    {
        /**
         * @param \Assetic\Asset\AssetInterface $asset
         */
        public function filterLoad(AssetInterface $asset)
        {
        }

        /**
         * @param $path
         */
        protected function buildExportPath($path, $fullPath = null)
        {
            $basePath = "../img";

            switch (true) {
                case (strpos($path, "/terrificmodule") !== false):

                    $matches = array();
                    if (preg_match('/\/Terrific\/Module\/([^\/]+)\//', $fullPath, $matches) !== false) {
                        return $basePath . "/" . $matches[1] . "/" . basename($path);
                    }

                    return $path;
                    break;

                case (strpos($path, "img/")):
                    return $basePath . "/common/" . substr($path, 5);
                    break;

                default:
                    return "X" . $path . "X";
                    break;
            }
        }

        /**
         * @param \Assetic\Asset\AssetInterface $asset
         */
        public function filterDump(AssetInterface $asset)
        {

            $sourcePath = $asset->getSourcePath();
            $targetPath = $asset->getTargetPath();

            if (null === $sourcePath || null === $targetPath || $sourcePath == $targetPath) {
                return;
            }

            $matches = array();
            if (preg_match_all('/\(.?([^\)\'"]*)/i', $asset->getContent(), $matches) !== false) {
                $matches = array_unique($matches[1]);

                $content = $asset->getContent();

                foreach ($matches as $path) {
                    $isFile = false;

                    $pInfo = pathinfo($path);
                    switch ($pInfo["extension"]) {
                        case "png":
                        case "gif":
                        case "jpg":
                            $isFile = true;
                            break;
                    }

                    if (!$isFile) {
                        continue;
                    }

                    try {
                        $f = new \Symfony\Component\Finder\Finder();
                        $f = $f->files()->in(realpath(__DIR__ . "/../../../../../web/"))->in(realpath(__DIR__ . "/../../../../../src/Terrific/Module"))->depth("< 99");
                        $r = array_values(iterator_to_array($f->name(basename($path))));

                        if (isset($r[0]) && $r[0] != null) {
                            $ePath = $this->buildExportPath($path, $r[0]->getPathName());
                            $content = str_replace($path, $ePath, $content);
                        } else {
                        }
                    } catch (\Exception $ex) {
                    }
                }
            }

            $asset->setContent($content);
        }


    }
}
