<?php
namespace Hug\Group\Console;

use Hug\Group\Console\Command;

class ClearCompiledCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'clear-compiled';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Remove the compiled class file";

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		if (file_exists($path = storage_path('framework/compiled.php')))
		{
			@unlink($path);
		}
	}

}
