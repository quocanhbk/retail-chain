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

class ListRefundItems extends StatelessWidget {
  List<RefundItem> returnItems;
  NumberFormat formatter = NumberFormat();
  ListRefundItems(this.returnItems);

  List<Widget> buildColumn() {
    List<Widget> listItems = [];
    returnItems.forEach((RefundItem item) {
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
                    Text("Số lượng :" + item.quantity.toString()),
                    Text("Giá nhập :" + formatter.format(item.sellPrice)),
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

class RefundDetailPage extends StatefulWidget {
  DetailRefundSheet? refundSheet;
  RefundDetailPage(this.refundSheet);
  @override
  _RefundDetailPageState createState() => _RefundDetailPageState();
}

class _RefundDetailPageState extends State<RefundDetailPage> {
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
    widget.refundSheet!.refundInfo.customerName == null
        ? "Khách hàng lẻ"
        : widget.refundSheet!.refundInfo.customerName!;
    totalPurchasePriceController.text =
        formatter.format(widget.refundSheet!.refundInfo.totalRefundPrice) +
            " VNĐ";
    createdDateTimeController.text = DateFormat("dd-MM-yyyy HH:mm:ss")
        .format(widget.refundSheet!.refundInfo.createdDatetime!);
    customerPhoneNumberController.text =
    widget.refundSheet!.refundInfo.customerPhone == null
        ? "Không có"
        : (widget.refundSheet!.refundInfo.customerPhone=="null"?"Không có":widget.refundSheet!.refundInfo.customerPhone!);
    sellerNameController.text = widget.refundSheet!.refundInfo.refunderName!;
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
          title: Text("Thông tin chi tiết đơn trả hàng nhập"),
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
                        "Đơn trả hàng #" +
                            widget.refundSheet!.refundInfo.refundSheetId
                                .toString(),
                        style: TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 24),
                      ),
                    ),
                    Center(
                      child: Text(
                        "(Trả hàng cho hóa đơn #" +
                            widget.refundSheet!.refundInfo.invoiceId
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
                      Expanded(flex: 1, child: Text("Tên khách hàng : ")),
                      Expanded(
                          flex: 2,
                          child: TextFormField(
                            enabled: isEdited,
                            controller: customerNameController,
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
                                InputDecoration(hintText: "Tên khách hàng"),
                          ))
                    ],
                  ),
                  Row(
                    children: [
                      Expanded(flex: 1, child: Text("Tên người thực hiện : ")),
                      Expanded(
                          flex: 2,
                          child: TextFormField(
                            enabled: isEdited,
                            controller: sellerNameController,
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
                                hintText: "Tên người thực hiện"),
                          ))
                    ],
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1, child: Text("Số điện thoại khách hàng : ")),
                      Expanded(
                          flex: 2,
                          child: TextFormField(
                            enabled: isEdited,
                            controller: customerPhoneNumberController,
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
                                hintText: "Số điện thoại khách hàng"),
                          ))
                    ],
                  ),
                  Row(
                    children: [
                      Expanded(flex: 1, child: Text("Tổng tiền trả: ")),
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
                                return " * Bắt buộc";
                              } else {
                                nameValid = true;
                                return null;
                              }
                            },
                            decoration: InputDecoration(hintText: "Tổng tiền"),
                          ))
                    ],
                  ),
                  Row(
                    children: [
                      Expanded(flex: 1, child: Text("Ngày trả : ")),
                      Expanded(
                          flex: 2,
                          child: TextFormField(
                            enabled: isEdited,
                            controller: createdDateTimeController,
                            validator: (deliveryDate) {
                              if (deliveryDate == null || deliveryDate == "") {
                                nameValid = false;
                                return " * Bắt buộc";
                              } else {
                                nameValid = true;
                                return null;
                              }
                            },
                            decoration: InputDecoration(hintText: "Ngày trả"),
                          ))
                    ],
                  ),
                  SizedBox(
                    height: 20,
                  ),
                  Center(
                    child: Text(
                      "Danh sách sản phẩm được trả",
                      style:
                          TextStyle(fontWeight: FontWeight.w500, fontSize: 18),
                    ),
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  ListRefundItems(widget.refundSheet!.refundItems),
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
