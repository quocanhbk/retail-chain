import 'dart:io';
import 'package:bkrm/pages/inventoryModule/purchasedSheet/returnPurchasedPage.dart';
import 'package:bkrm/pages/inventoryModule/returnPurchasedSheet/returnPurchasedDetailPage.dart';
import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/info/inventoryInfo/purchasedSheetPagination.dart';
import 'package:bkrm/services/info/inventoryInfo/returnPurchasedSheetInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/categoryInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/purchasedSheetInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/customerFormatter.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';
class DialogReturnPurchasedSheet extends StatefulWidget {
  List<ReturnPurchasedSheetInfo>? refundSheets;

  DialogReturnPurchasedSheet(this.refundSheets);

  @override
  _DialogReturnPurchasedSheetState createState() => _DialogReturnPurchasedSheetState();
}

class _DialogReturnPurchasedSheetState extends State<DialogReturnPurchasedSheet> {
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
                      DetailReturnPurchasedSheetInfo? detailRefundSheet = await BkrmService()
                          .getDetailReturnPurchasedSheet(widget.refundSheets![index]);
                      Navigator.pop(context);
                      Navigator.push(context,
                          PageTransition(child: ReturnPurchasedSheetDetail(detailRefundSheet),type: pageTransitionType));
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
                                    widget.refundSheets![index].returnPurchasedSheetId
                                        .toString())),
                            Expanded(
                                flex: 3,
                                child: Text(NumberFormat().format(widget
                                    .refundSheets![index].totalReturnMoney)))
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

class ListPurchasedItem extends StatelessWidget {
  List<PurchasedItem> purchasedItems;

  ListPurchasedItem(this.purchasedItems);

  List<Widget> buildColumn() {
    List<Widget> listItems = [];
    purchasedItems.forEach((PurchasedItem item) {
      listItems.add(Card(
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              flex: 2,
              child: item.imageUrl != null&&item.imageUrl!="null"
                  ? CachedNetworkImage(
                      imageUrl: ServerConfig.projectUrl + item.imageUrl!,
                      progressIndicatorBuilder:
                          (context, url,downloadProgress) =>
                              SizedBox(width:20,height:20,child: Center(child: CircularProgressIndicator(value: downloadProgress.progress,))),
                      errorWidget: (context, url, error) => Icon(Icons.error),
                    )
                  : Image.asset("asset/productImage/no-image.jpg"),
            ),
            Expanded(flex: 4, child: Text(item.name)),
            Expanded(
                flex: 4,
                child: Column(
                  children: [
                    Text("Số lượng :" + item.quantity.toString()),
                    Text("Giá nhập :" + item.purchasePrice.toString()),
                  ],
                ))
          ],
        ),
      ));
    });
    return listItems;
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      child: Column(
        children: buildColumn(),
      ),
    );
  }
}

class PurchasedSheetDetailPage extends StatefulWidget {
  DetailPurchasedSheetInfo? purchasedSheetDetail;
  PurchasedSheetDetailPage(this.purchasedSheetDetail,this.returnPurchasedSheets);
  List<ReturnPurchasedSheetInfo>? returnPurchasedSheets;
  @override
  _PurchasedSheetDetailPageState createState() =>
      _PurchasedSheetDetailPageState();
}

class _PurchasedSheetDetailPageState extends State<PurchasedSheetDetailPage> {
  final _formKey = GlobalKey<FormState>();

  TextEditingController deliveryNameController = TextEditingController();
  TextEditingController totalPurchasePriceController = TextEditingController();
  TextEditingController discountController = TextEditingController();
  TextEditingController deliveryDatetimeController = TextEditingController();
  TextEditingController supplierNameController = TextEditingController();
  TextEditingController purchaserNameController = TextEditingController();

  bool isEdited = false;
  bool needRefresh=false;
  bool storable = true;

  final ImagePicker picker = ImagePicker();
  File? imageFile;
  List<CategoryInfo>? categories;
  BkrmService bkrmService = BkrmService();
  String chosenCategory = "Loading...";
  @override
  void initState() {
    super.initState();
    setPurchasedSheetInfo();
  }

  setPurchasedSheetInfo(){
    deliveryNameController.text =
    widget.purchasedSheetDetail!.importInvoiceInfo.deliverName == null
        ? "Không có"
        : widget.purchasedSheetDetail!.importInvoiceInfo.deliverName!;
    totalPurchasePriceController.text = widget
        .purchasedSheetDetail!.importInvoiceInfo.totalPurchasePrice
        .toString();
    discountController.text =
        widget.purchasedSheetDetail!.importInvoiceInfo.discount.toString();
    deliveryDatetimeController.text = DateFormat("dd-MM-yyyy HH:mm:ss")
        .format(widget.purchasedSheetDetail!.importInvoiceInfo.deliveryDate!);
    supplierNameController.text =
    widget.purchasedSheetDetail!.importInvoiceInfo.supplierName == null||widget.purchasedSheetDetail!.importInvoiceInfo.supplierName=="null"
        ? "Nhà cung cấp lẻ"
        : widget.purchasedSheetDetail!.importInvoiceInfo.supplierName!;
    purchaserNameController.text =
    widget.purchasedSheetDetail!.importInvoiceInfo.purchaserName!;
  }

