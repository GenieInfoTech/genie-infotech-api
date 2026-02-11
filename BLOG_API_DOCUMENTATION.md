# Blog API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
Most endpoints are public. Protected endpoints require Bearer token authentication.

```
Authorization: Bearer {your-token}
```

---

## Table of Contents
1. [Blog Posts](#blog-posts)
2. [Categories](#categories)
3. [Comments](#comments)
4. [Search & Filters](#search--filters)
5. [Related Posts](#related-posts)
6. [Trending Posts](#trending-posts)
7. [Analytics](#analytics)

---

## Blog Posts

### 1. Get All Blog Posts (Paginated)
Retrieve a paginated list of published blog posts.

**Endpoint:** `GET /blog`

**Query Parameters:**
- `page` (integer, optional): Page number (default: 1)
- `per_page` (integer, optional): Items per page (default: 15, max: 50)
- `category` (string, optional): Filter by category slug
- `tag` (string, optional): Filter by tag
- `author` (integer, optional): Filter by author ID
- `sort` (string, optional): Sort by field (title, created_at, views, likes)
- `order` (string, optional): Sort order (asc, desc)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/blog?page=1&per_page=10&category=technology&sort=views&order=desc"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Getting Started with Laravel",
      "slug": "getting-started-with-laravel",
      "excerpt": "Learn the basics of Laravel framework...",
      "content": "Full content here...",
      "cover_image": "http://localhost:8000/storage/blog-covers/image.jpg",
      "status": "published",
      "published_at": "2024-01-15T10:00:00.000000Z",
      "reading_time": 8,
      "views": 1250,
      "likes": 45,
      "shares": 12,
      "meta_title": "Laravel Tutorial - Getting Started",
      "meta_description": "Complete guide to Laravel...",
      "focus_keyword": "laravel tutorial",
      "author": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com"
      },
      "category": {
        "id": 1,
        "name": "Technology",
        "slug": "technology"
      },
      "tags": ["laravel", "php", "tutorial"],
      "created_at": "2024-01-15T09:00:00.000000Z",
      "updated_at": "2024-01-15T10:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 10,
    "to": 10,
    "total": 47
  },
  "links": {
    "first": "http://localhost:8000/api/blog?page=1",
    "last": "http://localhost:8000/api/blog?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/blog?page=2"
  }
}
```

---

### 2. Get Single Blog Post
Retrieve a single blog post by slug.

**Endpoint:** `GET /blog/{slug}`

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/blog/getting-started-with-laravel"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Getting Started with Laravel",
    "slug": "getting-started-with-laravel",
    "excerpt": "Learn the basics of Laravel framework...",
    "content": "Full HTML content here...",
    "cover_image": "http://localhost:8000/storage/blog-covers/image.jpg",
    "status": "published",
    "published_at": "2024-01-15T10:00:00.000000Z",
    "reading_time": 8,
    "views": 1251,
    "likes": 45,
    "shares": 12,
    "meta_title": "Laravel Tutorial - Getting Started",
    "meta_description": "Complete guide to Laravel...",
    "focus_keyword": "laravel tutorial",
    "canonical_url": null,
    "author": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com"
    },
    "category": {
      "id": 1,
      "name": "Technology",
      "slug": "technology",
      "description": "Tech articles and tutorials"
    },
    "tags": ["laravel", "php", "tutorial"],
    "comments_count": 12,
    "created_at": "2024-01-15T09:00:00.000000Z",
    "updated_at": "2024-01-15T10:00:00.000000Z"
  }
}
```

**Error Response (404 Not Found):**
```json
{
  "success": false,
  "message": "Blog post not found"
}
```

**Note:** This endpoint automatically increments the view count.

---

### 3. Get Featured Posts
Retrieve featured blog posts.

**Endpoint:** `GET /blog/featured`

**Query Parameters:**
- `limit` (integer, optional): Number of posts (default: 5, max: 20)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/blog/featured?limit=3"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "title": "Advanced Laravel Techniques",
      "slug": "advanced-laravel-techniques",
      "excerpt": "Take your Laravel skills to the next level...",
      "cover_image": "http://localhost:8000/storage/blog-covers/image2.jpg",
      "published_at": "2024-01-20T10:00:00.000000Z",
      "reading_time": 12,
      "views": 2500,
      "author": {
        "id": 1,
        "name": "Admin User"
      },
      "category": {
        "id": 1,
        "name": "Technology",
        "slug": "technology"
      }
    }
  ]
}
```

---

### 4. Get Latest Posts
Retrieve the most recent blog posts.

**Endpoint:** `GET /blog/recent`

**Query Parameters:**
- `limit` (integer, optional): Number of posts (default: 5, max: 20)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/blog/recent?limit=5"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 10,
      "title": "New Features in PHP 8.3",
      "slug": "new-features-php-8-3",
      "excerpt": "Explore the latest PHP features...",
      "cover_image": "http://localhost:8000/storage/blog-covers/image3.jpg",
      "published_at": "2024-01-25T10:00:00.000000Z",
      "reading_time": 6,
      "views": 450,
      "author": {
        "id": 1,
        "name": "Admin User"
      },
      "category": {
        "id": 2,
        "name": "Programming",
        "slug": "programming"
      }
    }
  ]
}
```

---

### 5. Like a Blog Post
Like/Unlike a blog post (toggles like status).

**Endpoint:** `POST /blog/{slug}/like`

**Authentication:** Required

**Example Request:**
```bash
curl -X POST "http://localhost:8000/api/blog/getting-started-with-laravel/like" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Post liked successfully",
  "data": {
    "liked": true,
    "likes_count": 46
  }
}
```

**When Unliking:**
```json
{
  "success": true,
  "message": "Post unliked successfully",
  "data": {
    "liked": false,
    "likes_count": 45
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "message": "Unauthenticated."
}
```

---

### 6. Share a Blog Post
Record a share action for a blog post.

**Endpoint:** `POST /blog/{slug}/share`

**Example Request:**
```bash
curl -X POST "http://localhost:8000/api/blog/getting-started-with-laravel/share"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Share recorded successfully",
  "data": {
    "shares_count": 13
  }
}
```

---

### 7. Get Popular Posts
Get the most popular blog posts based on views.

**Endpoint:** `GET /blog/popular`

**Query Parameters:**
- `limit` (integer, optional): Number of posts (default: 5, max: 20)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/blog/popular?limit=10"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 3,
      "title": "Laravel Best Practices",
      "slug": "laravel-best-practices",
      "excerpt": "Learn industry best practices...",
      "cover_image": "http://localhost:8000/storage/blog-covers/image4.jpg",
      "published_at": "2024-01-18T10:00:00.000000Z",
      "reading_time": 10,
      "views": 890,
      "category": {
        "id": 1,
        "name": "Technology",
        "slug": "technology"
      }
    }
  ]
}
```

---

## Categories

### 8. Get All Categories
Retrieve all blog categories with post counts.

**Endpoint:** `GET /blog/categories`

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/blog/categories"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Technology",
      "slug": "technology",
      "description": "Latest tech news and tutorials",
      "posts_count": 15
    },
    {
      "id": 2,
      "name": "Programming",
      "slug": "programming",
      "description": "Programming guides and tips",
      "posts_count": 23
    },
    {
      "id": 3,
      "name": "Design",
      "slug": "design",
      "description": "UI/UX and design articles",
      "posts_count": 9
    }
  ]
}
```

