<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 10.12.12
 * Time: 08:06
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Helper {
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\Finder\Finder;
    use Symfony\Component\Finder\SplFileInfo;
    use Symfony\Component\Filesystem\Exception\IOException;
    use ZipArchive;


    /**
     *
     */
    abstract class FileHelper {

        /**
         * Returns true if the given filename is a javascript file.
         *
         * @param String $file
         * @return Boolean
         */
        public static function isJavascript($file) {
            return (strtolower(substr($file, -3)) == ".js");
        }

        /**
         * Returns true if the given filename is a cascading style sheet.
         *
         * @param String $file
         * @return Boolean
         */
        public static function isStylesheet($file) {
            return (strtolower(substr($file, -4)) == ".css");
        }

        /**
         * Returns true if the given path is a image.
         *
         * @param String $file
         * @return bool
         */
        public static function isImage($file) {
            $ext = strtolower(substr($file, -4));

            return ($ext == ".jpg" || $ext == ".gif" || $ext == ".png");
        }

        /**
         * Removes '//' from the given path.
         *
         * @param String $path
         * @return String
         */
        public static function cleanPath($path) {
            return str_replace("//", "/", $path);
        }

        /**
         * Removes '../' from the given path.
         *
         * @param String $path
         * @return String
         */
        public static function cleanRecursivePath($path) {
            while(substr($path, 0, 3) == '../') {
                // remove first 3 chars
                $path = substr($path, 3);
            }
            return $path;
        }

        /**
         * Creates a path recursive.
         *
         * @throws IOException
         * @param String $path
         * @return void
         */
        public static function createPathRecursive($targetPath) {
            $fs = new Filesystem();

            $split = explode("/", $targetPath);

            $t = "";
            while (!$fs->exists($targetPath)) {
                $t .= array_shift($split) . "/";

                if (!$fs->exists($t)) {
                    $fs->mkdir($t);
                }

                if (count($split) == 0) {
                    break;
                }
            }

            if (!$fs->exists($targetPath)) {
                throw new IOException("Couldn't create path [${targetPath}]");
            }
        }

        /**
         * Builds a zipArchive from the given folder and removes it if $removeFolder = true.
         * Returns the new filename.
         *
         * @param string $folder
         * @param string|null $target
         * @param bool $removeFolder
         * @return string
         */
        public static function buildZip($folder, $target = null, $removeFolder = false) {
            $fs = new Filesystem();
            $finder = new Finder();

            if (!$fs->exists($folder)) {
                throw new IOException(sprintf("Cannot find source folder [%s]", $folder));
            }

            if ($target == null) {
                $target = $folder . ".zip";
            }

            $zip = new ZipArchive();

            if ($zip->open($target, ZipArchive::CREATE) !== true) {
                throw new IOException(sprintf("Cannot create zip archive [%s]", $target));
            }

            $finder->in($folder);

            /** @var $file SplFileInfo */
            foreach ($finder->files() as $file) {
                $zip->addFile($file->getPathname(), $file->getRelativePathname());
            }

            if ($zip->close() === true) {
                if ($removeFolder === true) {
                    $fs->remove($folder);
                }
            } else {
                throw new IOException("Could not save zip file");
            }

            return $target;
        }
    }
}
