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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Output\StreamOutput;
use Assetic\Asset\AssetInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\ArrayInput;
use ZipArchive;

class ExportCommand extends AbstractCommand
{
    protected $fsys = null;


    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('build:export')
            ->setDescription('Builds a release')
            ->addOption('no-validation', null, InputOption::VALUE_OPTIONAL, "no build validation")
            ->addOption('no-image-optimization', null, InputOption::VALUE_OPTIONAL, "Do not optimize images")
            ->addOption('no-js-doc', null, InputOption::VALUE_OPTIONAL, 'Do not generate javascript doc');
    }


    /**
     * @throws \Exception
     */
    protected function buildTempPath($removeIfExists = false, $path = null)
    {
        $tempPath = sys_get_temp_dir() . "/TerrificExport" . ($path != null ? "/" . $path : "");
        if ($removeIfExists && file_exists($tempPath)) {
            $this->fsys->remove($tempPath);
        }
        if (false === $this->fsys->mkdir($tempPath, 0777)) {
            throw new \Exception("Couldn't create temp path.");
        }

        return $tempPath;
    }


    /**
     *
     */
    protected function exportAssets(InputInterface $input, OutputInterface $output)
    {
        $tempPath = $this->buildTempPath();
        $assets = $this->getContainer()->get('assetic.asset_manager');

        foreach ($assets->getNames() as $name) {
            $targetPath = $this->buildAssetPath($assets->get($name)->getTargetPath(), $tempPath, true);

            if (!file_exists(dirname($targetPath)) && false === $this->fsys->mkdir(dirname($targetPath), 0777)) {
                throw new \Exception("Couldn't create temp path.");
            }

            $sourceFile = $this->buildAssetPath($assets->get($name)->getTargetPath(), $this->rootPath . "/../web");
            if (file_exists($sourceFile)) {
                $this->fsys->copy($sourceFile, $targetPath);
            }
        }


        $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Fetching js dependencies"));
        $depPath = $this->buildTempPath(false, "js/dependencies");
        $finder = new Finder();
        foreach ($finder->in(realpath($this->rootPath . "/../web/js/dependencies"))->files()->name('*.js') as $f) {
            $file = $depPath . "/" . $f->getFileName();

            if (file_exists($file)) {
                throw new \Exception(sprintf('File [%s] already found in dependencies folder.', $f->getFileName()));
            }

            $output->writeln("  " . $this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Fetching dependency:" . $f->getFileName()));
            copy($f->getPathName(), $file);
        }


        $moduleManager = $this->getContainer()->get("terrific.composer.module.manager");
        $modules = $moduleManager->getModules();

        //
        // removing composer specific assets
        //
        $finder = new Finder();
        $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Removing composer specific assets for production usage"));
        foreach ($finder->in($tempPath)->files()->name('*composer*') as $f) {
            $output->writeln("  " . $this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Removing: " . $f->getFileName()));
            $this->fsys->remove($f->getPathName());
        }

        //
        // appending module documentation
        //
        $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, 'Appending module documentation'));
        foreach ($modules as $mod) {
            $tempPath = $this->buildTempPath(false, "modules/" . $mod->getName());

            $output->writeln("  " . $this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Appending documentation for module : " . $mod->getName()));

            $finder = new Finder();
            foreach ($finder->in($this->modulePath . "/" . $mod->getName())->files()->name('*.md') as $f) {
                $this->fsys->copy($f->getPathName(), $tempPath . "/" . $f->getFileName());
            }
        }


        //
        // appending images
        //
        $imgCount = 0;
        $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, 'Appending images'));
        foreach ($modules as $mod) {
            $tempPath = $this->buildTempPath(false, "img/" . $mod->getName());

            $output->writeln("  " . $this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Appending images for module : " . $mod->getName()));

            $finder = new Finder();
            foreach ($finder->in($this->modulePath . "/" . $mod->getName() . "/Resources")->files()->name('*.png')->name('*.jpg')->name('*.gif') as $f) {
                $this->fsys->copy($f->getPathName(), $tempPath . "/" . $f->getFileName());
                $imgCount++;
            }
        }

        $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, 'Appending common images'));
        $finder = new Finder();
        $imgPath = realpath($this->rootPath . "/../web/img");
        if ($imgPath !== false) {
            foreach ($finder->in($imgPath)->files() as $f) {
                $tempPath = $this->buildTempPath(false, "img/common" . dirname(str_replace($imgPath, "", $f->getPathName())));
                $this->fsys->copy($f->getPathName(), $tempPath . "/" . $f->getFileName());
                $imgCount++;
            }
        }
        $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, sprintf('Added %d images', $imgCount)));


        //
        // Optimize Images !
        //
        if (!$input->hasParameterOption('--no-image-optimization')) {
            $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, 'Checking trimage installation for optimising Images'));
            $retval = -1;
            $ret = array();
            exec('trimage --help 2>&1', $ret, $retval);
            if ($retval == 0) {
                $imgPath = $this->buildTempPath(false, "img");

                $ret = array();
                $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, 'Starting image optimization'));
                exec(sprintf('trimage -d %s 2> /dev/null', realpath($imgPath)), $ret, $retval);

                foreach ($ret as $r) {
                    $output->writeln(" " . $this->getMessage(AbstractCommand::MSG_LEVEL_INFO, str_replace($imgPath, "", $r)));
                }
            }
        }

        //
        // build javascript api documentation
        //
        if (!$input->hasParameterOption('--no-js-doc')) {
            $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, 'Checking yuidoc installation'));
            $retval = -1;
            $ret = array();
            exec('yuidoc -v 2>&1', $ret, $retval);
            if ($retval == 1) {
                $ret = json_decode(file_get_contents($this->rootPath . "/../yuidoc.json"));
                $ret->version = sprintf("%d.%d.%d", $this->buildOptions["version.major"], $this->buildOptions["version.minor"], $this->buildOptions["version.build"]);
                file_put_contents($this->rootPath . "/../yuidoc.json", json_encode($ret));

                $output->writeln("  " . $this->getMessage(AbstractCommand::MSG_LEVEL_INFO, 'Building API Doc'));
                exec(sprintf('yuidoc -o "%s" 2>&1', $this->buildTempPath(false, "apidoc/")), $ret, $retval);
            } else {
                $output->writeln("  " . $this->getMessage(AbstractCommand::MSG_LEVEL_WARN, 'Yuidoc not found in path. Cannot build API Doc :('));
            }
        }

        //
        // Append Changelogs
        //
        if (realpath($this->buildPath . "/changelogs") !== false) {
            $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, 'Append Changelogs'));
            $finder = new Finder();
            $logPath = $this->buildTempPath(false, 'changelogs/');
            foreach ($finder->in($this->buildPath . "/changelogs")->files()->name('*.md') as $file) {
                $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, 'Appending Changelog: ' . $file->getPathName()));
                $this->fsys->copy($file->getPathName(), $logPath . "/" . $file->getFileName());
            }
        }
    }

    /**
     *
     */
    protected function exportModules(InputInterface $input, OutputInterface $output)
    {
        $moduleManager = $this->getContainer()->get("terrific.composer.module.manager");
        $modules = $moduleManager->getModules();
        $templating = $this->getContainer()->get('templating');

        $request = new Request();
        $this->getContainer()->enterScope('request');
        $this->getContainer()->set('request', $request, 'request');

        $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Building modules"));
        foreach ($modules as $mod) {
            $tempPath = $this->buildTempPath(false, "modules/" . $mod->getName());
            $m = $moduleManager->getModuleByName($mod->getName());

            foreach ($m->getTemplates() as $tpl) {
                $ret = $templating->render('TerrificComposerBundle:Module:details.ajax.html.twig', array(
                    'layout' => $moduleManager->getModuleLayout(),
                    'module' => $mod->getName(),
                    'template' => $tpl->getName(),
                    'skins' => array()
                ));

                file_put_contents($tempPath . "/" . $tpl->getName() . ".html", $ret);
            }

            $output->writeln("  " . $this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Built module " . $mod->getName()));
        }

        $this->getContainer()->leaveScope('request');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function exportLayouts(InputInterface $input, OutputInterface $output)
    {
        $exportFilter = $this->getContainer()->get('terrific.exporter.filter.html');
        $pageManager = $this->getContainer()->get("terrific.composer.page.manager");
        $http = $this->getContainer()->get("http_kernel");


        $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Building layouts"));

        $tempPath = $this->buildTempPath(false, "layouts");
        foreach ($pageManager->getPages() as $page) {
            $request = Request::create($page->getUrl());
            $resp = $http->handle($request);
            $ret = $resp->getContent();

            $ret = $exportFilter->filter($ret);

            file_put_contents($tempPath . "/" . $page->getName() . ".html", $ret);
            $output->writeln("  " . $this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Built layout " . $page->getName()));
        }
    }

    /**
     *
     */
    protected function buildExportFilename()
    {
        return sprintf("%s-%d.%d.%d.zip", $this->buildOptions["version.name"], $this->buildOptions["version.major"], $this->buildOptions["version.minor"], $this->buildOptions["version.build"]);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //
        // Always override environment and debugging options
        // no one will export dev or debugging files
        //
        $input->setOption('env', 'export');
        $input->setOption('no-debug', true);


        $cmdInput = new ArrayInput(array(
            'command' => '',
            '--env' => 'export',
            '--no-debug' => false,
            '--help' => false,
            '--quiet' => $input->getOption('quiet'),
            '--verbose' => $input->getOption('verbose'),
            '--version' => $input->getOption('version'),
            '--ansi' => $input->getOption('ansi'),
            '--no-ansi' => $input->getOption('no-ansi'),
            '--no-interaction' => $input->getOption('no-interaction'),
            '--shell' => $input->getOption('shell')
        ));


        try {
            parent::execute($input, $output);

            $this->fsys = $this->getContainer()->get("filesystem");

            if (!$input->hasParameterOption('--no-validation')) {
                $command = $this->getApplication()->find('build:validatecss');
                $returnCode = $command->run($cmdInput, $output);

                $command = $this->getApplication()->find('build:validatejs');
                $returnCode = $command->run($cmdInput, $output);

                $command = $this->getApplication()->find('build:validatehtml');
                $returnCode = $command->run($cmdInput, $output);
            }

            $command = $this->getApplication()->find('assetic:dump');
            $returnCode = $command->run($cmdInput, new \Terrific\ExporterBundle\Service\EmptyOutput());

            $tempPath = $this->buildTempPath(true);
            $this->exportAssets($input, $output);
            $this->exportModules($input, $output);
            $this->exportLayouts($input, $output);

            $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Output router rules"));
            $sOutput = new StreamOutput(fopen($tempPath . "/.htaccess", "w"));
            $command = $this->getApplication()->find('router:dump-apache');
            $returnCode = $command->run($cmdInput, $sOutput);


            // build zip
            $file = $this->rootPath . "/../build/" . $this->buildExportFilename();
            $zip = new ZipArchive();
            $zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            $finder = new Finder();
            foreach ($finder->in($tempPath)->files()->sortByName() as $f) {
                $zip->addFile($f->getPathName(), str_replace(sys_get_temp_dir(), "", $f->getPathName()));
            }

            $zip->close();
            $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Exported to file: " . realpath($file)));

            $this->buildOptions["version.build"] = ((int)$this->buildOptions["version.build"]) + 1;
        } catch (\Exception $ex) {
            $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_ERROR, $ex->getMessage()));
        }

    }
}