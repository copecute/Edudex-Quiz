// Kiểm tra theme từ localStorage hoặc system
function getTheme() {
    const theme = localStorage.getItem('theme');
    if (theme) return theme;
    
    // Nếu không có theme được lưu, sử dụng system theme
    return 'system';
}

// Áp dụng theme
function applyTheme(theme) {
    if (theme === 'system') {
        // Kiểm tra system dark mode
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-bs-theme', 'light');
        }
    } else {
        document.documentElement.setAttribute('data-bs-theme', theme);
    }
}

// Set theme mới
function setTheme(theme) {
    localStorage.setItem('theme', theme);
    applyTheme(theme);
}

// Theo dõi thay đổi system theme
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
    const theme = getTheme();
    if (theme === 'system') {
        applyTheme('system');
    }
});

// Áp dụng theme khi tải trang
document.addEventListener('DOMContentLoaded', () => {
    applyTheme(getTheme());
}); 