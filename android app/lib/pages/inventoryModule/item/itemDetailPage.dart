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
          title: Text("Th??ng tin chi ti???t s???n ph???m"),
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
                        Expanded(flex: 1, child: Text("T??n s???n ph???m : ")),
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
                                  return " * B???t bu???c";
                                } else {
                                  nameValid = true;
                                  return null;
                                }
                              },
                              decoration: InputDecoration(
                                  hintText: "T??n s???n ph???m",
                                  enabledBorder: UnderlineInputBorder(
                                      borderSide:
                                          BorderSide(color: Colors.blue))),
                            ))
                      ],
                    ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("Danh m???c : ")),
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
                        Expanded(flex: 1, child: Text("????n gi?? : ")),
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
                                  return " *B???t bu???c";
                                }
                                if (int.tryParse(price.replaceAll(",", "")) ==
                                    null) {
                                  sellPriceValid = false;
                                  return " * S??? nh???p v??o kh??ng h???p l???";
                                }
                                if (int.tryParse(price.replaceAll(",", ""))! <
                                    0) {
                                  sellPriceValid = false;
                                  return "Gi?? b??n ph???i l?? s??? nguy??n d????ng";
                                }
                                sellPriceValid = true;
                                return null;
                              },
                              controller: sellPriceController,
                              keyboardType: TextInputType.number,
                              decoration: InputDecoration(
                                  hintText: "????n gi??",
                                  enabledBorder: UnderlineInputBorder(
                                      borderSide:
                                          BorderSide(color: Colors.blue))),
                            ))
                      ],
                    ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("V???n : ")),
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
                                  return " *B???t bu???c";
                                }
                                if (int.tryParse(capital.replaceAll(",", "")) ==
                                    null) {
                                  purchasePriceValid = false;
                                  return " * S??? nh???p v??o kh??ng h???p l???";
                                }
                                if ((int.tryParse(capital
                                            .replaceAll(",", "")
                                            .replaceAll(".", "")) ??
                                        -1) <
                                    0) {
                                  purchasePriceValid = false;
                                  return "Gi?? b??n ph???i l?? s??? d????ng";
                                }
                                purchasePriceValid = true;
                                return null;
                              },
                              controller: purchasepriceController,
                              keyboardType: TextInputType.number,
                              decoration: InputDecoration(
                                  hintText: "V???n",
                                  enabledBorder: UnderlineInputBorder(
                                      borderSide:
                                          BorderSide(color: Colors.blue))),
                            )),
                      ],
                    ),
                    // Row(
                    //   children: [
                    //     Expanded(flex: 1,
                    //         child: Text("M?? s??? k??? : ")),
                    //     Expanded(flex: 2,child: TextFormField(
                    //       enabled: isEdited,
                    //       autovalidateMode: AutovalidateMode.onUserInteraction,
                    //       validator: (shelf){
                    //         if(shelf==null||shelf==""){
                    //           shelfValid=false;
                    //           return " *B???t bu???c";
                    //         }
                    //         shelfValid=true;
                    //         return null;
                    //       },
                    //       controller: shelfController,
                    //       keyboardType: TextInputType.number,
                    //       decoration: InputDecoration(
                    //           hintText: "M?? s??? k???"
                    //       ),
                    //     )),
                    //   ],
                    // ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("S??? l?????ng h??ng : ")),
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
                                            title: Text("Ki???m k?? l?????ng h??ng"),
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
                                                            child: Text("L?? do:")),
                                                        Expanded(
                                                            flex: 3,
                                                            child: TextFormField(
                                                              controller:
                                                              reasonController,
                                                            ))
                                                      ],
                                                    ),
                                                    SizedBox(height: 10,),
                                                    Container(alignment:Alignment.centerLeft,child: Text("??i???u ch???nh:",style: TextStyle(fontWeight: FontWeight.bold),)),
                                                    Row(
                                                      children: [

                                                        Expanded(
                                                            flex: 1,
                                                            child: Text("T??ng")
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
                                                            child: Text("Gi???m")
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
                                                        child: Text("*B???t bu???c",style: TextStyle(fontSize: 12,color: Colors.red),)):Container(),
                                                    SizedBox(height: 10,),
                                                    Row(
                                                      children: [
                                                        Expanded(
                                                            flex: 1,
                                                            child: Text("S??? l?????ng:")),
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
                                                                  return "* B???t bu???c";
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
                                              }, child: Text("H???y")),
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
                                                        title: Text("Ki???m k?? th??nh c??ng."),
                                                        actions: [
                                                          TextButton(onPressed: (){
                                                            Navigator.pop(context);
                                                            this.setUpItemInfo();
                                                            this.setState(() {

                                                            });
                                                          }, child: Text("Ho??n th??nh"))
                                                        ],
                                                      );
                                                    });
                                                    return;
                                                  }else{
                                                    Navigator.pop(context);
                                                    Navigator.pop(context);
                                                    showDialog(context: context,builder: (context){
                                                      return AlertDialog(
                                                        title: Text("Ki???m k?? th???t b???i."),
                                                        actions: [
                                                          TextButton(onPressed: (){
                                                            Navigator.pop(context);
                                                          }, child: Text("????ng"))
                                                        ],
                                                      );
                                                    });
                                                    return;
                                                  }
                                                }
                                              }, child: Text("X??c nh???n"))
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
                                      return " * B??t bu???c";
                                    }
                                    if (int.tryParse(amount)! < 0) {
                                      amountValid = false;
                                      return " S??? l?????ng h??ng ph???i l?? s??? nguy??n d????ng";
                                    }
                                    amountValid = true;
                                    return null;
                                  },
                                  controller: amountController,
                                  keyboardType: TextInputType.number,
                                  decoration: InputDecoration(
                                      hintText: "S??? l?????ng h??ng",
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
                        Expanded(flex: 2, child: Text("M?? v???ch : ")),
                        Expanded(
                            flex: 4,
                            child: TextFormField(
                              enabled: editing,
                              controller: barCodeController,
                              decoration: InputDecoration(
                                  hintText: "M?? v???ch",
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
                                              .scanBarcode("#ffffff", "H???y",
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
                    //     Expanded(flex: 1, child: Text("L??u tr??? ???????c : ")),
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
                    //             Text("C??"),
                    //             Radio(
                    //               groupValue: storable,
                    //               value: false,
                    //               onChanged: (value) {
                    //                 setState(() {
                    //                   storable = value;
                    //                 });
                    //               },
                    //             ),
                    //             Text("Kh??ng"),
                    //           ],
                    //         ))
                    //   ],
                    // ),
                    Row(
                      children: [
                        Expanded(
                            flex: 1, child: Text("T??? l??? t??ch ??i???m (%) : ")),
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
                                  hintText: "T??? l??? t??ch ??i???m",
                                  enabledBorder: UnderlineInputBorder(
                                      borderSide:
                                          BorderSide(color: Colors.blue))),
                            ))
                      ],
                    ),
                    // Row(
                    //   children: [
                    //     Expanded(flex: 1,
                    //         child: Text("????n v??? : ")),
                    //     Expanded(flex: 2,child: TextFormField(
                    //       enabled: isEdited,
                    //       autovalidateMode: AutovalidateMode.onUserInteraction,
                    //       validator: (basicUnit){
                    //         if(basicUnit==null||basicUnit==""){
                    //           basicUnitValid=false;
                    //           return "* B???t bu???c";
                    //         }
                    //         basicUnitValid=true;
                    //         return null;
                    //       },
                    //       controller: basicUnitController,
                    //       decoration: InputDecoration(
                    //           hintText: "????n v??? "
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
                                      "H???y",
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
                                                        Text("??ang x??? l?? ..."),
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
                                                          "???? ch???nh s???a s???n ph???m th??nh c??ng."),
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
                                                          child: Text("????ng"),
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
                                                          "Ch???nh s???a s???n ph???m th???t b???i"),
                                                      actions: [
                                                        FlatButton(
                                                          onPressed: () {
                                                            Navigator.pop(
                                                                context);
                                                          },
                                                          child: Text("????ng"),
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
                                editing ? "X??c nh???n" : "Ch???nh s???a",
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
                                                        "B???n c?? ch???c ch???n mu???n xo?? s???n ph???m n??y?"),
                                                    actions: [
                                                      FlatButton(
                                                          onPressed: () {
                                                            Navigator.pop(
                                                                context);
                                                          },
                                                          child: Text("Hu???")),
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
                                                                          "Xo?? s???n ph???m th??nh c??ng."),
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
                                                                                Text("????ng"))
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
                                                                          "Xo?? s???n ph???m th???t b???i"),
                                                                      actions: [
                                                                        FlatButton(
                                                                            onPressed:
                                                                                () {
                                                                              Navigator.pop(context);
                                                                            },
                                                                            child:
                                                                                Text("????ng"))
                                                                      ],
                                                                    );
                                                                  });
                                                            }
                                                          },
                                                          child: Text("?????ng ??"))
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
                                  "Xo?? s???n ph???m",
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
                                        title: Text("???? c?? l???i x???y ra"),
                                        actions: [
                                          TextButton(
                                            onPressed: () {
                                              Navigator.pop(context);
                                            },
                                            child: Text("????ng"),
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
                              child: Text("L???ch s??? gi??",
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
                                        title: Text("???? c?? l???i x???y ra"),
                                        actions: [
                                          TextButton(
                                            onPressed: () {
                                              Navigator.pop(context);
                                            },
                                            child: Text("????ng"),
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
                              child: Text("L???ch s??? ki???m k??",
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
