const accountBtn = document.querySelector('.accountProfile');
const accountDiv = document.querySelector('.accountDiv');

accountBtn.addEventListener('click', () => {
    accountDiv.classList.toggle('active');
});
