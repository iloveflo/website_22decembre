<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        News::create([
            'title' => 'New 22.DÉCEMBRE Collection Released',
            'slug' => Str::slug('New 22.DÉCEMBRE Collection Released'),
            'content' => 'Discover our latest collection for the season...',
            'author_id' => 1,
            'status' => 'published'
        ]);
    }
}
