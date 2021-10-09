import 'package:bkrm/pages/inventoryModule/item/addNewProductPage.dart';
import 'package:bkrm/pages/sellerModule/pos/posPage.dart';
import 'package:bkrm/pages/sellerModule/shoppingCart/scanBarcode.dart';
import 'package:bkrm/services/cartService.dart';
import 'package:bkrm/services/printer/invoice_printer.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/listCustomer.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';
import 'cartItemCard.dart';

class CartsDropdown extends StatefulWidget {
  Function callbackToSetState;
  CartsDropdown(this.callbackToSetState);

  @override
  _CartsDropdownState createState() => _CartsDropdownState();
}

class _CartsDropdownState extends State<CartsDropdown> {
  @override
  Widget build(BuildContext context) {
    return Container(
      child: PopupMenuButton(
          color: Colors.white,
          child: Icon(Icons.swap_horiz),
          initialValue: BkrmService().listCart.indexOf(BkrmService().cart!),
          itemBuilder: (context) {
            List<PopupMenuEntry<int>> listCart = [];
            listCart.addAll(BkrmService().listCart.map((cart) {
              return PopupMenuItem(
                  value: BkrmService().listCart.indexOf(cart),
                  child: Container(
                    width: 125,
                    child: Center(
                        child: Row(children: [
                      Expanded(
                        flex: 3,
                        child: Text(
                          "#" +
                              (BkrmService().listCart.indexOf(cart) + 1)
                                  .toString() +
                              " " +
                              (cart.customer == null
                                  ? "Khách hàng lẻ"
                                  : cart.customer!.name??cart.customer!.phoneNumber!),
                          style: BkrmService().cart == cart
                              ? TextStyle(
                                  color: Colors.blue,
                                  fontWeight: FontWeight.bold)
                              : TextStyle(
                                  color: Colors.grey,
                                  fontWeight: FontWeight.w300),
                          overflow: TextOverflow.clip,
                        ),
                      ),
                      Expanded(
                        flex: 1,
                        child: IconButton(
                            color: Colors.black,
                            icon: Icon(Icons.highlight_remove),
                            onPressed: () {
                              showDialog(
                                  context: context,
                                  builder: (context) {
                                    return AlertDialog(
                                      title: Text(
                                          "Bạn có chắc muốn xóa giỏ hàng này"),
                                      actions: [
                                        FlatButton(
                                            onPressed: () {
                                              Navigator.pop(context);
                                            },
                                            child: Text("Hủy")),
                                        FlatButton(
                                            onPressed: () {
                                              {
                                                if (BkrmService().cart ==
                                                    cart) {
                                                  debugPrint("Current cart");
                                                  BkrmService()
                                                      .listCart
                                                      .removeWhere((element) =>
                                                          element == cart);
                                                  if (BkrmService()
                                                      .listCart
                                                      .isEmpty) {
                                                    debugPrint(
                                                        "List cart is empty");
                                                    BkrmService()
                                                        .listCart
                                                        .add(CartService());
                                                    BkrmService().cart =
                                                        BkrmService()
                                                            .listCart
                                                            .first;
                                                    BkrmService().requestCart();
                                                  } else {
                                                    debugPrint(
                                                        "List cart is not empty");
                                                    BkrmService().cart =
                                                        BkrmService()
                                                            .listCart
                                                            .first;
                                                    BkrmService().requestCart();
                                                  }
                                                } else {
                                                  debugPrint(
                                                      "Not current cart");
                                                  BkrmService()
                                                      .listCart
                                                      .removeWhere((element) =>
                                                          element == cart);
                                                  BkrmService().requestCart();
                                                }
                                                Navigator.pop(context);
                                                Navigator.pop(context);
                                                widget.callbackToSetState();
                                              }
                                            },
                                            child: Text("Xác nhận")),
                                      ],
                                    );
                                  });
                            }),
                      )
                    ])),
                  ));
            }).toList());
            listCart.add(PopupMenuItem(
                value: -1,
                child: Container(
                  child: Row(
                    children: [
                      Icon(
                        Icons.add,
                        color: Colors.grey,
                      ),
                      Text(
                        "Tạo giỏ mới",
                        style: TextStyle(
                            color: Colors.grey, fontWeight: FontWeight.w300),
                      )
                    ],
                  ),
                )));
            return listCart;
          },
          onSelected: (int index) {
            if (index == -1) {
              CartService newCart = CartService();
              BkrmService().listCart.add(newCart);
              BkrmService().cart = newCart;
              BkrmService().requestCart();
              widget.callbackToSetState();
              return;
            }
            if (BkrmService().listCart.indexOf(BkrmService().cart!) == index) {
              return;
            } else {
              BkrmService().cart = BkrmService().listCart[index];
              BkrmService().requestCart();
              widget.callbackToSetState();
            }
          }),
    );
  }
}

