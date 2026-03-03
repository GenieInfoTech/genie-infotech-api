<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\BlogSeries;
use App\Models\BlogComment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create an author
        $author = User::first();
        
        if (!$author) {
            $author = User::create([
                'name' => 'Admin User',
                'email' => 'admin@genieinfo.tech',
                'password' => bcrypt('ChangeMe123!'),
            ]);
        }

        // Create categories
        $categories = [
            [
                'name' => 'Technology',
                'slug' => 'technology',
                'description' => 'Latest technology trends and news',
            ],
            [
                'name' => 'Web Development',
                'slug' => 'web-development',
                'description' => 'Web development tutorials and best practices',
            ],
            [
                'name' => 'Digital Marketing',
                'slug' => 'digital-marketing',
                'description' => 'Digital marketing strategies and tips',
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'Business insights and entrepreneurship',
            ],
            [
                'name' => 'Design',
                'slug' => 'design',
                'description' => 'UI/UX design and creative resources',
            ],
        ];

        foreach ($categories as $categoryData) {
            BlogCategory::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }

        // Create tags
        $tagNames = [
            'Laravel', 'PHP', 'JavaScript', 'React', 'Vue.js', 'SEO',
            'Content Marketing', 'Tutorial', 'Guide', 'Best Practices',
            'Tips & Tricks', 'Case Study', 'Industry Trends', 'API Development'
        ];

        $tags = [];
        foreach ($tagNames as $tagName) {
            $tags[] = BlogTag::firstOrCreate(
                ['slug' => Str::slug($tagName)],
                ['name' => $tagName]
            );
        }

        // Create a blog series
        $series = BlogSeries::firstOrCreate(
            ['slug' => 'getting-started-with-laravel'],
            [
                'name' => 'Getting Started with Laravel',
                'description' => 'A comprehensive guide to Laravel for beginners',
            ]
        );

        // Create sample blog posts
        $posts = [
            [
                'title' => 'Getting Started with Laravel 11: A Complete Guide',
                'slug' => 'getting-started-with-laravel-11',
                'excerpt' => 'Learn the fundamentals of Laravel 11 and build your first web application with this comprehensive guide.',
                'content' => $this->generateSampleContent('Laravel is a powerful PHP framework that makes web development a breeze. In this comprehensive guide, we\'ll walk through everything you need to know to get started with Laravel 11.'),
                'category_id' => BlogCategory::where('slug', 'web-development')->first()->id,
                'status' => 'published',
                'featured' => true,
                'published_at' => now()->subDays(10),
                'meta_description' => 'Complete guide to getting started with Laravel 11. Learn routing, controllers, models, and more.',
                'focus_keyword' => 'Laravel 11 guide',
            ],
            [
                'title' => 'Top 10 SEO Strategies for 2024',
                'slug' => 'top-10-seo-strategies-2024',
                'excerpt' => 'Boost your website\'s search engine rankings with these proven SEO strategies for 2024.',
                'content' => $this->generateSampleContent('Search Engine Optimization (SEO) is constantly evolving. Stay ahead of the curve with these top 10 SEO strategies for 2024.'),
                'category_id' => BlogCategory::where('slug', 'digital-marketing')->first()->id,
                'status' => 'published',
                'featured' => true,
                'published_at' => now()->subDays(5),
                'meta_description' => 'Discover the top 10 SEO strategies for 2024 to improve your website rankings and organic traffic.',
                'focus_keyword' => 'SEO strategies 2024',
            ],
            [
                'title' => 'Building RESTful APIs with Laravel',
                'slug' => 'building-restful-apis-with-laravel',
                'excerpt' => 'A step-by-step tutorial on creating robust and scalable RESTful APIs using Laravel.',
                'content' => $this->generateSampleContent('APIs are the backbone of modern web applications. Learn how to build professional RESTful APIs with Laravel in this detailed tutorial.'),
                'category_id' => BlogCategory::where('slug', 'web-development')->first()->id,
                'status' => 'published',
                'published_at' => now()->subDays(15),
                'meta_description' => 'Learn how to build RESTful APIs with Laravel. Complete tutorial with examples and best practices.',
                'focus_keyword' => 'Laravel API development',
            ],
            [
                'title' => '5 UI/UX Design Principles Every Developer Should Know',
                'slug' => '5-ui-ux-design-principles',
                'excerpt' => 'Improve your development skills by understanding these essential UI/UX design principles.',
                'content' => $this->generateSampleContent('Great design is not just about aesthetics. These 5 essential UI/UX principles will help you build better user experiences.'),
                'category_id' => BlogCategory::where('slug', 'design')->first()->id,
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'meta_description' => 'Essential UI/UX design principles for developers. Learn how to create better user experiences.',
                'focus_keyword' => 'UI UX design principles',
            ],
            [
                'title' => 'How to Scale Your Startup in 2024',
                'slug' => 'how-to-scale-your-startup-2024',
                'excerpt' => 'Practical strategies for scaling your startup and achieving sustainable growth.',
                'content' => $this->generateSampleContent('Scaling a startup requires careful planning and execution. Discover proven strategies to grow your business sustainably.'),
                'category_id' => BlogCategory::where('slug', 'business')->first()->id,
                'status' => 'published',
                'sticky' => true,
                'published_at' => now()->subDay(),
                'meta_description' => 'Learn how to scale your startup with these proven growth strategies for 2024.',
                'focus_keyword' => 'scale startup 2024',
            ],
            [
                'title' => 'Modern JavaScript ES6+ Features You Should Know',
                'slug' => 'modern-javascript-es6-features',
                'excerpt' => 'Master modern JavaScript with these essential ES6+ features and syntax improvements.',
                'content' => $this->generateSampleContent('JavaScript has evolved significantly with ES6+. Learn the modern features that will make you a more productive developer.'),
                'category_id' => BlogCategory::where('slug', 'web-development')->first()->id,
                'status' => 'published',
                'published_at' => now()->subDays(7),
                'meta_description' => 'Complete guide to modern JavaScript ES6+ features including arrow functions, promises, and more.',
                'focus_keyword' => 'JavaScript ES6 features',
            ],
            [
                'title' => 'Content Marketing Strategy for B2B Companies',
                'slug' => 'content-marketing-strategy-b2b',
                'excerpt' => 'Develop an effective content marketing strategy to attract and convert B2B clients.',
                'content' => $this->generateSampleContent('B2B content marketing requires a different approach. Learn how to create content that resonates with business decision-makers.'),
                'category_id' => BlogCategory::where('slug', 'digital-marketing')->first()->id,
                'status' => 'published',
                'published_at' => now()->subDays(12),
                'meta_description' => 'Create an effective B2B content marketing strategy to generate leads and drive business growth.',
                'focus_keyword' => 'B2B content marketing',
            ],
            [
                'title' => 'The Future of Web Development: Trends to Watch',
                'slug' => 'future-of-web-development-trends',
                'excerpt' => 'Stay ahead of the curve with these emerging web development trends and technologies.',
                'content' => $this->generateSampleContent('Web development is constantly evolving. Explore the trends and technologies that will shape the future of the web.'),
                'category_id' => BlogCategory::where('slug', 'technology')->first()->id,
                'status' => 'draft',
                'published_at' => now()->addDays(2),
                'meta_description' => 'Discover the future of web development with these emerging trends and technologies.',
                'focus_keyword' => 'web development trends',
            ],
            [
                'title' => 'Draft: Optimizing Database Performance',
                'slug' => 'optimizing-database-performance-draft',
                'excerpt' => 'Tips and techniques for optimizing database performance in web applications.',
                'content' => $this->generateSampleContent('Database performance is crucial for application speed. Learn optimization techniques that work.'),
                'category_id' => BlogCategory::where('slug', 'web-development')->first()->id,
                'status' => 'draft',
                'meta_description' => 'Learn database optimization techniques for better application performance.',
                'focus_keyword' => 'database optimization',
            ],
        ];

        $createdPosts = [];
        foreach ($posts as $postData) {
            $post = BlogPost::firstOrCreate(
                ['slug' => $postData['slug']],
                array_merge($postData, [
                    'author_id' => $author->id,
                    'views' => rand(100, 5000),
                    'like_count' => rand(10, 500),
                    'share_count' => rand(5, 200),
                    'allow_comments' => true,
                ])
            );

            $createdPosts[] = $post;

            // Attach random tags (2-5 tags per post)
            $randomTags = collect($tags)->random(rand(2, 5));
            $post->tags()->sync($randomTags->pluck('id'));

            // Add comments to published posts
            if ($post->status === 'published') {
                $this->createComments($post, $author);
            }
        }

        // Add posts to series
        $seriesPosts = BlogPost::whereIn('slug', [
            'getting-started-with-laravel-11',
            'building-restful-apis-with-laravel',
        ])->get();
        
        $series->posts()->sync($seriesPosts->pluck('id'));

        $this->command->info('✅ Blog demo data seeded successfully!');
        $this->command->info('   - ' . count($categories) . ' categories');
        $this->command->info('   - ' . count($tags) . ' tags');
        $this->command->info('   - ' . count($createdPosts) . ' blog posts');
        $this->command->info('   - 1 blog series');
    }

    /**
     * Generate sample content
     */
    protected function generateSampleContent(string $intro): string
    {
        return <<<HTML
<p>{$intro}</p>

<h2>Key Points</h2>
<ul>
    <li>Understanding the fundamentals is crucial for success</li>
    <li>Best practices ensure maintainable and scalable code</li>
    <li>Real-world examples help solidify understanding</li>
    <li>Continuous learning keeps you ahead of the curve</li>
</ul>

<h2>Getting Started</h2>
<p>Before diving deep, let's cover the basics. This foundation will serve as the building block for more advanced concepts.</p>

<h3>Step 1: Setup Your Environment</h3>
<p>Setting up your development environment correctly is the first step to success. Make sure you have all the necessary tools installed and configured properly.</p>

<h3>Step 2: Learn the Core Concepts</h3>
<p>Understanding core concepts will make your learning journey much smoother. Take time to grasp each concept before moving forward.</p>

<h3>Step 3: Practice with Real Examples</h3>
<p>Theory is important, but practice makes perfect. Build real projects to solidify your understanding.</p>

<h2>Best Practices</h2>
<p>Following best practices from the start will save you countless hours of debugging and refactoring later. Here are some essential best practices:</p>

<ol>
    <li><strong>Write Clean Code:</strong> Code is read more often than it's written</li>
    <li><strong>Test Your Code:</strong> Automated tests catch bugs early</li>
    <li><strong>Document Your Work:</strong> Good documentation saves time for everyone</li>
    <li><strong>Stay Updated:</strong> Technology evolves rapidly, keep learning</li>
</ol>

<h2>Common Pitfalls to Avoid</h2>
<p>Learning from others' mistakes can save you time and frustration. Here are common pitfalls to watch out for:</p>

<blockquote>
    <p>"The only way to learn a new programming language is by writing programs in it." - Dennis Ritchie</p>
</blockquote>

<h2>Conclusion</h2>
<p>Mastering these concepts takes time and practice. Start small, be consistent, and don't be afraid to make mistakes. Every expert was once a beginner.</p>

<p>Remember to bookmark this guide and refer back to it as you progress in your learning journey. Happy coding!</p>
HTML;
    }

    /**
     * Create sample comments for a post
     */
    protected function createComments(BlogPost $post, User $author): void
    {
        $commentCount = rand(1, 5);
        
        for ($i = 0; $i < $commentCount; $i++) {
            BlogComment::create([
                'post_id' => $post->id,
                'user_id' => $author->id,
                'content' => $this->getRandomComment(),
                'status' => $i == 0 ? 'pending' : 'approved', // First comment pending
                'created_at' => now()->subDays(rand(1, 8)),
            ]);
        }
    }

    /**
     * Get random comment text
     */
    protected function getRandomComment(): string
    {
        $comments = [
            'Great article! Very informative and well-written.',
            'Thanks for sharing this. I learned a lot from your post.',
            'This is exactly what I was looking for. Bookmarked!',
            'Excellent explanation. Looking forward to more content like this.',
            'Very helpful tutorial. The examples are clear and easy to follow.',
        ];

        return $comments[array_rand($comments)];
    }
}
