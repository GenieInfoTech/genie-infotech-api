# Blog System - Comprehensive API Documentation
## For Frontend UI Development

**Version:** 1.0  
**Base URL:** `http://localhost:8000/api`  
**Date:** February 2026

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Rate Limiting](#rate-limiting)
4. [Response Format](#response-format)
5. [Error Handling](#error-handling)
6. [Blog Post Endpoints](#blog-post-endpoints)
7. [Category Endpoints](#category-endpoints)
8. [Tag Endpoints](#tag-endpoints)
9. [Comment Endpoints](#comment-endpoints)
10. [Analytics & Tracking](#analytics--tracking)
11. [Utility Endpoints](#utility-endpoints)
12. [Data Models](#data-models)

---

## Overview

This API provides a complete blog system with posts, categories, tags, comments, analytics, and SEO features. All endpoints return JSON responses and support modern web standards.

### Key Features
- ✅ Full blog post CRUD (via admin panel)
- ✅ Public read access for all blog content
- ✅ Rich text content with media support
- ✅ Categories and tags for organization
- ✅ Nested comments with moderation
- ✅ Analytics tracking (views, likes, shares)
- ✅ SEO optimization with meta tags and schema
- ✅ RSS/Sitemap generation
- ✅ Guest and authenticated commenting

---

## Authentication

### Public Endpoints
Most blog endpoints are **public** and require no authentication:
- Reading posts, categories, tags
- Viewing comments
- Tracking shares
- RSS/Sitemap feeds

### Protected Endpoints
These endpoints require a Bearer token:
- Liking posts
- Deleting comments (own comments only)

### Authentication Header
```http
Authorization: Bearer {your-access-token}
```

### Getting a Token
Users can obtain tokens via the authentication endpoints:
```bash
POST /api/auth/login
POST /api/auth/register
```

---

## Rate Limiting

| Endpoint Type | Rate Limit |
|--------------|------------|
| Public Routes | 60 requests/minute |
| Authenticated Routes | 60 requests/minute |
| Login Attempts | 5 requests/minute |

**Rate Limit Headers:**
```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
```

---

## Response Format

### Success Response
```json
{
  "success": true,
  "data": { },
  "message": "Optional success message"
}
```

### Paginated Response
```json
{
  "data": [],
  "links": {
    "first": "http://localhost:8000/api/blog?page=1",
    "last": "http://localhost:8000/api/blog?page=10",
    "prev": null,
    "next": "http://localhost:8000/api/blog?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "links": [],
    "path": "http://localhost:8000/api/blog",
    "per_page": 12,
    "to": 12,
    "total": 120
  }
}
```

---

## Error Handling

### Error Response Format
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |

---

## Blog Post Endpoints

### 1. Get All Blog Posts (Paginated)

**GET** `/api/blog`

Retrieve a paginated list of published blog posts with filtering and search.

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Page number |
| `per_page` | integer | No | 12 | Items per page (max: 50) |
| `category` | string | No | - | Filter by category slug |
| `tag` | string | No | - | Filter by tag slug |
| `search` | string | No | - | Search in title, excerpt, content |
| `featured` | boolean | No | false | Show only featured posts |

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/blog?page=1&per_page=12&category=technology"
```

#### Success Response (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "title": "Getting Started with Laravel 11",
      "slug": "getting-started-with-laravel-11",
      "excerpt": "A comprehensive guide to building modern web applications with Laravel 11. Learn the fundamentals and best practices.",
      "cover_image": "http://localhost:8000/storage/blog-covers/laravel-11-guide.jpg",
      "published_at": "2026-02-10T10:00:00.000000Z",
      "reading_time": 8,
      "views": 1250,
      "like_count": 45,
      "share_count": 12,
      "author_id": 1,
      "category_id": 1,
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
  ],
  "links": {
    "first": "http://localhost:8000/api/blog?page=1",
    "last": "http://localhost:8000/api/blog?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/blog?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 12,
    "to": 12,
    "total": 58
  }
}
```

---

### 2. Get Single Blog Post (Show Page)

**GET** `/api/blog/{slug}`

Retrieve detailed information about a single blog post by its slug. This endpoint automatically tracks views and returns related posts.

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `slug` | string | Yes | Unique slug identifier of the post |

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/blog/getting-started-with-laravel-11"
```

#### Success Response (200 OK)

```json
{
  "post": {
    "id": 1,
    "title": "Getting Started with Laravel 11",
    "slug": "getting-started-with-laravel-11",
    "excerpt": "A comprehensive guide to building modern web applications with Laravel 11. Learn the fundamentals and best practices.",
    "content": "<h2>Introduction</h2><p>Laravel 11 brings exciting new features...</p><h3>Installation</h3><p>To get started with Laravel 11...</p>",
    "cover_image": "http://localhost:8000/storage/blog-covers/laravel-11-guide.jpg",
    "status": "published",
    "published_at": "2026-02-10T10:00:00.000000Z",
    "reading_time": 8,
    "word_count": 1600,
    "views": 1251,
    "like_count": 45,
    "share_count": 12,
    "comment_count": 8,
    "featured": true,
    "sticky": false,
    "allow_comments": true,
    "author_id": 1,
    "category_id": 1,
    "author": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com"
    },
    "category": {
      "id": 1,
      "name": "Technology",
      "slug": "technology",
      "description": "Latest technology news and tutorials"
    },
    "tags": [
      {
        "id": 1,
        "name": "Laravel",
        "slug": "laravel",
        "post_count": 15
      },
      {
        "id": 2,
        "name": "PHP",
        "slug": "php",
        "post_count": 23
      },
      {
        "id": 3,
        "name": "Web Development",
        "slug": "web-development",
        "post_count": 42
      }
    ],
    "media": [
      {
        "id": 1,
        "type": "image",
        "url": "http://localhost:8000/storage/blog-media/laravel-architecture.png",
        "alt_text": "Laravel Architecture Diagram",
        "caption": "Overview of Laravel's MVC architecture",
        "display_order": 1
      }
    ],
    "seo": {
      "id": 1,
      "meta_title": "Getting Started with Laravel 11 - Complete Guide 2026",
      "meta_description": "Learn Laravel 11 from scratch with this comprehensive guide. Covers installation, routing, controllers, and best practices for modern web development.",
      "focus_keyword": "laravel 11 tutorial",
      "canonical_url": null,
      "og_title": "Getting Started with Laravel 11",
      "og_description": "Complete guide to Laravel 11 for beginners and intermediate developers",
      "og_image": "http://localhost:8000/storage/blog-covers/laravel-11-guide.jpg",
      "twitter_card": "summary_large_image",
      "twitter_title": "Getting Started with Laravel 11",
      "twitter_description": "Complete Laravel 11 tutorial",
      "seo_score": 85
    },
    "schema_markup": {
      "@context": "https://schema.org",
      "@type": "BlogPosting",
      "headline": "Getting Started with Laravel 11",
      "image": "http://localhost:8000/storage/blog-covers/laravel-11-guide.jpg",
      "datePublished": "2026-02-10T10:00:00+00:00",
      "dateModified": "2026-02-10T10:00:00+00:00",
      "author": {
        "@type": "Person",
        "name": "Admin User"
      },
      "publisher": {
        "@type": "Organization",
        "name": "Genie Infotech",
        "logo": {
          "@type": "ImageObject",
          "url": "http://localhost:8000/images/logo.png"
        }
      },
      "description": "A comprehensive guide to building modern web applications with Laravel 11.",
      "wordCount": 1600,
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "http://localhost:8000/blog/getting-started-with-laravel-11"
      }
    },
    "created_at": "2026-02-10T09:00:00.000000Z",
    "updated_at": "2026-02-10T10:00:00.000000Z"
  },
  "related": [
    {
      "id": 5,
      "title": "Advanced Laravel Techniques",
      "slug": "advanced-laravel-techniques",
      "excerpt": "Take your Laravel skills to the next level with these advanced patterns and techniques.",
      "cover_image": "http://localhost:8000/storage/blog-covers/advanced-laravel.jpg",
      "reading_time": 12,
      "views": 890,
      "published_at": "2026-02-08T14:00:00.000000Z"
    },
    {
      "id": 7,
      "title": "Laravel Performance Optimization",
      "slug": "laravel-performance-optimization",
      "excerpt": "Learn how to optimize your Laravel application for maximum performance and scalability.",
      "cover_image": "http://localhost:8000/storage/blog-covers/laravel-performance.jpg",
      "reading_time": 10,
      "views": 654,
      "published_at": "2026-02-05T11:30:00.000000Z"
    },
    {
      "id": 9,
      "title": "Building REST APIs with Laravel",
      "slug": "building-rest-apis-with-laravel",
      "excerpt": "A complete guide to creating robust REST APIs using Laravel and Laravel Sanctum.",
      "cover_image": "http://localhost:8000/storage/blog-covers/laravel-api.jpg",
      "reading_time": 15,
      "views": 1102,
      "published_at": "2026-02-03T09:00:00.000000Z"
    }
  ]
}
```

#### Error Response (404 Not Found)

```json
{
  "message": "No query results for model [App\\Models\\BlogPost]."
}
```

#### Notes
- ✅ View count is automatically incremented on each request
- ✅ Returns related posts based on category and tags
- ✅ Includes SEO metadata and JSON-LD schema
- ✅ Content is in HTML format (already rendered)
- ✅ All images are absolute URLs

---

### 3. Get Featured Posts

**GET** `/api/blog/featured`

Retrieve featured blog posts (manually curated by admin).

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | No | 5 | Number of posts (max: 20) |

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/blog/featured?limit=3"
```

#### Success Response (200 OK)

```json
{
  "data": [
    {
      "id": 5,
      "title": "Advanced Laravel Techniques",
      "slug": "advanced-laravel-techniques",
      "excerpt": "Take your Laravel skills to the next level with these advanced patterns.",
      "cover_image": "http://localhost:8000/storage/blog-covers/advanced-laravel.jpg",
      "published_at": "2026-02-08T14:00:00.000000Z",
      "reading_time": 12,
      "views": 2500,
      "like_count": 89,
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

### 4. Get Recent Posts

**GET** `/api/blog/recent`

Retrieve the most recently published blog posts.

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | No | 5 | Number of posts (max: 20) |

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/blog/recent?limit=5"
```

#### Success Response (200 OK)

```json
{
  "data": [
    {
      "id": 10,
      "title": "New Features in PHP 8.3",
      "slug": "new-features-php-8-3",
      "excerpt": "Explore the latest features and improvements in PHP 8.3.",
      "cover_image": "http://localhost:8000/storage/blog-covers/php-8-3.jpg",
      "published_at": "2026-02-12T15:00:00.000000Z",
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

### 5. Get Popular Posts

**GET** `/api/blog/popular`

Retrieve the most viewed blog posts.

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | No | 10 | Number of posts (max: 50) |

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/blog/popular?limit=10"
```

#### Success Response (200 OK)

```json
{
  "data": [
    {
      "id": 3,
      "title": "Laravel Best Practices 2026",
      "slug": "laravel-best-practices-2026",
      "excerpt": "Comprehensive guide to Laravel best practices and design patterns.",
      "cover_image": "http://localhost:8000/storage/blog-covers/best-practices.jpg",
      "published_at": "2026-01-20T10:00:00.000000Z",
      "reading_time": 10,
      "views": 5890,
      "like_count": 234,
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

### 6. Get Trending Posts

**GET** `/api/blog/trending`

Retrieve trending posts based on recent engagement (views, likes, comments).

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `period` | string | No | week | Time period: `day`, `week`, `month` |
| `limit` | integer | No | 10 | Number of posts (max: 50) |

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/blog/trending?period=week&limit=5"
```

#### Success Response (200 OK)

```json
{
  "data": [
    {
      "id": 12,
      "title": "ChatGPT and AI in Web Development",
      "slug": "chatgpt-ai-web-development",
      "excerpt": "How AI tools are transforming the way we build web applications.",
      "cover_image": "http://localhost:8000/storage/blog-covers/ai-webdev.jpg",
      "published_at": "2026-02-11T08:00:00.000000Z",
      "reading_time": 9,
      "views": 3201,
      "like_count": 156,
      "comment_count": 42,
      "trending_score": 98.5
    }
  ]
}
```

---

## Category Endpoints

### 7. Get All Categories

**GET** `/api/blog/categories`

Retrieve all blog categories with post counts.

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/blog/categories"
```

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Technology",
      "slug": "technology",
      "description": "Latest technology news, tutorials, and insights",
      "order": 1,
      "posts_count": 42,
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-01-01T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Programming",
      "slug": "programming",
      "description": "Programming languages, frameworks, and development tips",
      "order": 2,
      "posts_count": 38,
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-01-01T00:00:00.000000Z"
    },
    {
      "id": 3,
      "name": "Design",
      "slug": "design",
      "description": "UI/UX design, web design, and design systems",
      "order": 3,
      "posts_count": 25,
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-01-01T00:00:00.000000Z"
    },
    {
      "id": 4,
      "name": "Business",
      "slug": "business",
      "description": "Business strategy, entrepreneurship, and growth",
      "order": 4,
      "posts_count": 18,
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-01-01T00:00:00.000000Z"
    }
  ]
}
```

---

### 8. Get Posts by Category

**GET** `/api/blog/category/{slug}`

Retrieve all posts in a specific category with pagination.

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `slug` | string | Yes | Category slug identifier |

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Page number |
| `per_page` | integer | No | 15 | Items per page (max: 50) |

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/blog/category/technology?page=1&per_page=15"
```

#### Success Response (200 OK)

```json
{
  "category": {
    "id": 1,
    "name": "Technology",
    "slug": "technology",
    "description": "Latest technology news, tutorials, and insights",
    "order": 1
  },
  "posts": {
    "data": [
      {
        "id": 1,
        "title": "Getting Started with Laravel 11",
        "slug": "getting-started-with-laravel-11",
        "excerpt": "A comprehensive guide to building modern web applications with Laravel 11.",
        "cover_image": "http://localhost:8000/storage/blog-covers/laravel-11-guide.jpg",
        "published_at": "2026-02-10T10:00:00.000000Z",
        "reading_time": 8,
        "views": 1250,
        "like_count": 45,
        "author": {
          "id": 1,
          "name": "Admin User"
        },
        "tags": [
          {"id": 1, "name": "Laravel", "slug": "laravel"},
          {"id": 2, "name": "PHP", "slug": "php"}
        ]
      }
    ],
    "links": {
      "first": "http://localhost:8000/api/blog/category/technology?page=1",
      "last": "http://localhost:8000/api/blog/category/technology?page=3",
      "prev": null,
      "next": "http://localhost:8000/api/blog/category/technology?page=2"
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 3,
      "per_page": 15,
      "to": 15,
      "total": 42
    }
  }
}
```

#### Error Response (404 Not Found)

```json
{
  "message": "No query results for model [App\\Models\\BlogCategory]."
}
```

---

## Tag Endpoints

### 9. Get All Tags

**GET** `/api/blog/tags`

Retrieve all blog tags with post counts.

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | No | All | Number of tags to return |

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/blog/tags"
```

#### Success Response (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "name": "Laravel",
      "slug": "laravel",
      "description": "Laravel framework articles",
      "post_count": 15,
      "posts_count": 15,
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-02-10T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "PHP",
      "slug": "php",
      "description": "PHP programming language",
      "post_count": 23,
      "posts_count": 23,
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-02-10T00:00:00.000000Z"
    },
    {
      "id": 3,
      "name": "Web Development",
      "slug": "web-development",
      "description": "General web development topics",
      "post_count": 42,
      "posts_count": 42,
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-02-10T00:00:00.000000Z"
    },
    {
      "id": 4,
      "name": "JavaScript",
      "slug": "javascript",
      "description": "JavaScript and frontend frameworks",
      "post_count": 31,
      "posts_count": 31,
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-02-10T00:00:00.000000Z"
    }
  ]
}
```

---

### 10. Get Posts by Tag

**GET** `/api/blog/tag/{slug}`

Retrieve all posts with a specific tag.

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `slug` | string | Yes | Tag slug identifier |

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Page number |
| `per_page` | integer | No | 15 | Items per page (max: 50) |

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/blog/tag/laravel?page=1"
```

#### Success Response (200 OK)

```json
{
  "tag": {
    "id": 1,
    "name": "Laravel",
    "slug": "laravel",
    "description": "Laravel framework articles",
    "post_count": 15
  },
  "posts": {
    "data": [
      {
        "id": 1,
        "title": "Getting Started with Laravel 11",
        "slug": "getting-started-with-laravel-11",
        "excerpt": "A comprehensive guide to building modern web applications with Laravel 11.",
        "cover_image": "http://localhost:8000/storage/blog-covers/laravel-11-guide.jpg",
        "published_at": "2026-02-10T10:00:00.000000Z",
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
    "links": {
      "first": "http://localhost:8000/api/blog/tag/laravel?page=1",
      "last": "http://localhost:8000/api/blog/tag/laravel?page=2",
      "prev": null,
      "next": "http://localhost:8000/api/blog/tag/laravel?page=2"
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 2,
      "per_page": 15,
      "to": 15,
      "total": 15
    }
  }
}
```

---

## Comment Endpoints

### 11. Get Post Comments

**GET** `/api/blog/{slug}/comments`

Retrieve all approved comments for a specific blog post, including nested replies.

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `slug` | string | Yes | Blog post slug |

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/blog/getting-started-with-laravel-11/comments"
```

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "content": "Great article! Very helpful for beginners. I followed the steps and got my first Laravel app running in minutes.",
      "author_name": "John Doe",
      "is_registered_user": true,
      "created_at": "2026-02-11T14:30:00.000000Z",
      "replies": [
        {
          "id": 3,
          "content": "Thank you for your feedback! Glad it was helpful.",
          "author_name": "Admin User",
          "is_registered_user": true,
          "created_at": "2026-02-11T15:00:00.000000Z"
        },
        {
          "id": 5,
          "content": "I had the same experience! Laravel is amazing once you get started.",
          "author_name": "Jane Smith",
          "is_registered_user": true,
          "created_at": "2026-02-11T16:20:00.000000Z"
        }
      ]
    },
    {
      "id": 2,
      "content": "Could you cover more about Laravel's Eloquent ORM in a future post?",
      "author_name": "Mike Johnson",
      "is_registered_user": false,
      "created_at": "2026-02-11T18:45:00.000000Z",
      "replies": [
        {
          "id": 4,
          "content": "Great suggestion! I'll add that to my content calendar.",
          "author_name": "Admin User",
          "is_registered_user": true,
          "created_at": "2026-02-12T09:00:00.000000Z"
        }
      ]
    },
    {
      "id": 6,
      "content": "This is exactly what I needed to start my Laravel journey. Thanks!",
      "author_name": "Sarah Wilson",
      "is_registered_user": true,
      "created_at": "2026-02-12T11:30:00.000000Z",
      "replies": []
    }
  ]
}
```

#### Notes
- ✅ Only approved comments are returned
- ✅ Comments are nested (parent → replies structure)
- ✅ Root-level comments are ordered by oldest first
- ✅ Includes flag for registered vs guest users

---

### 12. Create Comment

**POST** `/api/blog/{slug}/comments`

Submit a new comment on a blog post. Supports both authenticated users and guests. Comments require moderation before appearing publicly.

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `slug` | string | Yes | Blog post slug |

#### Request Body (Authenticated User)

```json
{
  "content": "This is an excellent tutorial! Very clear and easy to follow.",
  "parent_id": null
}
```

#### Request Body (Guest User)

```json
{
  "content": "Great article! Thanks for sharing.",
  "name": "John Doe",
  "email": "john@example.com",
  "parent_id": null
}
```

#### Body Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `content` | string | Yes | Comment text (3-1000 characters) |
| `parent_id` | integer | No | ID of parent comment (for replies) |
| `name` | string | Guest only | Commenter's name (max 255 chars) |
| `email` | string | Guest only | Commenter's email |

#### Example Request (Authenticated)

```bash
curl -X POST "http://localhost:8000/api/blog/getting-started-with-laravel-11/comments" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Excellent tutorial! Very clear explanations.",
    "parent_id": null
  }'
```

#### Example Request (Guest)

```bash
curl -X POST "http://localhost:8000/api/blog/getting-started-with-laravel-11/comments" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Great article! Thanks for sharing.",
    "name": "John Doe",
    "email": "john@example.com",
    "parent_id": null
  }'
```

#### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Comment submitted successfully and is pending moderation",
  "data": {
    "id": 15,
    "content": "Excellent tutorial! Very clear explanations.",
    "author_name": "John Doe",
    "status": "pending",
    "created_at": "2026-02-14T10:30:00.000000Z"
  }
}
```

#### Error Response (422 Validation Error)

```json
{
  "message": "The content field is required.",
  "errors": {
    "content": [
      "The content field is required."
    ],
    "email": [
      "The email field must be a valid email address."
    ]
  }
}
```

#### Error Response (403 Forbidden - Comments Disabled)

```json
{
  "message": "Comments are disabled for this post"
}
```

#### Notes
- ✅ Comments require moderation (status: pending)
- ✅ Guest comments require name and email
- ✅ Authenticated users only need content
- ✅ Supports nested replies via parent_id
- ✅ IP address is recorded automatically

---

## Analytics & Tracking

### 13. Like a Post

**POST** `/api/blog/{slug}/like`

Like a blog post. Increments the like counter.

**Authentication:** Required

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `slug` | string | Yes | Blog post slug |

#### Example Request

```bash
curl -X POST "http://localhost:8000/api/blog/getting-started-with-laravel-11/like" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Success Response (200 OK)

```json
{
  "message": "Post liked successfully",
  "likes_count": 46
}
```

#### Error Response (401 Unauthorized)

```json
{
  "message": "Unauthenticated."
}
```

#### Notes
- ✅ Requires authentication
- ✅ Can be called multiple times (increments each time)
- ✅ No unlike functionality (simple counter)

---

### 14. Share a Post

**POST** `/api/blog/{slug}/share`

Track when a post is shared. Increments the share counter.

**Authentication:** Not required

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `slug` | string | Yes | Blog post slug |

#### Example Request

```bash
curl -X POST "http://localhost:8000/api/blog/getting-started-with-laravel-11/share"
```

#### Success Response (200 OK)

```json
{
  "message": "Share tracked successfully",
  "shares_count": 13
}
```

#### Notes
- ✅ No authentication required
- ✅ Call this endpoint when user shares via social media
- ✅ Simple counter increment

---

## Utility Endpoints

### 15. Blog Sitemap

**GET** `/api/blog/sitemap`

Generate XML sitemap of all published blog posts for SEO.

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/blog/sitemap"
```

#### Success Response (200 OK)

**Content-Type:** `application/xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>http://localhost:8000/blog/getting-started-with-laravel-11</loc>
    <lastmod>2026-02-10T10:00:00+00:00</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
  <url>
    <loc>http://localhost:8000/blog/advanced-laravel-techniques</loc>
    <lastmod>2026-02-08T14:00:00+00:00</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
</urlset>
```

#### Notes
- ✅ Returns all published posts
- ✅ Includes last modified date
- ✅ SEO-friendly format
- ✅ Submit to Google Search Console

---

### 16. RSS Feed

**GET** `/api/blog/rss`

Generate RSS feed of the 20 most recent blog posts.

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/blog/rss"
```

#### Success Response (200 OK)

**Content-Type:** `application/xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>Laravel Blog</title>
    <link>http://localhost:8000/blog</link>
    <description>Latest blog posts from Laravel</description>
    <language>en-us</language>
    <item>
      <title><![CDATA[Getting Started with Laravel 11]]></title>
      <link>http://localhost:8000/blog/getting-started-with-laravel-11</link>
      <description><![CDATA[A comprehensive guide to building modern web applications with Laravel 11.]]></description>
      <author>admin@example.com (Admin User)</author>
      <category>Technology</category>
      <pubDate>Mon, 10 Feb 2026 10:00:00 +0000</pubDate>
      <guid>http://localhost:8000/blog/getting-started-with-laravel-11</guid>
    </item>
  </channel>
</rss>
```

#### Notes
- ✅ Returns 20 most recent posts
- ✅ Standard RSS 2.0 format
- ✅ Subscribe in RSS readers

---

## Data Models

### BlogPost Model

Complete structure of a blog post object.

```typescript
interface BlogPost {
  // Core Fields
  id: number;
  title: string;
  slug: string;
  excerpt: string;
  content: string; // HTML content
  cover_image: string | null; // Full URL
  
  // Status & Publishing
  status: 'draft' | 'published' | 'archived';
  published_at: string | null; // ISO 8601 datetime
  scheduled_for: string | null; // ISO 8601 datetime
  
  // Flags
  featured: boolean;
  sticky: boolean;
  allow_comments: boolean;
  
  // Metadata
  reading_time: number; // Minutes
  word_count: number;
  
  // Analytics
  views: number;
  like_count: number;
  share_count: number;
  comment_count: number;
  
  // Foreign Keys
  author_id: number;
  category_id: number;
  last_modified_by: number | null;
  
  // Timestamps
  created_at: string; // ISO 8601
  updated_at: string; // ISO 8601
  
  // Relationships
  author?: Author;
  category?: Category;
  tags?: Tag[];
  comments?: Comment[];
  media?: Media[];
  seo?: SEO;
  schema_markup?: SchemaMarkup;
}
```

### Author Model

```typescript
interface Author {
  id: number;
  name: string;
  email: string;
}
```

### Category Model

```typescript
interface Category {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  order: number;
  posts_count?: number;
  created_at: string;
  updated_at: string;
}
```

### Tag Model

```typescript
interface Tag {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  post_count: number;
  posts_count?: number; // Same as post_count
  created_at: string;
  updated_at: string;
}
```

### Comment Model

```typescript
interface Comment {
  id: number;
  content: string;
  author_name: string;
  is_registered_user: boolean;
  created_at: string;
  replies: Comment[]; // Nested replies
  
  // Full model fields
  post_id?: number;
  user_id?: number | null;
  parent_id?: number | null;
  author_email?: string | null;
  author_ip?: string;
  status?: 'pending' | 'approved' | 'rejected';
  approved_at?: string | null;
}
```

### Media Model

```typescript
interface Media {
  id: number;
  type: 'image' | 'video' | 'document';
  url: string; // Full URL
  alt_text: string | null;
  caption: string | null;
  display_order: number;
}
```

### SEO Model

```typescript
interface SEO {
  id: number;
  post_id: number;
  
  // Meta Tags
  meta_title: string | null;
  meta_description: string | null;
  focus_keyword: string | null;
  canonical_url: string | null;
  
  // Open Graph
  og_title: string | null;
  og_description: string | null;
  og_image: string | null;
  og_type: string | null;
  
  // Twitter Card
  twitter_card: string | null;
  twitter_title: string | null;
  twitter_description: string | null;
  twitter_image: string | null;
  
  // Analytics
  seo_score: number | null; // 0-100
  
  // Timestamps
  created_at: string;
  updated_at: string;
}
```

### Schema Markup

```typescript
interface SchemaMarkup {
  '@context': 'https://schema.org';
  '@type': 'BlogPosting';
  headline: string;
  image: string;
  datePublished: string;
  dateModified: string;
  author: {
    '@type': 'Person';
    name: string;
  };
  publisher: {
    '@type': 'Organization';
    name: string;
    logo: {
      '@type': 'ImageObject';
      url: string;
    };
  };
  description: string;
  wordCount: number;
  mainEntityOfPage: {
    '@type': 'WebPage';
    '@id': string;
  };
}
```

---

## UI Implementation Guide

### Blog Show Page Requirements

When building the blog post show page UI, include these elements:

#### 1. Header Section
- **Cover Image**: Full-width hero image
- **Category Badge**: Linked to category page
- **Title**: H1 heading (use `meta_title` if available, else `title`)
- **Publication Date**: Format: "February 10, 2026"
- **Reading Time**: Format: "8 min read"
- **Author Info**: Avatar, name, and link to author page

#### 2. Main Content
- **HTML Content**: Render `content` field as HTML
- **Table of Contents**: Auto-generate from H2/H3 tags
- **Code Syntax Highlighting**: Use Prism.js or Highlight.js
- **Responsive Images**: All images should be responsive

#### 3. Post Meta
- **Tags**: Render as clickable badges linked to tag pages
- **View Count**: Display with icon
- **Like Button**: Authenticated users only
- **Share Buttons**: Social media share (Twitter, Facebook, LinkedIn)
  - Call `/api/blog/{slug}/share` on click

#### 4. Engagement Section
- **Like Button**: Heart icon with count
- **Share Count**: Display total shares
- **Social Share Buttons**:
  - Twitter
  - Facebook
  - LinkedIn
  - Copy Link

#### 5. Comments Section
- **Comment Count**: Display total approved comments
- **Comment List**: Nested structure with replies
- **Comment Form**: 
  - Show for authenticated users (name/email pre-filled)
  - Show for guests (require name and email)
  - Handle moderation message after submission
- **Reply Functionality**: Quote parent comment

#### 6. Sidebar/Related Content
- **Related Posts**: Show `related` array returned from show endpoint
- **Popular Posts**: Fetch from `/api/blog/popular?limit=5`
- **Categories**: Fetch from `/api/blog/categories`
- **Tags Cloud**: Fetch from `/api/blog/tags`

#### 7. SEO Implementation
- **Meta Tags**: Use `seo.meta_title`, `seo.meta_description`
- **Open Graph**: Use `seo.og_*` fields
- **Twitter Cards**: Use `seo.twitter_*` fields
- **Schema Markup**: Insert `schema_markup` in `<script type="application/ld+json">`
- **Canonical URL**: Use `seo.canonical_url` if present

### Example React/Next.js Blog Show Page

```typescript
// pages/blog/[slug].tsx
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import Head from 'next/head';

interface BlogPostResponse {
  post: BlogPost;
  related: BlogPost[];
}

export default function BlogPostPage() {
  const router = useRouter();
  const { slug } = router.query;
  const [data, setData] = useState<BlogPostResponse | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (slug) {
      fetch(`http://localhost:8000/api/blog/${slug}`)
        .then(res => res.json())
        .then(setData)
        .finally(() => setLoading(false));
    }
  }, [slug]);

  if (loading) return <div>Loading...</div>;
  if (!data) return <div>Post not found</div>;

  const { post, related } = data;

  return (
    <>
      <Head>
        <title>{post.seo?.meta_title || post.title}</title>
        <meta name="description" content={post.seo?.meta_description || post.excerpt} />
        
        {/* Open Graph */}
        <meta property="og:title" content={post.seo?.og_title || post.title} />
        <meta property="og:description" content={post.seo?.og_description || post.excerpt} />
        <meta property="og:image" content={post.seo?.og_image || post.cover_image} />
        
        {/* Twitter Card */}
        <meta name="twitter:card" content={post.seo?.twitter_card || "summary_large_image"} />
        <meta name="twitter:title" content={post.seo?.twitter_title || post.title} />
        
        {/* Schema Markup */}
        <script type="application/ld+json">
          {JSON.stringify(post.schema_markup)}
        </script>
      </Head>

      <article className="blog-post">
        {/* Cover Image */}
        {post.cover_image && (
          <img src={post.cover_image} alt={post.title} className="cover-image" />
        )}

        {/* Header */}
        <header>
          <div className="meta">
            <span className="category">{post.category?.name}</span>
            <time>{new Date(post.published_at).toLocaleDateString()}</time>
            <span>{post.reading_time} min read</span>
          </div>
          <h1>{post.title}</h1>
          <div className="author">
            <span>{post.author?.name}</span>
          </div>
        </header>

        {/* Content */}
        <div 
          className="content"
          dangerouslySetInnerHTML={{ __html: post.content }}
        />

        {/* Tags */}
        <div className="tags">
          {post.tags?.map(tag => (
            <a key={tag.id} href={`/blog/tag/${tag.slug}`}>
              #{tag.name}
            </a>
          ))}
        </div>

        {/* Engagement */}
        <div className="engagement">
          <button onClick={handleLike}>
            ❤️ {post.like_count}
          </button>
          <button onClick={handleShare}>
            🔗 {post.share_count}
          </button>
        </div>

        {/* Comments */}
        <CommentsSection postSlug={slug as string} />

        {/* Related Posts */}
        <section className="related-posts">
          <h2>Related Articles</h2>
          <div className="grid">
            {related.map(relatedPost => (
              <PostCard key={relatedPost.id} post={relatedPost} />
            ))}
          </div>
        </section>
      </article>
    </>
  );
}

