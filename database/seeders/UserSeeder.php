<?php

namespace Database\Seeders;

class UserSeeder extends BaseSeeder
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
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }

    protected function hasEntity($uniqueString)
    {
        $user = User::where($uniqueFieldName,$uniqueString)->get();

        return $user ? $user : false;
    }

    protected function insertEntity($datas)
    {
        
    }

    protected function updateEntity($entity, $datas)
    {

    }
}
