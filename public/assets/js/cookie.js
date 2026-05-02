(function(){
  const KEY = "cookie_consent_v1";
  const bar = document.querySelector(".cookie-bar");
  if(!bar) return;

  const saved = localStorage.getItem(KEY);
  if(!saved){
    bar.classList.add("show");
  }

  const accept = bar.querySelector("[data-cookie-accept]");
  const settings = bar.querySelector("[data-cookie-settings]");

  function hide(){ bar.classList.remove("show"); }

  if(accept){
    accept.addEventListener("click", function(){
      localStorage.setItem(KEY, "accepted");
      hide();
    });
  }
  if(settings){
    settings.addEventListener("click", function(){
      alert("หน้านี้เป็นเดโม: ถ้าต้องการหน้า 'ตั้งค่าคุกกี้' แบบละเอียด บอกได้ เดี๋ยวทำให้");
    });
  }
})();
