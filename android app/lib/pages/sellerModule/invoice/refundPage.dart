import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'package:bkrm/services/info/sellingInfo/customerInfo.dart';
import 'package:bkrm/services/info/invoice/invoiceReceivedWhenGet.dart';
import 'package:bkrm/services/services.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_barcode_scanner/flutter_barcode_scanner.dart';
import 'package:intl/intl.dart';
import 'package:permission_handler/permission_handler.dart';

class RefundItem {
  Item item;
  int quantity;

  RefundItem(this.item, this.quantity);
}

class RefundPage extends StatefulWidget {
  final DetailInvoiceInfo? invoice;
  final List<RefundItem> refundItems = [];
  RefundPage(this.invoice);

  @override
  _RefundPageState createState() => _RefundPageState();
}

class _RefundPageState extends State<RefundPage> {
  TextEditingController reasonController = TextEditingController();

  bool needRefresh = false;
  @override
  void initState() {
    super.initState();
  }

  processRefundSheet(List<Map<String,dynamic>> listRefundItems)async{
    MsgInfoCode? returnStatus = await BkrmService()
        .createRefundSheet(
        widget.invoice,
        reasonController.value.text,
        listRefundItems);
    if (returnStatus == MsgInfoCode.actionSuccess) {
      showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) {
            return AlertDialog(
              title: Text("Trả hàng thành công"),
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
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        FocusScope.of(context).requestFocus(FocusNode());
      },
      child: Scaffold(
        resizeToAvoidBottomInset: true,
        appBar: AppBar(
          title: Text("Trả hàng"),
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
                      "Trả hàng cho Hóa đơn #" +
                          widget.invoice!.invoiceInfo.invoiceId.toString(),
                      style:
                          TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
                    ),
                  ),
                  SizedBox(
                    height: 30,
                  ),
                  Center(
                    child: Text(
                      "Chọn số lượng cho mặt hàng muốn trả",
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
                        for(var purchasedItem in widget.invoice!.items){
                          if(purchasedItem.itemId==items.first.itemId){
                            var listItems = widget.refundItems.where((element) => element.item.itemId==purchasedItem.itemId).toList();
                            if(listItems.isEmpty){
                                widget.refundItems.add(RefundItem(purchasedItem, 1));
                            }else{
                              if(listItems.first.quantity<listItems.first.item.quantity!){
                                listItems.first.quantity+=1;
                              }
                            }
                          }
                        }
                      }
                      setState(() {

                      });
                      Navigator.pop(context);
                    },),
                  ),
                  SizedBox(
                    height: 20,
                  ),
                  ListView.builder(
                      physics: NeverScrollableScrollPhysics(),
                      shrinkWrap: true,
                      itemCount: widget.refundItems.length + 1,
                      itemBuilder: (context, index) {
                        if (index == widget.refundItems.length) {
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
                                                widget.invoice!.items.length,
                                            itemBuilder: (context, index) {
                                              return InkWell(
                                                onTap: () {
                                                  bool isDuplicate = false;
                                                  widget.refundItems
                                                      .forEach((element) {
                                                    if (element.item ==
                                                        widget.invoice!
                                                            .items[index]) {
                                                      isDuplicate = true;
                                                    }
                                                  });
                                                  if (!isDuplicate) {
                                                    widget.refundItems.add(
                                                        RefundItem(
                                                            widget.invoice!
                                                                .items[index],
                                                            1));
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
                                                                EdgeInsets.all(
                                                                    8.0),
                                                            child: widget
                                                                        .invoice!
                                                                        .items[
                                                                            index]
                                                                        .imageUrl ==
                                                                    null||widget
                                                                .invoice!
                                                                .items[
                                                            index]
                                                                .imageUrl=="null"
                                                                ? Image.asset(
                                                                    "asset/productImage/no-image.jpg")
                                                                : CachedNetworkImage(
                                                                    imageUrl: ServerConfig
                                                                            .projectUrl +
                                                                        widget
                                                                            .invoice!
                                                                            .items[index]
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
                                                                .invoice!
                                                                .items[index]
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
                        return RefundItemCard(
                          widget.refundItems[index],
                          onRemove: (RefundItem item) {
                            widget.refundItems
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
                            bool valid = true;
                            if (widget.refundItems.isEmpty) {
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
                            widget.refundItems.forEach((element) {
                              if (element.quantity == 0) {
                                valid = false;
                              }
                            });
                            if (valid) {
                              List<Map<String, dynamic>> listRefundItems = [];
                              widget.refundItems.forEach((element) {
                                if (element.quantity > 0) {
                                  listRefundItems.add({
                                    "invoice_item_id":
                                        element.item.invoiceItemId,
                                    "quantity": element.quantity
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
                              List<CustomerInfo> listGetCustomer = await BkrmService().getCustomer(customerId: widget.invoice!.invoiceInfo.customerId);
                              if(listGetCustomer.isNotEmpty){
                                CustomerInfo customer = listGetCustomer.first;
                                double pointWillBeSubtract = 0;
                                if(widget.invoice!=null){
                                  for(var refundItem in widget.refundItems){
                                    for(var item in widget.invoice!.items){
                                      if(item.itemId==refundItem.item.itemId){
                                        pointWillBeSubtract+=refundItem.quantity*refundItem.item.sellPrice!*item.pointRatio;
                                      }
                                    }
                                  }
                                  debugPrint("Customer point: "+customer.customerPoint.toString());
                                  debugPrint("Point will be subtract :"+pointWillBeSubtract.toString());
                                  if(customer.customerPoint!-pointWillBeSubtract<0){
                                    Navigator.pop(context);
                                    showDialog(context: context, builder: (context){
                                      return AlertDialog(
                                        title: Text("Thông báo"),
                                        content: Text("Nếu thực hiện việc trả hảng này, điểm tích lũy của khách hàng sẽ âm. Tiếp tục thực hiện?",textAlign: TextAlign.center,),
                                        actions: [
                                          TextButton(onPressed: (){
                                            Navigator.pop(context);
                                          }, child: Text("Hủy")),
                                          TextButton(onPressed: (){
                                            Navigator.pop(context);
                                            processRefundSheet(listRefundItems);
                                          }, child: Text("Xác nhận")),
                                        ],
                                      );
                                    });
                                    return;
                                  }
                                  processRefundSheet(listRefundItems);
                                  Navigator.pop(context,needRefresh);
                                }
                              }else{
                                processRefundSheet(listRefundItems);
                                Navigator.pop(context,needRefresh);
                              }
                            } else {
                              showDialog(
                                  context: context,
                                  builder: (context) {
                                    return AlertDialog(
                                      title: Text(
                                          "Có sản phẩm đang không có số lượng để trả hàng!"),
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

class RefundItemCard extends StatefulWidget {
  RefundItem item;
  Function(RefundItem)? onRemove;
  RefundItemCard(this.item, {Function(RefundItem)? onRemove}) {
    this.onRemove = onRemove;
  }
  @override
  _RefundItemCardState createState() => _RefundItemCardState();
}

class _RefundItemCardState extends State<RefundItemCard> {
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
                    SizedBox(
                      width: 90,
                      child: ButtonBar(
                        buttonPadding: EdgeInsets.zero,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          SizedBox(
                            width: 30,
                            child: IconButton(
                                padding: EdgeInsets.all(2),
                                iconSize: 12,
                                icon: Icon(Icons.remove),
                                onPressed: () {
                                  if (widget.item.quantity > 1) {
                                    widget.item.quantity -= 1;
                                    setState(() {});
                                  }
                                }),
                          ),
                          SizedBox(
                              width: 30,
                              child: RaisedButton(
                                  child: Text(widget.item.quantity.toString()),
                                  onPressed: () {
                                    showDialog(
                                        context: context,
                                        builder: (BuildContext context) {
                                          return CustomDialog(
                                            widget.item,
                                            onClose: (amount) {
                                              if (amount > 0 ||
                                                  amount <
                                                      widget.item.item
                                                          .quantity!) {
                                                widget.item.quantity = amount;
                                                this.remoteSetState();
                                              }
                                            },
                                          );
                                        });
                                  })),
                          SizedBox(
                            width: 30,
                            child: IconButton(
                                padding: EdgeInsets.all(2),
                                iconSize: 12,
                                icon: Icon(Icons.add),
                                onPressed: () {
                                  if (widget.item.quantity <
                                      widget.item.item.quantity!) {
                                    widget.item.quantity += 1;
                                    setState(() {});
                                  }
                                }),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                  flex: 3,
                  child: Text(
                      formatter.format(widget.item.item.sellPrice) + " VNĐ")),
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
  RefundItem item;
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
