import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/purchasedSheetInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/supplierInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_barcode_scanner/flutter_barcode_scanner.dart';
import 'package:intl/intl.dart';
import 'package:permission_handler/permission_handler.dart';

class ReturnPurchasedItem {
  PurchasedItem item;
  int quantity;

  ReturnPurchasedItem(this.item, this.quantity);
}

class ReturnPurchasedPage extends StatefulWidget {
  final DetailPurchasedSheetInfo? purchasedSheet;
  final List<ReturnPurchasedItem> returnItems = [];
  ReturnPurchasedPage(this.purchasedSheet);

  @override
  _ReturnPurchasedPageState createState() => _ReturnPurchasedPageState();
}

class _ReturnPurchasedPageState extends State<ReturnPurchasedPage> {
  TextEditingController reasonController = TextEditingController();
  GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey();


  bool needRefresh = false;
  @override
  void initState() {
    super.initState();
  }

  processReturnPurchasedSheet(List<Map<String,dynamic>> listRefundItems)async{

  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        FocusScope.of(context).requestFocus(FocusNode());
      },
      child: Scaffold(
        key: _scaffoldKey,
        resizeToAvoidBottomInset: true,
        appBar: AppBar(
          title: Text("Trả hàng nhập"),
        ),
        body: WillPopScope(
          onWillPop: () async {
            Navigator.pop(context, needRefresh);
            return needRefresh;
          },
          child: SingleChildScrollView(
            child: Container(
              padding: EdgeInsets.all(8.0),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Center(
                    child: Text(
                      "Trả hàng cho Đơn nhập #" +
                          widget.purchasedSheet!.importInvoiceInfo.purchasedSheetId.toString(),
                      style:
                      TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
                    ),
                  ),
                  SizedBox(
                    height: 30,
                  ),
                  Center(
                    child: Text(
                      "Chọn mặt hàng muốn trả",
                      style:
                      TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                    ),
                  ),
                  SizedBox(
                    height: 20,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Lý do: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.bold),
                          )),
                      Expanded(
                        flex: 4,
                        child: TextField(
                          controller: reasonController,
                          decoration: InputDecoration(
                            hintText: "Nhập lý do vào đây",
                          ),
                        ),
                      )
                    ],
                  ),
                  SizedBox(height: 10,),
                  Center(
                    child: IconButton(icon: Icon(Icons.qr_code),onPressed: ()async{
                      var status = await Permission.camera.status;
                      if (status.isPermanentlyDenied ||
                          status.isRestricted ||
                          status.isDenied) {
                        await Permission.camera.request();
                      }
                      var scanResult = await FlutterBarcodeScanner.scanBarcode(
                          "#ffffff",
                          "Hủy",
                          true,
                          ScanMode.DEFAULT);
                      if(scanResult=="-1"){
                        return;
                      }
                      showDialog(context: context, builder: (context){
                        return AlertDialog(
                          content: Container(
                            height: 50,
                            child: Center(
                                child:CircularProgressIndicator()
                            ),
                          ),
                        );
                      });
                      List<ItemInfo> items = await BkrmService().searchItemInBranch(barCode: scanResult);
                      if(items.isNotEmpty){
                        for(var purchasedItem in widget.purchasedSheet!.purchasedItems){
                          if(purchasedItem.itemId==items.first.itemId){
                            var listItems = widget.returnItems.where((element) => element.item.itemId==purchasedItem.itemId).toList();
                            if(listItems.isEmpty){
                              setState(() {
                                widget.returnItems.add(ReturnPurchasedItem(purchasedItem, purchasedItem.quantity!));
                              });
                            }
                          }
                        }
                      }
                      Navigator.pop(context);
                    },),
                  ),
                  SizedBox(
                    height: 20,
                  ),
                  ListView.builder(
                      physics: NeverScrollableScrollPhysics(),
                      shrinkWrap: true,
                      itemCount: widget.returnItems.length + 1,
                      itemBuilder: (context, index) {
                        if (index == widget.returnItems.length) {
                          return InkWell(
                            onTap: () {
                              showDialog(
                                  context: context,
                                  builder: (context) {
                                    return AlertDialog(
                                      title: Text("Chọn sản phẩm để đổi trả"),
                                      content: Container(
                                        decoration: BoxDecoration(
                                          border: Border.all(
                                              width: 1.0,
                                              color: Colors.white38),
                                        ),
                                        height: 300,
                                        width:
                                        MediaQuery.of(context).size.width *
                                            2 /
                                            3,
                                        child: ListView.builder(
                                            shrinkWrap: true,
                                            itemCount:
                                            widget.purchasedSheet!.purchasedItems.length,
                                            itemBuilder: (context, index) {
                                              return InkWell(
                                                onTap: () {
                                                  bool isDuplicate = false;
                                                  widget.returnItems
                                                      .forEach((element) {
                                                    if (element.item ==
                                                        widget.purchasedSheet!
                                                            .purchasedItems[index]) {
                                                      isDuplicate = true;
                                                    }
                                                  });
                                                  if (!isDuplicate) {
                                                    widget.returnItems.add(
                                                        ReturnPurchasedItem(
                                                            widget.purchasedSheet!
                                                                .purchasedItems[index],
                                                            widget.purchasedSheet!.purchasedItems[index].quantity!));
                                                    this.setState(() {});
                                                  }
                                                },
                                                child: Card(
                                                  elevation: 3.0,
                                                  child: Row(
                                                    crossAxisAlignment:
                                                    CrossAxisAlignment
                                                        .center,
                                                    children: [
                                                      Expanded(
                                                        flex: 3,
                                                        child: Padding(
                                                            padding:
                                                            EdgeInsets.all(8.0),
                                                            child: widget
                                                                .purchasedSheet!
                                                                .purchasedItems[
                                                            index]
                                                                .imageUrl ==
                                                                null||widget
                                                                .purchasedSheet!
                                                                .purchasedItems[
                                                            index]
                                                                .imageUrl=="null"
                                                                ? Image.asset(
                                                                "asset/productImage/no-image.jpg")
                                                                : CachedNetworkImage(
                                                              imageUrl: ServerConfig
                                                                  .projectUrl +
                                                                  widget
                                                                      .purchasedSheet!
                                                                      .purchasedItems[index]
                                                                      .imageUrl!,
                                                              progressIndicatorBuilder: (context,
                                                                  url,downloadProgress) =>
                                                                  SizedBox(width:20,height: 20,child: Center(child: CircularProgressIndicator(value: downloadProgress.progress,))),
                                                              errorWidget: (context,
                                                                  url,
                                                                  error) =>
                                                                  Icon(Icons
                                                                      .error),
                                                            )),
                                                      ),
                                                      Expanded(
                                                          flex: 7,
                                                          child: Padding(
                                                            padding:
                                                            EdgeInsets.all(
                                                                2.0),
                                                            child: Text(widget
                                                                .purchasedSheet!
                                                                .purchasedItems[index]
                                                                .name),
                                                          ))
                                                    ],
                                                  ),
                                                ),
                                              );
                                            }),
                                      ),
                                      actions: [
                                        FlatButton(
                                          child: Text(
                                            "Đóng",
                                            style: TextStyle(fontSize: 20),
                                          ),
                                          onPressed: () {
                                            Navigator.pop(context);
                                          },
                                        )
                                      ],
                                    );
                                  });
                            },
                            child: Container(
                              alignment: Alignment.center,
                              height: 90,
                              width: MediaQuery.of(context).size.width,
                              decoration: BoxDecoration(
                                  color: Colors.white38,
                                  border:
                                  Border.all(width: 2, color: Colors.grey)),
                              child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Center(
                                      child: Icon(
                                        Icons.add,
                                        color: Colors.grey,
                                      ),
                                    ),
                                    Center(
                                        child: Text(
                                          "Thêm sản phẩm",
                                          style: TextStyle(
                                              fontSize: 16,
                                              fontWeight: FontWeight.w300,
                                              color: Colors.grey),
                                        )),
                                  ]),
                            ),
                          );
                        }
                        debugPrint(widget.returnItems[index].item.purchasedItemId.toString());
                        return ReturnItemCard(
                          widget.returnItems[index],
                          onRemove: (ReturnPurchasedItem item) {
                            widget.returnItems
                                .removeWhere((element) => element == item);
                            setState(() {});
                          },
                        );
                      }),
                  SizedBox(
                    height: 30,
                  ),
                  SizedBox(
                    height: 30,
                  ),
                  Center(
                    child: Container(
                      height: 50,
                      width: 120,
                      child: RaisedButton(
                          onPressed: () async {
                            if (widget.returnItems.isEmpty) {
                              showDialog(
                                  context: context,
                                  builder: (context) {
                                    return AlertDialog(
                                      title: Text(
                                          "Hãy chọn ít nhất 1 sản phẩm để trả hàng"),
                                      actions: [
                                        FlatButton(
                                            onPressed: () {
                                              Navigator.pop(context);
                                            },
                                            child: Text("Đóng"))
                                      ],
                                    );
                                  });
                              return;
                            }
                              List<Map<String, dynamic>> listRefundItems = [];
                              widget.returnItems.forEach((element) {
                                if (element.quantity > 0) {
                                  listRefundItems.add({
                                    "purchased_item_id":
                                    element.item.purchasedItemId,
                                  });
                                }
                              });
                              showDialog(context: context, builder: (context){
                                return AlertDialog(
                                  content: Container(
                                    height: 50,
                                    child: Center(
                                        child:CircularProgressIndicator()
                                    ),
                                  ),
                                );
                              });
                            MsgInfoCode? returnStatus = await BkrmService()
                                .createReturnPurchasedSheet(
                                widget.purchasedSheet,
                                reasonController.value.text,
                                listRefundItems);
                            Navigator.pop(context);
                            if (returnStatus == MsgInfoCode.actionSuccess) {
                              debugPrint("Return purchased sheet success");
                              showDialog(
                                  context: context,
                                  barrierDismissible: false,
                                  builder: (context) {
                                    debugPrint("Build dialog success");
                                    return AlertDialog(
                                      title: Text("Trả hàng nhập thành công"),
                                      actions: [
                                        FlatButton(
                                            onPressed: () {
                                              needRefresh = true;
                                              Navigator.pop(context);
                                              Navigator.pop(
                                                  context, needRefresh);
                                            },
                                            child: Text("Đóng"))
                                      ],
                                    );
                                  });
                            }
                            else {
                              debugPrint("Return purchased sheet fail");
                              showDialog(
                                  context: context,
                                  builder: (context) {
                                    return AlertDialog(
                                      title: Text("Trả hàng thất bại"),
                                      actions: [
                                        FlatButton(
                                            onPressed: () {
                                              Navigator.pop(context);
                                            },
                                            child: Text("Đóng"))
                                      ],
                                    );
                                  });
                            }
                          },
                          color: Colors.blue,
                          child: Text(
                            "Xác nhận",
                            style: TextStyle(
                                fontSize: 20, fontWeight: FontWeight.bold),
                          )),
                    ),
                  )
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class ReturnItemCard extends StatefulWidget {
  ReturnPurchasedItem item;
  Function(ReturnPurchasedItem)? onRemove;
  ReturnItemCard(this.item, {Function(ReturnPurchasedItem)? onRemove}) {
    this.onRemove = onRemove;
  }
  @override
  _ReturnItemCardState createState() => _ReturnItemCardState();
}

