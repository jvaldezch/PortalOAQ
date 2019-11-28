self.addEventListener('message', function (e) {
    var data = e.data;
    if (data.cmd == 'start') {        
        try {
            var req = new XMLHttpRequest();
            req.open("GET", "/operaciones/validador/revisar-validacion?id=" + data.msg, false);
            req.setRequestHeader("Content-Type", "application/json;  charset=utf-8");
            req.onreadystatechange = function () {
                if (req.readyState === 4) {
                    if (this.status === 200) {
                        var response = this.responseText;
                        var obj = JSON.parse(response);
                        self.postMessage("RES: " + obj.md5 + " validando: ");
                    }
                }
            };
            req.send(data);
        } catch (e) {
            postMessage(null);
        }
    }
}, false);