---

### 9. Get Category with Posts
Retrieve a single category with its blog posts.

**Endpoint:** `GET /blog/category/{slug}`

**Query Parameters:**
- `page` (integer, optional): Page number (default: 1)
- `per_page` (integer, optional): Items per page (default: 15)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/blog/category/technology?page=1&per_page=10"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Technology",
    "slug": "technology",
    "description": "Latest tech news and tutorials",
    "posts_count": 15,
    "posts": {
      "data": [
        {
          "id": 1,
          "title": "Getting Started with Laravel",
          "slug": "getting-started-with-laravel",
          "excerpt": "Learn the basics...",
          "cover_image": "http://localhost:8000/storage/blog-covers/image.jpg",
          "published_at": "2024-01-15T10:00:00.000000Z",
          "reading_time": 8,
          "views": 1250
        }
      ],
      "meta": {
        "current_page": 1,
        "last_page": 2,
        "per_page": 10,
        "total": 15
      }
    }
  }
}
```

---

## Comments

### 10. Get Post Comments
Retrieve all approved comments for a blog post.

**Endpoint:** `GET /blog/{slug}/comments`

**Query Parameters:**
- `page` (integer, optional): Page number (default: 1)
- `per_page` (integer, optional): Items per page (default: 20)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/blog/getting-started-with-laravel/comments?page=1"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "content": "Great article! Very helpful for beginners.",
      "author": {
        "id": 2,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "replies": [
        {
          "id": 3,
          "content": "Thank you for your feedback!",
          "author": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
          },
          "created_at": "2024-01-16T14:30:00.000000Z"
        }
      ],
      "created_at": "2024-01-16T12:00:00.000000Z",
      "updated_at": "2024-01-16T12:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 12
  }
}
```

