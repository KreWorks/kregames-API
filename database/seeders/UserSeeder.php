<?php

namespace Database\Seeders;

class UserSeeder extends BaseSeeder
{
    protected $dataFileName;
    protected $uniqueFieldName;

    public function __construct()
    {
        $this->dataFileName = 'user.json';
        $this->uniqueFieldName = 'username';
    }
    

    protected function hasEntity($uniqueString)
    {
        $user = User::where($uniqueFieldName,$uniqueString)->get();

        return $user ? $user : false;
    }

    protected function insertEntity($datas)
    {
        $user = User::create([
            'name' => $datas['name'],
            'username' => $datas['username'],
            'email' => $datas['email'],
            'password' => Hash::make('jelszo123'),
        ]);

        return $user;
    }

    protected function updateEntity($entity, $datas)
    {
        $entity->name = $datas['name'];
        $entity->username = $datas['username'];
        $entity->email = $datas['email'];
        $entity->password = Hash::make('jelszo123');

        $entity->save();

        return $entity;
    }
       
}
