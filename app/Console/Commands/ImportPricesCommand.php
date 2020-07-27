<?php

namespace App\Console\Commands;

use App\Services\ImportPrices;
use Illuminate\Console\Command;

class ImportPricesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prices:import {path=/home/eduardo/Desktop/dev-test/import.csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Prices';

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
     * @throws \Exception
     */
    public function handle()
    {
        $path = $this->argument('path');


        $importPrices = new ImportPrices();

        $importPrices->import($path);
    }
}
