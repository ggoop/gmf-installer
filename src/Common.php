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
	public static function listDirs($dir) {
		static $alldirs = array();
		$dirs = glob($dir . '/*', GLOB_ONLYDIR);
		if (count($dirs) > 0) {
			foreach ($dirs as $d) {
				$alldirs[] = $d;
			}
		}
		foreach ($dirs as $dir) {
			static::listDirs($dir);
		}
		return $alldirs;
	}
	public static function listFiles($dir, $pattern = '*') {
		$dirs = static::listDirs($dir);
		$files = [];
		foreach ($dirs as $k => $v) {
			$files = array_merge($files, glob($v . DIRECTORY_SEPARATOR . $pattern));
		}
		return $files;
	}
	public static function studly($str, $separator = '_') {
		$str = " " . str_replace(["-", "_"], " ", strtolower($str));
		return ltrim(str_replace(" ", "", ucwords($str)), $separator);
	}
	public static function toNamespace($str, $separator = '\\') {
		$str = " " . str_replace(["-", "_"], " ", $str);
		return ltrim(str_replace(" ", $separator, ucwords($str)), $separator);
	}
	public static function snake($str, $separator = '_') {
		return strtolower(preg_replace('/([a-z0-9])([A-Z])/', "$1" . $separator . "$2", $str));
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