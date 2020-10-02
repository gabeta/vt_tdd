<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Article;
use Faker\Generator as Faker;

$factory->define(Article::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'title' => $faker->name,
        'description' => \Illuminate\Support\Str::random(500),
        'ip' => $faker->address,
        'category_id' => 2
    ];
});
