<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogPostRequest;
use App\Models\BlogPost;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogPostController extends Controller
{
    public function __construct(private FileUploadService $uploader) {}

    public function index(Request $request)
    {
        $query = BlogPost::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $posts = $query->orderByDesc('published_at')->paginate(15)->withQueryString();

        return view('admin.blog-posts.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.blog-posts.create');
    }

    public function store(BlogPostRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['title']);
        $data['is_published'] = $request->boolean('is_published', true);
        $data['published_at'] = $request->input('published_at_parsed') ?? now();
        $data['sort_order'] = $request->input('sort_order', 0);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploader->store($request->file('image'), 'blog_image');
        }

        unset($data['remove_image']);
        BlogPost::create($data);

        return redirect()->route('admin.blog-posts.index')->with('success', 'مقاله ایجاد شد.');
    }

    public function edit(BlogPost $blogPost)
    {
        return view('admin.blog-posts.edit', ['post' => $blogPost]);
    }

    public function update(BlogPostRequest $request, BlogPost $blogPost)
    {
        $data = $request->validated();
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['title'], $blogPost->id);
        $data['is_published'] = $request->boolean('is_published');
        $data['sort_order'] = $request->input('sort_order', 0);

        if ($request->input('published_at_parsed')) {
            $data['published_at'] = $request->input('published_at_parsed');
        } elseif (! $request->filled('published_at') && $data['is_published'] && ! $blogPost->published_at) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploader->replace($request->file('image'), $blogPost->image, 'blog_image');
        } elseif ($request->boolean('remove_image') && $blogPost->image) {
            $this->uploader->delete($blogPost->image);
            $data['image'] = null;
        }

        unset($data['remove_image']);
        $blogPost->update($data);

        return redirect()->route('admin.blog-posts.index')->with('success', 'مقاله به‌روزرسانی شد.');
    }

    public function destroy(BlogPost $blogPost)
    {
        if ($blogPost->image) {
            $this->uploader->delete($blogPost->image);
        }

        $blogPost->delete();

        return redirect()->route('admin.blog-posts.index')->with('success', 'مقاله حذف شد.');
    }

    private function resolveSlug(?string $slug, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $title) ?: 'post-'.time();
        $candidate = $base;
        $i = 1;

        while (
            BlogPost::where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $base.'-'.$i++;
        }

        return $candidate;
    }
}
