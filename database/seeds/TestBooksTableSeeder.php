<?php

use App\Eloquent\Book;
use App\Eloquent\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TestBooksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        factory(Book::class, 5)->create()->each(function ($book) use ($faker) {
            $userIds = app(User::class)->pluck('id')->random(5)->all();
            $stars = [];

            foreach ($userIds as $userId) {
                $star = $faker->numberBetween(1, 5);

                $book->users()->attach($userId, [
                    'status' => $faker->randomElement(Book::STATUS),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                $book->reviews()->attach($userId, [
                    'content' => $faker->text(200),
                    'star' => $star,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                $stars[] = $star;
            }

            $book->update([
                'avg_star' => array_sum($stars) / count($stars),
            ]);

            $book->media()->create([
                'name' => $faker->sentence(5),
                'path' => 'images/picture.jpg',
                'size' => $faker->numberBetween(500, 1024),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        });
    }
}