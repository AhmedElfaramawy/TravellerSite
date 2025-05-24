function LOader(){
    document.querySelector('.loader-container').classList.add('active');
  }
  
  function FadeOut(){
    setTimeout(LOader, 3000);
  }
  FadeOut();