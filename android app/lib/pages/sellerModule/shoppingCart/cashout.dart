import 'dart:async';

import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/cartService.dart';
import 'package:bkrm/services/printer/invoice_printer.dart';
import 'package:bkrm/services/services.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class CashoutPage extends StatefulWidget {
  @override
  _CashoutPageState createState() => _CashoutPageState();
}

class _CashoutPageState extends State<CashoutPage> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();

  BkrmService bkrmService = BkrmService();
  NumberFormat formatter = NumberFormat();
  TextEditingController controller = TextEditingController();
  double moneyReceive = 0;
  bool? useCustomerPoint = false;
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      key: _scaffoldKey,
      appBar: AppBar(
        title: Text("Thanh Toán"),
        actions: [
          // IconButton(
          //     icon: Icon(Icons.print),
          //     onPressed: () {
          //       Navigator.push(context, MaterialPageRoute(builder: (context) {
          //         return PreviewPrinterPage();
          //       }));
          //     })
        ],
      ),
      body: Column(
        children: [
          Expanded(
              child: ListView.builder(
                  itemCount: bkrmService.cart!.cartItems.length,
                  itemBuilder: (context, index) {
                    CartItem cartItem = bkrmService.cart!.cartItems[index];
                    return Padding(
                      padding: const EdgeInsets.all(8.0),
                      child: Container(
                        child: Row(
                          children: [
                            Expanded(
                                flex: 2,
                                child: cartItem.item.imageUrl == null
                                    ? Image.asset(
                                        "asset/productImage/no-image.jpg")
                                    : CachedNetworkImage(
                                  imageUrl:ServerConfig.projectUrl +
                                        cartItem.item.imageUrl!,        progressIndicatorBuilder: (context, url,downloadProgress) =>
                                    SizedBox(width:20,height:20,child: Center(child: CircularProgressIndicator(value: downloadProgress.progress,))),
                                  errorWidget: (context, url, error) => Icon(Icons.error),)),
                            Expanded(
                                flex: 5,
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      cartItem.item.itemName!,
                                      style: TextStyle(
                                          fontWeight: FontWeight.bold),
                                    ),
                                    Text("Số lượng : " +
                                        cartItem.amount.toString())
                                  ],
                                )),
                            Expanded(
                                flex: 3,
                                child: cartItem.discountPrice !=
                                        cartItem.item.sellPrice
                                    ? Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.end,
                                        children: [
                                          Text(formatter
                                                  .format(
                                                      cartItem.discountPrice *
                                                          cartItem.amount)
                                                  .toString() +
                                              " VNĐ"),
                                          Text(
                                            formatter
                                                    .format(cartItem
                                                            .item.sellPrice *
                                                        cartItem.amount)
                                                    .toString() +
                                                " VNĐ",
                                            style: TextStyle(
                                              decoration:
                                                  TextDecoration.lineThrough,
                                            ),
                                          ),
                                        ],
                                      )
                                    : Container(
                                  alignment: Alignment.centerRight,
                                      child: Text(formatter
                                          .format(cartItem.item.sellPrice *
                                              cartItem.amount)
                                          .toString()+" VNĐ"),
                                    ))
                          ],
                        ),
                      ),
                    );
                  })),
          Container(
            padding: EdgeInsets.all(8),
            height: BkrmService().cart!.customer != null ? 205 : 140,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                BkrmService().cart!.customer != null
                    ? Divider(
                        thickness: 3,
                      )
                    : Container(),
                BkrmService().cart!.customer != null
                    ? Row(
                        children: [
                          Expanded(
                              flex: 1,
                              child: Container(
                                alignment: Alignment.centerLeft,
                                child: Text(
                                  "Điểm tích luỹ :",
                                  style: TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold),
                                ),
                              )),
                          Expanded(
                            flex: 1,
                            child: Container(
                              alignment: Alignment.center,
                              child: Text(
                                BkrmService()
                                        .cart!
                                        .customer!
                                        .customerPoint
                                        .toString() +
                                    " điểm ",
                                style: TextStyle(
                                    fontSize: 16, fontWeight: FontWeight.bold),
                              ),
                            ),
                          ),
                          Expanded(
                              flex: 1,
                              child: Container(
                                alignment: Alignment.centerLeft,
                                child: Text(
                                  "Sử dụng :",
                                  style: TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold),
                                ),
                              )),
                          Expanded(
                            flex: 1,
                            child: Container(
                              alignment: Alignment.centerRight,
                              child: Checkbox(
                                onChanged: (value) {
                                  setState(() {
                                    useCustomerPoint = value;
                                    bkrmService.cart!.useCustomerPoint(
                                        this.useCustomerPoint);
                                    setState(() {});
                                  });
                                },
                                value: useCustomerPoint,
                              ),
                            ),
                          ),
                        ],
                      )
                    : Container(),
                Divider(
                  thickness: 3,
                ),
                Row(
                  children: [
                    Expanded(
                        flex: 1,
                        child: Container(
                          alignment: Alignment.centerLeft,
                          child: Text(
                            "Khách hàng :",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.bold),
                          ),
                        )),
                    Expanded(
                      flex: 1,
                      child: Container(
                        alignment: Alignment.centerRight,
                        child: Text(
                          bkrmService.cart!.customer == null
                              ? "Khách hàng lẻ"
                              : (bkrmService.cart!.customer!.name == null
                                  ? bkrmService.cart!.customer!.phoneNumber!
                                  : bkrmService.cart!.customer!.name!),
                          style: TextStyle(
                              fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                      ),
                    )
                  ],
                ),
                Divider(
                  thickness: 3,
                ),
                Row(
                  children: [
                    Expanded(
                        flex: 1,
                        child: Container(
                          alignment: Alignment.centerLeft,
                          child: Text(
                            "Nhân viên thanh toán :",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.bold),
                          ),
                        )),
                    Expanded(
                      flex: 1,
                      child: Container(
                        alignment: Alignment.centerRight,
                        child: Text(
                          bkrmService.currentUser!.name,
                          style: TextStyle(
                              fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                      ),
                    )
                  ],
                ),
                Divider(
                  thickness: 2,
                ),
                Row(children: [
                  Expanded(
                    flex: 2,
                    child: Text(
                      "Tổng số tiền phải thanh toán : ",
                      style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                  ),
                  Expanded(
                    flex: 1,
                    child: Container(
                      alignment: Alignment.centerRight,
                      child: Text(
                        formatter
                            .format(bkrmService.cart!.totalDiscountPrice)
                            .toString(),
                        style: TextStyle(
                            fontSize: 16, fontWeight: FontWeight.bold),
                      ),
                    ),
                  )
                ]),
                Divider(
                  thickness: 2,
                ),
/*                Row(children: [
                  Expanded(
                    flex: 2,
                    child: Text(
                      "Số tiền nhận của khách :",
                      style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                  ),
                  Expanded(
                    flex: 1,
                    child: Container(
                      height: 30,
                      alignment: Alignment.centerRight,
                      child: TextField(
                        inputFormatters: [
                          CustomerFormatter().currencyFormatter(decimalDigits: 0,symbol:"")
                        ],
                        textAlign: TextAlign.end,
                        decoration: InputDecoration(hintText: "Nhập vào đây"),
                        controller: controller,
                        keyboardType: TextInputType.number,
                        onChanged: (value) {
                          if (value != "") {
                            value = value.replaceAll(",", "");
                            moneyReceive = double.tryParse(value);
                            cashOutStream.sink.add({"receiveMoney": value});
                          } else {
                            moneyReceive = 0;
                          }
                        },
                      ),
                    ),
                  )
                ]),
                Divider(
                  thickness: 2,
                ),*/
/*                StreamBuilder(
                    stream: cashOutStream.stream,
                    initialData: {},
                    builder: (context, snapshot) {
                      if (snapshot.data["receiveMoney"] != null) {
                        snapshot.data["receiveMoney"] = snapshot
                            .data["receiveMoney"]
                            .toString()
                            .replaceAll(",", "");
                      }
                      return Row(children: [
                        Expanded(
                          flex: 2,
                          child: Text(
                            "Số tiền thối lại :",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.bold),
                          ),
                        ),
                        Expanded(
                          flex: 1,
                          child: Container(
                            alignment: Alignment.centerRight,
                            child: Text(
                              snapshot.data["receiveMoney"] != null
                                  ? formatter
                                      .format(double.tryParse(
                                              snapshot.data["receiveMoney"]) -
                                          bkrmService.cart.totalDiscountPrice)
                                      .toString()
                                  : 0.toString(),
                              style: TextStyle(fontWeight: FontWeight.bold),
                            ),
                          ),
                        )
                      ]);
                    })*/
              ],
            ),
          ),
          Container(
            height: 50,
            width: MediaQuery.of(context).size.width,
            child: RaisedButton(
              color: Colors.blue,
              child: Text(
                "Thanh Toán",
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
              onPressed: () {
                showDialog(
                    context: _scaffoldKey.currentContext!,
                    builder: (context) {
                      return AlertDialog(
                        title: Text("Xác nhận thanh toán"),
                        actions: [
                          FlatButton(
                              onPressed: () {
                                Navigator.pop(context);
                              },
                              child: Text("Không")),
                          FlatButton(
                              onPressed: () async{
                                Navigator.pop(context);
                                if (!bkrmService.currentUser!.roles
                                    .contains("selling")) {
                                  showDialog(
                                      context: _scaffoldKey.currentContext!,
                                      builder: (context) {
                                        return AlertDialog(
                                          title: Text(
                                              "Tài khoản này không được cấp quyền để thực hiện hoạt động thanh toán!!"),
                                          actions: [
                                            FlatButton(
                                                onPressed: () {
                                                  Navigator.pop(context);
                                                },
                                                child: Text("Đóng")),
                                          ],
                                        );
                                      });
                                } else {
                                  showDialog(context: _scaffoldKey.currentContext!, builder: (context){
                                    return AlertDialog(
                                      title: Text("Đang xử lý ..."),
                                      content: SizedBox(
                                          height: 50,
                                          width: 50,
                                          child: Center(
                                              child:
                                              CircularProgressIndicator())),
                                    );
                                  });
                                  Map<String,dynamic> returnStatus = await bkrmService.cart!
                                      .sendInvoice(
                                          moneyReceive -
                                              bkrmService
                                                  .cart!.totalDiscountPrice,
                                          moneyReceive);
                                  Navigator.pop(_scaffoldKey.currentContext!);
                                  if(returnStatus["state"]==MsgInfoCode.actionSuccess){
                                    showDialog(context: _scaffoldKey.currentContext!, barrierDismissible: false,builder: (context){
                                      return WillPopScope(
                                        onWillPop: ()async{
                                          return false;
                                        },
                                        child: AlertDialog(
                                          title: Text(
                                              "Đã thanh toán thành công!"),
                                          actions: [
                                            FlatButton(
                                              onPressed: () {
                                                Navigator.push(
                                                    context,
                                                    MaterialPageRoute(
                                                        builder:
                                                            (context) {
                                                          return InvoicePrinter(
                                                              returnStatus["invoice"]);
                                                        }));
                                                // Navigator.push(context, MaterialPageRoute(builder: (context){
                                                //   return PreviewPrinterPage(snapshot.data["invoice"]);
                                                // }));
                                              },
                                              child:
                                              Text("In Hóa Đơn"),
                                            ),
                                            FlatButton(
                                              onPressed: () {
                                                Navigator.pop(
                                                    context);
                                                Navigator.pop(
                                                    context);
                                                BkrmService().cart!.clearCart();
                                                BkrmService().listCart.removeWhere((element) => element==BkrmService().cart);
                                                BkrmService().cart=null;
                                                BkrmService().requestCart();
                                              },
                                              child: Text("Hoàn thành"),
                                            ),
                                          ],
                                        ),
                                      );
                                    });
                                  }else{
                                    showDialog(context: _scaffoldKey.currentContext!, builder: (context){
                                      return AlertDialog(
                                        title: Text(
                                            "Thanh toán thất bại!!"),
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
                              },
                              child: Text("Đồng ý"))
                        ],
                      );
                      // }
                    });
              },
            ),
          )
        ],
      ),
    );
  }

}
