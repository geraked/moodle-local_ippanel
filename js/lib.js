(() => {

    let drawer = document.querySelector("#nav-drawer");
    let nav;

    if (!drawer)
        return;

    nav = document.createElement("nav");
    nav.className = "list-group mt-1";
    nav.innerHTML = `
        <ul>
            <li>
                <a class="list-group-item list-group-item-action" href="${v[0].url}">
                    <div class="ml-0">
                        <div class="media">
                            <span class="media-left">
                                <i class="icon fa fa-envelope-o fa-fw" aria-hidden="true"></i>
                            </span>
                            <span class="media-body ">${v[0].name}</span>
                        </div>
                    </div>
                </a>
            </li>
            <li>
                <a class="list-group-item list-group-item-action" href="${v[1].url}">
                    <div class="ml-0">
                        <div class="media">
                            <span class="media-left">
                                <i class="icon fa fa-exclamation-circle fa-fw " aria-hidden="true"></i>
                            </span>
                            <span class="media-body ">${v[1].name}</span>
                        </div>
                    </div>
                </a>
            </li>            
        </ul>
    `;

    drawer.appendChild(nav);

})();