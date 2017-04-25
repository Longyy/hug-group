<?php
namespace Hug\Group\Console;

use Illuminate\Contracts\Console\Application as ApplicationContract;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class Application extends SymfonyApplication implements ApplicationContract
{

    protected $lastOutput;

    public function call($command, array $parameters = array())
    {
        $parameters['command'] = $command;

        $this->lastOutput = new BufferedOutput;

        return $this->find($command)->run(new ArrayInput($parameters), $this->lastOutput);
    }

    /**
     * Get the output for the last run command.
     *
     * @return string
     */
    public function output()
    {
        return $this->lastOutput ? $this->lastOutput->fetch() : '';
    }
}
