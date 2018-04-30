<?php

namespace Gmf\Installer\Console;
class Common {
	/**
	 * Get the composer command for the environment.
	 *
	 * @return string
	 */
	public static function findComposer() {
		if (file_exists(getcwd() . '/composer.phar')) {
			return '"' . PHP_BINARY . '" composer.phar';
		}
		return 'composer';
	}

	public static function copy_dir($src, $dst, $delete = false) {
		$dir = @opendir($src);
		@mkdir($dst);
		while (false !== ($file = readdir($dir))) {
			if (($file != '.') && ($file != '..')) {
				$srcFile = $src . DIRECTORY_SEPARATOR . $file;
				$newFile = $dst . DIRECTORY_SEPARATOR . $file;
				if (is_dir($srcFile)) {
					static::copy_dir($srcFile, $newFile, $delete);
					continue;
				} else {
					@copy($srcFile, $newFile);
					if ($delete) {
						@unlink($srcFile);
					}
				}
			}
		}
		@closedir($dir);
		@rmdir($src);
	}

}