---

### 11. Create Comment
Post a new comment on a blog post.

**Endpoint:** `POST /blog/{slug}/comments`

**Authentication:** Required

**Request Body:**
```json
{
  "content": "This is a great article! Thanks for sharing.",
  "parent_id": null
}
```

**Parameters:**
- `content` (string, required): Comment text (max 1000 characters)
- `parent_id` (integer, optional): ID of parent comment for replies

**Example Request:**
```bash
curl -X POST "http://localhost:8000/api/blog/getting-started-with-laravel/comments" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Excellent tutorial! Very clear explanations.",
    "parent_id": null
  }'
```

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Comment submitted successfully and is pending approval",
  "data": {
    "id": 15,
    "content": "Excellent tutorial! Very clear explanations.",
    "status": "pending",
    "author": {
      "id": 2,
      "name": "John Doe"
    },
    "created_at": "2024-01-25T15:45:00.000000Z"
  }
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
  "message": "The content field is required.",
  "errors": {
    "content": [
      "The content field is required."
    ]
  }
}
```

**Note:** Comments require admin approval before appearing publicly.

---

## Search & Filters

### 12. Get All Tags
Retrieve all blog tags.

**Endpoint:** `GET /blog/tags`

**Query Parameters:**
- `limit` (integer, optional): Number of tags (default: 20)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/blog/tags?limit=20"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Getting Started with Laravel",
      "slug": "getting-started-with-laravel",
      "excerpt": "Learn the basics of Laravel framework...",
      "cover_image": "http://localhost:8000/storage/blog-covers/image.jpg",
      "published_at": "2024-01-15T10:00:00.000000Z",
      "reading_time": 8,
      "views": 1250,
      "category": {
        "id": 1,
        "name": "Technology",
        "slug": "technology"
      },
      "author": {
        "id": 1,
        "name": "Admin User"
      }
    }
  ],
  "meta": {
    "search_query": "laravel",
    "total_results": 5,
    "current_page": 1,
    "last_page": 1,
    "per_page": 15
  }
}
```

---

### 13. Get Posts by Tag
Retrieve blog posts filtered by tag.

**Endpoint:** `GET /blog/tag/{tag}`

**Query Parameters:**
- `page` (integer, optional): Page number (default: 1)
- `per_page` (integer, optional): Items per page (default: 15)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/blog/tag/tutorial?page=1"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "tag": "tutorial",
    "posts": [
      {
        "id": 1,
        "title": "Getting Started with Laravel",
        "slug": "getting-started-with-laravel",
        "excerpt": "Learn the basics...",
        "cover_image": "http://localhost:8000/storage/blog-covers/image.jpg",
        "published_at": "2024-01-15T10:00:00.000000Z",
        "reading_time": 8,
        "views": 1250,
        "category": {
          "name": "Technology",
          "slug": "technology"
        }
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 2,
      "per_page": 15,
      "total": 18
    }
  }
}
```

---

### 14. Get Trending Posts
Get the most popular posts based on views and engagement.

**Endpoint:** `GET /blog/trending`

**Query Parameters:**
- `limit` (integer, optional): Number of posts (default: 10, max: 20)
- `days` (integer, optional): Time period in days (default: 7)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/blog/trending?limit=5&days=7"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "title": "Advanced Laravel Techniques",
      "slug": "advanced-laravel-techniques",
      "excerpt": "Take your Laravel skills to the next level...",
      "cover_image": "http://localhost:8000/storage/blog-covers/image2.jpg",
      "published_at": "2024-01-20T10:00:00.000000Z",
      "reading_time": 12,
      "views": 2500,
      "likes": 89,
      "shares": 34,
      "engagement_score": 2623,
      "category": {
        "id": 1,
        "name": "Technology",
        "slug": "technology"
      }
    }
  ]
}
```

