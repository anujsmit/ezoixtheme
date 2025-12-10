<?php
// Custom 404 Page
get_header();
?>

<style>
/* ---- Custom 404 Styling ---- */
.error-404-wrapper {
    text-align: center;
    padding: 80px 20px;
    max-width: 700px;
    margin: 0 auto;
}

.error-404-wrapper h1 {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 20px;
    color: var(--text-dark); /* Use theme variable */
}

.error-404-wrapper p {
    font-size: 18px;
    color: var(--text-medium); /* Use theme variable */
    margin-bottom: 25px;
}

.error-404-wrapper .countdown {
    margin-top: 15px;
    font-weight: bold;
    color: var(--primary-dark); /* Use theme variable */
}

.error-404-wrapper a.home-btn {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 25px;
    background: var(--primary-color);
    color: #fff;
    border-radius: var(--radius); /* Use theme variable */
    text-decoration: none;
}

.error-404-wrapper a.home-btn:hover {
    background: var(--primary-dark);
}
</style>

<div class="error-404-wrapper">

    <h1>404 - Page Not Found</h1>

    <p>Sorry, the page you are looking for doesn't exist or has been moved.</p>

    <p>Try searching below or wait, you'll be redirected to the homepage shortly.</p>

    <p class="countdown">Redirecting in <span id="redirectTimer">10</span> seconds...</p>

    <a href="<?php echo esc_url(home_url('/')); ?>" class="home-btn">Go to Homepage</a>

</div>

<script>
let timeLeft = 10;
const timerElement = document.getElementById("redirectTimer");

const countdown = setInterval(() => {
    timeLeft--;
    timerElement.textContent = timeLeft;

    if (timeLeft <= 0) {
        clearInterval(countdown);
        window.location.href = "<?php echo esc_url(home_url('/')); ?>";
    }
}, 1000);
</script>

<?php get_footer(); ?>
