<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

abstract class BaseSeeder extends Seeder
{
    protected const DATA_FOLDER = __DIR__.'../dataFiles/'; 
    
    protected $dataFileName;
    protected $uniqueFieldName;
    
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $contents = file_get_contents(self::DATA_FOLDER . $dataFileName);

        $this->command->info('Start processing data');

        foreach($contents as $content) {
            if ($entity = $this->hasEntity($content[$uniqueFieldName]) !== false) {
                $this->updateEntity($entity, $content);
            } else {
                $this->insertEntity($content);
            }
        }

        $this->command->info('Finished data processing');
    }

    protected abstract function hasEntity($uniqueString);
    protected abstract function insertEntity($datas);
    protected abstract function updateEntity($entity, $datas); 
}
