import 'dart:io';
import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/info/inventoryInfo/returnPurchasedSheetInfo.dart';
import '../../../services/info/inventoryInfo/categoryInfo.dart';
import '../../../services/info/sellingInfo/refundInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/customerFormatter.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';

class ListReturnPurchasedItems extends StatelessWidget {
  List<ReturnItemInfo> refundItems;
  NumberFormat formatter = NumberFormat();
  ListReturnPurchasedItems(this.refundItems);

  List<Widget> buildColumn() {
    List<Widget> listItems = [];
    refundItems.forEach((ReturnItemInfo item) {
      listItems.add(Container(
        padding: EdgeInsets.all(4.0),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Expanded(
                flex: 2,
                child: Padding(
                  padding: EdgeInsets.fromLTRB(0.0, 0.0, 4.0, 0.0),
                  child: item.imageUrl != null&&item.imageUrl!="null"
                      ? CachedNetworkImage(
                          imageUrl: ServerConfig.projectUrl + item.imageUrl!,
                          progressIndicatorBuilder:
                              (context, url, downloadProgress) =>
                                  Container(child: SizedBox(width:20,height: 20,child: Center(child: CircularProgressIndicator(value: downloadProgress.progress,)))),
                          errorWidget: (context, url, error) =>
                              Icon(Icons.error),
                        )
                      : Image.asset("asset/productImage/no-image.jpg"),
                )),
            Expanded(flex: 4, child: Text(item.name)),
            Expanded(
                flex: 4,
                child: Column(
                  children: [
                    Text("S??? l?????ng :" + item.oldQuantity.toString()),
                    Text("Gi?? b??n :" + formatter.format(item.oldPurchasedPrice)),
                  ],
                ))
          ],
        ),
      ));
      listItems.add(Divider(
        thickness: 2,
      ));
    });
    return listItems;
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(border: Border.all()),
      child: Column(
        children: buildColumn(),
      ),
    );
  }
}

class ReturnPurchasedSheetDetail extends StatefulWidget {
  DetailReturnPurchasedSheetInfo? returnPurchasedSheet;
  ReturnPurchasedSheetDetail(this.returnPurchasedSheet);
  @override
  _ReturnPurchasedSheetDetailState createState() => _ReturnPurchasedSheetDetailState();
}

class _ReturnPurchasedSheetDetailState extends State<ReturnPurchasedSheetDetail> {
  final _formKey = GlobalKey<FormState>();

  TextEditingController customerPhoneNumberController = TextEditingController();
  TextEditingController totalPurchasePriceController = TextEditingController();
  TextEditingController createdDateTimeController = TextEditingController();
  TextEditingController customerNameController = TextEditingController();
  TextEditingController sellerNameController = TextEditingController();

  bool nameValid = false;
  bool sellPriceValid = false;
  bool amountValid = false;

  bool isEdited = false;

  bool storable = true;
  final ImagePicker picker = ImagePicker();
  File? imageFile;
  List<CategoryInfo>? categories;
  BkrmService bkrmService = BkrmService();
  String chosenCategory = "Loading...";
  NumberFormat formatter = NumberFormat();
  @override
  void initState() {
    super.initState();
setUpCustomerInfo();
  }