  void remoteSetState() {
    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: () async{
        Navigator.pop(context,needRefresh);
        return needRefresh;
      },
      child: GestureDetector(
        onTap: () {
          FocusScope.of(context).requestFocus(FocusNode());
        },
        child: Scaffold(
          appBar: AppBar(
            title: Text("Thông tin chi tiết đơn nhập hàng"),
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
                    Center(
                      child: Text(
                        "Đơn nhập hàng #" +
                            widget.purchasedSheetDetail!.importInvoiceInfo
                                .purchasedSheetId
                                .toString(),
                        style:
                            TextStyle(fontWeight: FontWeight.bold, fontSize: 24),
                      ),
                    ),
                    SizedBox(
                      height: 30,
                    ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("Tên nhà cung cấp : ")),
                        Expanded(
                            flex: 2,
                            child: TextFormField(
                              enabled: isEdited,
                              controller: supplierNameController,
                              decoration:
                                  InputDecoration(hintText: "Tên nhà cung cấp"),
                            ))
                      ],
                    ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("Tên người nhập : ")),
                        Expanded(
                            flex: 2,
                            child: TextFormField(
                              enabled: isEdited,
                              controller: purchaserNameController,
                              decoration:
                                  InputDecoration(hintText: "Tên người nhập"),
                            ))
                      ],
                    ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("Tên người giao : ")),
                        Expanded(
                            flex: 2,
                            child: TextFormField(
                              enabled: isEdited,
                              controller: deliveryNameController,
                              decoration:
                                  InputDecoration(hintText: "Tên người giao"),
                            ))
                      ],
                    ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("Tổng tiền đơn nhập : ")),
                        Expanded(
                            flex: 2,
                            child: TextFormField(
                              inputFormatters: [
                                CustomerFormatter().currencyFormatter
                              ],
                              enabled: isEdited,
                              controller: totalPurchasePriceController,
                              decoration:
                                  InputDecoration(hintText: "Tổng tiền nhập"),
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
                              decoration: InputDecoration(hintText: "Giảm giá"),
                            ))
                      ],
                    ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("Ngày nhập : ")),
                        Expanded(
                            flex: 2,
                            child: TextFormField(
                              enabled: isEdited,
                              controller: deliveryDatetimeController,
                              decoration: InputDecoration(hintText: "Ngày nhập"),
                            ))
                      ],
                    ),
                    SizedBox(
                      height: 20,
                    ),
                    Center(
                      child: Text(
                        "Danh sách sản phẩm nhập",
                        style:
                            TextStyle(fontWeight: FontWeight.w500, fontSize: 18),
                      ),
                    ),
                    SizedBox(
                      height: 10,
                    ),
                    Divider(),
                    (widget.returnPurchasedSheets != null &&
                        widget.returnPurchasedSheets!.isNotEmpty)
                        ? Center(
                      child: FlatButton(
                        color: Colors.blue,
                        child:
                        Text("Danh sách đơn trả hàng nhập đã thực hiện"),
                        onPressed: () {
                          showDialog(
                              context: context,
                              builder: (context) {
                                return DialogReturnPurchasedSheet(
                                    widget.returnPurchasedSheets);
                              });
                        },
                      ),
                    )
                        : Container(),
                    SizedBox(
                      height: 20,
                    ),
                    ListPurchasedItem(widget.purchasedSheetDetail!.purchasedItems),
                    SizedBox(height: 20,),
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
                                  PageTransition(child: ReturnPurchasedPage(widget.purchasedSheetDetail), type: pageTransitionType)).then((value) async {
                                debugPrint("Return value");
                                debugPrint(value.toString());
                                if (value) {
                                  needRefresh=true;
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
                                  PurchasedSheetPagination? purchasedSheet = await BkrmService()
                                      .getImportInvoices(
                                      purchasedSheetId:
                                      widget.purchasedSheetDetail!.importInvoiceInfo.purchasedSheetId,
                                      order: "desc",
                                      orderBy: "created_datetime",
                                      page: 1);
                                  if(purchasedSheet==null||purchasedSheet.purchasedSheets.isEmpty){
                                    needRefresh=true;
                                    Navigator.pop(context);
                                    Navigator.pop(context,needRefresh);
                                  }else{
                                    BkrmService()
                                        .getDetailPurchasedSheet(purchasedSheet.purchasedSheets.first)
                                        .then((value) {
                                      widget.purchasedSheetDetail = value;
                                      Navigator.pop(context);
                                      this.setState(() {
                                        needRefresh = true;
                                        setPurchasedSheetInfo();
                                      });
                                    });
                                  }
                                }
                              });
                            },
                          ),
                        ))
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
