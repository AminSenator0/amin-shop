<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FooterLicenseRequest;
use App\Models\FooterLicense;
use App\Services\FileUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class FooterLicenseController extends Controller
{
    public function __construct(private FileUploadService $uploader) {}

    public function index(): View
    {
        $licenses = FooterLicense::query()->orderBy('sort_order')->paginate(20);

        return view('admin.footer-licenses.index', compact('licenses'));
    }

    public function create(): View
    {
        return view('admin.footer-licenses.create', ['license' => new FooterLicense(['is_active' => true])]);
    }

    public function store(FooterLicenseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $license = FooterLicense::create([
            'title'      => $validated['title'] ?? null,
            'image'      => $this->uploader->upload($request->file('image'), 'footer-license'),
            'link'       => $validated['link'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active'  => $request->boolean('is_active'),
        ]);

        Cache::forget('footer_licenses');

        return redirect()
            ->route('admin.footer-licenses.edit', $license)
            ->with('success', 'مجوز ایجاد شد.');
    }

    public function edit(FooterLicense $footerLicense): View
    {
        return view('admin.footer-licenses.edit', ['license' => $footerLicense]);
    }

    public function update(FooterLicenseRequest $request, FooterLicense $footerLicense): RedirectResponse
    {
        $validated = $request->validated();

        $image = $footerLicense->image;
        if ($request->hasFile('image')) {
            $image = $this->uploader->replace($request->file('image'), $footerLicense->image, 'footer-license');
        }

        $footerLicense->update([
            'title'      => $validated['title'] ?? null,
            'image'      => $image,
            'link'       => $validated['link'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active'  => $request->boolean('is_active'),
        ]);

        Cache::forget('footer_licenses');

        return redirect()
            ->route('admin.footer-licenses.edit', $footerLicense)
            ->with('success', 'مجوز به‌روزرسانی شد.');
    }

    public function destroy(FooterLicense $footerLicense): RedirectResponse
    {
        $this->uploader->delete($footerLicense->image);
        $footerLicense->delete();

        Cache::forget('footer_licenses');

        return redirect()
            ->route('admin.footer-licenses.index')
            ->with('success', 'مجوز حذف شد.');
    }
}