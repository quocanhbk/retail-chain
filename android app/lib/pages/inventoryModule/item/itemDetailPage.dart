import 'dart:io';
import 'dart:async';
import 'package:bkrm/pages/inventoryModule/item/priceHistoryPage.dart';
import 'package:bkrm/pages/inventoryModule/item/quantityChangeHistoryPage.dart';
import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/info/inventoryInfo/categoryInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/priceHistory.dart';
import 'package:bkrm/services/info/inventoryInfo/quantityHistory.dart';
import 'package:bkrm/services/printer/barcode_printer.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/customerFormatter.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_barcode_scanner/flutter_barcode_scanner.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:page_transition/page_transition.dart';
import 'package:permission_handler/permission_handler.dart';

import 'package:bkrm/pages/Nav2App.dart';

class ItemDetailPage extends StatefulWidget {
  ItemInfo dataItem;

  ItemDetailPage(this.dataItem);

  @override
  _ItemDetailPageState createState() => _ItemDetailPageState();
}

class _ItemDetailPageState extends State<ItemDetailPage> {
  final _formKey = GlobalKey<FormState>();

  TextEditingController nameController = TextEditingController();
  TextEditingController sellPriceController = TextEditingController();
  TextEditingController purchasepriceController = TextEditingController();
  // TextEditingController shelfController = TextEditingController();
  TextEditingController amountController = TextEditingController();
  TextEditingController barCodeController = TextEditingController();
  TextEditingController pointRatioController = TextEditingController();
  // TextEditingController basicUnitController = TextEditingController();

  bool nameValid = true;
  bool sellPriceValid = true;
  bool purchasePriceValid = true;
  // bool shelfValid=true;
  bool amountValid = true;
  // bool basicUnitValid=true;

  bool editing = false;

  bool edited = false;

  bool storable = true;
  final ImagePicker picker = ImagePicker();
  File? imageFile;
  List<CategoryInfo>? categories;
  BkrmService bkrmService = BkrmService();
  String? chosenCategory = "Loading...";
  @override
  void initState() {
    super.initState();
    getCategory();
    setUpItemInfo();
  }

  setUpItemInfo() {
    nameController.text = widget.dataItem.itemName!;
    sellPriceController.text = NumberFormat().format(widget.dataItem.sellPrice);
    purchasepriceController.text =
        NumberFormat().format(widget.dataItem.purchasePrice);
    // shelfController.text=widget.dataItem.shelf.toString();
    amountController.text = widget.dataItem.quantity.toString();
    pointRatioController.text =
        (widget.dataItem.pointRatio * 100).toInt().toString();
    if (widget.dataItem.barCode != null) {
      barCodeController.text = widget.dataItem.barCode == "null"
          ? ""
          : widget.dataItem.barCode.toString();
    }
    chosenCategory = widget.dataItem.categoryName;
    // basicUnitController.text=widget.dataItem.unitName.toString();
  }

  clearPage() {
    nameController.clear();
    sellPriceController.clear();
    // capitalController.clear();
    // shelfController.clear();
    amountController.clear();
    barCodeController.clear();
    pointRatioController.clear();
    // basicUnitController.text=widget.dataItem.unitName.toString();

    nameValid = true;
    sellPriceValid = true;
    // capitalValid=true;
    // shelfValid=true;
    amountValid = true;
    // basicUnitValid=true;
    for (CategoryInfo category in categories!) {
      if (category.id == widget.dataItem.categoryId) {
        chosenCategory = category.name;
        break;
      }
    }
  }

  void getCategory() async {
    categories = await bkrmService.getCategory();
    setState(() {
      for (CategoryInfo category in categories!) {
        debugPrint(category.name.toString());
        if (category.id == widget.dataItem.categoryId) {
          chosenCategory = category.name;
          break;
        }
      }
    });
  }

