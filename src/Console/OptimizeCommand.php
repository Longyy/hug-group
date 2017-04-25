<?php
namespace Hug\Group\Console;

use ClassPreloader\Command\PreCompileCommand;
use Hug\Group\Foundation\Composer;
use Symfony\Component\Console\Input\InputOption;

class OptimizeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Optimize the framework for better performance";

    /**
     * The composer instance.
     *
     * @var \Illuminate\Foundation\Composer
     */
    protected $composer;

    /**
     * Create a new optimize command instance.
     *
     * @param  \Illuminate\Foundation\Composer  $composer
     * @return void
     */
    public function __construct(Composer $composer)
    {
        parent::__construct();

        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // $this->info('Generating optimized class loader');

        // if ($this->option('psr')) {
        //     $this->composer->dumpAutoloads();
        // } else {
        //     $this->composer->dumpOptimized();
        // }

        $this->info('Compiling common classes');

        $this->compileClasses();
    }

    /**
     * Generate the compiled class file.
     *
     * @return void
     */
    protected function compileClasses()
    {
        $this->registerClassPreloaderCommand();

        $outputPath = storage_path('framework/compiled.php');

        $this->callSilent('compile', array(
            '--config'         => implode(',', $this->getClassFiles()),
            '--output'         => $outputPath,
            '--strip_comments' => 1,
        ));
    }

    /**
     * Get the classes that should be combined and compiled.
     *
     * @return array
     */
    protected function getClassFiles()
    {
        return require __DIR__ . '/Optimize/config.php';
    }

    /**
     * Register the pre-compiler command instance with Artisan.
     *
     * @return void
     */
    protected function registerClassPreloaderCommand()
    {
        $this->getApplication()->add(new PreCompileCommand);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('force', null, InputOption::VALUE_NONE, 'Force the compiled class file to be written.'),

            array('psr', null, InputOption::VALUE_NONE, 'Do not optimize Composer dump-autoload.'),
        );
    }

}
