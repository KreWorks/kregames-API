<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

abstract class BaseSeeder extends Seeder
{
    protected const DATA_FOLDER = __DIR__.'../dataFiles/'; 
    
    protected $dataFileName;
    
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $content = file_get_contents(self::DATA_FOLDER . $dataFileName);

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }

    protected abstract function hasEntity($uniqueString);
    protected abstract function insertEntity($datas);
    protected abstract function updateEntity($entity, $datas); 
}
