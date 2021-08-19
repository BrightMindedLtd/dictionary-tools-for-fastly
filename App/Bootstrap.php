<?php
namespace App;

use Symfony\Component\Console\Application;
use App\Command\ImportCommand;
use App\Command\ExportCommand;

class Bootstrap
{
	public function run()
	{
		$application = new Application();
		$application->add(new ImportCommand());
		$application->add(new ExportCommand());
		$application->run();
	}
}