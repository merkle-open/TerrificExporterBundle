**Table of Contents**  *generated with [DocToc](http://doctoc.herokuapp.com/)*

- [Terrific Exporter Bundle for the Terrific Composer - Documentation](#terrific-exporter-bundle-for-the-terrific-composer---documentation)
- [Changelog](#changelog)
- [Installation](#installation)
	- [Requirements](#requirements)
	- [Dependency management](#dependency-management)
	- [Tooling](#tooling)
		- [Node.js with YUIDoc, JSHint and CSSLint](#nodejs-with-yuidoc-jshint-and-csslint)
		- [jpegoptim, optipng, advpng, montage](#jpegoptim-optipng-advpng-montage)
		- [diff](#diff)
- [Configuration](#configuration)
	- [1. Setup an export environment](#1-setup-an-export-environment)
	- [2. Register TerrificExporterBundle in `AppKernel.php`](#2-register-terrificexporterbundle-in-appkernelphp)
	- [3. Configuration Settings](#3-configuration-settings)
	- [Example configuration](#example-configuration)
		- [YUIDoc](#yuidoc)
		- [CSS Lint](#css-lint)
		- [JSHint](#jshint)
		- [build.ini](#buildini)
	- [Usage](#usage)
		- [Export](#export)
		- [Export command line options](#export-command-line-options)
		- [Annotations](#annotations)
			- [Export](#export)
		- [LocaleExport](#localeexport)
	- [Debugging and Logging](#debugging-and-logging)
	- [Actions](#actions)
		- [`ClearAction`](#clearaction)
		- [`BuildJSDoc`](#buildjsdoc)
		- [`ValidateJS`](#validatejs)
		- [`ValidateCSS`](#validatecss)
		- [`ValidateModules`](#validatemodules)
		- [`ValidateViews`](#validateviews)
		- [`GenerateSprites`](#generatesprites)
		- [`ExportImages`](#exportimages)
		- [`ExportAssets`](#exportassets)
		- [`OptimizeImages`](#optimizeimages)
		- [`ExportModules`](#exportmodules)
		- [`ExportViews`](#exportviews)
		- [`ExportChangelogs`](#exportchangelogs)
		- [`ExportDiffs`](#exportdiffs)
	- [Extending](#extending)
		- [Components and Services](#components-and-services)
			- [`BuildOptions`](#buildoptions)
			- [`ConfigFinder`](#configfinder)
			- [`Log`](#log)
			- [`PathResolver`](#pathresolver)
			- [`TempFileManager`](#tempfilemanager)
			- [`TimerService`](#timerservice)
			- [`W3CValidator`](#w3cvalidator)
			- [`PageManager`](#pagemanager)
		- [Helpers](#helpers)
			- [`AsseticHelper`](#assetichelper)
			- [`FileHelper`](#filehelper)
			- [`NumberHelper`](#numberhelper)
			- [`OSHelper`](#oshelper)
			- [`ProcessHelper`](#processhelper)
			- [`StringHelper`](#stringhelper)
			- [`XmlLintHelper`](#xmllinthelper)
			- [`GitHelper`](#githelper)
		- [Build own actions](#build-own-actions)
		- [Add new file type extensions to the Exporter](#add-new-file-type-extensions-to-the-exporter)
	- [Known Problems](#known-problems)
- [FAQ](#faq)
- [Some Notes](#some-notes)
- [Todo](#todo)
- [Authors](#authors)

# Terrific Exporter Bundle for the Terrific Composer - Documentation #

# Changelog #

* v2.0.8
    * Added possibility to export nested modules.
    * Added new MIME types to export (e.g. `WEB FLV SWF MP4 OGG ...`)
    * Documented code.
    * Added new paths to check for assets.
    * Fixed a bug with base64 encoded images in CSS files.
    * Ported documentation from `doc/build/` LaTeX files to this Markdown `README.md` and extended with additional information. Please keep in mind, that `doc/Documantation.pdf` is out of date.
* v2.0.7

# Installation #

## Requirements ##

During some functionality is only available since Symfony 2.1 it is **necessary to have your project on a Symfony 2.1 basis**. Some actions require additional tools to do their work. **As long as this actions are part of the actionstack they will force you to have this tools installed and callable!** If you don't need this action and don't want to install the tools needed for it you have to configure a action stack without this specific action. See chapter Configuration for more information about defining you own action stack.

## Dependency management ##

Update your `composer.json` and add a new repository.

    "repositories": [
        {
            "type": "vcs",
            "url": "http://github.com/brunschgi/TerrificComposerBundle"
        },
        ...
    ]

After that add a new requirement to your project.

    "require": {
        ...
        "namics/terrific-exporter-bundle" : "2.*",
        ...
    }

Now it is time to update your project vendors using the PHP Composer: `php composer.phar update`. This should now install alle necessary requirements for your project.



## Tooling ##

There are a number of tools needed depending on your configuration and tasks.

* Node.js
* YUIDoc (based on NodeJS)
* CSSLint
* JSHint
* jpegoptim
* optipng
* advpng
* montage
* diff

It is necessary to have all the tools within path, the exporter won't search for tools on your hardrive. So you have to setup your path variable depending on your os system correctly to have all tools within path's.

* Windows: <http://www.computerhope.com/issues/ch000549.htm>
* nix/MacOSX: <http://www.troubleshooters.com/linux/prepostpath.htm>

To do a permanent change it is necessary to change `~/.bashrc` or `~/.bash_profile` depending on your OS.

### Node.js with YUIDoc, JSHint and CSSLint ###

YUIDoc, JSHint and CSSLint are installed using [Node.js](http://nodejs.org/). Just go to [nodejs.org](http://nodejs.org/) download the package fits for you operating system and install it. After the installation is done open up a new commandline and install Node.js and the Node.js Pakage Manager.

* Unix/Debian:
    1. Install Node.js (on most Debian systems): `$ sudo apt-get install nodejs` or `$ sudo apt-get install node-legacy` (if `nodejs` don't work).
    2. Additionally you may need the `php5-curl` package.
    3. Install Node.js Pakage Manager (NPM): `$ sudo apt-get install npm`.
    4. Install YUIDoc, JSHint and CSSLint via NPM: `$ sudo npm -g install yuidocjs jshint csslint`.
* MacOSX with [Homebrew](http://www.asconix.com/howtos/mac-os-x/homebrew-mac-os-x-howto):
    1. Visit [Node.js auf Mac OS X 10.7 "Lion" Howto ](http://www.asconix.com/howtos/mac-os-x/node-js-mac-os-howto) for further information on how to install Node.js on Mac OSX systems.
* MacOSX with [Ports](http://www.macports.org/install.php):
    1. Install [Mac Ports](http://www.macports.org/install.php)
    2. `$ sudo port selfupdate`
    3. Install Node.js: `$ sudo port install nodejs`
    4. Install NPM: `$ sudo port install npm`
    5. Install YUIDoc, JSHint and CSSLint via NPM: `$ sudo npm -g install yuidocjs jshint csslint`

For further help and syntax for YUIDoc visit <http://yui.github.com/yuidoc/>.

### jpegoptim, optipng, advpng, montage ###

**Windows:**

* `jpegoptim` is currently not available on Windows systems.
* `optipng` can be retrieved from <http://optipng.sourceforge.net/>. Just download the Windows package and unzip it - no installation required.
* `advpng` or `advancecomp` can be fetched from <http://advancemame.sourceforge.net/comp-download.html>. The same just-  download and unzip it.
* `montage` is part of the ImageMagick toolset. To install ImageMagick visit: <http://www.imagemagick.org/script/binary-releases.php>.

**Unix/Linux:**

On Ubuntu/Debian based linux systems it is possible to install all the tools directly using your package manager: `$ sudo apt-get install jpegoptim advancecomp optipng imagemagick`.


The rest of the tools can be installed using `yum`. Download the current version from <http://www.kokkonen.net/tjko/projects.html> and install it with `$ sudo yum install advancecomp optipng ImageMagick`.

**RHEL/Fedora/Centos Linux**

On RHEL/Fedora/Centos Linux you have to install `jpegoptim` from source.you have to install `jpegoptim` from source:

    $ tar zxf jpegoptim-1.2.4.tar.gz
    $ cd jpegoptim-1.2.4
    $ ./configure /&/& make /&/& make install

**MacOSX:**

On MacOSX the easiest way to get the whole toolset is to install [ImageOptim](http://imageoptim.com/). This application contains all necessary image optimizing tools needed by the exporter. You can find the binarys of advpng and optipng in the application package (/Applications/ImageOptim.app/Contents/MacOS/).
You can just symlink the binarys to your /bin folder:
    $ ln -s /Applications/ImageOptim.app/Contents/MacOS/advpng /bin/advpng
    $ ln -s /Applications/ImageOptim.app/Contents/MacOS/optipng /bin/optipng


`montage` is part of the ImageMagick toolset. Visit [ImageMagick](http://www.imagemagick.org/script/binary-releases.php) to install it.
You can also choose to install ImageMagick with an MaxOSX Installer from http://cactuslab.com/imagemagick/ . Use the installer without XQuartz.

### diff ###

**Windows:**

On windows there are a number of tools doing the same job as `diff` on *nixes. You can install a commandline version from `diff` with [Cygwin](http://www.cygwin.com/)}.

**Unix/Linux:**

Normally `diff` should be installed on all *nixes. If not just install it using your package manager.

* Debian/Ubuntu: `$ sudo apt-get install diff`
* RHEL/CentOS/Fedora: `$ sudo yum install diff`

**MacOSX:**

On MacOSX `diff` is already installed.


# Configuration #

## 1. Setup an export environment ##

It is necessary to setup a new environment for you export. To setup _one or many_ export environment(s) just copy your `app/config.yml` to `app/config_export.yml`. Now you created a new environment called "export". The Exporter configuration starts at block `terrific_exporter:`.

Now you can configure the environment to your project needs. Visit [Symfony2 - How to Master and Create new Environments](http://symfony.com/doc/current/cookbook/configuration/environments.html) for further information.



## 2. Register TerrificExporterBundle in `AppKernel.php` ##

Edit `app/AppKernel.php` and add a new entry to the `$bundles` array:

    public function registerBundles() {
        $bundles = array(
            // ...
            new Terrific\ExporterBundle\TerrificExporterBundle(),
            // ...
        );
    }

## 3. Configuration Settings ##

All configuration settings are going beyond a `terrific_exporter` node within your (export) environment configuration file.

1. `build_local_paths: true/false`
    * If `build_local_paths` is enabled the Exporter will change all urls within HTML and CSS files to match within the exported package.
    * Actions: `Actions/ExportAssets`, `ExportViews`

2. `build_js_doc: true/false`
    * Enables the export of a javascript documentation. The documentation is generated using YUIDoc.
    * Actions: `Actions/BuildJSDoc`

3. `build_settings: <path>`
    * This setting should target to a `build.ini` file. Within this file there are only settings for the projectname an versioning data.

4. `build_path: <path>`
    * Has to target to a path which is the export target path.
    * TODO Link to build.ini section

5. `export_with_version: true/false`
    * Set to `true` if the exporter should build zips/folders with version numbers within its name.
    * TODO Link to build.ini section

6. `autoincrement_build`: true/false
    * Set `true` if the Exporter should increase the revision after each build.
    * TODO Link to build.ini section

7. `validate_js: true/false`
    * Activates the validation of JavaScript. Validation is done using JSHint.
    * Actions: `Actions/ValidateJS`

8. `validate_css: true/false`
    * Activate the validation of CSS. Validation is done using CSSLint.
    * Actions: `Actions/ValidateCSS`

9. `optimize_image: true/false`
    * Set to `true` to optimize images in the output directory. Optimization is done using `jpegoptim`, `optipng` and `advpng`.
    * Actionns: `Actions/OptimizeImages`

10. `export_views: true/false`
    * Activates the export of the views marked with a `@Export` annotation.
    * Actions: `Actions/ExportViews`

11. `export_modules: true/false`
    * Activates the export of plain module HTML. The url within this modules are not rewriten even if the `build_local_paths` option is activated.
    * Actions: `Actions/ExportModules`

12. `export_type: <string> "folder" / "zip"`
    * Set the export type if the export should be done as "folder" or as a "zip".

13. `build_actions: <list of objects>`
    * These option allows to setup a build chain. Here you can append custom project related exporting tasks or change the building order.
        * `Terrific\ExporterBundle\Actions\ClearAction`
        * `Terrific\ExporterBundle\Actions\BuildJSDoc`
        * `Terrific\ExporterBundle\Actions\ValidateJS`
        * `Terrific\ExporterBundle\Actions\ValidateCSS`
        * `Terrific\ExporterBundle\Actions\ValidateModules`
        * `Terrific\ExporterBundle\Actions\ValidateViews`
        * `Terrific\ExporterBundle\Actions\GenerateSprites`
        * `Terrific\ExporterBundle\Actions\ExportImages`
        * `Terrific\ExporterBundle\Actions\ExportAssets`
        * `Terrific\ExporterBundle\Actions\OptimizeImages`
        * `Terrific\ExporterBundle\Actions\ExportModules`
        * `Terrific\ExporterBundle\Actions\ExportViews`
        * `Terrific\ExporterBundle\Actions\ExportChangelogs`
    * TODO Link to Actions and Extending for further iniformation.

14. `pathtemplates: <set of string values>`
    * The `pathtemplates` option allows you to customize the paths within your export package. All paths begin with a starting `/`. Each given directory will begin relative to the given `export_path`. So a values like `/img/common` will end up in `/exportpath/img/common`. The optional `\%module\%` variable within will be resolved by the `PathResolver` into a module name. This variable only get matched in `module\_*` options.
    * It is possible to set paths for the following type of files:

        * Global scope:
            * `image: (default: ’/img/common’)`
            * `font: (default: ’/fonts’)`
            * `css: (default: ’/css’)`
            * `js: (default: ’/js’)`
            * `json: (default: ’/json’)`
            * `view: (default: ’/views’)`
            * `changelog: (default: ’/changelogs’)`
            * `diff: (default: ’/changelogs/diff’)`
            * `flash: (default: '/flash')`
            * `silverlight: (default: '/silverlight')`
            * `icon: (default: '/)`
            * `video: (default: '/media/video')`
            * `audio: (default: '/media/audio')`

        * Module scope:
            * `module_image: (default: ’/img/%module%’)`
            * `module_font: (default: ’/fonts/%module%’)`
            * `module_css: (default: ’/css/%module%’)`
            * `module_js: (default: ’/js/%module%’)`
            * `module_json: (default: ’/json/%module%’)`
            * `module_view: (default: ’/views/%module%’)`
            * `module_flash: (default: '/flash/%module%')`
            * `module_silverlight: (default: '/silverlight/%module%')`
            * `module_video: (default: '/media/video/%module%')`
            * `module_audio: (default: '/media/audio/%module%')`

    * Actions: `Actions/ExportModules`, `Actions/ExportAssets`, `Actions/ExportViews`

14. `sprites: >list of objects>`
    * Here you can setup CSS sprite image information. The exporter will build the sprites with the given data.
    * Actions: `Actions/GenerateSprites`

15. `changelog_path: <directory>`
    * Set this value to a valid path which contains the changelogs for your project. If this folder doesn't exist no changelogs are appended. The value is also relative from the projectfolder like `build_path` or `build_settings`.
    * Default value:  `build/changelogs`

## Example configuration ##

If you use `%%module%%` placeholder be aware of using two `%`.

    # /app/config/config_export.yml

    ...

    # Terrific Exporter Configuration
    terrific_exporter:
        build_local_paths:      false
        build_js_doc:           false
        build_settings:         "build/build.ini"
        build_path:             "build"
        validate_js:            false
        validate_css:           false
        validate_html:          false
        optimize_images:        false
        export_views:           true
        export_modules:         true
        # ZIP file or folder?
        export_type:            "zip"
        # ZIP file with version numbers?
        export_with_version:    false
        # Auto increment version build numbers for ZIP file or set it manually in build.ini?
        autoincrement_build:    false
        build_actions:
            # 1. Clear old files and directories
            - Terrific\ExporterBundle\Actions\ClearAction
            # 2. Run custom exports first
            # - Custom\ExporterBundle\Actions\CopyAssets
            # 3. Run built-in exports (if you need them)
            - Terrific\ExporterBundle\Actions\BuildJSDoc
            - Terrific\ExporterBundle\Actions\ValidateJS
            - Terrific\ExporterBundle\Actions\ValidateCSS
            - Terrific\ExporterBundle\Actions\ValidateModules
            - Terrific\ExporterBundle\Actions\ValidateViews
            - Terrific\ExporterBundle\Actions\GenerateSprites
            - Terrific\ExporterBundle\Actions\ExportImages
            # CSS Files and Paths
            - Terrific\ExporterBundle\Actions\ExportAssets
            - Terrific\ExporterBundle\Actions\OptimizeImages
            - Terrific\ExporterBundle\Actions\ExportModules
            - Terrific\ExporterBundle\Actions\ExportViews
            - Terrific\ExporterBundle\Actions\ExportChangelogs
        pathtemplates:
            image:                  "/img"
            font:                   "/fonts"
            css:                    "/css"
            js:                     "/js"
            view:                   "/html"
            flash:                  "/flash"
            silverlight:            "/silverlight"
            icon:                   "/"
            video:                  "/media/video"
            audio:                  "/media/audio"
            changelog:              "/changelogs"
            diff:                   "/changelogs/diff"
            module_image:           "/img/%%module%%"
            module_font:            "/fonts"
            module_css:             "/css/%%module%%"
            module_js:              "/js/%%module%%"
            module_view:            "/html/%%module%%"
            module_flash:           "/flash/%%module%%"
            module_silverlight:     "/silverlight/%%module%%"
            module_video:           "/media/video/%%module%%"
            module_audio:           "/media/audio/%%module%%"

        sprites:
            - {
                directory: "PROD/internet_sprite_icons",
                target: "web/img/sprite_icons.png", item: { height: 50, width: 100 }
            }


### YUIDoc ###

The exporter will use the `yuidoc` configuration file named `yuidoc.json` within the `app/config` directory. The syntax of the file could be read on the [YUIDoc page](http://yui.github.com/yuidoc/args/index.html).


### CSS Lint ###

* [CSS Lint](http://csslint.net/) will use a configuration if one is found under `app/config`. If there is no configuration named `csslint.cfg` the exporter will use its default configuration found within `Terrific/ExporterBundle/Resources/config/csslint.cfg`.
* If you want to specify a different `csslint.cfg` you should take a copy from the default and change the settings within.
* To show a list of available options just enter the following command in your console: `csslint --list-rules`.


### JSHint ###

* JSHint will also use a configuration file named `jshint.json`, normally found within `app/config`.
* If no configuration file is found there the default in `ExporterBundle/Resources/config/jshint.json` is used.
* See [JSHint - How does it work?](http://www.jshint.com/docs/) for configuration settings.


### build.ini ###

`build.ini` file:

    [version]
    name=Terrific
    major=0
    minor=0
    build=0

* Normally the default `build.ini will` look like this. If you specify a `build.ini` with option `build_settings` that is not available, the exporter will create it from the default.
* `name` describes the output folder or zip file (e.g. Terrific, frontend) name.
* Depending on option `autoincrement_build` the build number is incremented with each export.
* Don't add the zip file extension to that name - this will be done later within the export process - depending on your options.


## Usage ##

* Remember to clear the cache (`$ rm -rf app/cache/*`) after you've changed the `config_export.yml` file(s).

### Export ###
To startup an export use the following command(s):
* `$ php app/console build:export --env=export --no-debug`
* `$ php app/console build:export --env=export --no-debug --no-image-optimization --no-js-doc --no-validation`

### Export command line options ###

* `--no-image-optimization`
    * Overrides configuration setting `optimize_images` and starts the chain without optimizing images.
    * Actions skipped: `Terrific/ExporterBundle/Actions/OptimizeImages`

* `--no-js-doc`
    * Do not build a JavaScript documentation (YUIDoc) for this export run.
    * Actions skipped: `Terrific/ExporterBundle/Actions/BuildJSDoc`

* `--no-validation`
    * Ignores validation configuration and skip the following actions:
        * `Terrific/ExporterBundle/Actions/ValidateJS`
        * `Terrific/ExporterBundle/Actions/ValidateCSS`
        * `Terrific/ExporterBundle/Actions/ValidateModules`
        * `Terrific/ExporterBundle/Actions/ValidateViews`

* `--last-export=[directory]`
    * To generate diffs between the last export and the current one, it is needed to give additional information to build this files. This parameter has to be set to the folder of the last export.
    * If you don't add this information on your console call the following action is skipped: `Terrific/ExporterBundle/Actions/ExportDiffs`.

### Annotations ###

#### Export ####

To export a view it is necessary to annotate a controller method with this `@Export` annotation.

    // Terrific/Composition/Controller/DefaultController.php

    namespace Terrific\Composition\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Terrific\ComposerBundle\Annotation\Composer;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

    // 1. Add this!
    use Terrific\ExporterBundle\Annotation\Export;

    class DefaultController extends Controller
    {
        // ...

        // 2. Add @Export annotation in comment block

        /**
         * @Composer("Elements")
         * @Route("/elements", name="elements")
         * @Template()
         * @Export(name="elements.html")
         */
        public function elementsAction()
        {
            return array();
        }

        // ...
    }

It is possible to control a set of options for each view directly within this annotation. This example is only valid if you don't have to export a localized version of a view. If no environment is given this view will exported in all environments.


    @Export(name="viewname.html", environment="env1,env2,...")


### LocaleExport ###

Each view must have their own for each locale which should exported. To set different settings for each locale you have to use the `@LocaleExport` annotation. This annotation is used in combination with the `@Export` annotation. Setting up locale exportation will disable exporting the default language which means you have to annotate **all** locales that should exported. If no environment is given this view will exported in all environments.

Usage with @Export:

    @Export(
        @LocaleExport(name="viewname_de.html", locale="de"),
        @LocaleExport(name="viewname_en.html", locale="en", environment="env1"),
        @LocaleExport(name="viewname_lv.html", locale="lv", environment="env2"),
        ....
    )


## Debugging and Logging ##

1. Check if there is a `logs` folder in `app/`. If not available, `$ mkdir logs` one.
2. Add the following snippet to your export environment configuration, e.g. `/app/config/config_export.yml`.


* This snippet will activate logging to a file called like your environment beyond the `app/logs` directory.
* The loglevel debug will output a massive amount of logging from the exporter.
* Currently only the log file contains a list of validation errors for your CSS/JS files.
* For more information about how to configure your monolog see [Symfony 2 - How to use Monolog to write Logs](http://symfony.com/doc/current/cookbook/logging/monolog.html).

    ...

    services:
        terrific.formatter.line:
            class: Monolog\Formatter\LineFormatter
            arguments:
                - "[%%datetime%%] [%%level_name%%]: %%message%%\n"

    monolog:
        handlers:
            file:
                type: stream
                path: %kernel.logs_dir%/%kernel.environment%.log
                level: debug
                formatter: terrific.formatter.line

    terrific_exporter:
        ...

## Actions ##

All builtin actions are documented within these chapter. This maybe have some helpful information when you have to build your own action chain.


### `ClearAction` ###

* `Terrific/ExporterBundle/Actions/ClearAction` simply just clears all data from the target directory.
* If configuration option `export_with_version` is activated this action does simply nothing cause normally the export folder should not exists during startup.


### `BuildJSDoc` ###

* `Terrific/ExporterBundle/Actions/BuildJSDoc` will first test if `yuidoc` is callable. After that test it looks for a suitable `yuidoc.json` file, if a file has been found the action starts the `yuidoc` command with the found configuration.
* An example call could look like this and is going to be called withing the current project folder: `$ yuidoc -c /data/symfony2-project/app/config/yuidoc.json`


### `ValidateJS` ###

* Javascript will be validated within this action `Terrific/ExporterBundle/Actions/ValidateJS`. This action will get a list of all necessary javascript assets from the `PageManager` class. After retrieving this list it temporary removes the min filters and place the content for *each part* of the asset within a temporary file. After saving that file the `jslint` command will looking for a suitable configuration file and starts to verify the contents of the temp file.
* An example call could look like this: `$ jshint --jslint-reporter --config /sf2-project/app/config/jshint.json /tmp/folder`


### `ValidateCSS` ###

* Stylesheets will be validated using this action (`Terrific/ExporterBundle/Actions/ValidateCSS`). This action also retrieves a list of all potential used style assets from the `PageManager` class. After retrieving this list the minfilter will be removed for dumping the content for *each part* of the asset to a temp file.
* Example for the csslint validation command: `$ csslint --format=lint-xml --errors=[from cfg] --warning=[from cfg] --ignore=[from cfg] /tmpdir/foldername`


### `ValidateModules` ###

* First of all this action (`Terrific/ExporterBundle/Actions/ValidateModules`) has a additional requirement on the configuration. This configuration must have enabled the options `validate_html` and `export_modules`. If one of them is not activated this action will be skipped.
* After starting this action it retrieves a list of all module combinations `module <-> skin <-> view` from the `PageManager` class.
* The modules are exported without any HTML from the view, just the plain modules.
* After exporting this HTML to a temp file the action tries to send this file to the [W3CValidator](http://validator.w3.org/). Internet connection ist required to start this action.
* To generate a valid HTML `ValidateModule` uses a template file. This template file `module-template.tpl.html` can be replaced by placing a file with the same name in your `app/config folder`. Within this file just two variables are special `\%MODULE_NAME\%` and `\%MODULE_CONTENT\%`. Both variables are replaced with the corresponding content before sending to the W3CValidator.


### `ValidateViews` ###

* This action (`Terrific/ExporterBundle/Actions/ValidateViews`) handles the validation for the views just like the `ValidateModules` action. The difference between this both action are that `ValidateViews` requires other configuration options to start: `export_views` and `validate_html`.
* The views also have all source code from the used modules within this view. This `ValdationAction` also uses the W3CValidator.


### `GenerateSprites` ###

* In order to user sprite generation the config (yml) must contain sprite-defintion:
    sprites:
            - {
                directory: "PROD/internet_sprite_icons",
                target: "web/img/sprite_icons.png", item: { height: 50, width: 100 }
            }
* To generate a sprite this action uses the `montage` tool from the ImageMagick toolset.
* After retrieving a filelist for merging as a sprite the action will sort this files by name. This offers you the possibility to order you images within your sprite. Filenames like `0000_arrow.png` are best practice.
* If this file is part of the export, which should be the normal usecase, this action has to *run before* `ExportImages`.

Example for a sprite generation call:

    $ montage
        -mode Concatenate
        -tile x${height * count images}
        -geometry ${width}x${height}+0+0
        -bordercolor none
        -background none
        ${list of files} ${target}


### `ExportImages` ###

* All *images which are used* within the exported views should be exported by `Terrific/ExporterBundle/Actions/ExportImages`.
* To control which image is part of the export you have to encapsulate each image within the **Twig's `asset()`** function.
* **Image path's which are not generated using this function won't be part the export!***
* The images used within the CSS file(s) will also be exported - but there is it no possible to control which should be part of the export.
* This action have to **run before** `OptimizeImages`.


### `ExportAssets` ###

* This action (`Terrific/ExporterBundle/Actions/ExportAssets`) just simply do dumps of all used assets (CSS and JavaScript).
* It also retrieves a list of used assets from the `PageManager` and dumps the assets to the exporting files.
* This time the exported assets are also get minified if it's configured! If the `build_local_paths` options is enabled the paths within the CSS files are change to match the exporting paths.
* **Paths within JavaScript files currently won't get changed!**


### `OptimizeImages` ###
* All images within the export path are optimized. So the startup of the `Terrific/ExporterBundle/Actions/OptimizeImages` action depends on the `ExportImages` action.
* After retrieving a list of all image files in the export, the action will optimize file by file depending on the file extension.
* After running this command it will print the amount of bytes saved for each file and as total amount.

Example commands for optimizing images:

* `$ optipng -o7 /exportpath/img/picture.png`
* `$ advpng -q -4 /exportpath/img/picture.png`
* `$ jpegopim -q /exportpath/img/picture.jpg`


### `ExportModules` ###
* This action (`Terrific/ExporterBundle/Actions/ExportModules`) retrieves a list of all module combinations `module <-> skin <-> view` and exports the HTML for all modules in a seperate folder within the exporting path.


### `ExportViews` ###
* This action (`Terrific/ExporterBundle/Actions/ExportViews`) retrieves a list of all controller methods which are annotated with `@Export`.
* **Remeber: If there is no such a method the exporter simply won't just export anything.**


### `ExportChangelogs` ###
* This action (`Terrific/ExporterBundle/Actions/ExportChangelogs`) just copies your changelog files (`file_extensions: log, txt, md`) to the exporting path.
* To append all changelogs, create a folder under `build/changelogs` or setup your `changelog_path` option. If this folder is not available no changelogs will appended.


### `ExportDiffs` ###
* `Terrific/ExporterBundle/Actions/ExportDiffs` will generate diff files for each view and append it to your export.
* The exporter needs an additional information given as console parameter to find the last export. This argument is called `--last-export=[directory]` and must be set to the last export package.
* The diff path is customized in the export environment within the `pathtemplates: diff: "/changelogs/diff"`.



## Extending ##

This chapter describes how to build your own action and how to use the available infrastructure get retrieve your results.

### Components and Services ###

#### `BuildOptions` ####

`ConfigFinder` is just a simple class that holds data of hierarchical data. After startup the `BuildOptions` class will get initialized with a file given in the project configuration. When initalizing is complete you can access all data from the `*.ini` file like a array. A dot is used to do step downwards in the hierarchy. Saving is normally done in the `destructor` of this service. If you're having trouble to wait on the destructor call you can manually run the `save()` function.

TODO: This service resides within the servicecontainer id (TODO: FILL IN ID).

    $majorVersion = $buildOptions["version.major"];
    $buildOptions["version.major"] = 2;


#### `ConfigFinder` ####

This service helps you to find configuration files. It will first look for a configuration file within the `app/config` directory and after that in the bundle's `Resources/config` directory. You will just receive a path for the file which is first found. This service resides within the servicecontainer id.

TODO: FILL IN ID.

    $configFile = $configFinder->find('jshint.json');


#### `Log` ####

Just a simple logging wrapper for console output. During the fact it is a static class you can simply use it without initializing it or retrieve it from the servicecontainer. After the `ExportCommand` the `Log` class is feeded with all data needed to do outputs on the console. This wrapper is also helpful to do hierarchically output using the `blkstart()` and `blkend()` functions.

    Log::info("Console info");
    // intends the output for info/err/warns until blkend() called
    Log::blkstart();
        Log::err("Console error");
        Log::warning("Console warning")
    // safety always reset your hierarchy
    Log::blkend();


#### `PathResolver` ####

This service just have two tasks first look for a specific asset in your path(s), second resolve a new path (export path) for the given asset url. To build an export path, this class takes all configuration options from the `pathtemplates` option from the configuration. This service resides within the servicecontainer id. (TODO: FILL IN ID).

    $asset = $pathResolver->find('filename', 'path_with_file');
    $exportPath = $pathResolver->resolve('/terrificmoduleTest/img/testimage.png');


#### `TempFileManager` ####

It's a manger that handles temporary files. Normally you receive some output from the project and just safe a temp file for it than do further operations until the final results are received. This service helps you to always get a clean file with no content and removes all files during service destruction which were created during the whole export run. To prevent the `TempFileManager` from removing the temporary content you can set the `keepTemp` property to `true`. This service resides within the servicecontainer id (TODO: FILL IN ID).

    $content = "some content here";
    $tempFileMgr->putContent($content); // returns a complete path to a file


#### `TimerService` ####

This service just helps to get the span between two specific points of time. You can receive the difference between these points. The service resides within the servicecontainer id (TODO: FILL IN ID).

    $timer->lap("START-doSomething");
    // do something
    $timer->lap("END-doSomething");

    $time = $timer->getTime("START-doSomething", "END-doSomething");


#### `W3CValidator` ####

A service which uses the online W3CValidator to validate content or complete files against the online validator. Both validation commands will return a `ValidationResult` object. This service resides within the servicecontainer id (TODO: FILL IN ID).

    $w3val->validateFile('/path/to/file');
    $w3val->validate('html content here');


#### `PageManager` ####

`PageManager` is the complexest service within the exporter. It manages to retrieve all routes from the router and all assets which are used in the twig templates. During the huge amount of time `PageManager` needs to initialize it caches most of the data and do a lazy initalization after the first method is called, so the first answer may need some time depending on the amount of views/routes which are configured within your frontend project. This service resides within the servicecontainer id (TODO: FILL IN ID).

    $pageManager->findRoutes(true);

FYI: `PageManager` uses the following resources:
    * Doctrine Annotation Reader
    * Router
    * Twig environment
    * Monolog
    * ModuleManager (TerrificCoreBundle)
    * Translator
    * customized Route object

### Helpers ###

The exporter sometimes do specific things more than one time. This helpers are used to concentrate specific things to a concrete class. **All helper classes are static so you can just call the methods within.**

#### `AsseticHelper` ####

Helper contains functions to work with assetic and asset specific tasks. Currently the `AsseticHelper` can export fonts and images from css content and removes minification filter from assetic.

#### `FileHelper` ####

This helper helps you with file identification like it is a stylesheet / javascript. Other functionalities are removing query params from urls or create whole paths without having to create the parent folders.

#### `NumberHelper` ####

Currently this helper just can format byte information to the highest possibly unit.

#### `OSHelper` ####

At the moment `OSHelper` just can give an answer if the current running OS is Windows: `isWin()`.

#### `ProcessHelper` ####

The `ProcessHelper` has two specific tasks.

1. First check a command if its available.
2. Second start the command.

The command string will be retrieved using the `ProcessBuilder` class from Symfony 2.1.

#### `StringHelper` ####

StringHelper currently just helps you to build working filenames and strip chars like `&` or `/` out of the filename.

#### `XmlLintHelper` ####

This helper is a verify specific helper. It helps to convert a linter XML format to a `ValidationResult` objects.

#### `GitHelper` ####

`GitHelper` helps you to retrieve informations from git archives using the `git` commandline executeable. This helper is currently not in use and maybe used in future building actions or project based actions.

### Build own actions ###

To write you own action you have at least to implement the `IAction` interface. This interface will guarantee that all methods that are called by the `ExportCommand` are available.

    public function setWorkingDir($directory);
    public function run(OutputInterface $output, $params = array());
    public function isRunnable(array $params);
    public static function getRequirements();

First the `ExportCommand` will retrieve all requirements from the action stack. After checking all requirements it looks for the necessary to run this action with calling the `isRunnable()` method. When all requirements are fit and this action should be run the `ExportCommand` will run your action.

To make it more easy to implement your own action you can just use a predefined abstract class which already have some useful functions. There are two of them `AbstractAction` and `AbstractExportAction`.

* `AbstractAction` contains helpers like a reference to the servicecontainer and initializes a filesystem object.
* `AbstractExportAction` contains all function from the `AbstractAction` class and adds a helper function for saving files to a directory.

To get further information on implementing own actions just take a look on the API documentation.

### Add new file type extensions to the Exporter ###

To add new file type extensions to the Exporter you need to edit `TerrificExporterBundle/Service/PathResolver.php`:

1. Add new type constants to class, e.g. `const TYPE_IMAGE = 1;` and assign a new **bit** position (just double the last assign value).
2. Add new standard path settings in constructor `__construct()` according to global and module context, e.g. `$this->pathTemplate[(self::TYPE_AUDIO | self::SCOPE_GLOBAL)] = '/media/audio';`
3. Extend switch case function `getType` which returns the resource type for the given file extension, like:

        case "SWF":
            return self::TYPE_FLASH;

4. Extend the `setContainer()` with new `pathtemplate` config options.
5. At last you need to update the definition of the used configuration options in `TerrificExporterBundle/DependencyInjection/Configuration/Configuration.php` in function `getConfigTreeBuilder` in node `->arrayNode("pathtemplates")->children()`.

## Known Problems ##

During the complexity of a project and this exporter there will always some constructs which are problematic and the exporter won't export. Some of the currently known limitations are written down here.

1. Module command with parameter set by the controller

    Constructs like `{{ tc.module(module,skins,views) }}` which needs data from the controller will currently not work. This is due to the fact that `PageManager` generates a list of assets from the templates directly and won't call the controller method for this.

2. Methods that returns a response rather than connect a view with `@Template()`

3. Double content - Assets with the same path and name but different content within the same export

    If two or more views use a asset named `default.css` but with different content, the export won't understand that this files named the same but different content so it will tell you that there is already a file with this name and stop exporting.

4. Asset usage commands within parent templates

    At the moment the `PageManager` generates a list of all views with all used assets. Currently the `PageManager` ignores twig's block overrides. This means the exporter will also export assets within overriden unused blocks.

5. Templates engines other than Twig
    Currently not supported during the usage of the twig template lexer to find used assets within the templates.


# FAQ #
1. Q: Some of my JavaScript files located in folder `web/js/yourfolder/` were not exported. What's wrong?

    A: Please note, the your `*.js` files should be located in folder `web/js/dependencies` seperated into the subfolders `libraries`, `plugins` and `utils`.

        web/js/dependencies/
            libraries/*.js
            plugins/*.js
            ultil/*.js

    Folders will be exported only if they have files in it. If you need to adjust the dependencies folder structure, you need to edit the function `addDependencies` in `TerrificExporterBundle/Actions/ExportAssets.php`.

2. Q: In my export there are multiple CSS/JS files, like `base.css` and `default.css`. Where are they from? How to get rid of them?

    A: These files are comming from the [TerrificCoreBundle](https://github.com/brunschgi/TerrificCoreBundle) located `Terrific/CoreBundle/Resources/views/base.html.twig`. You just need to replace the `base.html.twig` with your custom edited `base.html.twig` file. Generarly the file inherits from `Terrific/CoreBundle/Resources/views/base.html.twig` so be aware of the changes if `TerrificCoreBundle` is updated. Maybe you need to adjust some settings in yor base Twig file.


3. LESS issues

Warning! This construct will be ignored by the TerrificExporter Bundle:

    .sprite-image (@x: 0px, @y: 0px, @url: 'sprites-icons.png', @color: transparent, @scroll: scroll) {
        background: url('/img/'+@url) no-repeat @x @y @color @scroll;
    }

Use sth. like this instead:

    .sprites-icons(@x: 0px, @y: 0px, @color: transparent, @scroll: scroll) {
        background: url(/img/sprites-icons.png) no-repeat @x @y @color @scroll;
    }


# Some Notes

* Don't forget to generate an update to the TOC for this documentation. Node.js [doctoc](https://npmjs.org/package/doctoc) generates TOC for markdown files of local git repo: `$ doctoc README.md`.

# Todo #

***

# Authors
* [Bruno Lorenz](https://github.com/senuphtyz) (Main Developer - The 'brain' behind this monster!)
* [Eduard Seifert](https://github.com/eduardseifert) (Contributer)
* [Lars-Olof Krause](https://github.com/LOK-Soft) (Contributer)
