var _data = [];
var index = 0;
var jumlah = 0;
var _callback = null;

var ci = {
    compress : function (files = null, MAX_WIDTH = 480, callback) {
        if (typeof callback == 'function'){
            _callback = callback;
        }

        jumlah = files.length;

        if ( index < jumlah ) {
            ci.compress_img( files[index], files[index].name, MAX_WIDTH, function(file) {
                _data[index] = file;
                index++;

                ci.compress(files, MAX_WIDTH);
            });
        } else {
            if (typeof _callback == 'function'){
                files = null;
                index = 0;
                jumlah = 0;
                
                var data = _data;
                _data = [];

                _callback(data);
            }
        }
    }, // end - compress

    compress_img : function(file, filename, MAX_WIDTH, callback) {
        if (!file) return;

        var srcEncoded = null;
        const reader = new FileReader();

        reader.readAsDataURL(file);

        reader.onload = function (event) {
            const imgElement = document.createElement("img");
            imgElement.src = event.target.result;
            // document.querySelector("#input").src = event.target.result;

            imgElement.onload = function (e) {
                const canvas = document.createElement("canvas");
                // const MAX_WIDTH = 480;

                const scaleSize = MAX_WIDTH / e.target.width;
                canvas.width = MAX_WIDTH;
                canvas.height = e.target.height * scaleSize;

                const ctx = canvas.getContext("2d");

                ctx.drawImage(e.target, 0, 0, canvas.width, canvas.height);

                srcEncoded = ctx.canvas.toDataURL(e.target, "image/jpeg");

                if (typeof callback == 'function'){
                    callback(ci.dataURLtoFile(srcEncoded, filename));
                }
            };
        };
    }, // end - compress_img

    dataURLtoFile : function(dataurl, filename) {
        var arr = dataurl.split(','),
            mime = arr[0].match(/:(.*?);/)[1],
            bstr = atob(arr[1]), 
            n = bstr.length, 
            u8arr = new Uint8Array(n);
            
        while(n--){
            u8arr[n] = bstr.charCodeAt(n);
        }
        
        return new File([u8arr], filename, {type:mime});
    }
};
