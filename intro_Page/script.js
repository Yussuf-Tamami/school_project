document.addEventListener('DOMContentLoaded', function () {
    const progressBar = document.querySelector('.progress');

   
    setTimeout(() => {
        progressBar.style.width = '100%';

        setTimeout(() => {
            window.location.href = 'intro_Page.php';
        }, 3000);
    }, 1000);
});