async function handleLike(slug: string) {
  const token = localStorage.getItem('auth_token');
  const response = await fetch(`http://localhost:8000/api/blog/${slug}/like`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  const data = await response.json();
  // Update UI with new like count
}

async function handleShare(slug: string) {
  await fetch(`http://localhost:8000/api/blog/${slug}/share`, {
    method: 'POST'
  });
  // Show share dialog or copy link
}
```

### Mobile Responsiveness

- **Breakpoints**:
  - Mobile: < 640px
  - Tablet: 640px - 1024px
  - Desktop: > 1024px

- **Mobile Optimizations**:
  - Stack sidebar below content
  - Full-width images
  - Larger touch targets (min 44x44px)
  - Collapsible table of contents
  - Bottom sheet for share menu

### Performance Optimization

1. **Image Optimization**: Use Next.js Image component or lazy loading
2. **Code Splitting**: Lazy load comments section
3. **Caching**: Cache API responses (5 minutes for posts, 1 hour for categories/tags)
4. **Prefetching**: Prefetch related posts on hover

---

## Testing the API

### Using cURL

```bash
# Get all posts
curl -X GET "http://localhost:8000/api/blog"

# Get single post
curl -X GET "http://localhost:8000/api/blog/getting-started-with-laravel-11"

# Get comments
curl -X GET "http://localhost:8000/api/blog/getting-started-with-laravel-11/comments"

# Post comment (authenticated)
curl -X POST "http://localhost:8000/api/blog/getting-started-with-laravel-11/comments" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content": "Great post!"}'

# Like post
curl -X POST "http://localhost:8000/api/blog/getting-started-with-laravel-11/like" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Using Postman

1. Import the API endpoints as a collection
2. Set base URL as environment variable
3. Configure Bearer token for authenticated requests
4. Test all endpoints

### Using JavaScript Fetch

```javascript
// Get single post
const getPost = async (slug) => {
  const response = await fetch(`http://localhost:8000/api/blog/${slug}`);
  const data = await response.json();
  return data;
};

