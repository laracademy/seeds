<?php

namespace Laracademy\Commands;

use Illuminate\Console\Command;

class SeedsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seeds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will allow you to quickly seed the database with a user interface.';

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
     * @return mixed
     */
    public function handle()
    {
        $willExit = false;
        $path = database_path('seeds');
        // read all database seeders, removing the original database seeder
        $files = collect(glob("{$path}\*.php"))->reject(function($record) {
            $filename = collect(explode("\\", $record))->last();
            return $filename == 'DatabaseSeeder.php';
        });
        // clean up path
        $files = $files->map(function($record) use($path) {
            return str_replace("{$path}\\", '', $record);
        });
        // add exit
        $files = $files->prepend('Exit');
        // reverse the listing
        $index = 0;
        $files = $files->flatMap(function($record) use (&$index) {
            return [
                $record => $index++,
            ];
        });
        while(! $willExit) {
            // show list
            $answer = $this->choice('Please choose a database seeder', $files->toArray());
            if($answer == 'Exit') {
                $willExit = true;
            } else {
                // find the answer in the listing
                $file = $files->reject(function($index, $value) use ($answer) {
                    return $value != $answer;
                })->keys()->first();
                // grab the class name
                $class = collect($this->file_get_php_classes("{$path}\\{$file}"))->first();
                $this->info('Running: '. $class);
                sleep(1);
                $this->call('db:seed', [
                    '--class' => $class,
                ]);
            }
        }
    }
    function file_get_php_classes($filepath)
    {
        $php_code = file_get_contents($filepath);
        $classes = $this->get_php_classes($php_code);
        return $classes;
    }
    function get_php_classes($php_code)
    {
        $classes = array();
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] == T_CLASS
                && $tokens[$i - 1][0] == T_WHITESPACE
                && $tokens[$i][0] == T_STRING) {
                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }
        return $classes;
    }

}