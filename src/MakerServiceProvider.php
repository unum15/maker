<?php namespace Unum\Maker;

use Illuminate\Support\ServiceProvider;

class MakerServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->commands([
            MakeModelCommand::class,
            MakeControllerCommand::class,
            MakeRoutesCommand::class,
            MakeFactoryCommand::class,
            MakeTestCommand::class,
            MakeAllCommand::class
        ]);
    }

    public function provides()
    {
        return [
            'command.maker.make_model'
        ];
    }
}