// Post comment
const postComment = async (slug, content, token = null) => {
  const headers = {
    'Content-Type': 'application/json'
  };
  
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }
  
  const response = await fetch(
    `http://localhost:8000/api/blog/${slug}/comments`,
    {
      method: 'POST',
      headers,
      body: JSON.stringify({ content })
    }
  );
  
  return await response.json();
};
```

---

## Common Use Cases

### 1. Blog Listing Page

```javascript
// Fetch paginated posts
const response = await fetch(
  'http://localhost:8000/api/blog?page=1&per_page=12'
);
const { data, meta } = await response.json();

// Display posts in grid
// Implement pagination using meta object
```

### 2. Blog Show Page

```javascript
// Fetch post details
const { post, related } = await fetch(
  `http://localhost:8000/api/blog/${slug}`
).then(r => r.json());

// Display post content
// Show related posts sidebar
// Load comments separately for better performance
```

### 3. Category Page

```javascript
// Fetch category and posts
const { category, posts } = await fetch(
  `http://localhost:8000/api/blog/category/${categorySlug}?page=1`
).then(r => r.json());

// Display category header
// List posts with pagination
```

### 4. Search/Filter

```javascript
// Search posts
const response = await fetch(
  `http://localhost:8000/api/blog?search=${query}&category=${category}`
);