class CashOutBottomSheet extends StatefulWidget {
  CashOutBottomSheet(this.scaffolKey);
  GlobalKey<ScaffoldState> scaffolKey;
  @override
  _CashOutBottomSheetState createState() => _CashOutBottomSheetState();
}

class _CashOutBottomSheetState extends State<CashOutBottomSheet> {
  double moneyReceive = 0;
  @override
  Widget build(BuildContext context) {
    return Container(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Divider(
            thickness: 2.0,
          ),
          Padding(
            padding: const EdgeInsets.all(4.0),
            child: Row(
              children: [
                Expanded(
                    flex: 1,
                    child: Container(
                      alignment: Alignment.centerLeft,
                      child: Text(
                        "Điểm tích luỹ :",
                        style:
                            TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
                    )),
                Expanded(
                  flex: 1,
                  child: Container(
                    alignment: Alignment.center,
                    child: Text(
                        (BkrmService().cart!.customer != null
                          ? BkrmService().cart!.customer!.customerPoint.toString()
                          : "0") + " điểm ",
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                  ),
                ),
                Expanded(
                    flex: 1,
                    child: Container(
                      alignment: Alignment.centerLeft,
                      child: Text(
                        "Sử dụng :",
                        style:
                            TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
                    )),
                Expanded(
                  flex: 1,
                  child: Container(
                    alignment: Alignment.centerRight,
                    child: Theme(
                      data: Theme.of(context).copyWith(
                        unselectedWidgetColor: BkrmService().cart!.customer!=null?Colors.blue:Colors.grey
                      ),
                      child: Checkbox(
                        onChanged: BkrmService().cart!.customer!=null
                            ? (value) {
                                setState(() {
                                  BkrmService().cart!.usedCustomerPoint =
                                      value ?? false;
                                  BkrmService()
                                      .cart!
                                      .useCustomerPoint(value ?? false);
                                  setState(() {});
                                });
                              }
                            : null,
                        value: BkrmService().cart!.usedCustomerPoint,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
          Divider(
            thickness: 1.0,
          ),
          Padding(
            padding: const EdgeInsets.all(8.0),
            child: Row(
              children: [
                Expanded(
                    flex: 1,
                    child: Container(
                      alignment: Alignment.centerLeft,
                      child: Text("Khách hàng"),
                    )),
                Expanded(
                    flex: 2,
                    child: Row(children: [
                      Expanded(
                        flex: 4,
                        child: FlatButton(
                            splashColor: Colors.blueAccent,
                            color: Colors.grey,
                            onPressed: () async {
                              await showDialog(
                                  context: widget.scaffolKey.currentContext!,
                                  builder: (context) {
                                    return ListCustomers();
                                  });
                              setState(() {});
                            },
                            child: Text(BkrmService().cart!.customer == null
                                ? "Khách hàng lẻ"
                                : (BkrmService().cart!.customer!.name == null
                                    ? BkrmService().cart!.customer!.phoneNumber!
                                    : BkrmService().cart!.customer!.name!))),
                      ),
                      Expanded(
                          child: IconButton(
                        icon: Icon(
                          Icons.person_remove_rounded,
                          color: Colors.blue,
                        ),
                        onPressed: () {
                          BkrmService().cart!.customer = null;
                          setState(() {

                          });
                        },
                      ))
                    ]))
              ],
            ),
          ),
          Divider(
            thickness: 3,
          ),
          Padding(
            padding: EdgeInsets.all(8),
            child: Row(
              children: [
                Expanded(
                    flex: 1,
                    child: Container(
                      alignment: Alignment.centerLeft,
                      child: Text("Tổng tiền"),
                    )),
                Expanded(
                    flex: 1,
                    child: Container(
                      alignment: Alignment.centerRight,
                      child: Text(NumberFormat()
                              .format(BkrmService().cart!.totalPrice) +
                          " VNĐ"),
                    ))
              ],
            ),
          ),
          Divider(),
          Padding(
            padding: EdgeInsets.all(8),
            child: Row(
              children: [
                Expanded(
                    flex: 1,
                    child: Container(
                      alignment: Alignment.centerLeft,
                      child: Text("Giảm giá"),
                    )),
                Expanded(
                    flex: 1,
                    child: Container(
                      alignment: Alignment.centerRight,
                      child: Text(NumberFormat()
                              .format(BkrmService().cart!.totalDiscount) +
                          " VNĐ"),
                    ))
              ],
            ),
          ),
          Divider(
            thickness: 3,
          ),
          Padding(
            padding: EdgeInsets.all(8),
            child: Row(
              children: [
                Expanded(
                    flex: 1,
                    child: Container(
                      alignment: Alignment.centerLeft,
                      child: Text(
                        "Tổng tiền sau khi giảm giá ",
                        style: TextStyle(fontWeight: FontWeight.bold),
                      ),
                    )),
                Expanded(
                    flex: 1,
                    child: Container(
                      alignment: Alignment.centerRight,
                      child: Text(
                        NumberFormat().format(
                                BkrmService().cart!.totalDiscountPrice) +
                            " VNĐ",
                        style: TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ))
              ],
            ),
          ),
          Container(
            child: ElevatedButton(
              child: Container(
                alignment: Alignment.center,
                height: 50,
                width: MediaQuery.of(context).size.width,
                child: BkrmService().cart!.valid
                    ? Text(
                        "Thanh toán",
                        style: TextStyle(fontSize: 20, color: Colors.white),
                      )
                    : Center(
                        child: Text(
                        "Có sản phẩm không đủ số lượng trong kho",
                        style: TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.bold),
                      )),
              ),
              onPressed: BkrmService().cart!.valid
                  ? () async {
                      if (BkrmService().cart!.empty) {
                        showDialog(
                            context: widget.scaffolKey.currentContext!,
                            builder: (context) {
                              return AlertDialog(
                                title: Text("Bạn không có gì trong giỏ hàng!"),
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
                        await BkrmService().cart!.checkCartValid();
                        if (!BkrmService().cart!.valid) {
                          showDialog(
                              context: widget.scaffolKey.currentContext!,
                              builder: (context) {
                                return AlertDialog(
                                  title: Text("Có sản phẩm đã hết hàng"),
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
                          showDialog(
                              context: widget.scaffolKey.currentContext!,
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
                                        onPressed: () async {
                                          Navigator.pop(context);
                                          if (!BkrmService()
                                              .currentUser!
                                              .roles
                                              .contains("selling")) {
                                            showDialog(
                                                context: widget.scaffolKey.currentContext!,
                                                builder: (context) {
                                                  return AlertDialog(
                                                    title: Text(
                                                        "Tài khoản này không được cấp quyền để thực hiện hoạt động thanh toán!!"),
                                                    actions: [
                                                      FlatButton(
                                                          onPressed: () {
                                                            Navigator.pop(
                                                                context);
                                                          },
                                                          child: Text("Đóng")),
                                                    ],
                                                  );
                                                });
                                          } else {
                                            showDialog(
                                                context: widget.scaffolKey.currentContext!,
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
                                            Map<String, dynamic> returnStatus =
                                                await BkrmService()
                                                    .cart!
                                                    .sendInvoice(
                                                        moneyReceive -
                                                            BkrmService()
                                                                .cart!
                                                                .totalDiscountPrice,
                                                        moneyReceive);
                                            Navigator.pop(widget.scaffolKey.currentContext!);
                                            if (returnStatus["state"] ==
                                                MsgInfoCode.actionSuccess) {
                                              showDialog(
                                                  context: widget.scaffolKey.currentContext!,
                                                  barrierDismissible: false,
                                                  builder: (context) {
                                                    return WillPopScope(
                                                      onWillPop: () async {
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
                                                                  PageTransition(child: InvoicePrinter(
                                                                      returnStatus[
                                                                      "invoice"]),type: pageTransitionType));
                                                              // Navigator.push(context, MaterialPageRoute(builder: (context){
                                                              //   return PreviewPrinterPage(snapshot.data["invoice"]);
                                                              // }));
                                                            },
                                                            child: Text(
                                                                "In Hóa Đơn"),
                                                          ),
                                                          FlatButton(
                                                            onPressed: () {
                                                              Navigator.pop(
                                                                  context);
                                                              Navigator.pop(
                                                                  context);
                                                              BkrmService()
                                                                  .cart!
                                                                  .clearCart();
                                                              BkrmService()
                                                                  .listCart
                                                                  .removeWhere((element) =>
                                                                      element ==
                                                                      BkrmService()
                                                                          .cart);
                                                              BkrmService()
                                                                  .cart = null;
                                                              BkrmService()
                                                                  .requestCart();
                                                            },
                                                            child: Text(
                                                                "Hoàn thành"),
                                                          ),
                                                        ],
                                                      ),
                                                    );
                                                  });
                                            } else {
                                              showDialog(
                                                  context: widget.scaffolKey.currentContext!,
                                                  builder: (context) {
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
/*                    Navigator.push(
                        context,
                        MaterialPageRoute(
                            settings:
                            ModalRoute.of(context)!.settings,
                            builder: (context) {
                              return CashoutPage();
                            }));*/
                        }
                      }
                    }
                  : null,
            ),
          )
        ],
      ),
    );
  }
}

class ShoppingCart extends StatefulWidget {
  @override
  _ShoppingCartState createState() => _ShoppingCartState();
}

class _ShoppingCartState extends State<ShoppingCart> {
  BkrmService bkrmService = BkrmService();
  bool firstTimeCall = true;
  bool firstTimeCallPrice = true;
  NumberFormat formatter = NumberFormat();
  final GlobalKey<ScaffoldState> scaffoldKey=GlobalKey();
  remoteSetState() {
    setState(() {});
  }
  
  void _onItemTapped(int index) {

      if(index==0){
        Navigator.push(context, PageTransition(child: ScanBarcode(),type: pageTransitionType)).then((value) => remoteSetState());
      }else{
        Navigator.push(context, PageTransition(child: PosPage(title: "Hàng hóa",),type: pageTransitionType)).then((value) => remoteSetState());
      }
  }
  
  @override
  Widget build(BuildContext context) {
    ScaffoldMessenger.of(context).hideCurrentSnackBar();
    return Scaffold(
      resizeToAvoidBottomInset: true,
      key: scaffoldKey,
      appBar: AppBar(
        title: Text("Giỏ Hàng"),
        actions: [
          IconButton(icon: Icon(Icons.add), onPressed: BkrmService().currentUser!.roles.contains("purchasing")?(){
            Navigator.push(context, PageTransition(child: AddNewItemPage(afterCreated: (ItemInfo? item){
              if(item!=null){
                BkrmService().cart!.addCartItem(item, 1);
                BkrmService().requestCart();
                BkrmService().cart!.checkCartValid();
              }
            },),type: pageTransitionType));
          }:null),
          CartsDropdown(remoteSetState),
          IconButton(
              icon: Icon(
                Icons.delete,
                color: Colors.white,
              ),
              onPressed:() {
                      BkrmService().cart!.clearCart();
                      BkrmService().requestCart();
                      setState(() {});
                    }),
        ],
      ),
      drawer: ExpansionDrawer(context),
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
            stream: bkrmService.cartServiceStream,
            initialData: {},
            builder: (context, snapshot) {
              if (firstTimeCall) {
                bkrmService.requestCart();
                firstTimeCall = false;
              }
              debugPrint(snapshot.data.toString());
              Map<dynamic, dynamic> cartStreamMap =
                  snapshot.data as Map<dynamic, dynamic>;
              if (cartStreamMap.length == 0 ||
                  cartStreamMap["listCartItem"].isEmpty) {
                return Container(
                  child: Center(
                    child: Text(
                      "Không có gì trong giỏ hàng!!",
                      style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.w400),
                    ),
                  ),
                );
              } else {
                return Column(children: [
                  Container(
                    child: Expanded(
                        child: ListView.builder(
                            itemCount: cartStreamMap["listCartItem"].length,
                            itemBuilder: (context, index) {
                              return CartItemCard(
                                  cartStreamMap["listCartItem"][index]);
                            })),
                  ),
                  ElevatedButton(
                      onPressed: cartStreamMap["valid"]?() {
                        showModalBottomSheet(
                            context: scaffoldKey.currentContext!,
                            isScrollControlled: true,
                            builder: (context) {
                              return CashOutBottomSheet(scaffoldKey);
                            });
                      }:null,
                      child: Container(
                        alignment: Alignment.center,
                        height: 50,
                        width: MediaQuery.of(context).size.width,
                        child: Text(cartStreamMap["valid"]?"Tiến hành thanh toán":"Có sản phẩm không đủ số lượng",
                            style: TextStyle(fontSize: 20)),
                      ))
                ]);
              }
            }),
      ),
    );
  }
}
