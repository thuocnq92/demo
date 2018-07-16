<?php
/**
 * Created by PhpStorm.
 * User: datlt
 * Date: 05/07/2018
 * Time: 09:53
 */

namespace App\Providers;


use App\Http\Helper\LogToChannels;
use Illuminate\Support\ServiceProvider;

class LogToChannelsServiceProvider extends ServiceProvider
{
    /**
     * Initialize the logger
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('App\Http\Helper\LogToChannels', function () {
            return new LogToChannels();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [LogToChannels::class];
    }
}