
if (!window.jQuery) {  
    loadScript("https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js")
}
function loadScript(src) {
    return new Promise(function (resolve, reject) {
        var s;
        s = document.createElement('script');
        s.src = src;
        s.onload = resolve;
        s.onerror = reject;
        document.head.appendChild(s);
    });
}

let arrayOfDataCoordsToSendToServer = [];
let arrayOfDataCoordsAtThisTime = [];
let checkIntervalCoords = 30
let sendIntervalCoords = 3000

setInterval(function() {
    sendDataWithMouseTrackerTimeToServer()
}, sendIntervalCoords);

setInterval(function () {
    makeArrayOfDataCoordsToSendToServer()
}, checkIntervalCoords);

//works only with elems with id or class
jQuery.fn.trackCoords = function(_address) {

    let obj = this;
    let elemName = obj[0].id
    if (elemName == "")
        elemName = obj[0].className
    if (elemName == "") {
        console.log("need id or class name for trackCoords");
        return;
    }
    // считаю, что класс всегда уникальный в этом задании
    this.mouseenter(function(evtEnter) {
        
        let x = evtEnter.pageX - obj.offset().left;
        let y = evtEnter.pageY - obj.offset().top;

        if (arrayOfDataCoordsAtThisTime.length == 0) {
            arrayOfDataCoordsAtThisTime.push({ 
                "address": _address,
                "data": [{
                    "x": x, 
                    "y": y,
                    "elemName": elemName
                }]
            })
        } else {
            let finded = false
            $.each(arrayOfDataCoordsAtThisTime, function(kArr, vArr){
                if (finded == true) return false
                $.each(vArr, function(k, v) {
                    if (k == "address" && v == _address) {
                        arrayOfDataCoordsAtThisTime[kArr].data.push({ 
                            "x": x, 
                            "y": y, 
                            "elemName": elemName 
                        })
                        finded = true
                        return false
                    }
                })
                if(kArr == (arrayOfDataCoordsAtThisTime.length - 1) && finded == false) {
                    arrayOfDataCoordsAtThisTime.push ({ 
                        "address": _address,
                        "data": [{
                            "x": x, 
                            "y": y,
                            "elemName": elemName
                        }]
                    })
                }
            });
        }

        obj.mousemove(function(evtMove) {
            $.each(arrayOfDataCoordsAtThisTime, function(kArr, vArr) {
                arrayOfDataCoordsAtThisTime[kArr].data.forEach((vData, kData) => {
                    if (vData.elemName == elemName) {
                        arrayOfDataCoordsAtThisTime[kArr].data[kData].x = evtMove.pageX - obj.offset().left
                        arrayOfDataCoordsAtThisTime[kArr].data[kData].y = evtMove.pageY - obj.offset().top
                    }
                });
            });
        })
    });

    this.mouseleave(function() {
        arrayOfDataCoordsAtThisTime.forEach((vArr, kArr) => {
            if (vArr.address == _address) {
                if (vArr.data.length > 1){
                    arrayOfDataCoordsAtThisTime[kArr].data.forEach((vData, kData) => {
                        if (vData.elemName == elemName) {
                            arrayOfDataCoordsAtThisTime[kArr].data.splice(kData, 1)
                        }
                    })
                } else {
                    arrayOfDataCoordsAtThisTime.splice(kArr, 1)
                }
            }
        });
        obj.unbind('mousemove');
    });
};

function makeArrayOfDataCoordsToSendToServer()
{
    let time = Date.now()
    if (arrayOfDataCoordsAtThisTime.length > 0) {
        if (arrayOfDataCoordsToSendToServer.length == 0) {
            arrayOfDataCoordsAtThisTime.forEach(element => {
                let addressToSend = element.address
                let dataToSend = { ... element.data}
                arrayOfDataCoordsToSendToServer.push(
                    {
                        "address": addressToSend,
                        "dataToSend": [{
                            "data": dataToSend,
                            "time": time
                        }]
                    }
                )
            });
        } else {
            arrayOfDataCoordsAtThisTime.forEach( (vArr) => {
                for (let i = 0; i < arrayOfDataCoordsToSendToServer.length; i++) {
                    if (vArr.address == arrayOfDataCoordsToSendToServer[i].address) {
                        let dataToSend = { ... vArr.data}
                        arrayOfDataCoordsToSendToServer[i].dataToSend.push({ "data": dataToSend, "time": time })
                        break;
                    }
                    if (i == arrayOfDataCoordsToSendToServer.length - 1) {
                        let dataToSend = { ... vArr.data}
                        arrayOfDataCoordsToSendToServer.push(
                            {
                                "address": vArr.address,
                                "dataToSend": [{
                                    "data": dataToSend,
                                    "time": time
                                }]
                            }
                        )
                        break
                    }
                }
            })
        }
    }
}

function sendDataWithMouseTrackerTimeToServer()
{   
    // добавить хедер на разрешение доступа со сторонних источников на сервере
    if (arrayOfDataCoordsToSendToServer.length > 0) {
        arrayOfDataCoordsToSendToServer.forEach(element => {
            fetch(element.address, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(element.dataToSend),
            })
            .then(response => response.json())
            .then(data => {
                console.log('Success:', data);
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        });
        arrayOfDataCoordsToSendToServer = [];
    }
}