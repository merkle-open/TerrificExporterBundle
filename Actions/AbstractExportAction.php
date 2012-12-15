<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 15.12.12
 * Time: 11:47
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Terrific\ExporterBundle\Helper\FileHelper;
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\Filesystem\Exception\IOException;

    /**
     *
     */
    abstract class AbstractExportAction extends AbstractAction {

        /**
         * @param $tmpFile String
         * @param $targetFile String
         */
        protected function saveToPath($tmpFile, $targetFile) {
            /** @var $fs Filesystem */
            $fs = $this->container->get("filesystem");

            $targetPath = dirname($targetFile);

            try {
                FileHelper::createPathRecursive(dirname($targetFile));

                if ($fs->exists($targetFile)) {
                    if (filesize($targetFile) == filesize($tmpFile)) {
                        if (md5_file($targetFile) != md5_file($tmpFile)) {
                            throw new IOException("Already found a file with same filesize but different content !");
                        }
                    } else {
                        throw new IOException("Already found a file with different filesize !");
                    }
                } else {
                    $fs->copy($tmpFile, $targetFile);
                }

                return true;
            } catch (IOException $ex) {
                $this->logger->err($ex->getMessage());
                $this->logger->err($ex->getTraceAsString());
            }

            return false;
        }
    }
}
