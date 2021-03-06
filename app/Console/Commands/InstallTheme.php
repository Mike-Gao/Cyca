<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Facades\ThemeManager;

class InstallTheme extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:install { name : Name of the theme to install }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install a theme for Cyca';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');

        ThemeManager::updateCache();

        $this->comment("Please be careful when installing themes.");
        $this->comment("It is highly recommended to check the repository files before installation.");

        //ThemeManager::installFromName($name);

        $this->info("Theme installed !");

        return 0;
    }
}
