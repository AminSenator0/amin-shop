<section class="space-y-6">
    <header>
        <h2 class="text-lg font-bold text-shop-text">حذف حساب</h2>
        <p class="mt-1 text-sm text-shop-muted">پس از حذف حساب، تمام اطلاعات شما به‌طور دائمی پاک می‌شود.</p>
    </header>

    <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">حذف حساب</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable maxWidth="md">
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6" data-no-delete-confirm>
            @csrf
            @method('delete')

            <div class="text-center">
                <div class="app-modal-danger-icon">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-shop-text">آیا از حذف حساب مطمئن هستید؟</h2>
                <p class="mt-2 text-sm leading-6 text-shop-muted">برای تأیید، رمز عبور خود را وارد کنید.</p>
            </div>

            <div class="mt-6">
                <x-input-label for="password" value="رمز عبور" class="sr-only" />
                <x-text-input id="password" name="password" type="password" class="input-shop mt-1 block w-full" placeholder="رمز عبور" />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 -mx-6 -mb-6 flex flex-col items-stretch justify-center gap-2 border-t border-shop-border/60 bg-shop-background px-6 py-4 sm:flex-row sm:items-center sm:justify-center sm:gap-3">
                <x-secondary-button x-on:click="$dispatch('close')" class="w-full justify-center !rounded-xl sm:w-auto sm:min-w-[7.5rem]">انصراف</x-secondary-button>
                <x-danger-button class="w-full justify-center !rounded-xl sm:w-auto sm:min-w-[7.5rem]">حذف حساب</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
