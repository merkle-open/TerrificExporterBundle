<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 24.06.12
 * Time: 08:04
 * To change this template use File | Settings | File Templates.
 */


namespace Terrific\ExporterBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Assetic\Asset\AssetInterface;
use Symfony\Component\Finder\Finder;
use DOMDocument;
use DOMXPath;

class SpriteGeneratorCommand extends AbstractCommand
{
    private $method = 'gd';

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('build:sprites')
            ->setDescription('Builds all configured sprites');
    }


    /**
     * @param $img
     * @param $targetFile
     */
    protected function addImageUsingGD($img, $targetFile)
    {
    }

    /**
     * @param $filelist
     */
    protected function generateSpriteFromFileList(array $filelist, $targetFile, $options)
    {
        var_dump($targetFile, $options);
        if ($this->method == "gd") {
            throw new \Exception("Sprite generation using GDLib is currently not supported.");

            foreach ($filelist as $file) {
                $this->addImageUsingGD($file, $targetFile);
            }
        } else if ($this->method == "imagick") {
            $retval = -1;
            $ret = array();
            $dim = "";

            switch ($options["type"]) {
                case "vertical":
                    $dim = sprintf("x%d", count($filelist));
                    break;

                case "horizontal":
                    $dim = sprintf("%dx", count($filelist));
                    break;

                case "clustered":
                    $dim = sprintf('%1$dx%1$d', ceil(count($filelist) / 2));
                    break;
            }

            exec($e = sprintf('montage -mode Concatenate %s -tile %s -geometry %dx%d+0+0 -bordercolor none -background none %s',
                implode(" ", $filelist),
                $dim,
                $options["width"],
                $options["height"],
                $targetFile
            ), $ret, $retval);
        }
    }


    /**
     * @param $directory
     */
    protected function generateSpriteFromDirectory($directory, $targetFile, $options)
    {
        $dir = realpath($this->getContainer()->getParameter('kernel.root_dir') . "/../" . $directory);

        if (!$dir) {
            throw new \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(sprintf("The '%s' directory does not exists.", $this->getContainer()->getParameter('kernel.root_dir') . "/../" . $directory));
        }

        $f = new Finder();
        $f->in($directory)->files()->sortByName();

        $fileList = array();
        foreach ($f as $file) {
            $fileList[] = $file->getPathName();
        }

        return $this->generateSpriteFromFileList($fileList, $targetFile, $options);
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        if ($this->checkCommand('montage -version', 0)) {
            $this->method = "imagick";
        } else {
            throw new \Exception("No valid sprite generation method supported. Missing 'Imagemagick'");
        }

        $spriteList = $this->getContainer()->getParameter('terrific_exporter.sprites');
        foreach ($spriteList as $sp) {
            if ($sp["directory"] != "") {
                $this->generateSpriteFromDirectory($sp["directory"], $sp["target"], array(
                    "width" => $sp["item"]["width"],
                    "height" => $sp["item"]["height"],
                    "type" => $sp["type"]
                ));
            } else if (count($sp["files"]) > 0) {
                var_dump($sp["files"]);
            }
        }
    }
}