class _ReturnItemCardState extends State<ReturnItemCard> {
  final NumberFormat formatter = NumberFormat();
  remoteSetState() {
    setState(() {
      debugPrint("Done set state");
    });
  }

  @override
  Widget build(BuildContext context) {
    return Card(
        child: InkWell(
          child: Stack(children: [
            Padding(
              padding: const EdgeInsets.all(8.0),
              child: Row(
                children: [
                  Expanded(
                    flex: 2,
                    child: Padding(
                        padding: EdgeInsets.only(right: 5),
                        child: widget.item.item.imageUrl == null||widget.item.item.imageUrl == "null"
                            ? Image.asset("asset/productImage/no-image.jpg")
                            : CachedNetworkImage(
                          imageUrl: ServerConfig.projectUrl +
                              widget.item.item.imageUrl!,
                          progressIndicatorBuilder:
                              (context, url,downloadProgress) =>
                              SizedBox(height:20,width:20,child: Center(child: CircularProgressIndicator(value: downloadProgress.progress,))),
                          errorWidget: (context, url, error) =>
                              Icon(Icons.error),
                        )),
                  ),
                  Expanded(
                    flex: 5,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          widget.item.item.name,
                          style: TextStyle(fontWeight: FontWeight.bold),
                        ),
                        // Text("Đơn vị:" + cartItem.item.unitName.toString()),
                        Text("SL: "+widget.item.quantity.toString()),
                      ],
                    ),
                  ),
                  Expanded(
                      flex: 3,
                      child: Text(
                          formatter.format(widget.item.item.purchasePrice) + " VNĐ")),
                ],
              ),
            ),
            Positioned(
              right: 0,
              top: 0,
              child: IconButton(
                  onPressed: () {
                    if (widget.onRemove != null) {
                      widget.onRemove!(widget.item);
                    }
                  },
                  icon: Icon(Icons.close)),
            )
          ]),
        ));
  }
}

