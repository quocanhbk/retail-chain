import 'dart:typed_data';

import 'package:bkrm/widget/customerFormatter.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:tiengviet/tiengviet.dart';

import 'package:bkrm/services/services.dart';
import 'package:blue_thermal_printer/blue_thermal_printer.dart';

import 'invoice_printer.dart';

class QrCodePrinter extends StatefulWidget {
  String qrCode;

  QrCodePrinter(this.qrCode);

  @override
  _QrCodePrinterState createState() => _QrCodePrinterState();
}

class _QrCodePrinterState extends State<QrCodePrinter> {
  final Uint8List normalSizeText = Uint8List.fromList([0x1B, 0x21, 0x03]);
  final Uint8List onlyBoldText = Uint8List.fromList([0x1B, 0x21, 0x08]);
  final Uint8List boldWithLargeText = Uint8List.fromList([0x1B, 0x21, 0x20]);
  final Uint8List strongText = Uint8List.fromList([0x1B, 0x21, 0x30]);

  final characterOnOneLine = 32;
  List<BluetoothDevice>? _devices;
  BlueThermalPrinter bluetooth = BlueThermalPrinter.instance;
  Future<List<BluetoothDevice>?>? bluetoothInit;
  @override
  void initState() {
    super.initState();
    bluetoothInit = initPlatformState();
  }

  Future<List<BluetoothDevice>?> initPlatformState() async {
    List<BluetoothDevice> devices = [];
    try {
      devices = await bluetooth.getBondedDevices();
    } on PlatformException {
      // TODO - Error
      debugPrint("Platform Exception");
    }
    setState(() {
      _devices = devices;
    });
    return _devices;
  }

  printCustom(String message, Uint8List align, Uint8List size) async {
    await bluetooth.writeBytes(FormatBluetooth.characterSize);
    await bluetooth.writeBytes(size);
    await bluetooth.writeBytes(align);
    await bluetooth.write(message);
    await bluetooth.printNewLine();
  }

  printLeftRight(String left, String right, Uint8List size) async {
    String defaultString = "";
    String remainLeftString = "";
    String remainRightString = "";
    if (left.length > characterOnOneLine ~/ 2) {
      defaultString = left.substring(0, characterOnOneLine ~/ 2 - 1);
      remainLeftString =
          left.substring(characterOnOneLine ~/ 2, left.length - 1);
    } else {
      defaultString += left;
      for (int i = 0; i < (characterOnOneLine ~/ 2 - left.length); i++) {
        defaultString += " ";
      }
    }
    if (right.length > characterOnOneLine ~/ 2) {
      defaultString += right.substring(0, characterOnOneLine ~/ 2 - 1);
      remainRightString =
          right.substring(characterOnOneLine ~/ 2, right.length - 1);
    } else {
      for (int i = 0; i < (characterOnOneLine ~/ 2 - right.length); i++) {
        defaultString += " ";
      }
      defaultString += right;
    }
    await printCustom(
        TiengViet.parse(defaultString), FormatBluetooth.alignLeft, size);
    if (remainLeftString != "" || remainRightString != "") {
      await printLeftRight(remainLeftString, remainRightString, size);
    }
  }

  printQrCode(String qrCode) async {
    await bluetooth.isConnected.then((isConnected) async {
      await bluetooth.writeBytes(Uint8List.fromList([0x1B, 0x4D, 0x00]));
      await bluetooth.printQRcode(qrCode, 384, 384, 1);
    });
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text("Chọn máy in"),
      actions: (_devices == null || _devices!.length == 0)
          ? [
        ElevatedButton(
            onPressed: () async {
              await initPlatformState();
              setState(() {});
            },
            child: Text("Thử lại"))
      ]
          : null,
      content: FutureBuilder(
        future: bluetoothInit,
        builder: (context, snapshot) {
          if (!snapshot.hasData) {
            return Container(
              height: 300,
              child: Center(
                child: CircularProgressIndicator(),
              ),
            );
          } else {
            return Container(
              height: (_devices == null || _devices!.length == 0)
                  ? 100
                  : MediaQuery.of(context).size.height * 2 / 3,
              width: MediaQuery.of(context).size.height * 2 / 3,
              child: _devices!.length == 0
                  ? Container(
                child: Center(
                  child: Text(
                    "Không tìm thấy thiết bị nào. Hãy chắc chắn rằng bạn đã bật bluetootth trên điện thoại!",
                    textAlign: TextAlign.center,
                    style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w400,
                        color: Colors.grey),
                  ),
                ),
              )
                  : Column(children: [
                Expanded(
                  child: ListView.builder(
                      shrinkWrap: true,
                      itemCount: _devices!.length,
                      itemBuilder: (context, index) {
                        return ListTile(
                          title: Text(_devices![index].name!),
                          onTap: () {
                              bluetooth.isConnected.then((isConnected) {
                                debugPrint(
                                    "Đã kết nối đc với bluetooth");
                                bluetooth.disconnect();
                                if (!isConnected!) {
                                  bluetooth
                                      .connect(_devices![index])
                                      .then((value) async {
                                    debugPrint(
                                        "Đã kết nối được với thiết bị in");
                                    await printQrCode(widget.qrCode);
                                    await bluetooth.disconnect();
                                    Navigator.pop(context);
                                  }).catchError((error) {
                                    debugPrint(error.toString());
                                    showDialog(
                                        context: context,
                                        builder: (context) {
                                          return AlertDialog(
                                            title: Center(
                                              child: Column(
                                                  crossAxisAlignment:
                                                  CrossAxisAlignment
                                                      .center,
                                                  children: [
                                                    Text(
                                                      "Không kết nối được với thiết bị. Vui lòng thử lại!",
                                                      textAlign:
                                                      TextAlign
                                                          .center,
                                                    ),
                                                  ]),
                                            ),
                                            actions: [
                                              FlatButton(
                                                child: Text("Đóng"),
                                                onPressed: () {
                                                  Navigator.pop(
                                                      context);
                                                },
                                              )
                                            ],
                                          );
                                        });
                                  });
                                } else {
                                  showDialog(
                                      context: context,
                                      builder: (context) {
                                        return AlertDialog(
                                          title: Center(
                                            child: Column(
                                                crossAxisAlignment:
                                                CrossAxisAlignment
                                                    .center,
                                                children: [
                                                  Text(
                                                      "Không kết nối được với thiết bị"),
                                                  Text(
                                                      "Vui lòng thử lại")
                                                ]),
                                          ),
                                          actions: [
                                            FlatButton(
                                              child: Text("Đóng"),
                                              onPressed: () {
                                                Navigator.pop(context);
                                              },
                                            )
                                          ],
                                        );
                                      });
                                }
                              });
                          },
                        );
                      }),
                ),
              ]),
            );
          }
        },
      ),
    );
  }
}
