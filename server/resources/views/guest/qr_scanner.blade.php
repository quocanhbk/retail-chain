@extends('container')
@section('content')

{{-- <script type="text/javascript" src="{{ asset('js\qr-scanner\grid.js') }}"></script>
<script type="text/javascript" src="{{ asset('js\qr-scanner\version.js') }}"></script>
<script type="text/javascript" src="{{ asset('js\qr-scanner\detector.js') }}"></script>
<script type="text/javascript" src="{{ asset('js\qr-scanner\formatinf.js') }}"></script>
<script type="text/javascript" src="{{ asset('js\qr-scanner\errorlevel.js') }}"></script>
<script type="text/javascript" src="{{ asset('js\qr-scanner\bitmat.js') }}"></script>
<script type="text/javascript" src="{{ asset('js\qr-scanner\datablock.js') }}"></script>
<script type="text/javascript" src="{{ asset('js\qr-scanner\bmparser.js') }}"></script>
<script type="text/javascript" src="{{ asset('js\qr-scanner\datamask.js') }}"></script>
<script type="text/javascript" src="{{ asset('js\qr-scanner\rsdecoder.js') }}"></script>
<script type="text/javascript" src="{{ asset('js\qr-scanner\gf256poly.js') }}"></script>
<script type="text/javascript" src="{{ asset('js\qr-scanner\gf256.js') }}"></script>
<script type="text/javascript" src="{{ asset('js\qr-scanner\decoder.js') }}"></script>
<script type="text/javascript" src="{{ asset('js\qr-scanner\qrcode.js') }}"></script>
<script type="text/javascript" src="{{ asset('js\qr-scanner\findpat.js') }}"></script>
<script type="text/javascript" src="{{ asset('js\qr-scanner\alignpat.js') }}"></script>
<script type="text/javascript" src="{{ asset('js\qr-scanner\databr.js') }}"></script> --}}

<script type="text/javascript" src="{{ asset('js\qr-scanner\html5-qrcode.min.js') }}"></script>


<nav class="navbar navbar-expand-lg navbar-dark bg-primary"  style="margin-left: -15px; margin-right: -15px;">
    <div class="col d-flex">
        <a href="{{ url()->previous() }}">
            <button class="btn btn-outline-light">
                <i class="fa fa-arrow-left fa-lg" aria-hidden="true"></i>
                Quay lại
            </button>
        </a>
    </div>
</nav>
<div class="mt-2" style="width: 100%" id="qr-scanner">
    {{-- <video id="cam-vid"src="{{ asset('test-qr.mp4') }}" autoplay loop controls muted style="width: 60%; margin-top: 20%"></video><br> --}}
    {{-- <video id="cam-vid" style="width: 90%; margin-top: 20%"></video><br>
    <canvas id="qr-canvas" hidden></canvas> --}}
    {{-- <div class="d-flex border w-100 justify-content-center align-items-center my-3" style="height: 10%" id="decode-info">
        -scanning-
    </div> --}}
</div>

<script>
    const html5QrCode = new Html5Qrcode("qr-scanner");
    const qrCodeSuccessCallback = message => { 
        if(confirm("Bạn có muốn đi đến: " + message)){
            window.location.href = message;
            stop_loop = 1
        }
     }
    const config = { fps: 10, qrbox: 250 };

    html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
    // var constraints = { video: { facingMode: { exact: "environment", width: 1280, height: 720 } } };
    // var canvas = document.getElementById("qr-canvas").getContext("2d");
    // var v = document.getElementById("cam-vid");
    // var stop_loop = 0
    // // var info = document.getElementById("decode-info");

    // //wait for the vid to load to set canvas width and height
    // function init() {
    //     if (v.readyState == 4) {
    //         $('#qr-canvas').attr('width', v.videoWidth);
    //         $('#qr-canvas').attr('height', v.videoHeight);
    //     } else {
    //         requestAnimationFrame(init);
    //     }
    // }

    // init();
    // //ask permission to use camera
    // navigator.mediaDevices.getUserMedia(constraints)
    // .then(function(mediaStream) {
    //     v.srcObject = mediaStream;
    //     v.onloadedmetadata = function(e) {
    //         v.play();
    //     };
    // })
    // .catch(function(err) { console.log(err.name + ": " + err.message); });
    // //loop each frame of vid
    // $('#cam-vid').on('timeupdate', function () {
    //     if ($('#cam-vid')[0].currentTime > 2) { //Loop for one second
    //         $('#cam-vid')[0].currentTime = 1;
    //     }

    //     var $this = $('#cam-vid')[0]; //cache
    //     (function loop() {
    //         if (!$this.paused && !$this.ended && !stop_loop) {
    //             drawCanvas();
    //             setTimeout(loop, 1000 / 25); // drawing at 25fps
    //         }
    //     })();
    // });

    // function drawCanvas(){
    //     canvas.drawImage(v, 0, 0, v.videoWidth, v.videoHeight, 0, 0, v.videoWidth, v.videoHeight);
        
    //     if(confirm("Bạn có muốn đi đến: " + qrcode.decode())){
    //         window.location.href = qrcode.decode();
    //         stop_loop = 1
    //     }
    //     // try {
    //     //     // console.log(qrcode.decode());
    //     //     // alert(qrcode.decode());
    //     //     // if(info.innerHTML != qrcode.decode()){
    //     //     //     info.innerHTML = qrcode.decode();
    //     //     // }
    //     //     // window.navigator.vibrate(200);
    //     //     // info.innerHTML += qrcode.decode();
    //     // } catch(err) {
    //     //     // info.innerHTML = err.message;
    //     // }
    // }
</script>
</html>
@endsection