/* v28 UI helpers: toast, ripple, add-to-cart UX */
(function(){
  const $=(s,el=document)=>el.querySelector(s);
  const $$=(s,el=document)=>Array.from(el.querySelectorAll(s));

  function ensureToastRoot(){
    let root=document.getElementById("toastRoot");
    if(!root){
      root=document.createElement("div");
      root.id="toastRoot";
      root.className="toast-root";
      document.body.appendChild(root);
    }
    return root;
  }
  window.toast=function(message,type="info",ttl=3200){
    const root=ensureToastRoot();
    const t=document.createElement("div");
    t.className="toast "+type;
    t.innerHTML='<div class="toast-dot"></div><div class="toast-msg"></div><button class="toast-x" aria-label="Close">✕</button>';
    t.querySelector(".toast-msg").textContent=message;
    t.querySelector(".toast-x").onclick=()=>t.remove();
    root.appendChild(t);
    requestAnimationFrame(()=>t.classList.add("show"));
    setTimeout(()=>{t.classList.remove("show");setTimeout(()=>t.remove(),180)},ttl);
  };

  document.addEventListener("DOMContentLoaded",()=>{
    $$(".js-flash").forEach(el=>{
      const msg=el.getAttribute("data-msg");
      const type=el.getAttribute("data-type")||"info";
      if(msg) window.toast(msg,type);
      el.remove();
    });

    $$(".btn, .pill, .chip").forEach(b=>{
      b.addEventListener("click",(e)=>{
        const r=document.createElement("span");
        r.className="ripple";
        const rect=b.getBoundingClientRect();
        r.style.left=(e.clientX-rect.left)+"px";
        r.style.top=(e.clientY-rect.top)+"px";
        b.appendChild(r);
        setTimeout(()=>r.remove(),520);
      },{passive:true});
    });
  });

  window.addToCartUX=function(url){
    fetch(url,{credentials:"same-origin"})
      .then(r=>r.json().catch(()=>null).then(j=>({ok:r.ok,j})))
      .then(({ok,j})=>{
        if(ok && j && j.ok){
          window.toast(j.message||"เพิ่มเข้าตะกร้าแล้ว","success");
          const badge=document.getElementById("cartBadgeCount");
          if(badge && j.cart_count!=null){
            badge.textContent=j.cart_count;
            badge.style.display="inline-flex";
          }
        }else{
          window.toast((j&&j.message)||"เพิ่มเข้าตะกร้าไม่สำเร็จ","error");
        }
      })
      .catch(()=>window.toast("เชื่อมต่อไม่ได้","error"));
  };
})();