<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HeroBannerRequest;
use App\Models\HeroBanner;
use App\Services\FileUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HeroBannerController extends Controller
{
    public function __construct(private FileUploadService $uploader) {}

    public function index(): View
    {
        $banners = HeroBanner::query()->orderBy('sort_order')->paginate(20);

        return view('admin.hero-banners.index', compact('banners'));
    }

    public function create(): View
    {
        return view('admin.hero-banners.create', ['banner' => new HeroBanner(['is_active' => true])]);
    }

    public function store(HeroBannerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $banner = HeroBanner::create([
            'title'      => $validated['title'],
            'image'      => $this->uploader->upload($request->file('image'), 'hero-banner'),
            'link'       => $validated['link'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active'  => $request->boolean('is_active'),
        ]);

        Cache::forget('home_hero_banners');

        return redirect()
            ->route('admin.hero-banners.edit', $banner)
            ->with('success', 'بنر هیرو ایجاد شد.');
    }

    public function edit(HeroBanner $heroBanner): View
    {
        return view('admin.hero-banners.edit', ['banner' => $heroBanner]);
    }

    public function update(HeroBannerRequest $request, HeroBanner $heroBanner): RedirectResponse
    {
        $validated = $request->validated();

        $image = $heroBanner->image;
        if ($request->hasFile('image')) {
            $image = $this->uploader->replace($request->file('image'), $heroBanner->image, 'hero-banner');
        }

        $heroBanner->update([
            'title'      => $validated['title'],
            'image'      => $image,
            'link'       => $validated['link'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active'  => $request->boolean('is_active'),
        ]);

        Cache::forget('home_hero_banners');

        return redirect()
            ->route('admin.hero-banners.edit', $heroBanner)
            ->with('success', 'بنر هیرو به‌روزرسانی شد.');
    }

    public function destroy(HeroBanner $heroBanner): RedirectResponse
    {
        $this->uploader->delete($heroBanner->image);
        $heroBanner->delete();

        Cache::forget('home_hero_banners');

        return redirect()
            ->route('admin.hero-banners.index')
            ->with('success', 'بنر هیرو حذف شد.');
    }
}