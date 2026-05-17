function applyTheme() {
    const savedTheme = localStorage.getItem('bloodline_theme');
    const darkIcon = document.getElementById('dark-icon');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
        if (darkIcon) darkIcon.classList.replace('fa-moon', 'fa-sun');
    } else {
        document.body.classList.remove('dark-theme');
        if (darkIcon) darkIcon.classList.replace('fa-sun', 'fa-moon');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    applyTheme();
    const darkIcon = document.getElementById('dark-icon');
    if (darkIcon) {
        darkIcon.addEventListener('click', () => {
            const isDark = document.body.classList.toggle('dark-theme');
            localStorage.setItem('bloodline_theme', isDark ? 'dark' : 'light');
            applyTheme();
        });
    }
});
