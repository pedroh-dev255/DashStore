
// Referente ao codigo JS que deve constar em todas as paginas

//popup
function showPopin(message, type = 'success') {
    const popin = document.getElementById('popin');
    const popinText = document.getElementById('popin-text');
  
    // Define a mensagem e a classe de estilo
    popinText.textContent = message;
    popin.classList.remove('popin-success', 'popin-warning', 'popin-error'); // Remove qualquer classe anterior
    popin.classList.add(`popin-${type}`); // Adiciona a nova classe baseada no tipo
    popin.classList.add('show');
  
    // Fecha automaticamente após 5 segundos
    setTimeout(() => popin.classList.remove('show'), 3000);
}
  