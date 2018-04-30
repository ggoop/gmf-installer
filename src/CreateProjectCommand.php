<?php

namespace Gmf\Installer\Console;

use GuzzleHttp\Client;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use ZipArchive;

class CreateProjectCommand extends Command {
	/**
	 * Configure the command options.
	 *
	 * @return void
	 */
	protected function configure() {
		$this
			->setName('create-project')
			->setDescription('Create a new application')
			->addArgument('name', InputArgument::OPTIONAL)
			->addOption('dev', null, InputOption::VALUE_NONE, 'Installs the latest "development" release')
			->addOption('no-install', null, InputOption::VALUE_NONE, 'no-install the package')
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

		$directory = $name ? getcwd() . '/' . $name : getcwd();

		if (!$input->getOption('force')) {
			$this->verifyApplicationDoesntExist($directory);
		}

		$output->writeln('<info>create gmf application...[' . $name . ']</info>');

		$version = $this->getVersion($input);
		$zipFile = $this->makeFilename();
		$this->download($zipFile, $version, $output);
		$this->extract($zipFile, $directory, $output);
		$this->prepareWritableDirectories($directory, $output);
		$this->cleanUp($zipFile);

		$composer = Common::findComposer();
		if (!$input->getOption('no-install')) {
			$commands = [
				$composer . ' install --no-scripts',
				$composer . ' run-script post-root-package-install',
				$composer . ' run-script post-create-project-cmd',
				$composer . ' run-script post-autoload-dump',
				$composer . ' run-script gmf-install',
			];
		} else {
			$commands = [
				$composer . ' run-script post-root-package-install',
			];
		}
		if ($input->getOption('no-ansi')) {
			$commands = array_map(function ($value) {
				return $value . ' --no-ansi';
			}, $commands);
		}

		$process = new Process(implode(' && ', $commands), $directory, null, null, null);

		if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
			$process->setTty(true);
		}

		$process->run(function ($type, $line) use ($output) {
			$output->write($line);
		});

		$output->writeln('<comment>Application ready! Build something amazing.</comment>');
	}

	/**
	 * Verify that the application does not already exist.
	 *
	 * @param  string  $directory
	 * @return void
	 */
	protected function verifyApplicationDoesntExist($directory) {
		if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
			throw new RuntimeException('Application already exists!');
		}
	}

	/**
	 * Generate a random temporary filename.
	 *
	 * @return string
	 */
	protected function makeFilename() {
		return getcwd() . '/gmf-laravel_' . md5(time() . uniqid()) . '.zip';
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
		$response = (new Client)->get('https://github.com/ggoop/gmf-laravel/archive/' . $filename);

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

		$tempDir = getcwd() . DIRECTORY_SEPARATOR . 'gmf-laravel_' . md5(time() . uniqid());

		$archive->extractTo($tempDir);

		$archive->close();

		Common::copy_dir($tempDir . DIRECTORY_SEPARATOR . 'gmf-laravel-master', $directory, true);
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

	/**
	 * Make sure the storage and bootstrap cache directories are writable.
	 *
	 * @param  string  $appDirectory
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 * @return $this
	 */
	protected function prepareWritableDirectories($appDirectory, OutputInterface $output) {
		$filesystem = new Filesystem;

		try {
			$filesystem->chmod($appDirectory . DIRECTORY_SEPARATOR . "bootstrap/cache", 0755, 0000, true);
			$filesystem->chmod($appDirectory . DIRECTORY_SEPARATOR . "storage", 0755, 0000, true);
		} catch (IOExceptionInterface $e) {
			$output->writeln('<comment>You should verify that the "storage" and "bootstrap/cache" directories are writable.</comment>');
		}

		return $this;
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
