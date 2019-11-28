self.addEventListener('message', function (e) {
    var data = e.data;
    if (data.cmd == 'start') {
        var timesRun = 0;
        var startTime = new Date().getTime();

        var doStuff = function () {
            var now = new Date().getTime();
//            Run only 5 times
//            if (++timesRun == 50)
//                clearInterval(timer);
            try {
                var req = new XMLHttpRequest();
                req.open("GET", "/operaciones/validador/estatus-revisar-pagos?id=" + data.msg, true);
                req.setRequestHeader("Content-Type", "application/json;  charset=utf-8");
                req.onreadystatechange = function () {
                    if (req.readyState === 4) {
                        if (this.status === 200) {
                            var response = this.responseText;
                            var obj = JSON.parse(response);
                            if(obj.success == true) {
                                self.postMessage(obj);
                            } else {
                                self.postMessage(obj);
                            }
                        }
                    }
                };
                req.send(data);
            } catch (e) {
                postMessage(null);
            }
        };
        var timer = setInterval(doStuff, 2000);
    }
}, false);