class CustomDialog extends StatefulWidget {
  ReturnPurchasedItem item;
  Function(int)? onClose;
  CustomDialog(this.item, {Function(int)? onClose}) {
    this.onClose = onClose;
  }
  @override
  _CustomDialogState createState() => _CustomDialogState();
}

class _CustomDialogState extends State<CustomDialog> {
  TextEditingController controller = TextEditingController();
  bool _validate = true;
  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text("Nhập số lượng"),
      content: TextFormField(
        keyboardType: TextInputType.number,
        controller: controller,
        decoration: new InputDecoration(
            hintText: _validate
                ? "Nhập số lớn hơn 0"
                : "Số nhập vào không hợp lệ hoặc lớn hơn số lượng hàng!",
            hintStyle: _validate
                ? TextStyle(color: Colors.grey)
                : TextStyle(color: Colors.red)),
      ),
      actions: [
        FlatButton(
            onPressed: () {
              Navigator.pop(context);
            },
            child: Text("Hủy")),
        FlatButton(
            onPressed: () {
              if (int.tryParse(controller.value.text) != null) {
                int amount = int.tryParse(controller.value.text)!;
                if (amount > 0) {
                  if (widget.item.item.quantity! < amount) {
                    _validate = false;
                  } else {
                    if (widget.onClose != null) {
                      widget.onClose!(amount);
                    }
                    Navigator.pop(context);
                    return;
                  }
                }
              }
              controller.text = "";
            },
            child: Text("Đóng")),
      ],
    );
  }
}