// Filter by multiple criteria
const response = await fetch(
  'http://localhost:8000/api/blog?' +
  new URLSearchParams({
    category: 'technology',
    tag: 'laravel',
    featured: 'true'
  })
);
```

### 5. Homepage Featured Content

```javascript
// Fetch featured posts
const featured = await fetch(
  'http://localhost:8000/api/blog/featured?limit=3'
).then(r => r.json());

// Fetch recent posts
const recent = await fetch(
  'http://localhost:8000/api/blog/recent?limit=5'
).then(r => r.json());

// Display in hero section and sidebar
```

---

## Appendix

### A. Date Formatting

All dates are in ISO 8601 format:
```
2026-02-10T10:00:00.000000Z
```

Parse in JavaScript:
```javascript
const date = new Date(post.published_at);
const formatted = date.toLocaleDateString('en-US', {
  year: 'numeric',
  month: 'long',
  day: 'numeric'
});
// Output: "February 10, 2026"
```

### B. Image URLs

All image URLs are absolute and include the base URL:
```
http://localhost:8000/storage/blog-covers/image.jpg
```

In production, these will use your production domain.

### C. Pagination Best Practices

- Use `meta.last_page` to determine total pages
- Use `links.next` for "Load More" functionality
- Use `meta.total` for "Showing X of Y results"
- Sensible default: 12 posts per page for grids, 15 for lists

### D. Error Handling Best Practices

```javascript
try {
  const response = await fetch(`/api/blog/${slug}`);
  
  if (!response.ok) {
    if (response.status === 404) {
      // Show 404 page
    } else if (response.status === 429) {
      // Show rate limit error
    } else {
      // Show generic error
    }
  }
  
  const data = await response.json();
  return data;
} catch (error) {
  // Network error
  console.error('Failed to fetch post:', error);
}
```

---

## Support

For issues or questions about the API:
- **Email**: support@genie-infotech.com
- **Documentation**: This file
- **API Status**: Check `/api/health` endpoint

---

**Last Updated**: February 14, 2026  
**API Version**: 1.0  
**Laravel Version**: 11.x
