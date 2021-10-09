import 'package:bkrm/pages/inventoryModule/import/importItemCard.dart';
import 'package:bkrm/pages/inventoryModule/import/listImportPage.dart';
import 'package:bkrm/pages/inventoryModule/item/addNewProductPage.dart';
import 'package:bkrm/pages/inventoryModule/purchasedSheet/purchasedSheetDetailPage.dart';
import 'package:bkrm/services/info/inventoryInfo/defaultItemInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/purchasedSheetInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/supplierInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/customerFormatter.dart';
import 'package:bkrm/widget/listSupplier.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:bkrm/widget/productItem.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_barcode_scanner/flutter_barcode_scanner.dart';
import 'package:flutter_typeahead/flutter_typeahead.dart';
import 'package:intl/intl.dart';
import 'package:page_transition/page_transition.dart';
import 'package:permission_handler/permission_handler.dart';

import 'package:bkrm/pages/Nav2App.dart';

class CustomDialog extends StatefulWidget {
  Function(int) onFinishedGetValue;

  CustomDialog(this.onFinishedGetValue);
  @override
  _CustomDialogState createState() => _CustomDialogState();
}

class _CustomDialogState extends State<CustomDialog> {
  TextEditingController quantityController = TextEditingController();
  bool _validate = true;
  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text("Nhập số lượng"),
      content: TextField(
        keyboardType: TextInputType.number,
        controller: quantityController,
        decoration: new InputDecoration(
            hintText: _validate
                ? "Nhập số lớn hơn 0"
                : "Số nhập vào không hợp lệ!",
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
              if (int.tryParse(quantityController.value.text) != null) {
                int amount = int.tryParse(quantityController.value.text)!;
                if (amount > 0) {
                  widget.onFinishedGetValue(amount);
                  Navigator.pop(context);
                  return;
                }
              }
              quantityController.text = "";
              _validate = false;
              setState(() {});
            },
            child: Text("Xác nhận")),
      ],
    );
  }
}

class ImportInfoBottomSheet extends StatefulWidget {
  TextEditingController discountController;
  TextEditingController deliverNameControler;
  ImportInfoBottomSheet(this.discountController, this.deliverNameControler);

  @override
  _ImportInfoBottomSheetState createState() => _ImportInfoBottomSheetState();
}

class _ImportInfoBottomSheetState extends State<ImportInfoBottomSheet> {
  String? supplierName;
  BkrmService bkrmService = BkrmService();
  NumberFormat formatter = NumberFormat();
  List<String> listDeliverName = [];
  @override
  void initState() {
    super.initState();
    if (bkrmService.importGood!.supplier != null) {
      supplierName = bkrmService.importGood!.supplier!.name;
    } else {
      supplierName = "Nhà cung cấp lẻ";
    }
  }

  Future<List<String>> searchSuggestDeliverName(String pattern) async {
    List<String> returnResults = [];
    if (listDeliverName.isEmpty) {
      this.listDeliverName = await bkrmService
          .getDeliverNameOfSupplier(bkrmService.importGood!.supplier);
    }
    debugPrint("this.listDeliverName");
    debugPrint(this.listDeliverName.toString());
    this.listDeliverName.forEach((element) {
      if (element.toLowerCase().contains(pattern.toLowerCase())) {
        returnResults.add(element);
      }
    });
    return returnResults;
  }

