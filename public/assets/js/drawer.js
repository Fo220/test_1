(function(){
  const openBtn = document.querySelector("[data-open-drawer]");
  const overlay = document.querySelector(".acct-drawer-overlay");
  if(!openBtn || !overlay) return;

  const closeBtn = overlay.querySelector("[data-close-drawer]");
  const backdrop = overlay.querySelector(".acct-drawer-backdrop");

  function open(){ overlay.classList.add("show"); }
  function close(){ overlay.classList.remove("show"); }

  openBtn.addEventListener("click", open);
  if(closeBtn) closeBtn.addEventListener("click", close);
  if(backdrop) backdrop.addEventListener("click", close);
  document.addEventListener("keydown", (e)=>{ if(e.key==="Escape") close(); });
})();