  void remoteSetState() {
    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        FocusScope.of(context).requestFocus(FocusNode());
      },
      child: Scaffold(
        appBar: AppBar(
          title: Text("Thông tin chi tiết sản phẩm"),
        ),
        body: WillPopScope(
          onWillPop: () async {
            Navigator.pop(context, edited);
            return edited;
          },
          child: SingleChildScrollView(
            child: Container(
              padding: EdgeInsets.all(8.0),
              child: Form(
                key: _formKey,
                child: Column(
                  children: [
                    Container(
                      decoration: BoxDecoration(
                          border: Border.all(),
                          borderRadius: BorderRadius.circular(8.0)),
                      child: ClipRRect(
                          borderRadius: BorderRadius.circular(8.0),
                          child: Container(
                            height: 150,
                            width: 150,
                            child: widget.dataItem.imageUrl == null
                                ? Image.asset("asset/productImage/no-image.jpg")
                                : CachedNetworkImage(
                                    imageUrl: ServerConfig.projectUrl +
                                        widget.dataItem.imageUrl!,
                                    progressIndicatorBuilder:
                                        (context, url, downloadProgress) =>
                                            CircularProgressIndicator(
                                      value: downloadProgress.progress,
                                    ),
                                    errorWidget: (context, url, error) =>
                                        Icon(Icons.error),
                                  ),
                          )),
                    ),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        IconButton(
                            icon: Icon(
                              Icons.folder,
                            ),
                            onPressed: editing &&
                                    BkrmService()
                                        .currentUser!
                                        .roles
                                        .contains("purchasing")
                                ? () async {
                                    PickedFile? image = await picker.getImage(
                                        source: ImageSource.gallery,
                                        maxWidth: 300,
                                        maxHeight: 300);
                                    setState(() {
                                      imageFile = File(image!.path);
                                    });
                                  }
                                : null),
                        IconButton(
                          icon: Icon(
                            Icons.camera_alt,
                          ),
                          onPressed: editing &&
                                  BkrmService()
                                      .currentUser!
                                      .roles
                                      .contains("purchasing")
                              ? () async {
                                  PickedFile? image = await picker.getImage(
                                      source: ImageSource.camera,
                                      maxWidth: 300,
                                      maxHeight: 300);
                                  setState(() {
                                    imageFile = File(image!.path);
                                  });
                                }
                              : null,
                        )
                      ],
                    ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("Tên sản phẩm : ")),
                        Expanded(
                            flex: 2,
                            child: TextFormField(
                              enabled: editing,
                              autovalidateMode:
                                  AutovalidateMode.onUserInteraction,
                              controller: nameController,
                              validator: (name) {
                                if (name == null || name == "") {
                                  nameValid = false;
                                  return " * Bắt buộc";
                                } else {
                                  nameValid = true;
                                  return null;
                                }
                              },
                              decoration: InputDecoration(
                                  hintText: "Tên sản phẩm",
                                  enabledBorder: UnderlineInputBorder(
                                      borderSide:
                                          BorderSide(color: Colors.blue))),
                            ))
                      ],
                    ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("Danh mục : ")),
                        Expanded(
                            flex: 2,
                            child: Container(
                              alignment: Alignment.centerLeft,
                              child: DropdownButton<String>(
                                onTap: () {
                                  FocusScope.of(context)
                                      .requestFocus(new FocusNode());
                                },
                                value: chosenCategory,
                                icon: Icon(Icons.arrow_drop_down),
                                iconSize: 18,
                                underline: Container(
                                  height: 1,
                                  color:
                                      editing ? Colors.blueAccent : Colors.grey,
                                ),
                                onChanged: editing
                                    ? (String? newValue) {
                                        setState(() {
                                          chosenCategory = newValue;
                                        });
                                      }
                                    : null,
                                items: categories == null
                                    ? [
                                        DropdownMenuItem(
                                            value: widget.dataItem.categoryName,
                                            child: Text(chosenCategory!))
                                      ]
                                    : categories!.map((CategoryInfo category) {
                                        return DropdownMenuItem(
                                            value: category.name,
                                            child: Text(category.name));
                                      }).toList(),
                              ),
                            ))
                      ],
                    ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("Đơn giá : ")),
                        Expanded(
                            flex: 2,
                            child: TextFormField(
                              enabled: editing,
                              inputFormatters: [
                                CustomerFormatter().currencyFormatter
                              ],
                              autovalidateMode:
                                  AutovalidateMode.onUserInteraction,
                              validator: (price) {
                                if (price == "" || price == null) {
                                  sellPriceValid = false;
                                  return " *Bắt buộc";
                                }
                                if (int.tryParse(price.replaceAll(",", "")) ==
                                    null) {
                                  sellPriceValid = false;
                                  return " * Số nhập vào không hợp lệ";
                                }
                                if (int.tryParse(price.replaceAll(",", ""))! <
                                    0) {
                                  sellPriceValid = false;
                                  return "Giá bán phải là số nguyên dương";
                                }
                                sellPriceValid = true;
                                return null;
                              },
                              controller: sellPriceController,
                              keyboardType: TextInputType.number,
                              decoration: InputDecoration(
                                  hintText: "Đơn giá",
                                  enabledBorder: UnderlineInputBorder(
                                      borderSide:
                                          BorderSide(color: Colors.blue))),
                            ))
                      ],
                    ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("Vốn : ")),
                        Expanded(
                            flex: 2,
                            child: TextFormField(
                              enabled: editing,
                              inputFormatters: [
                                CustomerFormatter().currencyFormatter
                              ],
                              autovalidateMode:
                                  AutovalidateMode.onUserInteraction,
                              validator: (capital) {
                                if (capital == "" || capital == null) {
                                  purchasePriceValid = false;
                                  return " *Bắt buộc";
                                }
                                if (int.tryParse(capital.replaceAll(",", "")) ==
                                    null) {
                                  purchasePriceValid = false;
                                  return " * Số nhập vào không hợp lệ";
                                }
                                if ((int.tryParse(capital
                                            .replaceAll(",", "")
                                            .replaceAll(".", "")) ??
                                        -1) <
                                    0) {
                                  purchasePriceValid = false;
                                  return "Giá bán phải là số dương";
                                }
                                purchasePriceValid = true;
                                return null;
                              },
                              controller: purchasepriceController,
                              keyboardType: TextInputType.number,
                              decoration: InputDecoration(
                                  hintText: "Vốn",
                                  enabledBorder: UnderlineInputBorder(
                                      borderSide:
                                          BorderSide(color: Colors.blue))),
                            )),
                      ],
                    ),
                    // Row(
                    //   children: [
                    //     Expanded(flex: 1,
                    //         child: Text("Mã số kệ : ")),
                    //     Expanded(flex: 2,child: TextFormField(
                    //       enabled: isEdited,
                    //       autovalidateMode: AutovalidateMode.onUserInteraction,
                    //       validator: (shelf){
                    //         if(shelf==null||shelf==""){
                    //           shelfValid=false;
                    //           return " *Bắt buộc";
                    //         }
                    //         shelfValid=true;
                    //         return null;
                    //       },
                    //       controller: shelfController,
                    //       keyboardType: TextInputType.number,
                    //       decoration: InputDecoration(
                    //           hintText: "Mã số kệ"
                    //       ),
                    //     )),
                    //   ],
                    // ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("Số lượng hàng : ")),
                        Expanded(
                            flex: 2,
                            child: InkWell(
                              onTap: editing?() {
                                showDialog(
                                  barrierDismissible: false,
                                    context: context,
                                    builder: (context) {
                                      TextEditingController reasonController =
                                      TextEditingController();
                                      TextEditingController
                                      quantityAdjustmentController =
                                      TextEditingController();
                                      bool increase=false;
                                      bool decrease=false;
                                      bool numberValid = false;
                                      return StatefulBuilder(
                                        builder: (BuildContext context, void Function(void Function()) setState) {
                                          return AlertDialog(
                                            title: Text("Kiểm kê lượng hàng"),
                                            content: SingleChildScrollView(
                                              child: Container(
                                                width:
                                                MediaQuery.of(context).size.width *
                                                    3 /
                                                    4,
                                                child: Column(
                                                  children: [
                                                    Row(
                                                      children: [
                                                        Expanded(
                                                            flex: 1,
                                                            child: Text("Lý do:")),
                                                        Expanded(
                                                            flex: 3,
                                                            child: TextFormField(
                                                              controller:
                                                              reasonController,
                                                            ))
                                                      ],
                                                    ),
                                                    SizedBox(height: 10,),
                                                    Container(alignment:Alignment.centerLeft,child: Text("Điều chỉnh:",style: TextStyle(fontWeight: FontWeight.bold),)),
                                                    Row(
                                                      children: [

                                                        Expanded(
                                                            flex: 1,
                                                            child: Text("Tăng")
                                                        ),
                                                        Expanded(
                                                          flex: 1,
                                                          child: Checkbox(
                                                            value: increase,
                                                            onChanged: (bool? value) {

                                                              setState(() {
                                                                increase = true;
                                                                decrease=false;
                                                              });
                                                            },
                                                          ),
                                                        ),
                                                        Expanded(
                                                            flex: 1,
                                                            child: Text("Giảm")
                                                        ),
                                                        Expanded(
                                                          flex: 1,
                                                          child: Checkbox(
                                                            value: decrease,
                                                            onChanged: (bool? value) {
                                                              setState(() {
                                                                decrease = true;
                                                                increase= false;
                                                              });

                                                            },
                                                          ),
                                                        ),
                                                      ],
                                                    ),
                                                    !increase&&!decrease?Container(alignment: Alignment.centerLeft,
                                                        child: Text("*Bắt buộc",style: TextStyle(fontSize: 12,color: Colors.red),)):Container(),
                                                    SizedBox(height: 10,),
                                                    Row(
                                                      children: [
                                                        Expanded(
                                                            flex: 1,
                                                            child: Text("Số lượng:")),
                                                        Expanded(
                                                            flex: 3,
                                                            child: TextFormField(
                                                              inputFormatters: [CustomerFormatter().numberFormatter],
                                                              controller:
                                                              quantityAdjustmentController,
                                                              keyboardType: TextInputType.number,
                                                              validator: (amount){
                                                                if(amount==null||amount==""){
                                                                  numberValid=false;
                                                                  return "* Bắt buộc";
                                                                }else{
                                                                  numberValid=true;
                                                                  return null;
                                                                }
                                                              },
                                                              autovalidateMode: AutovalidateMode.always,
                                                            ))
                                                      ],
                                                    ),
                                                  ],
                                                ),
                                              ),
                                            ),
                                            actions: [
                                              TextButton(onPressed: (){
                                                Navigator.pop(context);
                                              }, child: Text("Hủy")),
                                              TextButton(onPressed: ()async{
                                                if((increase||decrease)&&numberValid){
                                                  showDialog(context: context, builder: (context){
                                                    return AlertDialog(
                                                      content: Container(
                                                        height: 50,
                                                        child: Center(child: CircularProgressIndicator()),
                                                      ),
                                                    );
                                                  });
                                                  int? quantity = int.tryParse(quantityAdjustmentController.value.text);
                                                  if(quantity==null){
                                                    Navigator.pop(context);
                                                    Navigator.pop(context);
                                                    return;
                                                  }
                                                  MsgInfoCode? returnStatus = await BkrmService().createQuantityCheckingSheet(reasonController.value.text, [{
                                                    "item_id":widget.dataItem.itemId,
                                                    "quantity":quantity,
                                                    "adjustment":increase?"increase":"decrease"
                                                  }]);
                                                  if(returnStatus==MsgInfoCode.actionSuccess){
                                                    Navigator.pop(context);
                                                    Navigator.pop(context);
                                                    this.edited=true;
                                                    if(increase){
                                                      widget.dataItem.quantity+=quantity;
                                                    }else{
                                                      widget.dataItem.quantity-=quantity;
                                                    }
                                                    showDialog(context: context,builder: (context){
                                                      return AlertDialog(
                                                        title: Text("Kiểm kê thành công."),
                                                        actions: [
                                                          TextButton(onPressed: (){
                                                            Navigator.pop(context);
                                                            this.setUpItemInfo();
                                                            this.setState(() {

                                                            });
                                                          }, child: Text("Hoàn thành"))
                                                        ],
                                                      );
                                                    });
                                                    return;
                                                  }else{
                                                    Navigator.pop(context);
                                                    Navigator.pop(context);
                                                    showDialog(context: context,builder: (context){
                                                      return AlertDialog(
                                                        title: Text("Kiểm kê thất bại."),
                                                        actions: [
                                                          TextButton(onPressed: (){
                                                            Navigator.pop(context);
                                                          }, child: Text("Đóng"))
                                                        ],
                                                      );
                                                    });
                                                    return;
                                                  }
                                                }
                                              }, child: Text("Xác nhận"))
                                            ],
                                          );
                                        },
                                      );
                                    });
                              }:null,
                              child: IgnorePointer(
                                child: TextFormField(
                                  inputFormatters: [
                                    CustomerFormatter().numberFormatter
                                  ],
                                  enabled: editing,
                                  autovalidateMode:
                                      AutovalidateMode.onUserInteraction,
                                  validator: (amount) {
                                    if (amount == "" || amount == null) {
                                      amountValid = false;
                                      return " * Băt buộc";
                                    }
                                    if (int.tryParse(amount)! < 0) {
                                      amountValid = false;
                                      return " Số lượng hàng phải là số nguyên dương";
                                    }
                                    amountValid = true;
                                    return null;
                                  },
                                  controller: amountController,
                                  keyboardType: TextInputType.number,
                                  decoration: InputDecoration(
                                      hintText: "Số lượng hàng",
                                      enabledBorder: UnderlineInputBorder(
                                          borderSide:
                                              BorderSide(color: Colors.blue))),
                                ),
                              ),
                            )),
                      ],
                    ),
                    Row(
                      children: [
                        Expanded(flex: 2, child: Text("Mã vạch : ")),
                        Expanded(
                            flex: 4,
                            child: TextFormField(
                              enabled: editing,
                              controller: barCodeController,
                              decoration: InputDecoration(
                                  hintText: "Mã vạch",
                                  enabledBorder: UnderlineInputBorder(
                                      borderSide:
                                          BorderSide(color: Colors.blue))),
                            )),
                        Expanded(
                          flex: 1,
                          child: IconButton(
                              icon: Icon(Icons.qr_code),
                              onPressed: BkrmService()
                                          .currentUser!
                                          .roles
                                          .contains("purchasing") &&
                                      editing
                                  ? () async {
                                      var status =
                                          await Permission.camera.status;
                                      if (status.isPermanentlyDenied ||
                                          status.isRestricted ||
                                          status.isDenied) {
                                        await Permission.camera.request();
                                      }
                                      var scanResult =
                                          await FlutterBarcodeScanner
                                              .scanBarcode("#ffffff", "Hủy",
                                                  true, ScanMode.DEFAULT);
                                      if (scanResult == "-1") {
                                        return;
                                      }
                                      barCodeController.text = scanResult;
                                    }
                                  : null),
                        ),
                        Expanded(
                          flex: 1,
                          child: IconButton(
                              icon: Icon(Icons.print),
                              onPressed: widget.dataItem.barCode == null
                                  ? null
                                  : () async {
                                      showDialog(
                                          context: context,
                                          builder: (context) {
                                            return BarcodePrinter(
                                                widget.dataItem.barCode!,
                                                widget.dataItem.itemName!);
                                          });
                                    }),
                        ),
                      ],
                    ),
                    // Row(
                    //   children: [
                    //     Expanded(flex: 1, child: Text("Lưu trữ được : ")),
                    //     Expanded(
                    //         flex: 2,
                    //         child: Row(
                    //           children: [
                    //             Radio(
                    //               groupValue: storable,
                    //               value: true,
                    //               onChanged: (value) {
                    //                 setState(() {
                    //                   storable = value;
                    //                 });
                    //               },
                    //             ),
                    //             Text("Có"),
                    //             Radio(
                    //               groupValue: storable,
                    //               value: false,
                    //               onChanged: (value) {
                    //                 setState(() {
                    //                   storable = value;
                    //                 });
                    //               },
                    //             ),
                    //             Text("Không"),
                    //           ],
                    //         ))
                    //   ],
                    // ),
                    Row(
                      children: [
                        Expanded(
                            flex: 1, child: Text("Tỷ lệ tích điểm (%) : ")),
                        Expanded(
                            flex: 2,
                            child: TextField(
                              enabled: editing,
                              inputFormatters: [
                                CustomerFormatter().numberFormatter,
                                LengthLimitingTextInputFormatter(2),
                              ],
                              controller: pointRatioController,
                              keyboardType: TextInputType.number,
                              decoration: InputDecoration(
                                  hintText: "Tỷ lệ tích điểm",
                                  enabledBorder: UnderlineInputBorder(
                                      borderSide:
                                          BorderSide(color: Colors.blue))),
                            ))
                      ],
                    ),
                    // Row(
                    //   children: [
                    //     Expanded(flex: 1,
                    //         child: Text("Đơn vị : ")),
                    //     Expanded(flex: 2,child: TextFormField(
                    //       enabled: isEdited,
                    //       autovalidateMode: AutovalidateMode.onUserInteraction,
                    //       validator: (basicUnit){
                    //         if(basicUnit==null||basicUnit==""){
                    //           basicUnitValid=false;
                    //           return "* Bắt buộc";
                    //         }
                    //         basicUnitValid=true;
                    //         return null;
                    //       },
                    //       controller: basicUnitController,
                    //       decoration: InputDecoration(
                    //           hintText: "Đơn vị "
                    //       ),
                    //     ))
                    //   ],
                    // ),
                    Divider(),
                    Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          editing
                              ? TextButton(
                                  onPressed: !BkrmService()
                                          .currentUser!
                                          .roles
                                          .contains("purchasing")
                                      ? null
                                      : () {
                                          editing = false;
                                          clearPage();
                                          setUpItemInfo();
                                          setState(() {});
                                        },
                                  child: Container(
                                    decoration: BoxDecoration(
                                      color: Colors.blue,
                                      borderRadius: BorderRadius.circular(5.0),
                                    ),
                                    padding: EdgeInsets.all(10.0),
                                    child: Text(
                                      "Hủy",
                                      style: TextStyle(
                                          fontSize: 18, color: Colors.white),
                                    ),
                                  ),
                                )
                              : Container(),
                          TextButton(
                            onPressed: !BkrmService()
                                    .currentUser!
                                    .roles
                                    .contains("purchasing")
                                ? null
                                : BkrmService().networkAvailable
                                    ? () async {
                                        if (editing) {
                                          _formKey.currentState!.validate();
                                          if (amountValid &&
                                              nameValid &&
                                              sellPriceValid &&
                                              purchasePriceValid) {
                                            showDialog(
                                                context: context,
                                                builder: (context) {
                                                  return AlertDialog(
                                                    title:
                                                        Text("Đang xử lý ..."),
                                                    content: SizedBox(
                                                        height: 50,
                                                        width: 50,
                                                        child: Center(
                                                            child:
                                                                CircularProgressIndicator())),
                                                  );
                                                });
                                            int? categoryId;
                                            for (CategoryInfo element
                                                in categories!) {
                                              if (element.name ==
                                                  chosenCategory) {
                                                categoryId = element.id;
                                                break;
                                              }
                                            }
                                            int? sellPrice = int.tryParse(
                                                sellPriceController.value.text
                                                    .replaceAll(",", ""));
                                            // int capital = int.tryParse(capitalController.value.text.replaceAll(",", ""));
                                            int amount = int.tryParse(
                                                    amountController.value.text
                                                        .replaceAll(",", "")
                                                        .replaceAll(".", "")) ??
                                                widget.dataItem.quantity;
                                            int pointRatio = int.tryParse(
                                                    pointRatioController
                                                        .value.text) ??
                                                (widget.dataItem.pointRatio *
                                                        100)
                                                    .toInt();
                                            int purchasePrice = int.tryParse(
                                                    purchasepriceController
                                                        .value.text
                                                        .replaceAll(",", "")
                                                        .replaceAll(".", "")) ??
                                                widget.dataItem.purchasePrice;
                                            // int shelf = int.tryParse(shelfController.value.text);
                                            MsgInfoCode? returnStatus =
                                                await BkrmService().editProduct(
                                                    itemId:
                                                        widget.dataItem.itemId,
                                                    categoryId: categoryId!,
                                                    itemName: nameController
                                                        .value.text,
                                                    barCode: barCodeController
                                                                .value.text ==
                                                            ""
                                                        ? null
                                                        : barCodeController
                                                            .value.text,
                                                    quantity: amount,
                                                    sellValue: sellPrice!,
                                                    deleted: false,
                                                    pointRatio:
                                                        (pointRatio / 100)
                                                            .toDouble(),
                                                    imageFile: imageFile,
                                                    purchasePrice:
                                                        purchasePrice);
                                            if (returnStatus ==
                                                MsgInfoCode.actionSuccess) {
                                              widget.dataItem.categoryId =
                                                  categoryId;
                                              widget.dataItem.pointRatio =
                                                  pointRatio / 100;
                                              widget.dataItem.quantity = amount;
                                              widget.dataItem.itemName =
                                                  nameController.value.text;
                                              widget.dataItem.barCode =
                                                  barCodeController.value.text;
                                              widget.dataItem.sellPrice =
                                                  sellPrice;
                                              Future.delayed(
                                                  Duration(microseconds: 1),
                                                  () {
                                                for (var cart
                                                    in BkrmService().listCart) {
                                                  for (var cartItem
                                                      in cart.cartItems) {
                                                    if (cartItem.item.itemId ==
                                                        widget
                                                            .dataItem.itemId) {
                                                      cart.checkCartValid();
                                                      cart.calculateAllValueInCart();
                                                    }
                                                  }
                                                }
                                              });
                                              Navigator.pop(context);
                                              showDialog(
                                                  context: context,
                                                  builder: (context) {
                                                    return AlertDialog(
                                                      title: Text(
                                                          "Đã chỉnh sửa sản phẩm thành công."),
                                                      actions: [
                                                        FlatButton(
                                                          onPressed: () {
                                                            Navigator.pop(
                                                                context);
                                                            editing = false;
                                                            edited = true;
                                                            this.setState(
                                                                () {});
                                                          },
                                                          child: Text("Đóng"),
                                                        )
                                                      ],
                                                    );
                                                  });
                                            } else {
                                              showDialog(
                                                  context: context,
                                                  builder: (context) {
                                                    return AlertDialog(
                                                      title: Text(
                                                          "Chỉnh sửa sản phẩm thất bại"),
                                                      actions: [
                                                        FlatButton(
                                                          onPressed: () {
                                                            Navigator.pop(
                                                                context);
                                                          },
                                                          child: Text("Đóng"),
                                                        )
                                                      ],
                                                    );
                                                  });
                                            }
                                          }
                                        } else {
                                          editing = true;
                                          setState(() {});
                                        }
                                      }
                                    : null,
                            child: Container(
                              padding: EdgeInsets.all(10.0),
                              decoration: BoxDecoration(
                                  color: BkrmService().networkAvailable &&
                                          BkrmService()
                                              .currentUser!
                                              .roles
                                              .contains("purchasing")
                                      ? Colors.blue
                                      : Colors.grey,
                                  borderRadius: BorderRadius.circular(5.0)),
                              child: Text(
                                editing ? "Xác nhận" : "Chỉnh sửa",
                                style: TextStyle(
                                    color: Colors.white, fontSize: 18),
                              ),
                            ),
                          ),
                          TextButton(
                            onPressed:
                                !BkrmService()
                                        .currentUser!
                                        .roles
                                        .contains("purchasing")
                                    ? null
                                    : BkrmService().networkAvailable
                                        ? () {
                                            showDialog(
                                                context: context,
                                                builder: (context) {
                                                  return AlertDialog(
                                                    title: Text(
                                                        "Bạn có chắc chắn muốn xoá sản phẩm này?"),
                                                    actions: [
                                                      FlatButton(
                                                          onPressed: () {
                                                            Navigator.pop(
                                                                context);
                                                          },
                                                          child: Text("Huỷ")),
                                                      FlatButton(
                                                          onPressed: () async {
                                                            Navigator.pop(
                                                                context);
                                                            showDialog(
                                                                context: this
                                                                    .context,
                                                                builder:
                                                                    (context) {
                                                                  return AlertDialog(
                                                                    content:
                                                                        Container(
                                                                      height:
                                                                          60,
                                                                      child:
                                                                          Center(
                                                                        child:
                                                                            CircularProgressIndicator(),
                                                                      ),
                                                                    ),
                                                                  );
                                                                });
                                                            MsgInfoCode?
                                                                returnCode =
                                                                await BkrmService()
                                                                    .deleteItem(widget
                                                                        .dataItem
                                                                        .itemId);
                                                            if (returnCode ==
                                                                MsgInfoCode
                                                                    .actionSuccess) {
                                                              showDialog(
                                                                  context: this
                                                                      .context,
                                                                  builder:
                                                                      (context) {
                                                                    return AlertDialog(
                                                                      title: Text(
                                                                          "Xoá sản phẩm thành công."),
                                                                      actions: [
                                                                        FlatButton(
                                                                            onPressed:
                                                                                () {
                                                                              edited = true;
                                                                              Navigator.pop(context);
                                                                              Navigator.pop(context);
                                                                              Navigator.pop(context, edited);
                                                                            },
                                                                            child:
                                                                                Text("Đóng"))
                                                                      ],
                                                                    );
                                                                  });
                                                            } else {
                                                              showDialog(
                                                                  context:
                                                                      context,
                                                                  builder:
                                                                      (context) {
                                                                    return AlertDialog(
                                                                      title: Text(
                                                                          "Xoá sản phẩm thất bại"),
                                                                      actions: [
                                                                        FlatButton(
                                                                            onPressed:
                                                                                () {
                                                                              Navigator.pop(context);
                                                                            },
                                                                            child:
                                                                                Text("Đóng"))
                                                                      ],
                                                                    );
                                                                  });
                                                            }
                                                          },
                                                          child: Text("Đồng ý"))
                                                    ],
                                                  );
                                                });
                                          }
                                        : null,
                            child: Container(
                                padding: EdgeInsets.all(10.0),
                                decoration: BoxDecoration(
                                    color: BkrmService().networkAvailable &&
                                            BkrmService()
                                                .currentUser!
                                                .roles
                                                .contains("purchasing")
                                        ? Colors.blue
                                        : Colors.grey,
                                    borderRadius: BorderRadius.circular(5.0)),
                                child: Text(
                                  "Xoá sản phẩm",
                                  style: TextStyle(
                                      fontSize: 18, color: Colors.white),
                                )),
                          )
                        ]),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        TextButton(
                            onPressed: !BkrmService()
                                .currentUser!
                                .roles
                                .contains("purchasing")
                                ? null
                                : BkrmService().networkAvailable
                                ? ()async {
                              showDialog(
                                  context: context,
                                  builder: (context) {
                                    return AlertDialog(
                                      content: Container(
                                        height: 50,
                                        child: Center(
                                          child: CircularProgressIndicator(),
                                        ),
                                      ),
                                    );
                                  });
                              PriceHistory? priceHistory = await BkrmService()
                                  .getPriceHistory(item: widget.dataItem);
                              Navigator.pop(context);
                              if (priceHistory == null) {
                                showDialog(
                                    context: context,
                                    builder: (context) {
                                      return AlertDialog(
                                        title: Text("Đã có lỗi xảy ra"),
                                        actions: [
                                          TextButton(
                                            onPressed: () {
                                              Navigator.pop(context);
                                            },
                                            child: Text("Đóng"),
                                          )
                                        ],
                                      );
                                    });
                                return;
                              } else {
                                Navigator.push(context,
                                    PageTransition(child:PriceHistoryPage(priceHistory),type:pageTransitionType));
                              }
                            }:null,
                            child: Container(
                              padding: EdgeInsets.all(10.0),
                              decoration: BoxDecoration(
                                color:  BkrmService().networkAvailable &&
                                    BkrmService()
                                        .currentUser!
                                        .roles
                                        .contains("purchasing")
                                    ? Colors.blue
                                    : Colors.grey,
                                borderRadius: BorderRadius.circular(5.0),
                              ),
                              child: Text("Lịch sử giá",
                                  style: TextStyle(
                                      color: Colors.white, fontSize: 18)),
                            )),
                        TextButton(
                            onPressed: !BkrmService()
                                .currentUser!
                                .roles
                                .contains("purchasing")
                                ? null
                                : BkrmService().networkAvailable
                                ? () async{
                              showDialog(
                                  context: context,
                                  builder: (context) {
                                    return AlertDialog(
                                      content: Container(
                                        height: 50,
                                        child: Center(
                                          child: CircularProgressIndicator(),
                                        ),
                                      ),
                                    );
                                  });
                              QuantityHistory? quantityHistory = await BkrmService()
                                  .getQuantityHistory(item: widget.dataItem);
                              Navigator.pop(context);
                              if (quantityHistory == null) {
                                showDialog(
                                    context: context,
                                    builder: (context) {
                                      return AlertDialog(
                                        title: Text("Đã có lỗi xảy ra"),
                                        actions: [
                                          TextButton(
                                            onPressed: () {
                                              Navigator.pop(context);
                                            },
                                            child: Text("Đóng"),
                                          )
                                        ],
                                      );
                                    });
                                return;
                              } else {
                                Navigator.push(context,
                                    PageTransition(child: QuantityChangeHistoryPage(quantityHistory), type: pageTransitionType));
                              }
                            }:null,
                            child: Container(
                              padding: EdgeInsets.all(10.0),
                              decoration: BoxDecoration(
                                color:  BkrmService().networkAvailable &&
                                    BkrmService()
                                        .currentUser!
                                        .roles
                                        .contains("purchasing")
                                    ? Colors.blue
                                    : Colors.grey,
                                borderRadius: BorderRadius.circular(5.0),
                              ),
                              child: Text("Lịch sử kiểm kê",
                                  style: TextStyle(
                                      color: Colors.white, fontSize: 18)),
                            ))
                      ],
                    )
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
