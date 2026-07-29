<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SliderRequest;
use App\Models\Slider;
use App\Services\FileUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SliderController extends Controller
{
    public function __construct(private FileUploadService $uploader) {}

    public function index(Request $request): View
    {
        $query = Slider::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $sliders = $query->orderBy('sort_order')->orderBy('id')->paginate(20)->withQueryString();

        return view('admin.sliders.index', compact('sliders'));
    }

    public function create(): View
    {
        return view('admin.sliders.create', ['slider' => new Slider]);
    }

    public function store(SliderRequest $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploader->upload($request->file('image'), 'slider');
        }

        Slider::create($data);

        return redirect()->route('admin.sliders.index')->with('success', 'اسلاید ایجاد شد.');
    }

    public function edit(Slider $slider): View
    {
        return view('admin.sliders.edit', compact('slider'));
    }

    public function update(SliderRequest $request, Slider $slider): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploader->replace($request->file('image'), $slider->image, 'slider');
        }

        $slider->update($data);

        return redirect()->route('admin.sliders.index')->with('success', 'اسلاید ویرایش شد.');
    }

    public function destroy(Slider $slider): RedirectResponse
    {
        $this->uploader->delete($slider->image);
        $slider->delete();

        return redirect()->route('admin.sliders.index')->with('success', 'اسلاید حذف شد.');
    }

    private function validated(SliderRequest $request): array
    {
        $validated = $request->validated();

        return collect($validated)->except('image')->all();
    }
}
