<?php
// footer.php — à inclure en bas de chaque page
?>
    </div> <!-- /container -->

    <footer class="text-center text-muted py-3 small">
        © <?= date('Y') ?> School Timetable — Tous droits réservés.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ===== Dark mode =====
        (function () {
            const html = document.documentElement;
            const icon = document.getElementById('themeIcon');
            const saved = localStorage.getItem('theme') || 'light';
            html.setAttribute('data-bs-theme', saved);
            if (icon) icon.className = saved === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';

            const btn = document.getElementById('toggleTheme');
            if (btn) {
                btn.addEventListener('click', () => {
                    const current = html.getAttribute('data-bs-theme');
                    const next = current === 'dark' ? 'light' : 'dark';
                    html.setAttribute('data-bs-theme', next);
                    localStorage.setItem('theme', next);
                    if (icon) icon.className = next === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
                });
            }
        })();
    </script>
</body>
</html>