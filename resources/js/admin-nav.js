export function initAdminNavSearch() {
    const navSearch = document.getElementById('admin-nav-search');
    const nav = document.getElementById('admin-nav');

    if (!navSearch || !nav) {
        return;
    }

    const navItems = nav.querySelectorAll('.admin-nav-item');

    navSearch.addEventListener('input', (event) => {
        const query = event.target.value.trim().toLowerCase();

        navItems.forEach((item) => {
            const label = (item.dataset.navLabel || item.textContent || '').toLowerCase();
            item.classList.toggle('admin-nav-item-hidden', query.length > 0 && !label.includes(query));
        });

        nav.querySelectorAll('.admin-nav-group-label').forEach((group) => {
            let sibling = group.nextElementSibling;

            while (sibling && !sibling.classList.contains('admin-nav-group-label')) {
                if (sibling.classList.contains('admin-nav-item') && !sibling.classList.contains('admin-nav-item-hidden')) {
                    group.style.display = '';

                    return;
                }

                sibling = sibling.nextElementSibling;
            }

            group.style.display = query.length > 0 ? 'none' : '';
        });
    });
}
