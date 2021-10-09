import 'dart:io';
import 'dart:async';
import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/categoryInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/defaultItemInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/customerFormatter.dart';
import 'package:dio/dio.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_barcode_scanner/flutter_barcode_scanner.dart';
import 'package:image_picker/image_picker.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:path/path.dart';
import 'package:path_provider/path_provider.dart';

class AddNewItemPage extends StatefulWidget {
  DefaultItemInfo? defaultItem;
  String? barcode;
  Function(ItemInfo?)? afterCreated;
  bool? hideAmountField = false;
  bool? hidePurchasePriceField = false;
  AddNewItemPage(
      {DefaultItemInfo? defaultItem,
      String? barCode,
      this.afterCreated,
      bool? hideAmountField,
      bool? hidePurchasePriceField}) {
    if (defaultItem != null) {
      this.defaultItem = defaultItem;
    }
    if (barCode != null) {
      this.barcode = barCode;
    }
    if (hideAmountField != null) {
      this.hideAmountField = hideAmountField;
    }
    if (hidePurchasePriceField != null) {
      this.hidePurchasePriceField = hidePurchasePriceField;
    }
  }

  @override
  _AddNewItemPageState createState() => _AddNewItemPageState();
}

class _AddNewItemPageState extends State<AddNewItemPage> {
  final _formKey = GlobalKey<FormState>();

  TextEditingController nameController = TextEditingController();
  TextEditingController sellPriceController = TextEditingController();
  TextEditingController purchasePriceController = TextEditingController();
  // TextEditingController shelfController = TextEditingController();
  TextEditingController amountController = TextEditingController();
  TextEditingController barCodeController = TextEditingController();
  // TextEditingController leastStoredController = TextEditingController();
  // TextEditingController basicUnitController = TextEditingController();

  bool nameValid = false;
  bool sellPriceValid = false;
  bool purchasePriceValid = false;
  // bool shelfValid=false;
  bool amountValid = false;
  // bool basicUnitValid=false;

  bool needRefesh = false;

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

  setUpItemInfo(){
    if (widget.defaultItem != null) {
      amountValid = true;
    } else {
      if (widget.barcode != null) {
        amountValid = true;
        barCodeController.text = widget.barcode!;
      }
    }
    if (widget.hidePurchasePriceField!) {
      purchasePriceValid = true;
      purchasePriceController.text = "0";
    }
    if (widget.hideAmountField!) {
      amountValid = true;
      amountController.text = "0";
    }
  }

  Future<String?> getCategory() async {
    categories = await bkrmService.getCategory();
    setState(() {
      chosenCategory = categories!.first.name;
    });
    if (widget.defaultItem != null) {
      await initDefaultItem();
    }
    return chosenCategory;
  }

  Future<void> initDefaultItem() async {
    imageFile = await _fileFromImageUrl(widget.defaultItem!.imageUrl!);
    nameController.text = widget.defaultItem!.itemName!;
    barCodeController.text = widget.defaultItem!.barCode!;
    for (CategoryInfo category in categories!) {
      debugPrint("category id " + category.id.toString());
      debugPrint("category name " + category.name);
      if (category.name.toLowerCase() ==
          widget.defaultItem!.categoryName!.toLowerCase()) {
        chosenCategory = category.name;
        break;
      }
    }
    setState(() {});
  }

