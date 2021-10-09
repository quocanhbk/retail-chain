import 'dart:io';
import 'package:bkrm/pages/sellerModule/invoice/refundPage.dart';
import 'package:bkrm/pages/sellerModule/refund/refundDetailPage.dart';
import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/info/inventoryInfo/categoryInfo.dart';
import 'package:bkrm/services/info/invoice/invoiceReceivedWhenGet.dart';
import 'package:bkrm/services/info/sellingInfo/invoicePagination.dart';
import 'package:bkrm/services/info/sellingInfo/refundInfo.dart';
import 'package:bkrm/services/printer/invoice_printer.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/customerFormatter.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';

class DialogRefundSheets extends StatefulWidget {
  List<RefundSheet>? refundSheets;

  DialogRefundSheets(this.refundSheets);

  @override
  _DialogRefundSheetsState createState() => _DialogRefundSheetsState();
}

class _DialogRefundSheetsState extends State<DialogRefundSheets> {
  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text("Danh sách đơn trả hàng"),
      content: Container(
        constraints: BoxConstraints(
            maxHeight: MediaQuery.of(context).size.height * 2 / 3),
        width: MediaQuery.of(context).size.width * 2 / 3,
        child: SingleChildScrollView(
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            Row(
              children: [
                Expanded(
                  flex: 1,
                  child: Text(
                    "Mã số",
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ),
                Expanded(
                    flex: 3,
                    child: Text(
                      "Tổng số tiền trả",
                      style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    ))
              ],
            ),
            ListView.builder(
                physics: NeverScrollableScrollPhysics(),
                shrinkWrap: true,
                itemCount: widget.refundSheets!.length,
                itemBuilder: (context, index) {
                  return InkWell(
                    onTap: () async {
                      DetailRefundSheet? detailRefundSheet = await BkrmService()
                          .getDetailRefundSheet(widget.refundSheets![index]);
                      Navigator.pop(context);
                      Navigator.push(context,
                          PageTransition(child: RefundDetailPage(detailRefundSheet), type: pageTransitionType));
                    },
                    child: Card(
                      child: Container(
                        padding: EdgeInsets.all(8.0),
                        height: 50,
                        child: Row(
                          children: [
                            Expanded(
                                flex: 1,
                                child: Text("#" +
                                    widget.refundSheets![index].refundSheetId
                                        .toString())),
                            Expanded(
                                flex: 3,
                                child: Text(NumberFormat().format(widget
                                    .refundSheets![index].totalRefundPrice)))
                          ],
                        ),
                      ),
                    ),
                  );
                }),
          ]),
        ),
      ),
      actions: [
        FlatButton(
            onPressed: () {
              Navigator.pop(context);
            },
            child: Text("Đóng"))
      ],
    );
  }
}

class ListSoldItem extends StatelessWidget {
  List<Item> purchasedItems;
  NumberFormat formatter = NumberFormat();
  ListSoldItem(this.purchasedItems);

