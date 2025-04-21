document.addEventListener('DOMContentLoaded', () => {
    const carousel = document.querySelector('.carrousel');
    const btnPrev  = document.querySelector('.btn-prev');
    const btnNext  = document.querySelector('.btn-next');
  
    // Précédent : déplace le dernier item en tête
    btnPrev.addEventListener('click', () => {
      carousel.insertBefore(
        carousel.lastElementChild,
        carousel.firstElementChild
      );
    });
  
    // Suivant : déplace le premier item à la fin
    btnNext.addEventListener('click', () => {
      carousel.appendChild(carousel.firstElementChild);
    });
  });
  