---

## Additional Features

### 15. Get Blog Sitemap
Retrieve blog sitemap for SEO.

**Endpoint:** `GET /blog/sitemap`

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/blog/sitemap"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "loc": "http://localhost:8000/blog/getting-started-with-laravel",
      "lastmod": "2024-01-15T10:00:00.000000Z",
      "changefreq": "weekly",
      "priority": "0.8"
    }
  ]
}
```

---

### 16. Get RSS Feed
Retrieve blog RSS feed.

**Endpoint:** `GET /blog/rss`

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/blog/rss"
```

**Response:** XML RSS feed

---

## Analytics

### 17. Get Popular Tags
Get the most used tags across all blog posts.

**Endpoint:** `GET /blog/popular-tags`

**Query Parameters:**
- `limit` (integer, optional): Number of tags (default: 20)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/blog/tags?limit=10"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "tag": "laravel",
      "count": 15
    },
    {
      "tag": "php",
      "count": 12
    },
    {
      "tag": "tutorial",
      "count": 10
    },
    {
      "tag": "javascript",
      "count": 8
    }
  ]
}
```

---

### 18. Get Blog Statistics
Get overall blog statistics.

**Endpoint:** `GET /blog/stats`

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/blog/stats"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "total_posts": 47,
    "total_categories": 5,
    "total_comments": 234,
    "total_views": 45789,
    "total_likes": 1256,
    "published_posts": 42,
    "draft_posts": 5,
    "posts_this_month": 8,
    "most_viewed_post": {
      "id": 5,
      "title": "Advanced Laravel Techniques",
      "slug": "advanced-laravel-techniques",
      "views": 2500
    },
    "most_popular_category": {
      "id": 1,
      "name": "Technology",
      "posts_count": 15
    }
  }
}
```

---

## Error Responses

All endpoints may return the following error responses:

### 400 Bad Request
```json
{
  "success": false,
  "message": "Invalid request parameters"
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### 422 Unprocessable Entity
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Error message here"
    ]
  }
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "An error occurred while processing your request"
}
```

---

## Rate Limiting

API endpoints are rate-limited:
- **Public endpoints:** 60 requests per minute
- **Authenticated endpoints:** 120 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1706182800
```

---

## CORS Configuration

CORS is enabled for all origins in development. For production:
- Allowed origins need to be configured
- Credentials are supported
- All standard methods are allowed

---

## Best Practices

### 1. Image Loading
Always use responsive images and lazy loading:
```html
<img 
  src="{cover_image}" 
  alt="{title}"
  loading="lazy"
  class="w-full h-auto"
/>
```

### 2. Pagination
Implement infinite scroll or pagination for better UX:
```javascript
const loadMorePosts = async (page) => {
  const response = await fetch(`/api/blog?page=${page}`);
  const data = await response.json();
  // Append data.data to your posts list
};
```

### 3. Search Debouncing
Debounce search requests to reduce API calls:
```javascript
const debounce = (func, wait) => {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => func(...args), wait);
  };
};

const searchPosts = debounce(async (query) => {
  const response = await fetch(`/api/blog?q=${query}`);
  // Handle response
}, 300);
```

### 4. Error Handling
Always handle errors gracefully:
```javascript
try {
  const response = await fetch('/api/blog/slug');
  if (!response.ok) {
    throw new Error('Failed to fetch post');
  }
  const data = await response.json();
  // Use data
} catch (error) {
  console.error('Error:', error);
  // Show user-friendly error message
}
```

### 5. Caching
Implement client-side caching for better performance:
```javascript
const cache = new Map();

