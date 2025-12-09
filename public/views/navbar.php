<nav class="navbar">
    <button class="hamburger-menu" id="hamburger-menu" aria-label="Toggle menu">
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
    </button>
    <div class="nav-left">
        <a href="/" class="nav-btn">🏠 Home</a>
        <a href="/dashboard" class="nav-btn">📊 Dashboard</a>
        <a href="/account" class="nav-btn">👤 Account</a>
    </div>
    <div class="nav-right">
        <a href="/logout" class="nav-btn logout">🚪 Logout</a>
    </div>
</nav>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger-menu');
    const navLeft = document.querySelector('.nav-left');
    const navRight = document.querySelector('.nav-right');

    hamburger.addEventListener('click', function() {
        navLeft.classList.toggle('nav-open');
        navRight.classList.toggle('nav-open');
        hamburger.classList.toggle('active');
    });
});
</script>