  setUpCustomerInfo(){
    customerNameController.text =
    widget.returnPurchasedSheet!.returnPurchasedSheet.supplierName == null
        ? "Nh?? cung c???p l???"
        : widget.returnPurchasedSheet!.returnPurchasedSheet.supplierName!;
    totalPurchasePriceController.text =
        formatter.format(widget.returnPurchasedSheet!.returnPurchasedSheet.totalReturnMoney) +
            " VN??";
    createdDateTimeController.text = DateFormat("dd-MM-yyyy HH:mm:ss")
        .format(widget.returnPurchasedSheet!.returnPurchasedSheet.createdDateTime);
    customerPhoneNumberController.text =
    widget.returnPurchasedSheet!.returnPurchasedSheet.returnerName == null
        ? "Kh??ng c??"
        : (widget.returnPurchasedSheet!.returnPurchasedSheet.returnerName=="null"?"Kh??ng c??":widget.returnPurchasedSheet!.returnPurchasedSheet.returnerName);
    sellerNameController.text = widget.returnPurchasedSheet!.returnPurchasedSheet.returnerName;
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
          title: Text("Th??ng tin chi ti???t ????n tr???"),
        ),
        body: SingleChildScrollView(
          child: Container(
            padding: EdgeInsets.all(8.0),
            child: Form(
              key: _formKey,
              child: Column(
                children: [
                  SizedBox(
                    height: 30,
                  ),
                  Column(children: [
                    Center(
                      child: Text(
                        "????n tr??? h??ng nh???p #" +
                            widget.returnPurchasedSheet!.returnPurchasedSheet.returnPurchasedSheetId
                                .toString(),
                        style: TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 24),
                      ),
                    ),
                    Center(
                      child: Text(
                        "(Tr??? h??ng cho ????n nh???p #" +
                            widget.returnPurchasedSheet!.returnPurchasedSheet.purchasedSheetId
                                .toString() +
                            ")",
                        style: TextStyle(
                            fontWeight: FontWeight.w400, fontSize: 18),
                      ),
                    ),
                  ]),
                  SizedBox(
                    height: 30,
                  ),
                  Row(
                    children: [
                      Expanded(flex: 1, child: Text("T??n nh?? cung c???p : ")),
                      Expanded(
                          flex: 2,
                          child: TextFormField(
                            enabled: isEdited,
                            controller: customerNameController,
                            validator: (name) {
                              if (name == null || name == "") {
                                nameValid = false;
                                return " * B???t bu???c";
                              } else {
                                nameValid = true;
                                return null;
                              }
                            },
                            decoration:
                                InputDecoration(hintText: "T??n nh?? cung c???p"),
                          ))
                    ],
                  ),
                  Row(
                    children: [
                      Expanded(flex: 1, child: Text("T??n ng?????i th???c hi???n : ")),
                      Expanded(
                          flex: 2,
                          child: TextFormField(
                            enabled: isEdited,
                            controller: sellerNameController,
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
                                hintText: "T??n ng?????i th???c hi???n"),
                          ))
                    ],
                  ),
                  Row(
                    children: [
                      Expanded(flex: 1, child: Text("T???ng ti???n tr???: ")),
                      Expanded(
                          flex: 2,
                          child: TextFormField(
                            inputFormatters: [
                              CustomerFormatter().currencyFormatter
                            ],
                            enabled: isEdited,
                            controller: totalPurchasePriceController,
                            validator: (totalPrice) {
                              if (totalPrice == null || totalPrice == "") {
                                nameValid = false;
                                return " * B???t bu???c";
                              } else {
                                nameValid = true;
                                return null;
                              }
                            },
                            decoration: InputDecoration(hintText: "T???ng ti???n"),
                          ))
                    ],
                  ),
                  Row(
                    children: [
                      Expanded(flex: 1, child: Text("Ng??y tr??? : ")),
                      Expanded(
                          flex: 2,
                          child: TextFormField(
                            enabled: isEdited,
                            controller: createdDateTimeController,
                            validator: (deliveryDate) {
                              if (deliveryDate == null || deliveryDate == "") {
                                nameValid = false;
                                return " * B???t bu???c";
                              } else {
                                nameValid = true;
                                return null;
                              }
                            },
                            decoration: InputDecoration(hintText: "Ng??y tr???"),
                          ))
                    ],
                  ),
                  SizedBox(
                    height: 20,
                  ),
                  Center(
                    child: Text(
                      "Danh s??ch s???n ph???m ???????c tr???",
                      style:
                          TextStyle(fontWeight: FontWeight.w500, fontSize: 18),
                    ),
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  ListReturnPurchasedItems(widget.returnPurchasedSheet!.returnItems),
                  SizedBox(
                    height: 30,
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
