!function(){Element.prototype.matches||(Element.prototype.matches=Element.prototype.msMatchesSelector||Element.prototype.webkitMatchesSelector),Element.prototype.closest||(Element.prototype.closest=function(e){var t=this;do{if(Element.prototype.matches.call(t,e))return t;t=t.parentElement||t.parentNode}while(null!==t&&1===t.nodeType);return null}),document.addEventListener("click",(e=>{if(e.target.matches(".levels .remove")){let t=e.target.parentNode.getAttribute("data-id");Swal.fire({html:"<strong>Opravdu si přejete odstranit členskou sekci/úroveň?</strong><br><br>Smazáním sekce/úrovně nedojde ke smazání stránek v sekci/úrovni.",showDenyButton:!0,confirmButtonText:"Smazat",denyButtonText:"Ponechat",customClass:{confirmButton:"removeConfirmButton",denyButton:"removeDenyButton"}}).then((e=>{if(e.isConfirmed){let e=document.getElementById("LevelRemoveForm");e.querySelector('[name="level_id"]').setAttribute("value",t),e.submit()}else e.isDenied}))}})),document.addEventListener("click",(e=>{if(e.target.matches(".levels .edit")){let t=e.target.parentNode.querySelector("span").innerText,r=e.target.parentNode.getAttribute("data-id");Swal.fire({input:"text",inputLabel:"Nový název",inputValue:t,showDenyButton:!0,confirmButtonText:"Přejmenovat",denyButtonText:"Ponechat",customClass:{confirmButton:"renameConfirmButton",denyButton:"renameDenyButton"}}).then((e=>{if(e.isConfirmed){let t=document.getElementById("LevelEditForm");t.querySelector('[name="level_id"]').setAttribute("value",r),t.querySelector('[name="name"]').setAttribute("value",e.value),t.submit()}else e.isDenied}))}})),document.addEventListener("click",(e=>{if(e.target.matches("form.pages button")){e.preventDefault();let t=s(),r=e.target.closest("form");r.querySelector('[name="level_id"]').value=t,r.submit()}})),document.addEventListener("click",(t=>{if(t.target.matches(".levels a")){t.preventDefault();let r=t.target.parentNode;Array.from(document.querySelectorAll(".levels li.selected")).forEach((e=>{e.classList.remove("selected")})),r.classList.add("selected"),n(),l(),o(),e()}})),document.addEventListener("DOMContentLoaded",(e=>{s()&&n(),l(),r(),o()})),document.addEventListener("click",(e=>{(e.target.matches(".oneEmail .carret")||e.target.matches(".oneEmail .header h3"))&&e.target.closest(".oneEmail").classList.toggle("open")})),document.addEventListener("click",(e=>{if(e.target.matches(".specifyLevelEmailCheckbox")){let t=e.target.closest(".oneEmail").querySelector("#mail_subject"),r=e.target.closest(".oneEmail").querySelector("#mail_body"),o=e.target.closest(".oneEmail").querySelector(".inputs");e.target.checked?(t.removeAttribute("readonly"),r.removeAttribute("readonly"),o.classList.remove("collapsed")):(t.value="",r.value="",t.setAttribute("readonly",!0),r.setAttribute("readonly",!0),o.classList.add("collapsed"))}})),document.addEventListener("click",(e=>{(e.target.matches(".shortcodes h3")||e.target.matches(".shortcodes h3 .carret"))&&e.target.closest(".shortcodes").classList.toggle("open")}));const e=()=>{let e=s();Array.from(document.querySelectorAll(".subsubmenuitem")).forEach((t=>{let r=t.getAttribute("href");new RegExp("&level=").test(r)?t.setAttribute("href",r.replace(/(&level=[0-9]*)/,`&level=${e}`)):t.setAttribute("href",`${r}&level=${e}`)}))},t=e=>{if(!window.hasOwnProperty("LevelToPage")){let e=document.getElementById("LevelToPage");e&&(window.LevelToPage=JSON.parse(e.innerText))}return window.hasOwnProperty("LevelToPage")&&window.LevelToPage.hasOwnProperty(e)?window.LevelToPage[e]:[]},r=()=>{let e=document.querySelector(".removePagesForm .danger");e&&(e.disabled=!0);let t=document.querySelector(".addPagesForm .btn");t&&(t.disabled=!0)},o=()=>{if(s()){let e=document.querySelector(".removePagesForm .danger");e&&null!==document.querySelector(".removePagesForm .onePage")&&(e.disabled=!1);let t=document.querySelector(".addPagesForm .btn");t&&null!==document.querySelector(".addPagesForm .onePage")&&(t.disabled=!1)}},n=()=>{let e=document.querySelector(".removePagesForm");if(!e)return;let r=t(s()),n=r.reduce(((e,t)=>e+"&include[]="+t),""),l=e.querySelector(".inner");l.innerHTML="",r.length<=0?l.insertAdjacentHTML("afterbegin","<p>Sekce/úroveň nemá přiřazené stránky.</p>"):(c(e),fetch("/?rest_route=/wp/v2/pages&per_page=100&context=embed"+n).then((e=>e.json())).then((t=>{a(l,t),d(e),o()})))},l=()=>{let e=document.querySelector(".addPagesForm");if(!e)return;let r=t(s());Array.from(e.querySelectorAll('input[type="checkbox"]')).forEach((e=>{let t=parseInt(e.value);r.indexOf(t)>=0?e.disabled=!0:e.disabled=!1}))},a=(e,t)=>{let r=t.map((e=>`<div class="onePage"><input type="checkbox" name="toRemove[]" value="${e.id}"> ${e.title.rendered}</div>`));e.innerHTML="",e.insertAdjacentHTML("beforeend",r.join(""))},s=()=>{let e=document.querySelector(".levels li.selected");return e?parseInt(e.getAttribute("data-id")):null},c=e=>{e.classList.add("loading")},d=e=>{e.classList.remove("loading")}}();