  Future<File> _fileFromImageUrl(String imageUrl) async {
    final response = await Dio().get<List<int>>(
        ServerConfig.ipMergedDb + imageUrl,
        options: Options(responseType: ResponseType.bytes));
    final documentDirectory = await getApplicationDocumentsDirectory();
    String name = imageUrl.split("/").last;
    final file = File(join(documentDirectory.path, name));
    file.writeAsBytesSync(response.data!);
    return file;
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        FocusScope.of(context).requestFocus(FocusNode());
      },
      child: WillPopScope(
        onWillPop: () async {
          Navigator.pop(context, needRefesh);
          return needRefesh;
        },
        child: Scaffold(
          appBar: AppBar(
            title: Text("Thêm sản phẩm mới"),
          ),
          body: SingleChildScrollView(
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
                        child: imageFile == null
                            ? Image.asset(
                                "asset/productImage/no-image.jpg",
                                height: 150,
                                width: 150,
                              )
                            : Image.file(
                                imageFile!,
                                height: 150,
                                width: 150,
                              ),
                      ),
                    ),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        IconButton(
                            icon: Icon(
                              Icons.folder,
                              color: Colors.blueAccent,
                            ),
                            onPressed: () async {
                              PickedFile? image = await picker.getImage(
                                  source: ImageSource.gallery,
                                  maxWidth: 300,
                                  maxHeight: 300);
                              setState(() {
                                imageFile = File(image!.path);
                              });
                            }),
                        IconButton(
                          icon: Icon(
                            Icons.camera_alt,
                            color: Colors.blueAccent,
                          ),
                          onPressed: () async {
                            PickedFile? image = await picker.getImage(
                                source: ImageSource.camera,
                                maxHeight: 300,
                                maxWidth: 300);
                            setState(() {
                              imageFile = File(image!.path);
                            });
                          },
                        )
                      ],
                    ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("Tên sản phẩm : ")),
                        Expanded(
                            flex: 2,
                            child: TextFormField(
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
                              decoration:
                                  InputDecoration(hintText: "Tên sản phẩm"),
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
                                  height: 3,
                                  color: Colors.blueAccent,
                                ),
                                onChanged: (String? newValue) {
                                  setState(() {
                                    chosenCategory = newValue;
                                  });
                                },
                                items: categories == null
                                    ? [
                                        DropdownMenuItem(
                                            value: "Loading...",
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
                                  return "Giá bán phải là số dương";
                                }
                                sellPriceValid = true;
                                return null;
                              },
                              controller: sellPriceController,
                              keyboardType: TextInputType.number,
                              decoration: InputDecoration(hintText: "Đơn giá"),
                            ))
                      ],
                    ),
                    widget.hideAmountField!
                        ? Container()
                        : Row(
                            children: [
                              Expanded(
                                  flex: 1, child: Text("Số lượng hàng : ")),
                              Expanded(
                                  flex: 2,
                                  child: TextFormField(
                                    inputFormatters: [
                                      CustomerFormatter().numberFormatter
                                    ],
                                    autovalidateMode:
                                        AutovalidateMode.onUserInteraction,
                                    validator: (amount) {
                                      if (amount == "" || amount == null) {
                                        amountValid = false;
                                        return " * Băt buộc";
                                      }
                                      if ((int.tryParse(amount) ?? -1) < 0) {
                                        amountValid = false;
                                        return " Số lượng hàng phải là số nguyên dương";
                                      }
                                      amountValid = true;
                                      return null;
                                    },
                                    controller: amountController,
                                    keyboardType: TextInputType.number,
                                    decoration: InputDecoration(
                                        hintText: "Số lượng hàng"),
                                  )),
                            ],
                          ),
                    !widget.hidePurchasePriceField!
                        ? Row(
                            children: [
                              Expanded(flex: 1, child: Text("Vốn : ")),
                              Expanded(
                                  flex: 2,
                                  child: TextFormField(
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
                                      if (int.tryParse(
                                              capital.replaceAll(",", "")) ==
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
                                    controller: purchasePriceController,
                                    keyboardType: TextInputType.number,
                                    decoration: InputDecoration(
                                        hintText: "Vốn",
                                        enabledBorder: UnderlineInputBorder(
                                            borderSide: BorderSide(
                                                color: Colors.blue))),
                                  )),
                            ],
                          )
                        : Container(),
                    Row(
                      children: [
                        Expanded(flex: 2, child: Text("Mã vạch : ")),
                        Expanded(
                            flex: 3,
                            child: TextFormField(
                              autovalidateMode: AutovalidateMode.always,
                              validator: (barcode) {
                                if (barcode == null || barcode == "") {
                                  return "* Tạo tự động nếu trống";
                                }
                                return null;
                              },
                              controller: barCodeController,
                              decoration: InputDecoration(
                                  hintText: "Mã vạch",
                                  errorStyle: TextStyle(color: Colors.grey)),
                            )),
                        Expanded(
                          flex: 1,
                          child: IconButton(
                              icon: Icon(Icons.qr_code),
                              onPressed: () async {
                                var status = await Permission.camera.status;
                                if (status.isPermanentlyDenied ||
                                    status.isRestricted ||
                                    status.isDenied) {
                                  await Permission.camera.request();
                                }
                                var resultBarCode =
                                    await FlutterBarcodeScanner.scanBarcode(
                                        "#ffffff",
                                        "Hủy",
                                        true,
                                        ScanMode.DEFAULT);
                                if (resultBarCode == "-1") {
                                  return;
                                }
                                List<DefaultItemInfo> resultItem =
                                    await (bkrmService.searchItemInDefaultDb(
                                        barCode: resultBarCode));
                                if (resultItem.length == 0) {
                                  setState(() {
                                    barCodeController.text = resultBarCode;
                                  });
                                } else {
                                  showDialog(context: context, builder: (context){
                                    return AlertDialog(
                                      content: Container(
                                        height: 50,
                                        child: Center(
                                          child: CircularProgressIndicator(),
                                        ),
                                      ),
                                    );
                                  });
                                  widget.defaultItem = resultItem.first;
                                  setUpItemInfo();
                                  await getCategory().then((value){
                                    Navigator.pop(context);
                                    setState(() {

                                    });
                                  });
                                }
                              }),
                        )
                      ],
                    ),
                    Divider(),
                    RaisedButton(
                      onPressed: () async {
                        _formKey.currentState!.validate();
                        // debugPrint("basicUnitValid");
                        // debugPrint(basicUnitValid.toString());
                        debugPrint("amountValid");
                        debugPrint(amountValid.toString());
                        // debugPrint("sehlfValid");
                        // debugPrint(shelfValid.toString());
                        debugPrint("nameValid");
                        debugPrint(nameValid.toString());
                        debugPrint("sellPriceValid");
                        debugPrint(sellPriceValid.toString());
                        debugPrint("capitalvalid");
                        debugPrint(purchasePriceValid.toString());
                        if (amountValid &&
                            nameValid &&
                            sellPriceValid &&
                            purchasePriceValid) {
                          showDialog(
                              context: context,
                              builder: (context) {
                                return AlertDialog(
                                  title: Text("Đang xử lý ..."),
                                  content: SizedBox(
                                      height: 50,
                                      width: 50,
                                      child: Center(
                                          child: CircularProgressIndicator())),
                                );
                              });
                          int? categoryId;
                          for (CategoryInfo element in categories!) {
                            if (element.name == chosenCategory) {
                              categoryId = element.id;
                              break;
                            }
                          }
                          if (categoryId == null) {
                            categoryId =
                                categories != null ? categories!.first.id : 0;
                          }
                          int sellPrice = int.tryParse(sellPriceController
                                  .value.text
                                  .replaceAll(",", "")) ??
                              0;
                          int amount =
                              int.tryParse(amountController.value.text) ?? 0;
                          int purchasePrice = int.tryParse(
                                  purchasePriceController.value.text
                                      .replaceAll(",", "")
                                      .replaceAll(".", "")) ??
                              0;
                          MsgInfoCode? returnStatus;
                          if (widget.afterCreated != null) {
                            returnStatus = await BkrmService().createNewProduct(
                                categoryId: categoryId!,
                                itemName: nameController.value.text,
                                barCode: barCodeController.value.text == ""
                                    ? null
                                    : barCodeController.value.text,
                                quantity: amount,
                                sellValue: sellPrice,
                                imageFile: imageFile,
                                purchasePrice: purchasePrice,
                                processReturnProduct: widget.afterCreated);
                          } else {
                            returnStatus = await BkrmService().createNewProduct(
                                categoryId: categoryId!,
                                itemName: nameController.value.text,
                                barCode: barCodeController.value.text == ""
                                    ? null
                                    : barCodeController.value.text,
                                quantity: amount,
                                sellValue: sellPrice,
                                imageFile: imageFile,
                                purchasePrice: purchasePrice,
                                processReturnProduct: widget.afterCreated);
                          }

                          if (returnStatus == MsgInfoCode.actionSuccess) {
                            if (widget.defaultItem != null ||
                                widget.barcode != null) {
                              ItemInfo tempItem =
                                  (await bkrmService.searchItemInBranch(
                                          barCode:
                                              barCodeController.value.text))
                                      .first;
                              bkrmService.importGood!.addToImport(tempItem);
                            }
                            Navigator.pop(context);
                            showDialog(
                                context: context,
                                builder: (context) {
                                  return AlertDialog(
                                    title: Text("Đã thêm sản phẩm thành công!"),
                                    actions: [
                                      FlatButton(
                                        onPressed: () {
                                          needRefesh = true;
                                          Navigator.pop(context);
                                          Navigator.pop(context, needRefesh);
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
                                    title: Text("Thêm sản phẩm thất bại"),
                                    actions: [
                                      FlatButton(
                                        onPressed: () {
                                          Navigator.pop(context);
                                        },
                                        child: Text("Đóng"),
                                      )
                                    ],
                                  );
                                });
                          }
                        }
                      },
                      child: Text("Hoàn thành"),
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
