import 'dart:typed_data';

import 'package:bkrm/pages/Nav2App.dart';
import 'package:bkrm/services/info/invoice/invoiceReceivedWhenCreated.dart';
import 'package:bkrm/services/services.dart';
import 'package:blue_thermal_printer/blue_thermal_printer.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:tiengviet/tiengviet.dart';

class FormatBluetooth {
  static final defaultSize = Uint8List.fromList([0x1b, 0x21, 0x00]); //default
  static final Uint8List normalSizeText =
      Uint8List.fromList([0x1B, 0x21, 0x03]);
  static final smallSize = Uint8List.fromList([0x1b, 0x21, 0x00]); //small font
  static final bold = Uint8List.fromList([0x1b, 0x21, 0x08]); //bold
  static final doubleHeight =
      Uint8List.fromList([0x1b, 0x21, 0x10]); //doubleHeight
  static final doubleWidth =
      Uint8List.fromList([0x1b, 0x21, 0x20]); //doubleWidth
  static final doubleHeightAndWidth =
      Uint8List.fromList([0x1b, 0x21, 0x30]); //doubleHeightAndWidth
  static final alignLeft = Uint8List.fromList([0x1b, 0x61, 0x00]);
  static final alignRight = Uint8List.fromList([0x1b, 0x61, 0x02]);
  static final alignCenter = Uint8List.fromList([0x1b, 0x61, 0x01]);
  static final characterSize = Uint8List.fromList([0x1D, 0x21, 0x00]);
}

class InvoicePrinter extends StatefulWidget {
  DetailInvoiceInfo? invoice;

  InvoicePrinter(this.invoice);

  @override
  _InvoicePrinterState createState() => _InvoicePrinterState();
}

class _InvoicePrinterState extends State<InvoicePrinter> {
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
    _devices = devices;
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

