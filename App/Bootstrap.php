<?php
namespace App;

use Symfony\Component\Console\Application;
use App\Command\ImportCommand;

class Bootstrap
{
	public function run()
	{
		$application = new Application();
		$application->add(new ImportCommand());
		$application->run();
	}
}