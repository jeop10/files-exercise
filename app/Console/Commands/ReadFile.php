<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class ReadFile extends Command
{
    const SUCCESS_CODE = 0;
    const ERROR_CODE = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:readfile {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reads a given file and sums its content recursively';

    protected $file_list = [];

    protected $results = [];

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
        $this->file_list[] = $this->argument('file');
        $result = 0;

        $this->info('Starting the readfile command');

        do {
            $file = array_shift($this->file_list);

            $this->newLine();
            $this->info('Reading file: '. $file);
            $result = $this->readContent($file);

            if ($result > self::SUCCESS_CODE) {
                break;
            }

        } while (count($this->file_list) > 0);

        if (!empty($this->results)) {
            $this->displayResults();
        }

        $this->newLine();
        $this->info('readfile command finished');
        return $result;
    }

    protected function readContent($file)
    {
        if (!File::exists($file)) {
            $this->error(sprintf('The file \'%s\' does not exists', $file));
            return self::ERROR_CODE;
        }

        if (!in_array(File::extension($file), ['txt'])) {
            $this->error('The file does not have a valid extension, only .txt files are supported');
            return self::ERROR_CODE;
        }

        if (!File::isReadable($file)) {
            $this->error(sprintf('The file %s cannot be read, please check the permissions', $file));
            return self::ERROR_CODE;
        }

        $lines = File::lines($file);

        $sum = 0;
        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            $number = (int) $line;

            if ($number == 0 && $line == "0") {
                $line = 0;
            }

            if ($number == 0 && str_contains($line, '.txt')) {
                $this->file_list[] = $line;
                continue;
            }

            if ($number) {
                $sum += $number;

                $this->info(sprintf('Integer %d found. Current sum: %d', $number, $sum), OutputInterface::VERBOSITY_VERBOSE);
            }
        }

        $this->results[] = [$file, $sum];
        return self::SUCCESS_CODE;
    }

    protected function displayResults()
    {
        $this->newLine();
        $this->info('Results');
        $this->newLine();
        $this->table(['Filename', 'Result'], $this->results);
    }

}
