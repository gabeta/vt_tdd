<?php

namespace Tests\Feature;

use App\Article;
use App\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /**
     * Test affichage du formulaire de création d'article
     *
     * @return void
     */
    public function test_get_create_article_view()
    {
        $response = $this->get(route('articles.create'));

        $response->assertSuccessful();

        $response->assertViewIs('articles.create');
    }

    /**
     * Test si le formulaire n'est pas correctement remplir
     *
     * @test
     */
    public function test_store_article_required_field ()
    {
        $response = $this->from(route('articles.create'))->post(route('articles.store'), [
            'name' => '',
            'title' => '',
            'description' => '',
            'category_id' => ''
        ]);

        $response->assertRedirect(route('articles.create'));

        $response->assertSessionHasErrors(['title', 'description', 'category_id']);
    }

    /**
     * @test
     */
    public function test_store_article_description_field()
    {
        $response = $this->from(route('articles.create'))->post(route('articles.store'), [
            'name' => $this->faker->name,
            'title' => $this->faker->title,
            'description' => $this->faker->text(499),
            'category_id' => 2
        ]);

        $response->assertRedirect(route('articles.create'));

       $response->assertSessionHasErrors(['description']);

       $this->assertTrue(session()->hasOldInput('name'));

       $this->assertTrue(session()->hasOldInput('title'));

        $this->assertTrue(session()->hasOldInput('description'));
    }

    public function test_store_article_category_field()
    {
        $response = $this->from(route('articles.create'))->post(route('articles.store'), [
            'name' => $this->faker->name,
            'title' => $this->faker->title,
            'description' => $this->faker->text(2000),
            'category_id' => 2
        ]);

        $response->assertRedirect(route('articles.create'));

       $response->assertSessionHasErrors('category_id');

       $this->assertTrue(session()->hasOldInput('name'));

       $this->assertTrue(session()->hasOldInput('title'));

        $this->assertTrue(session()->hasOldInput('description'));
    }

    public function test_store_article()
    {
        $category = factory(Category::class)->create();

        $response = $this->from(route('articles.create'))->post(route('articles.store'), [
            'name' => ($name = $this->faker->name),
            'title' => ($title = $this->faker->name),
            'description' => ($description = Str::random(500)),
            'category_id' => ($cat = $category->id)
        ]);

        $response->assertRedirect(route('articles.create'));

        $this->assertEquals($response->getStatusCode(), 302);

        $this->assertFalse(session()->hasOldInput('name'));

        $this->assertFalse(session()->hasOldInput('title'));

        $this->assertFalse(session()->hasOldInput('description'));

        $this->assertFalse(session()->hasOldInput('category_id'));

        $this->assertEquals(Article::count(), 1);

        $article = Article::first();

        $this->assertEquals($article->name, $name);

        $this->assertEquals($article->title, $title);

        $this->assertEquals($article->description, $description);

        $this->assertEquals($article->ip, request()->ip());

        $this->assertEquals($article->category_id, $cat);
    }

    public function test_store_article_with_empty_name()
    {
        $category = factory(Category::class)->create();

        $response = $this->from(route('articles.create'))->post(route('articles.store'), [
            'name' => '',
            'title' => ($title = $this->faker->name),
            'description' => ($description = Str::random(500)),
            'category_id' => ($cat = $category->id)
        ]);

        $response->assertRedirect(route('articles.create'));

        $this->assertEquals($response->getStatusCode(), 302);

        $this->assertFalse(session()->hasOldInput('name'));

        $this->assertFalse(session()->hasOldInput('title'));

        $this->assertFalse(session()->hasOldInput('description'));

        $this->assertFalse(session()->hasOldInput('category_id'));

        $this->assertEquals(Article::count(), 1);

        $article = Article::first();

        $this->assertEquals($article->name, config('press.article.nullable_name_prefix').$article->id);

        $this->assertEquals($article->title, $title);

        $this->assertEquals($article->description, $description);

        $this->assertEquals($article->ip, request()->ip());

        $this->assertEquals($article->category_id, $cat);
    }

    public function test_guest_cannot_store_article_more_than_three_with_same_ip_daily()
    {
        $category = factory(Category::class)->create();

        factory(Article::class, 3)->create([
            'name' => $this->faker->name,
            'title' => $this->faker->name,
            'description' => Str::random(500),
            'ip' => request()->ip(),
            'category_id' => $category->id
        ]);

        $response = $this->from(route('articles.create'))->post(route('articles.store'), [
            'name' => '',
            'title' => ($title = $this->faker->name),
            'description' => ($description = Str::random(500)),
            'category_id' => ($cat = $category->id)
        ]);

        $response->assertRedirect(route('articles.create'));

        $this->assertEquals(Article::count(), 3);
    }
}
