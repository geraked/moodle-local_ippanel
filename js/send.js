(() => {

    let phoneSelect = document.getElementById('phone-select');
    let cohortSelect = document.getElementById('cohort-select');
    let tableRows = document.getElementById('table-rows');
    let chkAll = document.getElementById('usr-chk-all');
    let msgTxt = document.getElementById('msg-txt');
    let numbersTxt = document.getElementById('numbers-txt');
    let charCnt = document.getElementById('char-cnt');
    let numCnt = document.getElementById('num-cnt');
    let creditAvl = document.getElementById('credit-avl');
    let sendBtn = document.getElementById('send-btn');
    let msgResp = document.getElementById('msg-resp');
    let numbers = [];
    let isSending = false;
    let msgUpdateInterval;

    msgTxt.addEventListener('keyup', updateCharCnt);
    msgTxt.addEventListener('paste', updateCharCnt);
    msgTxt.addEventListener('change', updateCharCnt);
    numbersTxt.addEventListener('paste', updateNumbers);
    numbersTxt.addEventListener('change', updateNumbers);

    cohortSelect.addEventListener('change', (e) => {
        request(`op=1&cohortid=${e.target.value}`, (data) => {
            let enb = '';
            let dis = '';
            for (let k in data) {
                let obj = data[k];
                let phone = phoneSelect.value == 2 ? getNumber(obj.phone2) : getNumber(obj.phone1);
                let checked = phone ? 'checked' : 'disabled';
                let row = `
                    <tr>
                        <th scope="row" class="text-center"><input type="checkbox" class="usr-chk" id="usr-chk-${obj.id}" value="${phone}" ${checked}></th>
                        <td>${obj.lastname} - ${obj.firstname}</td>
                        <td class="text-center">${phone}</td>
                    </tr>                        
                `;
                if (phone)
                    enb += row;
                else
                    dis += row;
            }
            tableRows.innerHTML = enb + dis;
            toggleEmpty();
            updateNumbers();
            updateCharCnt();
            updateNumCnt();

            let chks = tableRows.querySelectorAll('.usr-chk');
            chks.forEach(chk => {
                chk.addEventListener('change', updateNumbers);
            });
        });
    });

    chkAll.addEventListener('change', (e) => {
        let chks = tableRows.querySelectorAll('.usr-chk');
        if (e.target.checked) {
            chks.forEach(chk => {
                if (!chk.disabled)
                    chk.checked = true;
            });
        } else {
            chks.forEach(chk => {
                if (!chk.disabled)
                    chk.checked = false;
            });
        }
        updateNumbers();
    });

    sendBtn.addEventListener('click', (e) => {
        if (msgUpdateInterval) {
            clearInterval(msgUpdateInterval);
            msgUpdateInterval = 0;
        }
        msgResp.innerHTML = '';
        if (isSending || !numbers.length || !msgTxt.value)
            return;
        isSending = true;
        sendBtn.innerHTML = s.sending;
        let postData = {
            message: JSON.stringify(msgTxt.value),
            numbers: JSON.stringify(numbers),
        };
        request('op=3', (data) => {
            isSending = false;
            sendBtn.innerHTML = s.submit;
            updateCredit();
            if (data != -1) {
                let d = data;
                cohortSelect.value = 0;
                numbersTxt.value = '';
                cohortSelect.dispatchEvent(new Event('change'));
                msgResp.innerHTML = `
                    <div class="alert alert-success" role="alert">
                        <h4 class="alert-heading">${s.success}!</h4>
                        <p>
                            <b>${s.id}:<b> ${d.bulkId}<br>
                            <b>${s.status}:<b> ${d.status}<br>
                            <b>${s.cost}:<b> ${d.cost}<br>
                            <b>${s.payback}:<b> ${d.paybackCost}<br>
                            <b>${s.rcnt}:<b> ${d.recipientsCount}<br>
                            <b>${s.pcnt}:<b> ${d.page}<br>
                        </p>
                    </div>
                `;
                msgUpdateInterval = setInterval(() => {
                    request(`op=4&bulkid=${d.bulkId}`, (data) => {
                        if (data == -1) {
                            if (msgUpdateInterval) {
                                clearInterval(msgUpdateInterval);
                                msgUpdateInterval = 0;
                            }
                            return;
                        }
                        let d = data;
                        msgResp.innerHTML = `
                                <div class="alert alert-success" role="alert">
                                    <h4 class="alert-heading">${s.success}!</h4>
                                    <p>
                                        <b>${s.id}:<b> ${d.bulkId}<br>
                                        <b>${s.status}:<b> ${d.status}<br>
                                        <b>${s.cost}:<b> ${d.cost}<br>
                                        <b>${s.payback}:<b> ${d.paybackCost}<br>
                                        <b>${s.rcnt}:<b> ${d.recipientsCount}<br>
                                        <b>${s.pcnt}:<b> ${d.page}<br>
                                    </p>
                                </div>
                            `;
                        updateCredit();
                        if (d.status == 'finish' && msgUpdateInterval) {
                            clearInterval(msgUpdateInterval);
                            msgUpdateInterval = 0;
                        }
                    });
                }, 10000);
            } else {
                msgResp.innerHTML = `<div class="alert alert-danger" role="alert">${s.error}</div>`;
            }
        }, postData);
    });

    updateCredit();
    cohortSelect.dispatchEvent(new Event('change'));
    phoneSelect.addEventListener('change', () => {
        cohortSelect.dispatchEvent(new Event('change'));
    });

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

    function toggleEmpty() {
        if (tableRows.childElementCount == 0) {
            tableRows.parentElement.parentElement.classList.add('d-none');
        } else {
            tableRows.parentElement.parentElement.classList.remove('d-none');
        }
    }

    function updateNumbers() {
        let chks = tableRows.querySelectorAll('.usr-chk[checked]');
        let customNumbers = numbersTxt.value.split('\n');
        numbers = [];
        chks.forEach(chk => {
            if (chk.checked && chk.value)
                numbers.push(chk.value);
        });
        customNumbers.forEach(cn => {
            let number = getNumber(cn);
            if (number)
                numbers.push(number);
        });
        updateNumCnt();
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

    function testGetNumber() {
        console.log(1, getNumber('+989179338815  '));
        console.log(2, getNumber('989179338815 '));
        console.log(3, getNumber('+9809179338815 '));
        console.log(4, getNumber('09179338815'));
        console.log(5, getNumber('9179338815'));
        console.log(6, getNumber('179338815'));
    }

    function updateCharCnt() {
        charCnt.innerHTML = msgTxt.value.length;
    }

    function updateNumCnt() {
        numCnt.innerHTML = numbers.length;
    }

    function updateCredit() {
        request('op=2', (data) => {
            creditAvl.innerHTML = data;
        });
    }

})();