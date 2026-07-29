<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Models\Banner;
use App\Services\BannerFormUploadCache;
use App\Services\FileUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BannerController extends Controller
{
    public function __construct(
        private FileUploadService $uploader,
        private BannerFormUploadCache $uploadCache,
    ) {}

    public function index(Request $request): View
    {
        $query = Banner::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $banners = $query->orderBy('sort_order')->orderBy('id')->paginate(20)->withQueryString();

        return view('admin.banners.index', compact('banners'));
    }

    public function create(): View
    {
        if (! session()->hasOldInput()) {
            $this->uploadCache->clear();
        }

        return view('admin.banners.create', ['banner' => new Banner]);
    }

    public function store(BannerRequest $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['image'] = $this->resolveImage($request);

        Banner::create($data);
        $this->uploadCache->clear();

        return redirect()->route('admin.banners.index')->with('success', 'بنر ایجاد شد.');
    }

    public function edit(Banner $banner): View
    {
        if (! session()->hasOldInput()) {
            $this->uploadCache->clear();
        }

        return view('admin.banners.edit', compact('banner'));
    }

    public function update(BannerRequest $request, Banner $banner): RedirectResponse
    {
        $data = $this->validated($request);

        if ($image = $this->resolveImage($request, $banner)) {
            if ($banner->image && $image !== $banner->image) {
                $this->uploader->delete($banner->image);
            }

            $data['image'] = $image;
        }

        $banner->update($data);
        $this->uploadCache->clear();

        return redirect()->route('admin.banners.index')->with('success', 'بنر ویرایش شد.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $this->uploader->delete($banner->image);
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'بنر حذف شد.');
    }

    public function formUpload(string $key): Response
    {
        $path = $this->uploadCache->getPathByKey($key);

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    private function validated(BannerRequest $request): array
    {
        $validated = $request->validated();

        return collect($validated)->except(['image', 'cached_image'])->all();
    }

    private function resolveImage(BannerRequest $request, ?Banner $banner = null): ?string
    {
        if ($request->hasFile('image')) {
            if ($banner?->image) {
                return $this->uploader->replace($request->file('image'), $banner->image, 'banner');
            }

            return $this->uploader->upload($request->file('image'), 'banner');
        }

        $cachedKey = $request->input('cached_image');

        if (is_string($cachedKey) && $cachedKey !== '' && $this->uploadCache->getPathByKey($cachedKey)) {
            return $this->uploadCache->promote($this->uploader);
        }

        return $banner?->image;
    }
}
