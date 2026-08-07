export default () => ({
    isFavorite: false,
    loading: false,
    postId: null,

    init() {
        this.postId = this.$el.dataset.postId;
        this.checkInitialStatus();
    },

    async checkInitialStatus() {
        if (!window.localistData.isLoggedIn) return;
        
        try {
            const response = await fetch(`${window.localistData.restUrl}favorites`, {
                headers: { 'X-WP-Nonce': window.localistData.nonce }
            });
            const favorites = await response.json();
            this.isFavorite = favorites.some(f => f.id == this.postId);
        } catch (e) {
            console.error('Failed to load favorites status');
        }
    },

    async toggle() {
        if (!window.localistData.isLoggedIn) {
            window.location.href = window.localistData.loginUrl;
            return;
        }

        this.loading = true;
        try {
            const formData = new FormData();
            formData.append('action', 'localist_toggle_favorite');
            formData.append('post_id', this.postId);
            formData.append('nonce', window.localistData.favoritesNonce);

            const response = await fetch(window.localistData.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();
            if (data.success) {
                this.isFavorite = data.data.is_favorite;
                // Optional: show toast notification
                this.showToast(data.data.message);
            }
        } catch (error) {
            console.error('Favorite toggle failed:', error);
        } finally {
            this.loading = false;
        }
    },

    showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-gray-900 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-opacity duration-300';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }
});
