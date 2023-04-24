<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

/**
 * -------------------------------------------------------------------
 * AUTO-LOADER
 * -------------------------------------------------------------------
 *
 * This file defines the namespaces and class maps so the Autoloader
 * can find the files as needed.
 *
 * NOTE: If you use an identical key in $psr4 or $classmap, then
 * the values in this file will overwrite the framework's values.
 */
class Autoload extends AutoloadConfig
{
	/**
	 * -------------------------------------------------------------------
	 * Namespaces
	 * -------------------------------------------------------------------
	 * This maps the locations of any namespaces in your application to
	 * their location on the file system. These are used by the autoloader
	 * to locate files the first time they have been instantiated.
	 *
	 * The '/app' and '/system' directories are already mapped for you.
	 * you may change the name of the 'App' namespace if you wish,
	 * but this should be done prior to creating any namespaced classes,
	 * else you will need to modify all of those classes for this to work.
	 *
	 * Prototype:
	 *
	 *   $psr4 = [
	 *       'CodeIgniter' => SYSTEMPATH,
	 *       'App'	       => APPPATH
	 *   ];
	 *
	 * @var array<string, string>
	 */
	public $psr4 = [
		APP_NAMESPACE => APPPATH, // For custom app namespace
		'Config'      => APPPATH.'Config',
		'Twig'		  =>APPPATH.'ThirdParty/Twig',
		//'Dompdf\Cpdf' =>APPPATH . 'ThirdParty/dompdf/lib',
		'Dompdf'      => APPPATH . 'ThirdParty/dompdf/src',
	];

	/**
	 * -------------------------------------------------------------------
	 * Class Map
	 * -------------------------------------------------------------------
	 * The class map provides a map of class names and their exact
	 * location on the drive. Classes loaded in this manner will have
	 * slightly faster performance because they will not have to be
	 * searched for within one or more directories as they would if they
	 * were being autoloaded through a namespace.
	 *
	 * Prototype:
	 *
	 *   $classmap = [
	 *       'MyClass'   => '/path/to/class/file.php'
	 *   ];
	 *
	 * @var array<string, string>
	 */
	public $classmap = [
	'Dompdf\Cpdf'=>APPPATH . 'ThirdParty/dompdf/lib/Cpdf.php',
	'elFinder' => FCPATH.'templates/vendor/elfinder/php/elFinder.class.php',
        'elFinderConnector' => FCPATH.'templates/vendor/elfinder/php/elFinderConnector.class.php',
        'elFinderEditor' => FCPATH.'templates/vendor/elfinder/php/editors/editor.php',
        'elFinderLibGdBmp' => FCPATH.'templates/vendor/elfinder/php/libs/GdBmp.php',
        'elFinderPlugin' => FCPATH.'templates/vendor/elfinder/php/elFinderPlugin.php',
        'elFinderPluginAutoResize' => FCPATH.'templates/vendor/elfinder/php/plugins/AutoResize/plugin.php',
        'elFinderPluginAutoRotate' => FCPATH.'templates/vendor/elfinder/php/plugins/AutoRotate/plugin.php',
        'elFinderPluginNormalizer' => FCPATH.'templates/vendor/elfinder/php/plugins/Normalizer/plugin.php',
        'elFinderPluginSanitizer' => FCPATH.'templates/vendor/elfinder/php/plugins/Sanitizer/plugin.php',
        'elFinderPluginWatermark' => FCPATH.'templates/vendor/elfinder/php/plugins/Watermark/plugin.php',
        'elFinderSession' => FCPATH.'templates/vendor/elfinder/php/elFinderSession.php',
        'elFinderSessionInterface' => FCPATH.'templates/vendor/elfinder/php/elFinderSessionInterface.php',
        'elFinderVolumeDriver' => FCPATH.'templates/vendor/elfinder/php/elFinderVolumeDriver.class.php',
        'elFinderVolumeDropbox2' => FCPATH.'templates/vendor/elfinder/php/elFinderVolumeDropbox2.class.php',
        'elFinderVolumeFTP' => FCPATH.'templates/vendor/elfinder/php/elFinderVolumeFTP.class.php',
        'elFinderVolumeFlysystemGoogleDriveCache' => FCPATH.'templates/vendor/elfinder/php/elFinderFlysystemGoogleDriveNetmount.php',
        'elFinderVolumeFlysystemGoogleDriveNetmount' => FCPATH.'templates/vendor/elfinder/php/elFinderFlysystemGoogleDriveNetmount.php',
        'elFinderVolumeGoogleDrive' => FCPATH.'templates/vendor/elfinder/php/elFinderVolumeGoogleDrive.class.php',
        'elFinderVolumeGroup' => FCPATH.'templates/vendor/elfinder/php/elFinderVolumeGroup.class.php',
        'elFinderVolumeLocalFileSystem' => FCPATH.'templates/vendor/elfinder/php/elFinderVolumeLocalFileSystem.class.php',
        'elFinderVolumeMySQL' => FCPATH.'templates/vendor/elfinder/php/elFinderVolumeMySQL.class.php',
        'elFinderVolumeTrash' => FCPATH.'templates/vendor/elfinder/php/elFinderVolumeTrash.class.php',
	
	];
}