const fetchWithCache = async (url, ttl = 60000) => {
  if (cache.has(url)) {
    const { data, timestamp } = cache.get(url);
    if (Date.now() - timestamp < ttl) {
      return data;
    }
  }
  
  const response = await fetch(url);
  const data = await response.json();
  cache.set(url, { data, timestamp: Date.now() });
  return data;
};
```

---

## Example Frontend Implementation

### React/Next.js Example

```jsx
// app/blog/page.jsx
'use client';
import { useState, useEffect } from 'react';

export default function BlogList() {
  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  useEffect(() => {
    const fetchPosts = async () => {
      try {
        const res = await fetch(
          `http://localhost:8000/api/blog?page=${page}&per_page=12`
        );
        const data = await res.json();
        setPosts(data.data);
      } catch (error) {
        console.error('Error fetching posts:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchPosts();
  }, [page]);

  if (loading) return <div>Loading...</div>;

  return (
    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
      {posts.map(post => (
        <article key={post.id} className="card">
          <img 
            src={post.cover_image} 
            alt={post.title}
            className="w-full h-48 object-cover"
          />
          <div className="p-4">
            <h2 className="text-xl font-bold">{post.title}</h2>
            <p className="text-gray-600">{post.excerpt}</p>
            <div className="flex items-center mt-4">
              <span className="text-sm">{post.author.name}</span>
              <span className="mx-2">•</span>
              <span className="text-sm">{post.reading_time} min read</span>
            </div>
            <a 
              href={`/blog/${post.slug}`}
              className="btn btn-primary mt-4"
            >
              Read More
            </a>
          </div>
        </article>
      ))}
    </div>
  );
}
```

### Vue/Nuxt Example

```vue
<!-- pages/blog/index.vue -->
<template>
  <div class="blog-list">
    <div v-if="loading" class="loading">Loading...</div>
    
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <article 
        v-for="post in posts" 
        :key="post.id"
        class="card"
      >
        <img 
          :src="post.cover_image" 
          :alt="post.title"
          class="w-full h-48 object-cover"
        />
        <div class="p-4">
          <h2 class="text-xl font-bold">{{ post.title }}</h2>
          <p class="text-gray-600">{{ post.excerpt }}</p>
          <div class="flex items-center mt-4">
            <span class="text-sm">{{ post.author.name }}</span>
            <span class="mx-2">•</span>
            <span class="text-sm">{{ post.reading_time }} min read</span>
          </div>
          <NuxtLink 
            :to="`/blog/${post.slug}`"
            class="btn btn-primary mt-4"
          >
            Read More
          </NuxtLink>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
const posts = ref([]);
const loading = ref(true);

const { data } = await useFetch('http://localhost:8000/api/blog', {
  params: { per_page: 12 }
});

if (data.value) {
  posts.value = data.value.data;
  loading.value = false;
}
</script>
```

---

## Testing the API

### Using cURL

```bash
# Get all posts
curl http://localhost:8000/api/blog

# Get single post
curl http://localhost:8000/api/blog/getting-started-with-laravel

# Get trending posts
curl http://localhost:8000/api/blog/trending

# Like post (requires authentication)
curl -X POST http://localhost:8000/api/blog/slug/like \
  -H "Authorization: Bearer YOUR_TOKEN"

# Create comment (requires authentication)
curl -X POST http://localhost:8000/api/blog/slug/comments \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content":"Great article!"}'
```

### Using JavaScript Fetch

```javascript
// Get posts
fetch('http://localhost:8000/api/blog')
  .then(res => res.json())
  .then(data => console.log(data));

// Like post
fetch('http://localhost:8000/api/blog/slug/like', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Accept': 'application/json'
  }
})
  .then(res => res.json())
  .then(data => console.log(data));
```

---

## Support & Questions

For implementation questions or issues:
- Check the error response messages
- Verify authentication tokens
- Ensure all required parameters are provided
- Check CORS configuration for frontend domain

---

**Last Updated:** February 12, 2026  
**API Version:** 1.0  
**Laravel Version:** 11.x