  printInvoice() async {
    await bluetooth.isConnected.then((isConnected) async {
      if (isConnected!) {
        await bluetooth.writeBytes(Uint8List.fromList([
          0x1B,
          0x4D,
          0x00
        ])); // 32 character / line = 0x00 - 42 character / line = 0x01 - 48 character / line = 0x03
        await printCustom(
            TiengViet.parse(BkrmService().currentUser!.branchName),
            FormatBluetooth.alignCenter,
            FormatBluetooth.doubleHeight);
        await printCustom(
            TiengViet.parse(BkrmService().currentUser!.branchAddress),
            FormatBluetooth.alignCenter,
            FormatBluetooth.smallSize);
        await printCustom("--------------------------------",
            FormatBluetooth.alignCenter, FormatBluetooth.smallSize);
        await printCustom(TiengViet.parse("H??a ????n"),
            FormatBluetooth.alignCenter, FormatBluetooth.doubleHeightAndWidth);
        await printCustom(
            TiengViet.parse("Kh??ch h??ng:" +
                (widget.invoice!.invoiceInfo!.customerName == null
                    ? "Kh??ch h??ng l???"
                    : widget.invoice!.invoiceInfo!.customerName!)),
            FormatBluetooth.alignLeft,
            FormatBluetooth.smallSize);
        await printCustom(
            TiengViet.parse("Nh??n vi??n thanh to??n :" +
                widget.invoice!.invoiceInfo!.sellerName!),
            FormatBluetooth.alignLeft,
            FormatBluetooth.smallSize);
        await printCustom(
            TiengViet.parse("Th???i gian: " +
                DateFormat("HH:mm:ss dd-MM-yyyy")
                    .format(widget.invoice!.invoiceInfo!.createdDatetime!)),
            FormatBluetooth.alignLeft,
            FormatBluetooth.smallSize);
        await printCustom("--------------------------------",
            FormatBluetooth.alignCenter, FormatBluetooth.smallSize);
        for (Item item in widget.invoice!.items) {
          await printCustom(TiengViet.parse(item.name),
              FormatBluetooth.alignLeft, FormatBluetooth.smallSize);
          await printLeftRight("????n gi??: " + item.sellPrice.toString(),
              "SL:" + item.quantity.toString(), FormatBluetooth.smallSize);
          await printCustom(
              TiengViet.parse("Th??nh ti???n : " +
                  (item.sellPrice! * item.quantity!).toString()),
              FormatBluetooth.alignLeft,
              FormatBluetooth.smallSize);
          await bluetooth.printNewLine();
        }
        await printCustom("--------------------------------",
            FormatBluetooth.alignCenter, FormatBluetooth.smallSize);
        await printLeftRight(
            "T???ng c???ng :",
            (widget.invoice!.invoiceInfo!.totalSellPrice! +
                    widget.invoice!.invoiceInfo!.discount!)
                .toString(),
            FormatBluetooth.smallSize);
        await printLeftRight(
            "Gi???m gi?? :",
            widget.invoice!.invoiceInfo!.discount.toString(),
            FormatBluetooth.smallSize);
        await printLeftRight(
            "Thanh to??n :",
            widget.invoice!.invoiceInfo!.totalSellPrice.toString(),
            FormatBluetooth.smallSize);
        await printCustom("--------------------------------",
            FormatBluetooth.alignCenter, FormatBluetooth.smallSize);
        if (widget.invoice!.invoiceInfo!.invoiceId != -1) {
          await bluetooth.printQRcode(
              widget.invoice!.invoiceInfo!.invoiceId.toString(), 200, 200, 1);
        }
        await printCustom(TiengViet.parse("Xin c???m ??n qu?? kh??ch"),
            FormatBluetooth.alignCenter, FormatBluetooth.smallSize);
        await printCustom(TiengViet.parse("H???n g???p l???i"),
            FormatBluetooth.alignCenter, FormatBluetooth.smallSize);
        await bluetooth.printNewLine();
        await bluetooth.printNewLine();
        await bluetooth.paperCut();
        await bluetooth.disconnect();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder(
        future: bluetoothInit,
        builder: (context, snapshow) {
          if (!snapshow.hasData) {
            return AlertDialog(
              content: Container(
                height: 50,
                child: Center(
                  child: CircularProgressIndicator(),
                ),
              ),
            );
          } else {
            if (_devices == null || _devices!.length == 0) {
              return AlertDialog(
                  content: Container(
                    height: 100,
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
                  ),
                  actions: [
                    ElevatedButton(
                        onPressed: () async {
                          await initPlatformState();
                          setState(() {});
                        },
                        child: Text("Th??? l???i")),
                    ElevatedButton(
                        onPressed: () async {
                          Navigator.pop(context);
                        },
                        child: Text("????ng"))
                  ]);
            } else {}
            return AlertDialog(
              title: Text("Ch???n m??y in"),
              actions: [
                ElevatedButton(
                    onPressed: () async {
                      Navigator.pop(context);
                    },
                    child: Text("????ng"))
              ],
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
                        height: 400,
                        width: MediaQuery.of(context).size.height * 2 / 3,
                        child: ListView.builder(
                            shrinkWrap: true,
                            itemCount: _devices!.length,
                            itemBuilder: (context, index) {
                              return ListTile(
                                title: Text(_devices![index].name!),
                                onTap: () {
                                  bluetooth.isConnected.then((isConnected) {
                                    debugPrint("???? k???t n???i ??c v???i bluetooth");
                                    bluetooth.disconnect();
                                    if (!isConnected!) {
                                      bluetooth
                                          .connect(_devices![index])
                                          .then((value) async {
                                        debugPrint(
                                            "???? k???t n???i ???????c v???i thi???t b??? in");
                                        await printInvoice();
                                        BkrmService().cart!.clearCart();
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
                                                            "Kh??ng k???t n???i ???????c v???i thi???t b???"),
                                                        Text("Vui l??ng th??? l???i")
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
                                                      Text("Vui l??ng th??? l???i")
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
                                },
                              );
                            }));
                  }
                },
              ),
            );
          }
        });
/*    return AlertDialog(
        title: Text("Ch???n m??y in"),
        actions: _devices!=null&&_devices!.length==0?[ElevatedButton(onPressed: ()async{
          await initPlatformState();
          setState(() {

          });
        }, child: Text("Th??? l???i")),ElevatedButton(onPressed: ()async{
            Navigator.pop(context);
        }, child: Text("????ng"))]:null,
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
                height: _devices!.length==0?130:400,
                width: MediaQuery.of(context).size.height * 2 / 3,
                child: _devices!.length == 0
                    ? Container(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [Center(
                            child: Text(
                              "Kh??ng t??m th???y thi???t b??? n??o. H??y ch???c ch???n r???ng b???n ???? b???t bluetootth tr??n ??i???n tho???i!",
                              textAlign: TextAlign.center,
                              style: TextStyle(fontSize: 20,fontWeight: FontWeight.w400,color: Colors.grey),
                            ),
                          ),
*/ /*                          Container(
                            alignment: Alignment.centerRight,
                            child: TextButton(
                              onPressed: (){Navigator.pop(context);},
                              child: Text("????ng"),
                            ),
                          )*/ /*]
                        ),
                      )
                    : ListView.builder(
                        shrinkWrap: true,
                        itemCount: _devices!.length,
                        itemBuilder: (context, index) {
                          return ListTile(
                            title: Text(_devices![index].name!),
                            onTap: () {
                              bluetooth.isConnected.then((isConnected) {
                                debugPrint("???? k???t n???i ??c v???i bluetooth");
                                bluetooth.disconnect();
                                if (!isConnected!) {
                                  bluetooth
                                      .connect(_devices![index])
                                      .then((value) async {
                                    debugPrint(
                                        "???? k???t n???i ???????c v???i thi???t b??? in");
                                    await printInvoice();
                                    BkrmService().cart!.clearCart();
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
                                                      CrossAxisAlignment.center,
                                                  children: [
                                                    Text(
                                                        "Kh??ng k???t n???i ???????c v???i thi???t b???"),
                                                    Text("Vui l??ng th??? l???i")
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
                                  });
                                } else {
                                  showDialog(
                                      context: context,
                                      builder: (context) {
                                        return AlertDialog(
                                          title: Center(
                                            child: Column(
                                                crossAxisAlignment:
                                                    CrossAxisAlignment.center,
                                                children: [
                                                  Text(
                                                      "Kh??ng k???t n???i ???????c v???i thi???t b???"),
                                                  Text("Vui l??ng th??? l???i")
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
                            },
                          );
                        }),
              );
            }
          },
        ),
);*/
  }
}

class PreviewPrinterPage extends StatelessWidget {
  DetailInvoiceInfo invoice;
  List<Widget> listWidgetItem = [];
  PreviewPrinterPage(this.invoice) {
    for (Item item in this.invoice.items) {
      listWidgetItem.add(Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(TiengViet.parse(item.name)),
          Row(
            children: [
              Expanded(
                  flex: 1,
                  child: Container(
                    alignment: Alignment.centerLeft,
                    child: Text(TiengViet.parse(
                        "????n gi??:" + item.sellPrice.toString())),
                  )),
              Expanded(
                flex: 1,
                child: Container(
                    alignment: Alignment.centerRight,
                    child: Text(
                      TiengViet.parse("SL:" + item.quantity.toString()),
                    )),
              ),
            ],
          ),
          Text(TiengViet.parse("Th??nh ti???n :") +
              (item.sellPrice! * item.quantity!).toString()),
          SizedBox(
            height: 10,
          )
        ],
      ));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text("Xem tr?????c h??a ????n"),
        actions: [IconButton(icon: Icon(Icons.print), onPressed: () {})],
      ),
      body: SingleChildScrollView(
        child: Container(
          child: Center(
            child: Container(
              width: 200,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Text(
                      TiengViet.parse(BkrmService().currentUser!.branchName),
                      style:
                          TextStyle(fontWeight: FontWeight.bold, fontSize: 24),
                    ),
                  ),
                  Center(
                    child: Text(
                      TiengViet.parse(BkrmService().currentUser!.branchAddress),
                      style:
                          TextStyle(fontWeight: FontWeight.w400, fontSize: 20),
                    ),
                  ),
                  Center(
                    child: Text(
                      "--------------------------------",
                      style:
                          TextStyle(fontWeight: FontWeight.w400, fontSize: 20),
                    ),
                  ),
                  Center(
                    child: Text(
                      TiengViet.parse("H??a ????n"),
                      style:
                          TextStyle(fontWeight: FontWeight.bold, fontSize: 24),
                    ),
                  ),
                  Text(TiengViet.parse("Kh??ch h??ng: " +
                      (invoice.invoiceInfo!.customerName == null
                          ? "Kh??ch h??ng l???"
                          : invoice.invoiceInfo!.customerName!))),
                  Text(TiengViet.parse("Nh??n vi??n thanh to??n: " +
                      invoice.invoiceInfo!.sellerName!)),
                  Text(
                    TiengViet.parse("Th???i gian: " +
                        DateFormat("HH:mm:ss dd-MM-yyyy")
                            .format(invoice.invoiceInfo!.createdDatetime!)),
                  ),
                  Center(
                    child: Text(
                      "--------------------------------",
                      style:
                          TextStyle(fontWeight: FontWeight.w400, fontSize: 20),
                    ),
                  ),
                  Column(
                    children: listWidgetItem,
                  ),
                  Center(
                    child: Text(
                      "--------------------------------",
                      style:
                          TextStyle(fontWeight: FontWeight.w400, fontSize: 20),
                    ),
                  ),
                  Text(TiengViet.parse("T???ng c???ng: " +
                      (invoice.invoiceInfo!.totalSellPrice! +
                              invoice.invoiceInfo!.discount!)
                          .toString())),
                  Text(TiengViet.parse(
                      "Gi???m gi??: " + invoice.invoiceInfo!.discount.toString())),
                  Text(TiengViet.parse("Thanh to??n: " +
                      invoice.invoiceInfo!.totalSellPrice.toString())),
                  Center(
                    child: Text(
                      "--------------------------------",
                      style:
                          TextStyle(fontWeight: FontWeight.w400, fontSize: 20),
                    ),
                  ),
                  Center(child: Text(TiengViet.parse("Xin c???m ??n qu?? kh??ch"))),
                  Center(child: Text(TiengViet.parse("H???n g???p l???i"))),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
