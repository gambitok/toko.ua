/*!
 * iosmodal.js - a small Bootstrap modal styled like an iOS modal
 * Copyright 2020 Terry Morse
 * MIT license
 */ const iOSModal=function(){let o=document.createElement("div");o.innerHTML=`
    <div class="modal fade" id="iosmodal" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-dialog-ios" role="document">
        <div class="modal-content">
          <div class="modal-body" id="iosmodal-body">
            <!-- body content goes here -->
          </div>
          <div class="modal-footer">
            <div class="btn-group" id="iosmodal-buttons" role="group">
              <!-- buttons go here -->
            </div>
          </div>
        </div>
      </div>
    </div>
  `;let a=o.querySelector(".modal.fade"),d=a.querySelector(".modal-dialog"),t=d.querySelector(".modal-content"),i=t.querySelector(".modal-body"),e=t.querySelector(".modal-footer"),l=e.querySelector(".btn-group"),n=`
    /*!
     * Bootstrap modal styled to look like an iOS modal
     * Copyright 2020 Terry Morse
     * Licensed under MIT
     */
    
    .modal-dialog.modal-dialog-ios {
      position: fixed;
      bottom: 50px; /* match iPhone */
      margin: 0 16px;
      width: calc(100% - 32px);
      display: flex;
      align-items: center;
    }
    
    .modal.fade:not(.show) .modal-dialog.modal-dialog-ios {
      transform: translate(0, 50vh); /* off bottom of screen */
      transition: transform .5s linear;
    }
    
    .modal.fade.show .modal-dialog.modal-dialog-ios {
      transform: translate(0, 0);
      transition: transform .7s ease-out;
    }
    
    /* don't fade in or out */
    #iosmodal.fade.show {
      opacity: 1;
      transition: opacity 1s linear;
    }
    #iosmodal.fade:not(.show) {
      opacity: 1;
      transition: opacity 1s linear;
    }
    
    .modal-dialog.modal-dialog-ios .modal-content {
      border-radius: 12px;
      border: none;
    }
    
    .modal-dialog.modal-dialog-ios .modal-body {
      padding: 1rem 1.5rem;
      text-align: center;
      font-weight: 300;
      font-size: 1rem;
    }
    
    .modal-dialog.modal-dialog-ios .modal-footer {
      justify-content: center;
      padding: 0;
      border-top: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    /* full-width vertically stacked buttons */
    .modal-dialog.modal-dialog-ios .modal-footer .btn-group {
      width: 100%;
      margin: 0;
      -ms-flex-direction: column;
      flex-direction: column;
      -ms-flex-align: start;
      align-items: flex-start;
      -ms-flex-pack: center;
      justify-content: center;
    }
    
    .modal-dialog.modal-dialog-ios .modal-footer .btn {
      margin: 2px 12px;
      padding: 0.3rem 0.75rem;
      border-style: none;
      border-radius: 8px;
      width: calc(100% - 24px);
    }
    
    .modal-dialog.modal-dialog-ios .modal-footer .btn:hover {
      background-color: rgba(0, 0, 0, 0.15);
    }
    
    /* on wider screens, center modal on screen, and stack buttons
       horizontally
     */
    @media (min-width: 576px) {
    
      .modal-dialog.modal-dialog-ios {
        position: relative;
        top: calc(50vh - 32px);
        margin: 0 auto;
        display: flex;
        align-items: center;
      }
    
      .modal.fade:not(.show) .modal-dialog.modal-dialog-ios {
        transform: none;
      }
    
      .modal-dialog.modal-dialog-ios .modal-footer .btn-group {
        width: 100%;
        margin: 0;
        flex-direction: row;
        align-items: flex-start;
        justify-content: space-evenly;
      }
    
      .modal-dialog.modal-dialog-ios .modal-footer .btn {
      }
    
      #iosmodal.fade.show {
        opacity: 1;
        transition: opacity .4s linear;
      }
      #iosmodal.fade:not(.show) {
        opacity: 0;
        transition: opacity .2s linear;
      }
    
    }
    
    /* iOS modal backdrop is more transparent than default Bootstrap */
    .modal-backdrop.show {
      opacity: 0.1;
    }
  `,r=document.createElement("style");return r.id="iosModalStyle",r.textContent=n,document.head.appendChild(r),document.body.appendChild(a),{customize:function o({bottom:a}){a&&(console.log("iOSModal setting bottom to:",a),d.style.bottom=a)},show:function o(a,d){i.innerHTML="",l.innerHTML="",a&&("string"==typeof a?i.innerHTML=a:i.appendChild(a)),d&&d.forEach(o=>{let a=Object.assign({},o);a.class&&(a.className=a.class,delete a.class);let d=Object.assign(document.createElement("button"),a);l.appendChild(d)}),$("#iosmodal").modal("show")},hide:function o(){$("#iosmodal").modal("hide")}}}();