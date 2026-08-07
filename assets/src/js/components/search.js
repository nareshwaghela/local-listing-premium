export default () => ({
    query: '',
    category: '',
    location: '',
    lat: null,
    lng: null,
    radius: 10,
    sort: 'date',
    page: 1,
    perPage: 12,
    listings: [],
    total: 0,
    pages: 0,
    loading: false,
    initialized: false,

    init() {
        // Read URL params on load
        const params = new URLSearchParams(window.location.search);
        this.query = params.get('q') || '';
        this.category = params.get('category') || '';
        this.location = params.get('location') || '';
        this.radius = parseInt(params.get('radius')) || 10;
        this.sort = params.get('sort') || 'date';
        this.page = parseInt(params.get('page')) || 1;

        // Debounced search on input
        this.$watch('query', () => this.debouncedSearch());
        this.$watch('category', () => this.search());
        this.$watch('location', () => this.search());
        this.$watch('sort', () => this.search());

        // Initial search if params exist
        if (this.query || this.category || this.location) {
            this.search();
        }
        this.initialized = true;
    },

    debouncedSearch() {
        clearTimeout(this._debounceTimer);
        this._debounceTimer = setTimeout(() => this.search(), 300);
    },

    async search(resetPage = true) {
        if (!this.initialized) return;
        if (resetPage) this.page = 1;

        this.loading = true;
        this.updateURL();

        try {
            const params = new URLSearchParams({
                q: this.query,
                category: this.category,
                location: this.location,
                radius: this.radius,
                sort: this.sort,
                page: this.page,
                per_page: this.perPage,
            });

            if (this.lat && this.lng) {
                params.set('lat', this.lat);
                params.set('lng', this.lng);
            }

            const response = await fetch(`${window.localistData.restUrl}search?${params}`, {
                headers: { 'X-WP-Nonce': window.localistData.nonce }
            });

            const data = await response.json();
            this.listings = data.listings;
            this.total = data.total;
            this.pages = data.pages;
        } catch (error) {
            console.error('Search failed:', error);
            this.listings = [];
        } finally {
            this.loading = false;
        }
    },

    updateURL() {
        const params = new URLSearchParams();
        if (this.query) params.set('q', this.query);
        if (this.category) params.set('category', this.category);
        if (this.location) params.set('location', this.location);
        if (this.radius !== 10) params.set('radius', this.radius);
        if (this.sort !== 'date') params.set('sort', this.sort);
        if (this.page > 1) params.set('page', this.page);

        const newURL = `${window.location.pathname}?${params.toString()}`;
        window.history.replaceState({}, '', newURL);
    },

    goToPage(page) {
        if (page < 1 || page > this.pages) return;
        this.page = page;
        this.search(false);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    setGeolocation() {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.lat = pos.coords.latitude;
                this.lng = pos.coords.longitude;
                this.search();
            },
            () => alert('Location access denied.')
        );
    }
});
