    </main>
</div>

<script>
    // Toggle mobile menu
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
    });
    
    // Theme toggle
    document.getElementById('theme-toggle').addEventListener('click', function() {
        const isDark = document.documentElement.classList.toggle('dark');
        const theme = isDark ? 'dark' : 'light';
        
        // Create form to save preference
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        form.style.display = 'none';
        
        const themeInput = document.createElement('input');
        themeInput.type = 'hidden';
        themeInput.name = 'theme';
        themeInput.value = theme;
        
        const saveInput = document.createElement('input');
        saveInput.type = 'hidden';
        saveInput.name = 'save_preferences';
        saveInput.value = '1';
        
        form.appendChild(themeInput);
        form.appendChild(saveInput);
        document.body.appendChild(form);
        form.submit();
    });
    
    // Apply theme based on preference
    document.addEventListener('DOMContentLoaded', function() {
        const theme = '<?= $preferences['theme'] ?>';
        
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else if (theme === 'system') {
            // Check system preference
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            }
        }
        
        // Watch for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if ('<?= $preferences['theme'] ?>' === 'system') {
                document.documentElement.classList.toggle('dark', e.matches);
            }
        });
    });
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        
        if (window.innerWidth < 768 && sidebar.classList.contains('active') && 
            !sidebar.contains(event.target) && 
            event.target !== mobileMenuButton) {
            sidebar.classList.remove('active');
        }
    });
</script>
</body>
</html>