import 'dart:typed_data';

import 'package:bkrm/widget/customerFormatter.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:tiengviet/tiengviet.dart';

import 'package:bkrm/services/services.dart';
import 'invoice_printer.dart';

import 'package:blue_thermal_printer/blue_thermal_printer.dart';

class BarcodePrinter extends StatefulWidget {
  String barcode;
  String name;

  BarcodePrinter(this.barcode, this.name);

  @override
  _BarcodePrinterState createState() => _BarcodePrinterState();
}

class _BarcodePrinterState extends State<BarcodePrinter> {
  TextEditingController amountController = TextEditingController();
  bool amountValid = false;
  int amount = 0;

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

  printBarcodeItem(String barCode, String name) async {
    await bluetooth.isConnected.then((isConnected) async {
      await bluetooth.writeBytes(Uint8List.fromList([0x1B, 0x4D, 0x00]));
      await bluetooth.printBarcode(barCode, 384, 60, 1);
      await bluetooth.printNewLine();
      String first;
      String second;
      if(name.length>28){
        first=name.substring(0,27);
        if(name.length>56){
          second=name.substring(28,52)+"...";
        }else{
          second=name.substring(28,name.length-1);
        }
      }else{
        first=name;
        second="";
      }
        await printCustom(TiengViet.parse(first), FormatBluetooth.alignCenter,
            FormatBluetooth.defaultSize);
      await printCustom(TiengViet.parse(second), FormatBluetooth.alignCenter,
          FormatBluetooth.defaultSize);
      await bluetooth.printNewLine();
      await bluetooth.printNewLine();
      await bluetooth.printNewLine();
      await bluetooth.paperCut();
    });
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text("Ch???n m??y in"),
      actions: (_devices == null || _devices!.length == 0)
          ? [
              ElevatedButton(
                  onPressed: () async {
                    await initPlatformState();
                    setState(() {});
                  },
                  child: Text("Th??? l???i"))
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
                          "Kh??ng t??m th???y thi???t b??? n??o. H??y ch???c ch???n r???ng b???n ???? b???t bluetootth tr??n ??i???n tho???i!",
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
                                  debugPrint("amountValid: "+amountValid.toString());
                                  if (amountValid) {
                                      bluetooth.isConnected.then((isConnected) {
                                        debugPrint(
                                            "???? k???t n???i ??c v???i bluetooth");
                                        bluetooth.disconnect();
                                        if (!isConnected!) {
                                          bluetooth
                                              .connect(_devices![index])
                                              .then((value) async {
                                            debugPrint(
                                                "???? k???t n???i ???????c v???i thi???t b??? in");
                                            for(int i=0;i<amount;i++){
                                              await printBarcodeItem(
                                                  widget.barcode, widget.name);
                                            }
                                            await bluetooth.disconnect();
                                            Navigator.pop(context);
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
                                                              "Kh??ng k???t n???i ???????c v???i thi???t b???. Vui l??ng th??? l???i!",
                                                              textAlign:
                                                                  TextAlign
                                                                      .center,
                                                            ),
                                                          ]),
                                                    ),
                                                    actions: [
                                                      FlatButton(
                                                        child: Text("????ng"),
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
                                                              "Kh??ng k???t n???i ???????c v???i thi???t b???"),
                                                          Text(
                                                              "Vui l??ng th??? l???i")
                                                        ]),
                                                  ),
                                                  actions: [
                                                    FlatButton(
                                                      child: Text("????ng"),
                                                      onPressed: () {
                                                        Navigator.pop(context);
                                                      },
                                                    )
                                                  ],
                                                );
                                              });
                                        }
                                      });
                                    }
                                },
                              );
                            }),
                      ),
                      Divider(),
                      Container(
                        height: 100,
                        child: Row(
                          children: [
                            Expanded(
                                flex: 3,
                                child: Text("Nh???p s??? l?????ng mu???n in: ")),
                            Expanded(
                                child: TextFormField(
                                  inputFormatters: [CustomerFormatter().numberFormatter],
                              keyboardType: TextInputType.phone,
                              controller: amountController,
                              autovalidateMode: AutovalidateMode.always,
                              validator: (String? amount) {
                                if (amount == null || amount == "") {
                                  amountValid = false;
                                  return " *B???t bu???c";
                                }
                                amount = amount.replaceAll(",", "");
                                amount = amount.replaceAll(".", "");
                                int amountNumber = int.tryParse(amount) == null
                                    ? 0
                                    : int.parse(amount);
                                debugPrint(amountNumber.toString());
                                if (amountNumber <= 0) {
                                  amountValid = false;
                                  return "Nh???p s??? l???n h??n 0";
                                } else {
                                  amountValid = true;
                                  this.amount=amountNumber;
                                  return null;
                                }
                              },
                            ))
                          ],
                        ),
                      )
                    ]),
            );
          }
        },
      ),
    );
  }
}
