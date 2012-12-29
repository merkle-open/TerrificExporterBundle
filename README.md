# Exporter for Terrifc-Composer



# Installation

## Dependency management

Update your composer.json and add a new repository.

`
{
"type" : "vcs",
"url"  : "http://github.com/senuphtyz/TerrificExporterBundle"
}
`


After that add the new requirement to your project.

`
"senuphtyz/terrific-exporter-bundle" : "dev-refactored"
`

## Setup an export environment

It is necessary to setup a new environment for you export.
To setup an export environment copy your app/config.yml to app/config_export.yml.
Now you created a new environment called "export". 

You can now configure the exporter to your project needs.

## Tooling
There are a number of tools needed depending on your configuration and tasks.

- yuidoc
- jpegoptim
- optipng
- advpng
- csshint
- jslint
- montage


# Configuration

All configuration goes beyond a terrific_exporter node within the config_export.yml.

**build_local_paths: (true/false)**
If build_local_paths is enabled the exporter will change all urls within html and css files to match within the exported package.

**build_js_doc: (true/false)**
Enables the export of a javascript documentation. The documentation is generated using YUIDoc.

**build_settings: (path)**
This setting should target to a build.ini file. Within this file there are only settings for the projektname an versioning data.

**build_path: (path)**
Has to target to a path which is the export target path.

**export_with_version: (true/false)**
Set to true if the exporter should build zips/folders with version numbers within its name.

**autoincrement_build: (true/false)**
True if the exporter should increase the revision after each build.

**validate_js: (true/false)**
Activates the validation of javascript. Validation is done using jshint.

**validate_css: (true/false)**
Activate the validation of css. Validation is done using csshint.

**optimize_image: (true/false)**
Set to true to optimize images in the output directory. Optimization is done using jpegoptim, optipng and advpng.

**export_views: (true/false)**
Activates the export of the views marked with a @Export annotation.

**export_modules: (true/false)**
Activates the export of plain module html. The url within this modules are not rewriten even if the build_local_paths option is activated.

**export_type: (string: folder/zip)**
Set the export type if the export should be done as folder or as a zip.

**build_actions: (list of objects)**
These option allows to setup a build chain. Here you can append project related exporting tasks or change the buildin order.

**sprites: (list of objects)**
Here you can setup sprite information. The exporter will build the sprites with the given data.



## Example configuration

	terrific_exporter:
	    build_local_paths:        true
	    build_js_doc:             true
	    build_settings:           "build/build.ini"
	    build_path:               "build/"
	    export_with_version:      false
	    autoincrement_build:      true
	    validate_js:              false
	    validate_css:             false
	    validate_html:            false
	    optimize_images:          true
	    export_views:             true
	    export_modules:           true
	    export_type:              folder

	    build_actions:
	          - Terrific\ExporterBundle\Actions\ClearAction
	          - Terrific\ExporterBundle\Actions\BuildJSDoc
	          - Terrific\ExporterBundle\Actions\ValidateJS
	          - Terrific\ExporterBundle\Actions\ValidateCSS
	          - Terrific\ExporterBundle\Actions\ValidateModules
	          - Terrific\ExporterBundle\Actions\ValidateViews
	          - Terrific\ExporterBundle\Actions\GenerateSprites
	          - Terrific\ExporterBundle\Actions\ExportImages
	          - Terrific\ExporterBundle\Actions\ExportAssets
	          - Terrific\ExporterBundle\Actions\OptimizeImages
	          - Terrific\ExporterBundle\Actions\ExportModules
	          - Terrific\ExporterBundle\Actions\ExportViews

	    sprites:
	          - { directory: "PROD/internet_sprite_icons", target: "web/img/sprite_icons.png", item: { height: 50, width: 100 }}

 