document.addEventListener('DOMContentLoaded', () => {
  const carousel = document.querySelector('.carrousel');
  const btnPrev  = document.querySelector('.btn-prev');
  const btnNext  = document.querySelector('.btn-next');
  let currentIndex = 0;

  // Renvoie tous les items non cachés
  function getVisibleItems() {
    return Array.from(carousel.querySelectorAll('.carrousel-item'))
                .filter(item => item.offsetParent !== null);
  }

  // Calcule la largeur (largeur+margin-right) du premier item visible
  function getSlideWidth() {
    const visibles = getVisibleItems();
    if (visibles.length === 0) return 0;
    const first = visibles[0];
    const style = getComputedStyle(first);
    return first.getBoundingClientRect().width
         + parseInt(style.marginRight, 10);
  }

  function goTo(offset) {
    const visibles = getVisibleItems();
    const count    = visibles.length;
    if (count === 0) return;

    // wrap-around
    currentIndex = (currentIndex + offset + count) % count;
    const w = getSlideWidth();
    carousel.style.transform = `translateX(-${w * currentIndex}px)`;
  }

  btnNext.addEventListener('click', () => goTo( 1));
  btnPrev.addEventListener('click', () => goTo(-1));

  window.addEventListener('resize', () => {
    // recalcule au cas où les tailles ont changé
    const w = getSlideWidth();
    carousel.style.transform = `translateX(-${w * currentIndex}px)`;
  });
});