  List<Widget> buildColumn() {
    List<Widget> listItems = [];
    purchasedItems.forEach((Item item) {
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
                              (context, url,downloadProgress) =>
                                  SizedBox(width:20,height:20,child: Center(child: CircularProgressIndicator(value:downloadProgress.progress))),
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
                    Text("Giá bán :" + formatter.format(item.sellPrice)),
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

class InvoiceDetailPage extends StatefulWidget {
  DetailInvoiceInfo? invoice;
  InvoiceDetailPage(this.invoice, this.refundSheets);
  List<RefundSheet>? refundSheets;
  @override
  _InvoiceDetailPageState createState() => _InvoiceDetailPageState();
}

class _InvoiceDetailPageState extends State<InvoiceDetailPage> {
  final _formKey = GlobalKey<FormState>();

  bool needRefresh = false;

  TextEditingController customerPhoneNumberController = TextEditingController();
  TextEditingController totalPurchasePriceController = TextEditingController();
  TextEditingController discountController = TextEditingController();
  TextEditingController createdDateTimeController = TextEditingController();
  TextEditingController customerNameController = TextEditingController();
  TextEditingController sellerNameController = TextEditingController();

  bool nameValid = false;
  bool sellPriceValid = false;
  // bool capitalValid=false;
  // bool shelfValid=false;
  bool amountValid = false;
  // bool basicUnitValid=false;

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
    setTextField();
  }

  setTextField() {
    customerNameController.text =
        widget.invoice!.invoiceInfo.customerName == null
            ? "Khách hàng lẻ"
            : widget.invoice!.invoiceInfo.customerName!;
    debugPrint("Total sell price");
    debugPrint(formatter.format(widget.invoice!.invoiceInfo.totalSellPrice));
    totalPurchasePriceController.text =
        formatter.format(widget.invoice!.invoiceInfo.totalSellPrice) + " VNĐ";
    discountController.text =
        formatter.format(widget.invoice!.invoiceInfo.discount) + " VNĐ";
    createdDateTimeController.text = DateFormat("dd-MM-yyyy HH:mm:ss")
        .format(widget.invoice!.invoiceInfo.createdDatetime!);
    customerPhoneNumberController.text =
        widget.invoice!.invoiceInfo.customerPhone == null
            ? "Không có"
            : widget.invoice!.invoiceInfo.customerPhone!;
    sellerNameController.text = widget.invoice!.invoiceInfo.sellerName!;
    setState(() {});
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
          title: Text("Thông tin chi tiết hóa đơn"),
        ),
        body: WillPopScope(
          onWillPop: () async {
            Navigator.pop(context, needRefresh);
            return needRefresh;
          },
          child: SingleChildScrollView(
            child: Container(
              padding: EdgeInsets.all(8.0),
              child: Form(
                key: _formKey,
                child: Column(
                  children: [
                    SizedBox(
                      height: 30,
                    ),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [Center(
                        child: Text(
                          "Hóa đơn #" +
                              widget.invoice!.invoiceInfo.invoiceId.toString(),
                          style: TextStyle(
                              fontWeight: FontWeight.bold, fontSize: 24),
                        ),
                      ),                    IconButton(icon: Icon(Icons.print), onPressed: (){
                        Navigator.push(
                            context,
                            PageTransition(child: InvoicePrinter(
                                widget.invoice!.toInvoiceWhenCreated()),type: pageTransitionType));
                      }),]
                    ),
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
                        Expanded(flex: 1, child: Text("Tên người bán : ")),
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
                              decoration:
                                  InputDecoration(hintText: "Tên người bán"),
                            ))
                      ],
                    ),
                    Row(
                      children: [
                        Expanded(
                            flex: 1,
                            child: Text("Số điện thoại khách hàng : ")),
                        Expanded(
                            flex: 2,
                            child: TextFormField(
                              keyboardType: TextInputType.phone,
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
                        Expanded(flex: 1, child: Text("Tổng tiền : ")),
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
                              decoration:
                                  InputDecoration(hintText: "Tổng tiền"),
                            ))
                      ],
                    ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("Giảm giá : ")),
                        Expanded(
                            flex: 2,
                            child: TextFormField(
                              enabled: isEdited,
                              controller: discountController,
                              validator: (disocunt) {
                                if (disocunt == null || disocunt == "") {
                                  nameValid = false;
                                  return " * Bắt buộc";
                                } else {
                                  nameValid = true;
                                  return null;
                                }
                              },
                              decoration: InputDecoration(hintText: "Giảm giá"),
                            ))
                      ],
                    ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("Ngày bán : ")),
                        Expanded(
                            flex: 2,
                            child: TextFormField(
                              enabled: isEdited,
                              controller: createdDateTimeController,
                              validator: (deliveryDate) {
                                if (deliveryDate == null ||
                                    deliveryDate == "") {
                                  nameValid = false;
                                  return " * Bắt buộc";
                                } else {
                                  nameValid = true;
                                  return null;
                                }
                              },
                              decoration: InputDecoration(hintText: "Ngày bán"),
                            ))
                      ],
                    ),
                    SizedBox(
                      height: 20,
                    ),
                    (widget.refundSheets != null &&
                            widget.refundSheets!.isNotEmpty)
                        ? Center(
                            child: FlatButton(
                              color: Colors.blue,
                              child:
                                  Text("Danh sách đơn trả hàng đã thực hiện"),
                              onPressed: () {
                                showDialog(
                                    context: context,
                                    builder: (context) {
                                      return DialogRefundSheets(
                                          widget.refundSheets);
                                    });
                              },
                            ),
                          )
                        : Container(),
                    SizedBox(
                      height: 20,
                    ),
                    Center(
                      child: Text(
                        "Danh sách sản phẩm trong hóa đơn",
                        style: TextStyle(
                            fontWeight: FontWeight.w500, fontSize: 18),
                      ),
                    ),
                    SizedBox(
                      height: 10,
                    ),
                    ListSoldItem(widget.invoice!.items),
                    SizedBox(
                      height: 30,
                    ),
                    Center(
                        child: Container(
                      height: 50,
                      width: 200,
                      child: RaisedButton(
                        color: Colors.blue,
                        child: Text(
                          "Trả Hàng",
                          style: TextStyle(
                              fontSize: 24, fontWeight: FontWeight.bold),
                        ),
                        onPressed: () {
                          Navigator.push(context,
                              PageTransition(child: RefundPage(widget.invoice), type: pageTransitionType)).then((value) async {
                            debugPrint("Return value");
                            debugPrint(value.toString());
                            if (value) {
                              showDialog(
                                  context: context,
                                  builder: (context) {
                                    return Material(
                                      type: MaterialType.transparency,
                                      child: Container(
                                        height: 250,
                                        child: Center(
                                          child: Column(
                                              mainAxisAlignment:
                                                  MainAxisAlignment.center,
                                              children: [
                                                Center(
                                                    child:
                                                        CircularProgressIndicator()),
                                                Container(
                                                  height: 30,
                                                ),
                                                Text(
                                                  "Đang xử lý. Xin vui lòng đợi !",
                                                  style: TextStyle(
                                                      fontWeight:
                                                          FontWeight.bold,
                                                      fontSize: 16,
                                                      color: Colors.grey),
                                                )
                                              ]),
                                        ),
                                      ),
                                    );
                                  });
                              InvoicePagination? invoice = await BkrmService()
                                  .getInvoices(
                                      invoiceId:
                                          widget.invoice!.invoiceInfo.invoiceId,
                                      order: "desc",
                                      orderBy: "created_datetime",
                                      page: 1);
                              if(invoice==null){
                                needRefresh=true;
                                Navigator.pop(context);
                                Navigator.pop(context,needRefresh);
                              }else{
                                BkrmService()
                                    .getDetailInvoice(invoice.invoices.first)
                                    .then((value) {
                                  widget.invoice = value;
                                  Navigator.pop(context);
                                  this.setState(() {
                                    needRefresh = true;
                                    setTextField();
                                  });
                                });
                              }
                            }
                          });
                        },
                      ),
                    )),
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
