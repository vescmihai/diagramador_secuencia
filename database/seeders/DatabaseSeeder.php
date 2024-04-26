<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->crearUsuario();
    }
    public function crearUsuario(){
        $u = new User();
        $u->name = "Mihai Vescan";
        $u->email = "mihai@gmail.com";
        $u->password= bcrypt("12345678");
        $u->save();

        $u1 = new User();
        $u1->name = "Mihai Vescan";
        $u1->email = "mihai2@gmail.com";
        $u1->password= bcrypt("12345678");
        $u1->save();

        $u2 = new User();
        $u2->name = "Mihai Vescan";
        $u2->email = "vescmihai@gmail.com";
        $u2->password= bcrypt("12345678");
        $u2->save();
    }
}
