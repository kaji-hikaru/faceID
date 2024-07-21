document.addEventListener('DOMContentLoaded', function () {
    var reg = document.getElementById('registerBtn');
    var loginBtn = document.getElementById('loginBtn');
    var redv = document.getElementById('REG');
    var LOGIN = document.getElementById('LOGIN');
    var snapButton = document.getElementById('snap');
    var loginSnapButton = document.getElementById('loginSnap');
    var nameInput = document.getElementById('name');
    var loginNameInput = document.getElementById('loginName');
    var FR = document.querySelector('.front');

    reg.addEventListener('click', function () {
        redv.style.display = "block";
        LOGIN.style.display = "none";
        FR.style.display = "none";

        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function (stream) {
                var video = document.getElementById('video');
                video.srcObject = stream;
                video.onloadedmetadata = function () {
                    video.play();
                };
            })
            .catch(function (err) {
                console.log("エラー: " + err);
            });
    });

    loginBtn.addEventListener('click', function () {
        redv.style.display = "none";
        LOGIN.style.display = "block";
        FR.style.display = "none";

        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function (stream) {
                var loginVideo = document.getElementById('loginVideo');
                loginVideo.srcObject = stream;
                loginVideo.onloadedmetadata = function () {
                    loginVideo.play();
                };
            })
            .catch(function (err) {
                console.log("エラー" + err);
            });
    });

    nameInput.addEventListener('input', function () {
        snapButton.disabled = nameInput.value.trim() === '';
    });

    loginNameInput.addEventListener('input', function () {
        loginSnapButton.disabled = loginNameInput.value.trim() === '';
    });

    snapButton.addEventListener('click', function () {
        var canvas = document.getElementById('canvas');
        var context = canvas.getContext('2d');
        var video = document.getElementById('video');
        context.drawImage(video, 0, 0, 1000, 600);

        video.pause();

        var dataURL = canvas.toDataURL('image/png');
        var name = document.getElementById('name').value;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'face_invers.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                alert(xhr.responseText);
                video.play();
            }
        };
        xhr.send('imgdata=' + encodeURIComponent(dataURL) + '&name=' + encodeURIComponent(name));
    });

    loginSnapButton.addEventListener('click', function () {
        var loginCanvas = document.getElementById('loginCanvas');
        var context = loginCanvas.getContext('2d');
        var loginVideo = document.getElementById('loginVideo');
        context.drawImage(loginVideo, 0, 0, 1000, 600);

        loginVideo.pause();


        var loginDataURL = loginCanvas.toDataURL('image/png');
        var loginName = loginNameInput.value;


        var loginXhr = new XMLHttpRequest();
        loginXhr.open('POST', 'face_invers.php', true);
        loginXhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');


        loginXhr.onreadystatechange = function () {
            if (loginXhr.readyState === 4 && loginXhr.status === 200) {
                loginVideo.play();
                alert(loginXhr.responseText);
            }
            else if (loginXhr.readyState === 4) {
                alert("エラーが発生しました: " + loginXhr.status);
            }
        };

        loginXhr.send('loginImgdata=' + encodeURIComponent(loginDataURL) + '&loginName=' + encodeURIComponent(loginName));
    });
});