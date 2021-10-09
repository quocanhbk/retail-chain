import 'dart:io';

import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/material.dart';
import 'package:flutter_beep/flutter_beep.dart';
import 'package:intl/intl.dart' as intl;
import 'package:qr_code_scanner/qr_code_scanner.dart';

import 'cartItemCard.dart';



class ScanBarcode extends StatefulWidget {
  @override
  _ScanBarcodeState createState() => _ScanBarcodeState();
}

class _ScanBarcodeState extends State<ScanBarcode> {
  late QRViewController controller;
  final GlobalKey qrKey = GlobalKey(debugLabel: 'QR');
  bool firstTimeCall = true;
  bool enableScan = true;
  bool flashToggle = false;
  @override
  void reassemble() {
    super.reassemble();
    if (Platform.isAndroid) {
      controller.pauseCamera();
    } else if (Platform.isIOS) {
      controller.resumeCamera();
    }
  }
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(),
      body: Container(
        child: Column(
          children: [
            Expanded(
              flex: 3,
              child: Stack(
                children: [QRView(
                  onQRViewCreated: _onQRViewCreated, key: qrKey,
                  overlay:
                    RectangularScannerOverlayShape(
                        borderColor: Colors.red,
                        borderRadius:  MediaQuery.of(context).size.height*0.3*0.02,
                        borderLength: MediaQuery.of(context).size.height*0.3*0.03,
                        borderWidth: MediaQuery.of(context).size.height*0.3*0.02,
                        cutOutSize: MediaQuery.of(context).size.height*0.3,
                    )
                  // QrScannerOverlayShape(
                  //   borderColor: Colors.red,
                  //   borderRadius:  MediaQuery.of(context).size.height*0.3*0.02,
                  //   borderLength: MediaQuery.of(context).size.height*0.3*0.05,
                  //   borderWidth: MediaQuery.of(context).size.height*0.3*0.05,
                  //   cutOutSize: MediaQuery.of(context).size.height*0.3*1.7/3,
                  // ),
                ),
                  Align(
                    alignment: Alignment.bottomCenter,
                    child: IconButton(
                      icon: flashToggle?Icon(Icons.flash_off):Icon(Icons.flash_on),
                      onPressed: ()async{
                        await controller.toggleFlash();
                        setState(() {
                          flashToggle=!flashToggle;
                        });
                      },
                    ),
                  )
                ]
              ),
            ),
            Expanded(
              flex: 7,
              child: StreamBuilder(
                stream: BkrmService().cartServiceStream,
                initialData: {},
                builder: (context,snapshot){
                  if (firstTimeCall) {
                    BkrmService().requestCart();
                    firstTimeCall = false;
                  }
                  debugPrint(snapshot.data.toString());
                  Map<dynamic, dynamic> cartStreamMap =
                  snapshot.data as Map<dynamic, dynamic>;
                  if (cartStreamMap.length == 0 ||
                      cartStreamMap["listCartItem"].isEmpty) {
                    return Container(
                      child: Center(
                        child: Text(
                          "Không có gì trong giỏ hàng!!",
                          style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.w400),
                        ),
                      ),
                    );
                  }else{
                    return Column(
                      children: [Expanded(
                        child: ListView.builder(
                          shrinkWrap: true,
                            itemCount: cartStreamMap["listCartItem"].length,
                            itemBuilder: (context, index) {
                              return CartItemCard(
                                  cartStreamMap["listCartItem"][index]);
                            }),
                      ),
                        Divider(),
                        Padding(
                          padding: EdgeInsets.all(8),
                          child: Row(
                            children: [
                              Expanded(
                                  flex: 1,
                                  child: Container(
                                    alignment: Alignment.centerLeft,
                                    child: Text(
                                      "Tổng tiền ",
                                      style: TextStyle(fontWeight: FontWeight.bold),
                                    ),
                                  )),
                              Expanded(
                                  flex: 1,
                                  child: Container(
                                    alignment: Alignment.centerRight,
                                    child: Text(
                                      intl.NumberFormat().format(
                                          cartStreamMap["totalDiscountPrice"]) +
                                          " VNĐ",
                                      style: TextStyle(fontWeight: FontWeight.bold),
                                    ),
                                  ))
                            ],
                          ),
                        )]
                    );
                  }
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
  void _onQRViewCreated(QRViewController controller) {
    DateTime onCreate = DateTime.now();
    Duration? length;
    this.controller = controller;
    controller.scannedDataStream.listen((scanData) async{
      if(length==null){
        length=DateTime.now().difference(onCreate);
        debugPrint("Time :"+length.toString());
      }
      if(this.enableScan){
        this.enableScan=false;
        FlutterBeep.beep();
        List<ItemInfo> resultItem = await BkrmService()
            .searchItemInBranch(barCode: scanData.code);
        if(resultItem.isNotEmpty){
          BkrmService().cart!.addCartItem(resultItem.first, 1);
        }
        Future.delayed(Duration(seconds: 3),(){
          this.enableScan=true;
        });

      }
    });
  }
  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }
}


class RectangularScannerOverlayShape extends QrScannerOverlayShape {
  RectangularScannerOverlayShape({
    Color borderColor = Colors.red,
    double borderWidth = 3.0,
    Color overlayColor = const Color.fromRGBO(0, 0, 0, 80),
    double borderRadius = 0,
    double borderLength = 40,
    double cutOutSize = 250,
    double cutOutBottomOffset = 0,
  }) : super(
    borderColor: borderColor,
    borderWidth: borderWidth,
    overlayColor: overlayColor,
    borderRadius: borderRadius,
    borderLength: borderLength,
    cutOutSize: cutOutSize,
    cutOutBottomOffset: cutOutBottomOffset,
  );

  @override
  void paint(Canvas canvas, Rect rect, {TextDirection? textDirection}) {
    final width = rect.width;
    final borderWidthSize = width / 2;
    final height = rect.height;
    final borderOffset = borderWidth / 2;
    final _borderLength = borderLength > cutOutSize / 2 + borderWidth * 2
        ? borderWidthSize / 2
        : borderLength;
    final _cutOutSize = cutOutSize != null && cutOutSize < width
        ? cutOutSize
        : width - borderOffset;

    final backgroundPaint = Paint()
      ..color = overlayColor
      ..style = PaintingStyle.fill;

    final borderPaint = Paint()
      ..color = borderColor
      ..style = PaintingStyle.stroke
      ..strokeWidth = borderWidth;

    final boxPaint = Paint()
      ..color = borderColor
      ..style = PaintingStyle.fill
      ..blendMode = BlendMode.dstOut;

    final cutOutRect = Rect.fromLTWH(
      rect.left + width / 2 - _cutOutSize / 2 + borderOffset,
      rect.top + height / 2 - _cutOutSize / 3 + borderOffset,
      _cutOutSize - borderOffset * 2,
      _cutOutSize / 2 - borderOffset * 2- cutOutBottomOffset,
    );

    canvas
      ..saveLayer(
        rect,
        backgroundPaint,
      )
      ..drawRect(
        rect,
        backgroundPaint,
      )
    // Draw top right corner
      ..drawRRect(
        RRect.fromLTRBAndCorners(
          cutOutRect.right - _borderLength,
          cutOutRect.top,
          cutOutRect.right,
          cutOutRect.top + _borderLength,
          topRight: Radius.circular(borderRadius),
        ),
        borderPaint,
      )
    // Draw top left corner
      ..drawRRect(
        RRect.fromLTRBAndCorners(
          cutOutRect.left,
          cutOutRect.top,
          cutOutRect.left + _borderLength,
          cutOutRect.top + _borderLength,
          topLeft: Radius.circular(borderRadius),
        ),
        borderPaint,
      )
    // Draw bottom right corner
      ..drawRRect(
        RRect.fromLTRBAndCorners(
          cutOutRect.right - _borderLength,
          cutOutRect.bottom - _borderLength,
          cutOutRect.right,
          cutOutRect.bottom,
          bottomRight: Radius.circular(borderRadius),
        ),
        borderPaint,
      )
    // Draw bottom left corner
      ..drawRRect(
        RRect.fromLTRBAndCorners(
          cutOutRect.left,
          cutOutRect.bottom - _borderLength,
          cutOutRect.left + _borderLength,
          cutOutRect.bottom,
          bottomLeft: Radius.circular(borderRadius),
        ),
        borderPaint,
      )
      ..drawRRect(
        RRect.fromRectAndRadius(
          cutOutRect,
          Radius.circular(borderRadius),
        ),
        boxPaint,
      )
      ..restore();
  }
}