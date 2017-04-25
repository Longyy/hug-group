<?php
namespace Hug\Group\Foundation\Console;

use Hug\Group\Console\Scheduling\Schedule;
use Symfony\Component\Console\Application;

class Kernel
{

    protected $commands = [
        'Hug\Group\Console\OptimizeCommand',
        'Hug\Group\Console\ClearCompiledCommand',
        'Hug\Group\Console\Scheduling\ScheduleRunCommand',
    ];

    public function register(Application $console = null)
    {
        if (!$console) {
            $console = new Illuminate\Console\Application;
        }

        foreach ($this->commands as $command) {
            $console->add(app('app')->make($command));
        }

        $this->schedule(app('Hug\Group\Console\Scheduling\Schedule'));
    }

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
