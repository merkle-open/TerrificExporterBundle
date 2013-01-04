<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 03.01.13
 * Time: 23:15
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Helper {
    use Terrific\ExporterBundle\Helper\ProcessHelper;

    /**
     *
     */
    class GitHelper {

        /** @var String */
        protected $repoDirectory;


        /**
         *
         */
        public function retrieveLatestVersion() {
            $process = ProcessHelper::startCommand("git", array("for-each-ref", '--sort=taggerdate', '--format="%(refname:short)"', "refs/tags"), $this->repoDirectory);

            if ($process->isSuccessful()) {
                $versions = explode(PHP_EOL, $process->getOutput());


                if (count($versions) > 1) {
                    return trim($versions[count($versions) - 2], '"');
                }

                return null;
            } else {
                throw new ExporterException("Could not retrieve git tags");
            }

            return null;
        }

        /**
         * @param $fromVersion
         * @param string $toVersion
         */
        public function retrieveChangedFiles($fromVersion, $toVersion = "HEAD") {
            $process = ProcessHelper::startCommand("git", array("diff", $fromVersion, $toVersion, "--name-only"), $this->repoDirectory);

            $fileList = array();
            if ($process->isSuccessful()) {
                $fileList = explode(PHP_EOL, $process->getOutput());
            }

            return $fileList;
        }

        /**
         * @param $file
         */
        public function retrieveDiff($file, $fromVersion, $toVersion = "HEAD") {
            $process = ProcessHelper::startCommand("git", array("diff", $fromVersion, $toVersion, $file), $this->repoDirectory);


            return $process->getOutput();
        }


        public function __construct($cwd) {
            $this->repoDirectory = $cwd;
        }

    }
}
