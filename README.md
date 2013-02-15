# Exporter for Terrifc-Composer



# Documentation Overview
See doc/Documentation.pdf for additional information (maybe no the latest version, so be carefull).

## Installation

1. Edit `app/AppKernel.php` and add a new entry to the `$bundles` array:
```php
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Terrific\ExporterBundle\TerrificExporterBundle(),
            // ...
        );
    }

}
```

2. Check if there is a `logs` folder in `app/`. If not available, `$ mkdir logs` one.

3. Create a new directory named `build` in the root project folder. Add a file `build.ini` into it. `name` describes the output folder or zip file (e.g. Terrific, frontend). Don't add the zip file extension to that name - this will be done later within the export process - depending on your options.

```yml
[version]
name=frontend
major=0
minor=0
build=0
```

4. Create a file named `config_export.yml` in folder `app/config/` or just copy your `config.yml`. The Exporter Configuration starts at line `terrific_exporter:`.
```yml
# /app/config/config_export.yml
imports:
    - { resource: config.yml }

framework:
    templating: { assets_version: 1 } # increment this to invalidate caches in production

services:
    terrific.formatter.line:
        class: Monolog\Formatter\LineFormatter
        arguments:
            - "[%%datetime%%] [%%level_name%%]: %%message%%\n"

monolog:
    handlers:
        main:
            type: fingers_crossed
            action_level: debug
            handler: nested
        nested:
            type: stream
            path: %kernel.logs_dir%/%kernel.environment%.log
            level: debug
            formatter: terrific.formatter.line

assetic:
    filters:
        cssmin:
            apply_to: "\.css$"
            file: %kernel.root_dir%/../vendor/natxet/CssMin/src/CssMin.php

        jsmin:
            apply_to: "\.js"
            file: %kernel.root_dir%/../vendor/mrclay/jsmin_minify/JSMin.php
            resource: %kernel.root_dir%/../vendor/brunschgi/terrific-core-bundle/Terrific/CoreBundle/Resources/config/jsmin.xml

terrific_composer:
    toolbar: false

# Terrific Exporter Configuration
# @see https://know.namics.com/display/frontend/Terrific+Exporter
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
        #- Custom\ExporterBundle\Actions\CopyAssets
        # 3. Run built-in exports (if you need them)
        #- Terrific\ExporterBundle\Actions\BuildJSDoc
        #- Terrific\ExporterBundle\Actions\ValidateJS
        #- Terrific\ExporterBundle\Actions\ValidateCSS
        #- Terrific\ExporterBundle\Actions\ValidateModules
        #- Terrific\ExporterBundle\Actions\ValidateViews
        #- Terrific\ExporterBundle\Actions\GenerateSprites
        #- Terrific\ExporterBundle\Actions\ExportImages
        # CSS Files and Paths
        - Terrific\ExporterBundle\Actions\ExportAssets
        #- Terrific\ExporterBundle\Actions\OptimizeImages
        #- Terrific\ExporterBundle\Actions\ExportModules
        - Terrific\ExporterBundle\Actions\ExportViews
        #- Terrific\ExporterBundle\Actions\ExportChangelogs
    #pathtemplates:
        #image: "/bilder/common23"
        #font: "/schriften"
        #css: "/styles"
        #js: "/scripts"
        #view: "/html"
        #changelog: "/changelogs"
        #diff: "/changelogs/diff"
        #module_image: "/module/%%module%%/bilder"
        #module_font: "/module/%%module%%/schriften"
        #module_css: "/module/%%module%%/styles"
        #module_js: "/module/%%module%%/scripts"
        #module_view: "/module/%%module%%/html"
```

# Usage
Remember to clear cache when you change the `config_export.yml` files.

* one
* two
* three


# Authors
* [Bruno Lorenz](https://github.com/senuphtyz) (Main Developer)
* [Eduard Seifert](https://github.com/eduardseifert) (Contributer)
