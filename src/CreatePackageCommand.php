<?php

namespace Gmf\Installer\Console;

use GuzzleHttp\Client;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ZipArchive;

class CreatePackageCommand extends Command {
	/**
	 * Configure the command options.
	 *
	 * @return void
	 */
	protected function configure() {
		$this
			->setName('create-package')
			->setDescription('Create a new package')
			->addArgument('name', InputArgument::OPTIONAL)
			->addOption('user', null, InputOption::VALUE_OPTIONAL, 'the user who created!')
			->addOption('force', null, InputOption::VALUE_NONE, 'Forces install even if the directory already exists');
	}

	/**
	 * Execute the command.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 * @return void
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		if (!class_exists('ZipArchive')) {
			throw new RuntimeException('The Zip PHP extension is not installed. Please install it and try again.');
		}
		$name = $input->getArgument('name');
		if (empty($name)) {
			throw new \Exception("name is null;");
		}

		$directory = $name ? getcwd() . '/' . $name : getcwd();

		if (!$input->getOption('force')) {
			$this->verifyApplicationDoesntExist($directory);
		}

		$output->writeln('<info>create gmf package...[' . $name . ']</info>');

		$version = $this->getVersion($input);
		$zipFile = $this->makeFilename();
		$this->download($zipFile, $version, $output);
		$this->extract($zipFile, $directory, $output);
		$this->replaceFileContent($directory, $output);
		$this->cleanUp($zipFile);

		$output->writeln('<comment>package ready! Build something amazing.</comment>');
	}

	/**
	 * Verify that the application does not already exist.
	 *
	 * @param  string  $directory
	 * @return void
	 */
	protected function verifyApplicationDoesntExist($directory) {
		if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
			throw new RuntimeException('package already exists!');
		}
	}

	/**
	 * Generate a random temporary filename.
	 *
	 * @return string
	 */
	protected function makeFilename() {
		return getcwd() . '/gmf-package_' . md5(time() . uniqid()) . '.zip';
	}

	/**
	 * Download the temporary Zip to the given file.
	 *
	 * @param  string  $zipFile
	 * @param  string  $version
	 * @return $this
	 */
	protected function download($zipFile, $version = 'master', OutputInterface $output) {
		if ($version) {
			$filename = $version . '.zip';
		} else {
			$filename = 'master.zip';
		}
		//http://cabinet.laravel.com/' . $filename
		$response = (new Client)->get('https://github.com/ggoop/gmf-package/archive/' . $filename);

		file_put_contents($zipFile, $response->getBody());

		return $this;
	}

	/**
	 * Extract the Zip file into the given directory.
	 *
	 * @param  string  $zipFile
	 * @param  string  $directory
	 * @return $this
	 */
	protected function extract($zipFile, $directory, OutputInterface $output) {
		$archive = new ZipArchive;

		$archive->open($zipFile);

		$tempDir = getcwd() . DIRECTORY_SEPARATOR . 'gmf-package_' . md5(time() . uniqid());

		$archive->extractTo($tempDir);

		$archive->close();

		Common::copy_dir($tempDir . DIRECTORY_SEPARATOR . 'gmf-package-master', $directory, true);
		@chmod($tempDir, 0777);
		@rmdir($tempDir);
		return $this;
	}

	/**
	 * Clean-up the Zip file.
	 *
	 * @param  string  $zipFile
	 * @return $this
	 */
	protected function cleanUp($zipFile) {
		@chmod($zipFile, 0777);

		@unlink($zipFile);

		return $this;
	}
	protected function replaceFileContent($directory, OutputInterface $output) {
		$name = basename($directory);

		$DummyRootNamespaceString = Common::toNamespace($name, '\\\\');
		$DummyPackageName = Common::snake(Common::studly($name), '-');
		$DummyRootNamespace = Common::toNamespace($name, '\\');
		$DummyUserName = $input->getOption('force');

		$file = $directory . DIRECTORY_SEPARATOR . 'composer.json';
		$content = @file_get_contents($file);
		if ($content) {
			$content = str_replace('DummyRootNamespaceString', $DummyRootNamespaceString, $content);
			$content = str_replace('DummyPackageName', $DummyPackageName, $content);
			if ($DummyUserName) {
				$content = str_replace('DummyUserName', $DummyUserName, $content);
			}
			@file_put_contents($file, $content);
		}
		$list = Common::listFiles($directory, "*.php");
		foreach ($list as $file) {
			$content = @file_get_contents($file);
			$content = str_replace('DummyRootNamespaceString', $DummyRootNamespaceString, $content);
			$content = str_replace('DummyPackageName', $DummyPackageName, $content);
			$content = str_replace('DummyRootNamespace', $DummyRootNamespace, $content);
			@file_put_contents($file, $content);
		}
	}

	/**
	 * Get the version that should be downloaded.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @return string
	 */
	protected function getVersion(InputInterface $input) {
		if ($input->getOption('dev')) {
			return 'develop';
		}
		return 'master';
	}
}
