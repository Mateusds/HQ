document.addEventListener('DOMContentLoaded', () => {
    const stars = document.querySelectorAll('.star-rating .fa-star-o');
    stars.forEach(star => {
        star.addEventListener('mouseover', () => {
            const value = star.getAttribute('data-value');
            stars.forEach(s => {
                s.classList.toggle('fa-star', s.getAttribute('data-value') <= value);
                s.classList.toggle('fa-star-o', s.getAttribute('data-value') > value);
            });
        });

        star.addEventListener('mouseout', () => {
            const currentRating = document.getElementById('avaliacao_hidden').value;
            stars.forEach(s => {
                s.classList.toggle('fa-star', s.getAttribute('data-value') <= currentRating);
                s.classList.toggle('fa-star-o', s.getAttribute('data-value') > currentRating);
            });
        });

        star.addEventListener('click', () => {
            const value = star.getAttribute('data-value');
            document.getElementById('avaliacao_hidden').value = value;
            stars.forEach(s => {
                s.classList.toggle('fa-star', s.getAttribute('data-value') <= value);
                s.classList.toggle('fa-star-o', s.getAttribute('data-value') > value);
            });
        });
    });
});