  updateSupplier(BuildContext context, SupplierInfo supplier) {
    setState(() {
      bkrmService.importGood!.supplier = supplier;
      supplierName = supplier.name;
    });
    Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        FocusScope.of(context).requestFocus(new FocusNode());
      },
      child: SingleChildScrollView(
        child: Padding(
            padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom),
            child: Container(
                height: 420,
                padding: EdgeInsets.all(8.0),
                color: Colors.white,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      height: 40,
                      child: Center(
                        child: Text(
                          'Thông tin nhập hàng',
                          style: TextStyle(
                              fontSize: 20, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                    Divider(),
                    Row(
                      children: [
                        Expanded(
                            flex: 3,
                            child: Container(
                              alignment: Alignment.centerLeft,
                              child: Text(
                                "Nhà cung cấp :",
                                style: TextStyle(
                                    fontSize: 15, fontWeight: FontWeight.bold),
                              ),
                            )),
                        Expanded(
                            flex: 3,
                            child: FlatButton(
                                splashColor: Colors.blueAccent,
                                color: Colors.grey,
                                onPressed: () async {
                                  await showDialog(
                                      context: context,
                                      builder: (context) {
                                        return ListSupplier(
                                          onTapSupplier: updateSupplier,
                                        );
                                      });
                                },
                                child: Text(supplierName!))),
                        Expanded(flex: 1,child: IconButton(onPressed: () {
                          BkrmService().importGood!.supplier=null;
                          supplierName="Nhà cung cấp lẻ";
                          setState(() {

                          });
                          BkrmService().importGood!.updateInfo();
                        }, icon: Icon(Icons.close,color: Colors.blue,),),)
                      ],
                    ),
                    Divider(
                      thickness: 1,
                    ),
                    Row(
                      children: [
                        Text(
                          "Người nhập hàng :",
                          style: TextStyle(
                              fontSize: 15, fontWeight: FontWeight.bold),
                        ),
                        Expanded(
                          child: Container(
                              alignment: Alignment.centerRight,
                              child: Text(BkrmService().currentUser!.name,
                                  style: TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.bold))),
                        ),
                      ],
                    ),
                    Divider(),
                    Row(
                      children: [
                        Text(
                          "Người giao hàng :",
                          style: TextStyle(
                              fontSize: 15, fontWeight: FontWeight.bold),
                        ),
                        Expanded(
                          child: Container(
                              alignment: Alignment.centerRight,
                              child: TypeAheadField(
                                textFieldConfiguration: TextFieldConfiguration(
                                  textAlign: TextAlign.end,
                                  onChanged: (value){
                                    bkrmService.importGood!.deliverName=value;
                                  },
                                    autofocus: false,
                                    controller: widget.deliverNameControler),
                                suggestionsCallback: (String pattern) async {
                                  debugPrint(
                                      (await searchSuggestDeliverName(pattern))
                                          .toString());
                                  return await searchSuggestDeliverName(
                                      pattern);
                                },
                                noItemsFoundBuilder: (context) {
                                  return ListTile(
                                    title: Text(
                                      "Không tìm thấy!",
                                      style: TextStyle(
                                          color: Colors.grey,
                                          fontWeight: FontWeight.w300,
                                          fontSize: 14),
                                    ),
                                  );
                                },
                                itemBuilder: (context, dynamic suggestion) {
                                  debugPrint(suggestion.toString());
                                  return ListTile(
                                    title: Text(suggestion),
                                  );
                                },
                                onSuggestionSelected: (dynamic suggestion) {
                                  bkrmService.importGood!.deliverName =
                                      suggestion;
                                  widget.deliverNameControler.text = suggestion;
                                  setState(() {});
                                },
                              )
                              /*TextFormField(
                                textAlign: TextAlign.end,
                                onChanged: (deliverName){
                                  bkrmService.importGood.deliverName=deliverName;
                                },
                                  controller: widget.deliverNameControler,
                                  style: TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.bold))*/
                              ),
                        ),
                      ],
                    ),
                    Divider(),
                    Row(
                      children: [
                        Text(
                          "Tổng tiền hàng :",
                          style: TextStyle(
                              fontSize: 15, fontWeight: FontWeight.bold),
                        ),
                        Expanded(
                            child: Container(
                          alignment: Alignment.centerRight,
                          child: Text(formatter
                                  .format(bkrmService.importGood!.totalPrice) +
                              " VNĐ"),
                        )),
                      ],
                    ),
                    Divider(),
                    Row(
                      children: [
                        Text(
                          "Số tiền được giảm :",
                          style: TextStyle(
                              fontSize: 15, fontWeight: FontWeight.bold),
                        ),
                        Expanded(
                          child: Container(
                              alignment: Alignment.centerRight,
                              child: TextField(
                                textAlign: TextAlign.end,
                                controller: widget.discountController,
                                inputFormatters: [
                                  CustomerFormatter().currencyFormatter
                                ],
                                onChanged: (value) {
                                  value = value.replaceAll(",", "");
                                  int? discount = int.tryParse(value);
                                  if (discount != null) {
                                    bkrmService.importGood!.discount = discount;
                                  }
                                  setState(() {});
                                },
                              )),
                        ),
                        Text(" VNĐ"),
                      ],
                    ),
                    Divider(),
                    Row(
                      children: [
                        Text(
                          "Cần trả :",
                          style: TextStyle(
                              fontSize: 15, fontWeight: FontWeight.bold),
                        ),
                        Expanded(
                            child: Container(
                          alignment: Alignment.centerRight,
                          child: Text(formatter.format(
                                (bkrmService.importGood!.totalPrice) -
                                    (bkrmService.importGood!.discount == null
                                        ? 0
                                        : bkrmService.importGood!.discount!),
                              ) +
                              " VNĐ"),
                        )),
                      ],
                    ),
                    Divider(),
                    Container(
                      color: Colors.blue,
                      child: Center(
                        child: FlatButton(
                          onPressed: () async {
                            showDialog(
                                context: context,
                                builder: (context) {
                                  return AlertDialog(
                                    title: Text("Đang xử lý"),
                                    content: Container(
                                      height: 50,
                                      width: 50,
                                      child: Center(
                                        child: CircularProgressIndicator(),
                                      ),
                                    ),
                                  );
                                });
                            Map<String,dynamic> returnStatus =
                                await BkrmService().importGood!.sendInvoice();
                            if (returnStatus["state"] == MsgInfoCode.actionSuccess) {
                              if (bkrmService.importGood!.deliverName != null &&
                                  bkrmService.importGood!.deliverName != "") {
                                await bkrmService.addDeliverNameToSupplier(
                                    bkrmService.importGood!.deliverName!,
                                    bkrmService.importGood!.supplier);

                                this.listDeliverName.clear();
                              }
                              widget.deliverNameControler.text="";
                              Navigator.pop(context);
                              showDialog(
                                  context: context,
                                  builder: (context) {
                                    return AlertDialog(
                                      title: Text("Nhập hàng thành công"),
                                      actions: [
                                        TextButton(onPressed: (){
                                          DetailPurchasedSheetInfo detailPurchasedInfo = returnStatus["detailPurchasedSheet"];
                                          Navigator.pop(context);
                                          Navigator.pop(context);
                                          widget.discountController.text="";
                                          BkrmService()
                                              .importGood!
                                              .clearImportService();
                                          this.setState(() {

                                          });
                                          Navigator.push(context, PageTransition(child: PurchasedSheetDetailPage(detailPurchasedInfo, []),type: pageTransitionType));
                                        }, child: Text("Xem đơn nhập")),
                                        TextButton(
                                            onPressed: () {
                                              Navigator.pop(context);
                                              Navigator.pop(context);
                                              widget.discountController.text="";
                                              BkrmService()
                                                  .importGood!
                                                  .clearImportService();
                                              this.setState(() {});
                                            },
                                            child: Text("Đóng"))
                                      ],
                                    );
                                  });
                            } else {
                              showDialog(
                                  context: context,
                                  builder: (context) {
                                    return AlertDialog(
                                      title: Text("Nhập hàng thất bại"),
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
                          child: Text(
                            "Tiến hành nhập hàng",
                            style: TextStyle(
                                fontSize: 18, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ),
                    ),
                  ],
                ))),
      ),
    );
  }
}

class ImportGoodPage extends StatefulWidget {
  List<ImportItemCard> listGoodCard = [];
  @override
  _ImportGoodPageState createState() => _ImportGoodPageState();
}

class _ImportGoodPageState extends State<ImportGoodPage> {
  Stream importServiceStream = BkrmService().importGood!.importGoodServiceStream;
  BkrmService bkrmService = BkrmService();
  bool firstTimeCall = true;
  NumberFormat formatter = NumberFormat();
  TextEditingController discountController = TextEditingController();
  TextEditingController deliverNameController = TextEditingController();
  String? supplierName;
  @override
  void initState() {
    super.initState();
    supplierName = bkrmService.importGood!.supplier == null
        ? "Nhà cung cấp lẻ"
        : bkrmService.importGood!.supplier!.name;
  }

  updateSupplier(BuildContext context, SupplierInfo supplier) {
    setState(() {
      bkrmService.importGood!.supplier = supplier;
      supplierName = supplier.name;
      Navigator.pop(context);
    });
  }
  void _onItemTapped(int index) async{

    if(index==0){
      var status = await Permission.camera.status;
      if (status.isPermanentlyDenied||status.isRestricted || status.isDenied) {
        await Permission.camera.request();
      }
      var resultBarCode = await FlutterBarcodeScanner.scanBarcode(
          "#ffffff",
          "Hủy",
          true,
          ScanMode.DEFAULT);
      if(resultBarCode=="-1"){
        return;
      }
      List<ItemInfo> resultItem = await (bkrmService
          .searchItemInBranch(barCode: resultBarCode));
      if (resultItem.length == 1) {
        bkrmService.importGood!.addToImport(resultItem.first);
      } else {
        if (resultItem.length > 1) {
          showDialog(
              context: context,
              builder: (context) {
                return AlertDialog(
                  title: Text(
                      "Thêm hàng hoá vào danh sách nhập hàng"),
                  content: Container(
                    constraints: BoxConstraints(maxHeight: 500),
                    width:
                    MediaQuery.of(context).size.width * 0.75,
                    child: resultItem.length == 0
                        ? Container(
                      child: Center(
                        child: Text(
                          "Không có sản phẩm ",
                          style: TextStyle(
                              fontWeight: FontWeight.w400,
                              fontSize: 16),
                        ),
                      ),
                    )
                        : Column(children: [
                      Divider(),
                      ListView.builder(
                          itemCount: resultItem.length,
                          shrinkWrap: true,
                          itemBuilder: (context, index) {
                            return ProductItem(
                              resultItem[index],
                              fontSize: 12.0,
                              onTapOnProduct: (context,
                                  rawDataItem) async {
                                await bkrmService.importGood!
                                    .addToImport(
                                    rawDataItem);
                                Navigator.pop(context);
                                BkrmService()
                                    .importGood!
                                    .updateInfo();
                              },
                            );
                          }),
                    ]),
                  ),
                );
              });
        } else {
          List<DefaultItemInfo> resultItem = await (bkrmService
              .searchItemInDefaultDb(barCode: resultBarCode));
          if (resultItem.length == 0) {
            Navigator.push(context,
                PageTransition(child: AddNewItemPage(
                  barCode: resultBarCode,
                  hideAmountField: true,
                  hidePurchasePriceField: true,
                ),type: pageTransitionType));
          } else {
            Navigator.push(context,
                PageTransition(child:                   AddNewItemPage(
                    hideAmountField: true,
                    hidePurchasePriceField: true,
                    defaultItem: resultItem.first), type: pageTransitionType));
          }
        }
      }
      setState(() {});
    }else{
      Navigator.push(context,
          PageTransition(child: ListImportPage(title: "Hàng hóa"), type: pageTransitionType));
    }
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        FocusScope.of(context).requestFocus(new FocusNode());
      },
      child: Scaffold(
          resizeToAvoidBottomInset: true,
          drawer: ExpansionDrawer(context),
          appBar: AppBar(
            title: Text("Nhập hàng hoá"),
            actions: [
              IconButton(
                  icon: Icon(
                    Icons.add,
                  ),
                  onPressed: () {
                    Navigator.push(context,
                        PageTransition(child: ListImportPage(title: "Hàng hóa"), type: pageTransitionType));
                  }),
              IconButton(
                  icon: Icon(
                    Icons.delete,
                    color: Colors.white,
                  ),
                  onPressed: () {
                    BkrmService().importGood!.clearImportService();
                    setState(() {});
                  }),
            ],
          ),
          bottomNavigationBar: BottomNavigationBar(
            selectedItemColor: Colors.black,
            unselectedItemColor: Colors.black,
            items: [
              BottomNavigationBarItem(
                icon: Icon(Icons.qr_code),
                label: 'Quét mã vạch',
              ),
              BottomNavigationBarItem(
                icon: Icon(Icons.business),
                label: 'Hàng hóa',
              ),

            ],
            onTap: _onItemTapped,

          ),
          body: Container(
              child: StreamBuilder(
                  stream: bkrmService.importGood!.importGoodServiceStream,
                  initialData: {},
                  builder: (context, snapshot) {
                    if (firstTimeCall) {
                      bkrmService.importGood!.updateInfo();
                      firstTimeCall = false;
                      return Container();
                    }
                    Map<dynamic,dynamic> streamImport = snapshot.data as Map<dynamic,dynamic>;
                    if (streamImport["importItems"] == null) {
                      return Container();
                    }
                    if (streamImport["importItems"].isEmpty) {
                      return Center(
                        child: Container(
                          width: 250,
                          child: RichText(
                              text: TextSpan(
                                  style: TextStyle(
                                      color: Colors.grey, fontSize: 16),
                                  children: [
                                TextSpan(
                                    text:
                                        "Hiện không có sản phẩm nào để nhập. Hãy tìm kiếm và thêm hàng bằng nút "),
                                WidgetSpan(
                                    child: Padding(
                                        padding: EdgeInsets.all(2.0),
                                        child: Icon(Icons.search))),
                                TextSpan(
                                    text:
                                        "trước. Hoặc tạo hàng hoá mới bằng nút "),
                                WidgetSpan(
                                    child: Padding(
                                  padding: EdgeInsets.all(2.0),
                                  child: Icon(Icons.add),
                                ))
                              ])),
                        ),
                      );
                    }
                    if (discountController.value.text == "") {
                      discountController.text =
                         streamImport["discount"] == null
                              ? ""
                              : streamImport["discount"].toString();
                    }
                    if (deliverNameController.value.text == "") {
                      deliverNameController.text =
                          streamImport["deliverName"] == null
                              ? ""
                              : streamImport["deliverName"].toString();
                    }
                    return RefreshIndicator(
                      onRefresh: () async {
                        BkrmService().importGood!.updateInfo();
                        return;
                      },
                      child: Column(children: [
                        Expanded(
                          child: Container(
                            child: ListView.builder(
                                shrinkWrap: true,
                                itemCount: streamImport["importItems"].length,
                                itemBuilder: (context, index) {
                                  return ImportItemCard(
                                      streamImport["importItems"][index]);
                                }),
                          ),
                        ),
                        Divider(),
                        InkWell(
                          onTap: () {
                            showModalBottomSheet(
                                shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.vertical(
                                        top: Radius.circular(25.0))),
                                isScrollControlled: true,
                                context: context,
                                builder: (context) {
                                  return ImportInfoBottomSheet(
                                      discountController,
                                      deliverNameController);
                                });
                          },
                          child: Container(
                            height: 50,
                            color: Colors.blueAccent,
                            child: Center(
                              child: Text(
                                'Nhập hàng',
                                style: TextStyle(
                                    fontSize: 18, fontWeight: FontWeight.bold),
                              ),
                            ),
                          ),
                        ),
                      ]),
                    );
                  }))),
    );
  }
}
