<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AddressRequest;
use App\Models\Address;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = auth()->user()->addresses()->latest()->get();

        return view('user.addresses.index', compact('addresses'));
    }

    public function create()
    {
        return view('user.addresses.create');
    }

    public function store(AddressRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data) {
            if ($request->boolean('is_default')) {
                auth()->user()->addresses()->update(['is_default' => false]);
            }

            auth()->user()->addresses()->create($data);
        });

        return redirect()->route('user.addresses.index')->with('success', 'آدرس با موفقیت ثبت شد.');
    }

    public function edit(Address $address)
    {
        $this->authorizeAddress($address);

        return view('user.addresses.edit', compact('address'));
    }

    public function update(AddressRequest $request, Address $address)
    {
        $this->authorizeAddress($address);

        $data = $request->validated();

        DB::transaction(function () use ($request, $address, $data) {
            if ($request->boolean('is_default')) {
                auth()->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update($data);
        });

        return redirect()->route('user.addresses.index')->with('success', 'آدرس به‌روزرسانی شد.');
    }

    public function destroy(Address $address)
    {
        $this->authorizeAddress($address);
        $address->delete();

        return redirect()->route('user.addresses.index')->with('success', 'آدرس حذف شد.');
    }

    private function authorizeAddress(Address $address): void
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
