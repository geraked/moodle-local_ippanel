(() => {

    let links = document.querySelectorAll('.bid-links');
    let refs = document.querySelectorAll('.ref-links');
    let modal = document.getElementById('report-modal');
    let modalTitle = modal.querySelector('.modal-title');
    let modalMsg = modal.querySelector('.modal-msg');
    let modalTbody = modal.querySelector('.modal-tbody');
    let nextBtn = document.getElementById('next-btn');
    let prevBtn = document.getElementById('prev-btn');

    links.forEach(l => l.addEventListener('click', linkClick));
    refs.forEach(l => l.addEventListener('click', refClick));

    request('op=5', (data) => {
        for (let k in data) {
            let obj = data[k];
            request(`op=4&bulkid=${obj.bulkid}`, (data) => {
                if (data == -1)
                    return;
            });
        }
    });

    function lockBtn(...btns) {
        btns.forEach(b => b.disabled = true);
    }

    function unlockBtn(...btns) {
        btns.forEach(b => b.disabled = false);
    }

    function linkClick(e) {
        let link = e.target;
        let bid = link.getAttribute('data-whatever');
        let page = 0;

        modalTitle.innerText = bid;
        modalMsg.innerHTML = '';
        modalTbody.innerHTML = '';
        lockBtn(nextBtn, prevBtn);

        nextBtn.onclick = () => updateModalBody(bid, ++page);
        prevBtn.onclick = () => updateModalBody(bid, --page);

        updateModalBody(bid, page);
    }

    function refClick(e) {
        e.preventDefault();

        let link = e.target.tagName == 'I' ? e.target.parentElement : e.target;
        let bid = link.getAttribute('data-whatever');

        request(`op=4&bulkid=${bid}`, (data) => {
            location.reload();
        });
    }

    function updateModalBody(bid, page) {
        lockBtn(nextBtn, prevBtn);

        request(`op=7&bulkid=${bid}`, (data) => {
            modalMsg.innerHTML = data.message;
        });

        request(`op=6&bulkid=${bid}&page=${page}`, (data) => {
            unlockBtn(nextBtn, prevBtn);
            if (data == -1)
                return;

            let statuses = data[0];
            let pageInfo = data[1];
            let rows = [];

            if (!pageInfo.next)
                lockBtn(nextBtn);
            if (!pageInfo.prev)
                lockBtn(prevBtn);

            for (let k in statuses) {
                let o = statuses[k];
                let users = [];

                for (let k in o.users) {
                    let u = o.users[k];
                    let a = `
                        <a href="${v.usrurl}${u.id}" target="_blank">${u.firstname} ${u.lastname}</a>
                    `;
                    users.push(a);
                }

                let tr = `
                    <tr>
                        <td dir="ltr">${getNumber(o.recipient)}</td>
                        <td>${o.status}</td>
                        <td>${users.join(', ')}</td>
                    </tr>
                `;
                rows.push(tr);
            }

            modalTbody.innerHTML = rows.join('');
        });
    }

    function request(params, callBack, postData = null) {
        let xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                callBack(this.response);
            }
        };
        if (postData) {
            let qs = Object.keys(postData).map(k => {
                return encodeURIComponent(k) + '=' + encodeURIComponent(postData[k]);
            }).join('&');
            xhttp.open('POST', `${v.apiurl}?` + params, true);
            xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhttp.responseType = 'json';
            xhttp.send(qs);
        } else {
            xhttp.open('GET', `${v.apiurl}?` + params, true);
            xhttp.responseType = 'json';
            xhttp.send();
        }
    }

    function getNumber(n) {
        let reg;
        n = n.replace(/\s/g, '');
        if (!n)
            return '';
        reg = /^[+]?\d{1,2}(\d{10})$/i.exec(n);
        if (reg)
            return `0${reg[1]}`;
        reg = /^0\d{10}$/i.exec(n);
        if (reg)
            return reg[0];
        reg = /^\d{10}$/i.exec(n);
        if (reg)
            return `0${reg[0]}`;
        return '';
    }

})();